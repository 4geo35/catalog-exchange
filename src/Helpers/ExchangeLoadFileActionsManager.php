<?php

namespace GIS\CatalogExchange\Helpers;

use GIS\CatalogExchange\Facades\ExchangeProtocolActions;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Interfaces\CmlInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExchangeLoadFileActionsManager
{
    const TMP_FOLDER = "cml-files";
    const DISK = "local";

    protected string|null $originalFileName = null;
    protected string|null $fileType = null;

    public function getAvailableFileTypes(): array
    {
        return ["import.xml", "offers.xml", "yml.xml", "archive.zip",];
    }

    public function getFileTypeByName(string $name): string
    {
        if (str_contains($name, "yml")) { return "yml"; }
        if (str_contains($name, "import")) { return "import"; }
        if (str_contains($name, "offers")) { return "offers"; }
        if (str_contains($name, ".zip")) { return "zip"; }
        return "undefined";
    }

    public function getFileName(): ?string
    {
        $this->originalFileName = request()->input("filename");
        return $this->originalFileName;
    }

    public function modeLoadFile(CmlInterface $cml): string
    {
        $this->getFileName();
        if (! $this->originalFileName) { return ExchangeProtocolActions::failure("File name not found."); }

        $name = $this->fixFileName();
        $this->fileType = $this->getFileTypeByName($name);
        if ($this->fileType === "zip" && ! $cml->is_zip) { $cml->update(["is_zip" => now(),]); }

        $fileData = request()->getContent();
        if (empty($fileData)) { return ExchangeProtocolActions::failure("Input data is empty."); }

        $file = $cml->files()
            ->where("original_name", $this->originalFileName)
            ->first();

        try {
            // Если такой файл уже есть, дописать в него данные
            if ($file) {
                file_put_contents($file->full_path, $fileData, FILE_APPEND);
            } else {
                $uploadedFile = $this->storeFile($fileData);
                if (! $uploadedFile) { return ExchangeProtocolActions::failure("File upload failed."); }
                $cmlFile = $cml->files()->create([
                    "original_name" => $this->originalFileName,
                ]); /** @var CmlFileInterface $cmlFile */
                $cmlFile->storeUploadedFileDirectly($uploadedFile);
                $this->clearTmpFileByName($uploadedFile->getFilename());
            }
            return ExchangeProtocolActions::answer("success\n");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ExchangeProtocolActions::failure("Error while uploading file.");
        }
    }

    public function checkZipFiles(CmlInterface $cml): ?string
    {
        if (! $cml->is_zip) { return null; }

        foreach ($cml->files as $file) {
            $type = $file->type;
            if ($type !== "zip") { continue; }

            $result = $this->extractZipFile($file, $cml);
            if (! empty($result)) { return $result; }
            $file->delete();
        }

        $cml->update(["is_zip" => null]);
        return null;
    }

    protected function extractZipFile(CmlFileInterface $cmlFile, CmlInterface $cml): ?string
    {
        $path = $cmlFile->full_path;

        $zipObject = new ZipArchive();
        $open = $zipObject->open($path);

        if ($open !== true) {
            return ExchangeProtocolActions::failure("Error while opening zip file.");
        }
        if (! $zipObject->count()) {
            $zipObject->close();
            return ExchangeProtocolActions::failure("Zip file is empty.");
        }

        $dir = self::TMP_FOLDER . "/{$cmlFile->id}";
        if (! Storage::disk(self::DISK)->exists($dir)) {
            Storage::disk(self::DISK)->makeDirectory($dir);
        }
        $fullDirPath = Storage::disk(self::DISK)->path($dir);
        try {
            $zipObject->extractTo($fullDirPath);
        } catch (\Exception $e) {
            return ExchangeProtocolActions::failure("Error while extracting zip file.");
        } finally {
            $zipObject->close();
        }

        $files = Storage::disk(self::DISK)->files($dir);
        foreach ($files as $filePath) {
            $originalFileName = basename($filePath);
            $uploadedFile = new UploadedFile(
                Storage::disk(self::DISK)->path($filePath),
                $originalFileName,
                Storage::disk(self::DISK)->mimeType($filePath)
            );
            $cmlFile = $cml->files()->create([
                "original_name" => $originalFileName,
            ]); /** @var CmlFileInterface $cmlFile */
            $cmlFile->storeUploadedFileDirectly($uploadedFile);
            $this->clearTmpFileByName($filePath);
        }

        if (Storage::disk(self::DISK)->exists($dir)) {
            Storage::disk(self::DISK)->deleteDirectory($dir);
        }

        return null;
    }

    protected function storeFile($contents): ?UploadedFile
    {
        $tmpFileName = "tmp_cml_file_" . uniqid() . "_" . $this->originalFileName;

        try {
            Storage::disk(self::DISK)->put($tmpFileName, $contents);
            $mimeType = Storage::disk(self::DISK)->mimeType($tmpFileName);
            $tmpFilePath = Storage::disk(self::DISK)->path($tmpFileName);
            return new UploadedFile($tmpFilePath, $this->originalFileName, $mimeType);
        } catch (\Exception $e) {}

        $this->clearTmpFileByName($tmpFileName);
        return null;
    }

    protected function clearTmpFileByName(string $tmpFileName): void
    {
        if (Storage::disk(self::DISK)->exists($tmpFileName)) {
            Storage::disk(self::DISK)->delete($tmpFileName);
        }
    }

    protected function fixFileName(): string
    {
        // так сделано в 1cBitrix, наверное они что-то знают
        $filename = preg_replace("#^(/tmp/|upload/1c/webdata)#", "", $this->originalFileName);
        return trim(str_replace("\\", "/", trim($filename)), "/");
    }
}

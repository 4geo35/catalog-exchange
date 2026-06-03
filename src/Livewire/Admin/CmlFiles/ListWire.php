<?php

namespace GIS\CatalogExchange\Livewire\Admin\CmlFiles;

use GIS\CatalogExchange\Facades\ExchangeLoadFileActions;
use GIS\CatalogExchange\Facades\ExchangeParserActions;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Interfaces\CmlInterface;
use GIS\CatalogExchange\Models\CmlFile;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ListWire extends Component
{
    use WithFileUploads;

    #[Locked]
    public CmlInterface $cml;

    public array $typeList;

    public bool $displayDelete = false;
    public bool $displayRun = false;
    public bool $displayStop = false;
    public string|null $fileId = null;

    public TemporaryUploadedFile|null $cmlFile = null;
    public string $fileType = "";

    public function __construct()
    {
        $this->typeList = ExchangeLoadFileActions::getAvailableFileTypes();
    }

    public function rules(): array
    {
        return [
            "cmlFile" => ["required", "file"],
            "fileType" => ["nullable", Rule::in($this->typeList)],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            "cmlFile" => "Файл",
            "fileType" => "Тип файла",
        ];
    }

    public function render(): View
    {
        $files = $this->cml->files()
            ->with(["file"])
            ->orderBy('created_at', 'desc')
            ->get();

        return view("cexc::livewire.admin.cml-files.list-wire", compact("files"));
    }

    public function store(): void
    {
        if (! $this->checkAuth()) { return; }
        $this->validate();

        $fileName = empty($this->fileType) ? $this->cmlFile->getClientOriginalName() : $this->fileType;
        $cmlFile = $this->cml->files()->create([
            "original_name" => $fileName,
        ]);
        /**
         * @var CmlFileInterface $cmlFile
         */
        $cmlFile->livewireFile($this->cmlFile);

        $this->resetFields();
        session()->flash("cml-files-success", "Файл успешно добавлен");
    }

    public function closeDelete(): void
    {
        $this->resetFields();
        $this->displayDelete = false;
    }

    public function showDelete(string $modelId): void
    {
        $this->resetFields();
        $this->fileId = $modelId;
        $model = $this->findModel();
        if (! $model) { return; }
        if (! $this->checkAuth()) { return; }
        $this->displayDelete = true;
    }

    public function confirmDelete(): void
    {
        $model = $this->findModel();
        if (! $model) { return; }
        if (! $this->checkAuth()) { return; }

        try {
            $model->delete();
        } catch (\Exception $exception) {
            session()->flash("cml-files-error", "Ошибка при удалении файла");
            $this->closeDelete();
            return;
        }

        session()->flash("cml-files-success", "Файл успешно удален");
        $this->closeDelete();
    }

    public function closeRun(): void
    {
        $this->resetFields();
        $this->displayRun = false;
    }

    public function showRun(string $modelId): void
    {
        $this->resetFields();
        $this->fileId = $modelId;
        $model = $this->findModel();
        if (! $model) { return; }
        if (! $this->checkAuth()) { return; }
        if (! $this->canRun($model)) { return; }
        $this->displayRun = true;
    }

    public function confirmRun(): void
    {
        $model = $this->findModel();
        if (! $model) { return; }
        if (! $this->checkAuth()) { return; }
        if (! $this->canRun($model)) { return; }

        ExchangeParserActions::fireParseFile($model);

        session()->flash("cml-files-success", "Файл передан в очередь обработки");
        $this->closeRun();
    }

    public function closeStop(): void
    {
        $this->resetFields();
        $this->displayStop = false;
    }

    public function showStop(string $modelId): void
    {
        $this->resetFields();
        $this->fileId = $modelId;
        $model = $this->findModel();
        if (! $model) { return; }
        if (! $this->checkAuth()) { return; }
        if (! $this->canStop($model)) { return; }
        $this->displayStop = true;
    }

    public function confirmStop(): void
    {
        $model = $this->findModel();
        if (! $model) { return; }
        if (! $this->checkAuth()) { return; }
        if (! $this->canStop($model)) { return; }

        ExchangeParserActions::stopParseFile($model);

        session()->flash("cml-files-success", "Послан сигнал остановки обработки файла");
        $this->closeStop();
    }

    protected function findModel(): ?CmlFileInterface
    {
        $modelClass = config("catalog-exchange.customCmlFileModel") ?? CmlFile::class;
        $model = $modelClass::query()->find($this->fileId);
        if (! $model) {
            session()->flash("cml-files-error", "Файл не найден");
            $this->closeDelete();
            return null;
        }
        return $model;
    }
    protected function resetFields(): void
    {
        $this->reset("cmlFile", "fileType", "fileId");
    }

    protected function canRun(CmlFileInterface $file): bool
    {
        if ($file->can_run) { return true; }
        session()->flash("cml-files-error", "Файл обрабатывается");
        return false;
    }

    protected function canStop(CmlFileInterface $file): bool
    {
        if ($file->can_stop) { return true; }
        session()->flash("cml-files-error", "Файл не обрабатывается");
        return false;
    }

    protected function checkAuth(): bool
    {
        try {
            $this->authorize("update", $this->cml);
            return true;
        } catch (AuthorizationException $e) {
            session()->flash("cml-files-error", "Неавторизованное действие");
            $this->closeDelete();
            return false;
        }
    }
}

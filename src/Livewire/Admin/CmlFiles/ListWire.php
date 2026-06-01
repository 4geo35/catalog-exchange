<?php

namespace GIS\CatalogExchange\Livewire\Admin\CmlFiles;

use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\CatalogExchange\Interfaces\CmlInterface;
use GIS\CatalogExchange\Models\Cml;
use GIS\CatalogExchange\Models\CmlFile;
use Illuminate\Auth\Access\AuthorizationException;
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

    public bool $displayDelete = false;

    public TemporaryUploadedFile|null $cmlFile = null;

    public function rules(): array
    {
        return [
            "cmlFile" => ["required", "file"],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            "cmlFile" => "Файл",
        ];
    }

    public function render(): View
    {
        $files = $this->cml->files()->orderBy('created_at', 'desc')->get();
        return view("cexc::livewire.admin.cml-files.list-wire", compact("files"));
    }

    public function store(): void
    {
        if (! $this->checkAuth()) { return; }
        $this->validate();

        

        $this->resetFields();
        session()->flash("cml-files-success", "Файл успешно добавлен");
    }

    public function closeDelete(): void
    {
        $this->resetFields();
        $this->displayDelete = false;
    }

    protected function resetFields(): void
    {
        $this->reset("cmlFile");
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

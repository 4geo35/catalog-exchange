<?php

namespace GIS\CatalogExchange\Livewire\Admin\Cmls;

use GIS\CatalogExchange\Interfaces\CmlInterface;
use GIS\CatalogExchange\Models\Cml;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class IndexWire extends Component
{
    use WithPagination;

    public bool $displayData = false;
    public bool $displayDelete = false;

    public string|null $cmlId = null;

    public function render(): View
    {
        $modelClass = config('catalog-exchange.customCmlModel') ?? Cml::class;
        $query = $modelClass::query()
            ->with("files");
        $cmls = $query->orderBy("created_at", "desc")->paginate();
        return view("cexc::livewire.admin.cmls.index-wire", compact("cmls"));
    }

    public function closeData(): void
    {
        $this->resetFields();
        $this->displayData = false;
    }

    public function createNew(): void
    {
        $this->resetFields();
        if (! $this->checkAuth("create")) { return; }
        $modelClass = config('catalog-exchange.customCmlModel') ?? Cml::class;
        $modelClass::query()->create([]);
        session()->flash("success", "Обмен успешно добавлен");
        $this->resetPage();
    }

    public function closeDelete(): void
    {
        $this->resetFields();
        $this->displayDelete = false;
    }

    public function showDelete(string $modelId): void
    {
        $this->resetFields();
        $this->cmlId = $modelId;
        $model = $this->findModel();
        if (! $model) { return; }
        if (! $this->checkAuth("delete", $model)) { return; }
        $this->displayDelete = true;
    }

    public function confirmDelete(): void
    {
        $model = $this->findModel();
        if (! $model) { return; }
        if (! $this->checkAuth("delete", $model)) { return; }

        try {
            $model->delete();
        } catch (\Exception $exception) {
            session()->flash("error", "Ошибка при удалении обмена");
            $this->closeDelete();
            return;
        }

        session()->flash("success", "Обмен успешно удален");
        $this->closeDelete();
    }

    protected function findModel(): ?CmlInterface
    {
        $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
        $model = $modelClass::query()->find($this->cmlId);
        if (! $model) {
            session()->flash("error", "Обмен не найден");
            $this->closeData();
            $this->closeDelete();
            return null;
        }
        return $model;
    }

    protected function resetFields(): void
    {
        $this->reset();
    }

    protected function checkAuth(string $action, CmlInterface $modelObject = null): bool
    {
        try {
            $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
            $this->authorize($action, $modelObject ?? $modelClass);
            return true;
        } catch (AuthorizationException $e) {
            session()->flash("error", "Неавторизованное действие");
            $this->closeData();
            $this->closeDelete();
            return false;
        }
    }
}

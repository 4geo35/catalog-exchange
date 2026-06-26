<div class="flex justify-between mb-indent-half">
    <div class="flex flex-col space-y-indent-half md:flex-row md:space-x-indent-half md:space-y-0">
        <div class="flex items-center justify-start relative">
            <div class="flex items-center justify-center h-full px-indent-half border border-secondary rounded-l-base rounded-r-none bg-secondary/25">
                Дата:
            </div>
            <input type="date" aria-label="Дата от" placeholder="Дата от" class="form-control rounded-none -mx-px focus:z-10"
                   wire:model.live="searchDateFrom">
            <input type="date" aria-label="Дата до" placeholder="Дата до" class="form-control rounded-l-none focus:z-10"
                   wire:model.live="searchDateTo">
        </div>
        <button type="button" class="btn btn-outline-primary" wire:click="clearSearch">
            Очистить
        </button>
    </div>

    <div>
        <button type="button" class="btn btn-primary px-btn-x-ico lg:px-btn-x ml-indent-half"
                wire:loading.attr="disabled"
                wire:click="createNew">
            <x-tt::ico.circle-plus/>
            <span class="hidden lg:inline-block pl-btn-ico-text">Добавить</span>
        </button>
    </div>
</div>

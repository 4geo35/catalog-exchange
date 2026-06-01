<form wire:submit.prevent="store" id="cmlFileLoadForm"
      class="flex flex-col space-y-indent-half md:flex-row md:space-x-indent-half md:space-y-0">

    <input type="file" class="form-control {{ $errors->has('cmlFile') ? 'border-danger' : '' }}"
           wire:loading.attr="disabled"
           wire:model.lazy="cmlFile"/>


    <button type="submit" form="cmlFileLoadForm" class="btn btn-primary"
            wire:loading.attr="disabled">
        Загрузить
    </button>
</form>

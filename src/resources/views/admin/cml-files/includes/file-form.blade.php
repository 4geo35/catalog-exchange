<form wire:submit.prevent="store" id="cmlFileLoadForm"
      class="">

    <div class="flex flex-col space-y-indent-half md:flex-row md:space-x-indent-half md:space-y-0">
        <input type="file" class="form-control {{ $errors->has('cmlFile') ? 'border-danger' : '' }}"
               required
               wire:loading.attr="disabled"
               wire:model.lazy="cmlFile"/>

        <select class="form-select {{ $errors->has('fileType') ? 'border-danger' : '' }}"
                wire:model="fileType">
            <option value="">Выберите тип файла...</option>
            @foreach($typeList as $item)
                <option value="{{ $item }}">{{ $item }}</option>
            @endforeach
        </select>


        <button type="submit" form="cmlFileLoadForm" class="btn btn-primary"
                wire:loading.attr="disabled">
            Загрузить
        </button>
    </div>
    <x-tt::form.error name="cmlFile" />
    <x-tt::form.error name="fileType"/>
</form>


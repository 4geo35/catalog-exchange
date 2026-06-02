<x-tt::table>
    <x-slot name="head">
        <tr>
            <x-tt::table.heading class="text-left text-nowrap">ID</x-tt::table.heading>
            <x-tt::table.heading class="text-left text-nowrap">Тип</x-tt::table.heading>
            <x-tt::table.heading class="text-left text-nowrap">Загружен</x-tt::table.heading>
            <x-tt::table.heading>Действия</x-tt::table.heading>
        </tr>
    </x-slot>
    <x-slot name="body">
        @foreach($files as $item)
            <tr>
                <td class="text-nowrap">{{ $item->id }}</td>
                <td class="text-nowrap">{{ $item->type }}</td>
                <td class="text-nowrap">{{ $item->created_human }}</td>
                <td>
                    <div class="flex justify-center">
                        @can("viewAny", $cml::class)
                            <a href="{{ route('download-file', ['filename' => $item->file->file_name]) }}"
                               target="_blank"
                               class="btn btn-primary px-btn-ico-text rounded-e-none">
                                <x-tt::ico.eye/>
                            </a>
                        @endcan
                        <button type="button" class="btn btn-danger px-btn-x-ico rounded-s-none"
                                @cannot("update", $cml) disabled
                                @else wire:loading.attr="disabled"
                                @endcannot
                                wire:click="showDelete('{{ $item->id }}')">
                            <x-tt::ico.trash/>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-slot>
</x-tt::table>

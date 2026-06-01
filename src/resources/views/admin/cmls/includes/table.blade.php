<x-tt::table>
    <x-slot name="head">
        <tr>
            <x-tt::table.heading class="text-left text-nowrap">UUID</x-tt::table.heading>
            <x-tt::table.heading class="text-left text-nowrap">Файлы</x-tt::table.heading>
            <x-tt::table.heading class="text-left text-nowrap">Архив</x-tt::table.heading>
            <x-tt::table.heading>Действия</x-tt::table.heading>
        </tr>
    </x-slot>
    <x-slot name="body">
        @foreach($cmls as $item)
            <tr>
                <td>
                    <div class="text-nowrap">{{ $item->id }}</div>
                    <div class="text-nowrap text-sm text-body/60">
                        {{ $item->created_human }}
                    </div>
                </td>
                <td>{{ $item->files->count() }}</td>
                <td>{{ $item->is_zip ? "Да" : "Нет" }}</td>
                <td>
                    <div class="flex justify-center">
                        @can("viewAny", $item::class)
                            <a href="{{ route('admin.cmls.show', ['cml' => $item]) }}"
                               class="btn btn-primary px-btn-ico-text rounded-e-none">
                                <x-tt::ico.eye/>
                            </a>
                        @else
                            <button type="button" class="btn btn-primary px-btn-x-ico rounded-e-none" disabled>
                                <x-tt::ico.eye/>
                            </button>
                        @endcan
                        <button type="button" class="btn btn-danger px-btn-x-ico rounded-s-none"
                                @cannot("delete", $item) disabled
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
    <x-slot name="caption">
        <div class="flex justify-between">
            <div>{{ __("Total") }}: {{ $cmls->total() }}</div>
            {{ $cmls->links("tt::pagination.live") }}
        </div>
    </x-slot>
</x-tt::table>

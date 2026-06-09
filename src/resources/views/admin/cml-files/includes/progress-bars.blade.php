<x-tt::table>
    <x-slot name="head">
        <tr>
{{--            <x-tt::table.heading class="text-left text-nowrap">ID</x-tt::table.heading>--}}
            <x-tt::table.heading class="text-left text-nowrap">Тип</x-tt::table.heading>
            <x-tt::table.heading class="text-left text-nowrap">Начало</x-tt::table.heading>
            <x-tt::table.heading class="text-left text-nowrap">Завершен</x-tt::table.heading>
            <x-tt::table.heading>Прогресс</x-tt::table.heading>
        </tr>
    </x-slot>
    <x-slot name="body">
        @foreach($progressBars as $barItem)
            <tr>
{{--                <td>{{ $barItem->id }}</td>--}}
                <td class="text-nowrap">{{ $barItem->human_type }}</td>
                <td>{{ $barItem->created_human }}</td>
                <td>{{ $barItem->completed_human }}</td>
                <td>
                    <div class="flex items-center justify-start w-full rounded-base bg-light relative h-6 overflow-hidden min-w-[240px]">
                        <div class="absolute w-full h-full flex items-center justify-center mix-blend-difference text-white text-xs font-semibold text-nowrap">
                            {{ $barItem->pending }} / {{ $barItem->total }}
                        </div>
                        <div class="bg-primary h-full" style="width: {{ $barItem->percent }}%"></div>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-slot>
</x-tt::table>

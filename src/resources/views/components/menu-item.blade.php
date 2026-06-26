@php
    $cmlActive = in_array(Route::currentRouteName(), [
        "admin.cmls.index", "admin.cmls.show"
    ]);
@endphp
@can("viewAny", config("catalog-exchange.customCmlModel") ?? \GIS\CatalogExchange\Models\Cml::class)
    <x-tt::admin-menu.item href="{{ route('admin.cmls.index') }}"
                           :active="$cmlActive">
        <x-slot name="ico"><x-cexc::ico.cloud-download /></x-slot>
        CommerceML
    </x-tt::admin-menu.item>
@endcan

@foreach($files as $item)
    <livewire:cexc-admin-cml-file-item :cml-file="$item" wire:key="{{ $item->id . now()->toIso8601String() }}" />
@endforeach

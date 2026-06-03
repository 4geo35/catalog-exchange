<div class="flex justify-center">
    @if ($item->can_run)
        <button type="button" class="btn btn-success px-btn-x-ico mr-indent-half"
                @cannot("update", $cml) disabled
                @else wire:loading.attr="disabled"
                @endcannot
                wire:click="showRun('{{ $item->id }}')">
            <x-cexc::ico.play-arrow />
        </button>
    @else
        <button type="button" class="btn btn-warning px-btn-x-ico mr-indent-half"
                @cannot("update", $cml) disabled
                @else wire:loading.attr="disabled"
                @endcannot
                wire:click="showStop('{{ $item->id }}')">
            <x-cexc::ico.stop-circle />
        </button>
    @endif

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

<div class="card" @if ($cmlFile->started_at && !$cmlFile->completed_at) wire:poll.2s @endif>
    <div class="card-header">
        <div class="flex items-center justify-between">
            <div class="font-semibold mr-indent">{{ $item->original_name }} ({{ $item->format_size }})</div>
            @include("cexc::admin.cml-files.includes.controls")
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col w-1/2">
                <dl class="row">
                    <dt class="col w-3/12 font-semibold">ID</dt>
                    <dt class="col w-9/12 mb-indent-xs">{{ $item->id }}</dt>

                    <dt class="col w-3/12 font-semibold">Тип файла</dt>
                    <dt class="col w-9/12 mb-indent-xs">{{ $item->type }}</dt>
                </dl>
                <button type="button" class="btn btn-sm btn-outline-warning mt-indent"
                        wire:click="showClearHistory('{{ $item->id }}')"
                        wire:loading.attr="disabled">
                    Очистить историю обработки
                </button>
            </div>

            <div class="col w-1/2">
                <dl class="row">
                    <dt class="col w-3/12 font-semibold">Добавлен</dt>
                    <dt class="col w-9/12 mb-indent-xs">{{ $item->created_human }}</dt>

                    @if ($item->started_at)
                        <dt class="col w-3/12 font-semibold">Запущен</dt>
                        <dt class="col w-9/12 mb-indent-xs">{{ $item->started_human }}</dt>
                    @endif

                    @if ($item->launched_at)
                        <dt class="col w-3/12 font-semibold">Обработка</dt>
                        <dt class="col w-9/12 mb-indent-xs">{{ $item->launched_human }}</dt>
                    @endif

                    @if ($item->completed_at)
                        <dt class="col w-3/12 font-semibold">Завершен</dt>
                        <dt class="col w-9/12 mb-indent-xs">{{ $item->completed_human }}</dt>
                    @endif
                </dl>
            </div>
        </div>
    </div>
    @include("cexc::admin.cml-files.includes.progress-bars")
</div>

<div class="card">
    <div class="card-body">
        <div class="space-y-indent-half">
            @include("cexc::admin.cml-files.includes.file-form")
            <x-tt::notifications.error prefix="cml-files-" />
            <x-tt::notifications.success prefix="cml-files-" />
        </div>
    </div>
    @include("cexc::admin.cml-files.includes.table")
    @include("cexc::admin.cml-files.includes.table-modals")
</div>

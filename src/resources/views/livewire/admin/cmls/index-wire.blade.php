<div class="card">
    <div class="card-body">
        <div class="space-y-indent-half">
            @include("cexc::admin.cmls.includes.search")
            <x-tt::notifications.error />
            <x-tt::notifications.success />
        </div>
    </div>
    @include("cexc::admin.cmls.includes.table")
    @include("cexc::admin.cmls.includes.table-modals")
</div>

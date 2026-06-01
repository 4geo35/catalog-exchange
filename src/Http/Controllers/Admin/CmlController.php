<?php

namespace GIS\CatalogExchange\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use GIS\CatalogExchange\Models\Cml;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CmlController extends Controller
{
    public function index(): View
    {
        $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
        Gate::authorize('viewAny', $modelClass);
        return view("cexc::admin.cmls.index");
    }
}

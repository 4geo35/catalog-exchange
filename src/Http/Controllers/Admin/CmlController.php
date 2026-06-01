<?php

namespace GIS\CatalogExchange\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use GIS\CatalogExchange\Interfaces\CmlInterface;
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

    public function show(CmlInterface $cml): View
    {
        Gate::authorize("viewAny", $cml::class);
        return view("cexc::admin.cmls.show", compact("cml"));
    }
}

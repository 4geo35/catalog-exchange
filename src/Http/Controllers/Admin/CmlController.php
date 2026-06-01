<?php

namespace GIS\CatalogExchange\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CmlController extends Controller
{
    public function index(): View
    {
        return view("cexc::admin.cmls.index");
    }
}

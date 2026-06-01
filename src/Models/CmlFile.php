<?php

namespace GIS\CatalogExchange\Models;

use Illuminate\Database\Eloquent\Model;

class CmlFile extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        "path",
        "type",
        "original_name",
        "completed_at",
    ];
}

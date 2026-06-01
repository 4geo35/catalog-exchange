<?php

namespace GIS\CatalogExchange\Models;

use GIS\CatalogExchange\Facades\ExchangeLoadFileActions;
use Illuminate\Database\Eloquent\Model;

class CmlFile extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        "path",
        "original_name",
        "completed_at",
    ];

    public function getTypeAttribute(): string
    {
        return ExchangeLoadFileActions::getFileTypeByName($this->original_name);
    }
}

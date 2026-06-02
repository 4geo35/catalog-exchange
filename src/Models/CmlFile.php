<?php

namespace GIS\CatalogExchange\Models;

use GIS\CatalogExchange\Facades\ExchangeLoadFileActions;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\Fileable\Traits\ShouldFile;
use GIS\TraitsHelpers\Traits\ShouldHumanDate;
use Illuminate\Database\Eloquent\Model;

class CmlFile extends Model implements CmlFileInterface
{
    use ShouldFile, ShouldHumanDate;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        "original_name",
        "completed_at",
    ];

    public function getTypeAttribute(): string
    {
        return ExchangeLoadFileActions::getFileTypeByName($this->original_name);
    }
}

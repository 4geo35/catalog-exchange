<?php

namespace GIS\CatalogExchange\Models;

use GIS\CatalogExchange\Interfaces\CmlInterface;
use GIS\TraitsHelpers\Traits\ShouldHumanDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cml extends Model implements CmlInterface
{
    use ShouldHumanDate;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        "is_zip"
    ];

    public function files(): HasMany
    {
        $modelClass = config("catalog-exchange.customCmlFileModel") ?? CmlFile::class;
        return $this->hasMany($modelClass);
    }
}

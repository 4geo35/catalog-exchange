<?php

namespace GIS\CatalogExchange\Models;

use GIS\CatalogExchange\Interfaces\CmlFileProgressBarInterface;
use GIS\TraitsHelpers\Traits\ShouldHumanDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmlFileProgressBar extends Model implements CmlFileProgressBarInterface
{
    use ShouldHumanDate;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        "type",
        "total",
        "pending",
        "completed_at",
    ];

    public function cmlFile(): BelongsTo
    {
        $modelClass = config("catalog-exchange.customCmlFileModel") ?? CmlFile::class;
        return $this->belongsTo($modelClass, "cml_file_id");
    }
}

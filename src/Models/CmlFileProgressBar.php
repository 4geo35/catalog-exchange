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

    public function getPercentAttribute(): int
    {
        if ($this->total <= 0) return 0;
        return (int) ($this->pending * 100 / $this->total);
    }

    public function getHumanTypeAttribute(): string
    {
        return match ($this->type) {
            "specifications" => "Характеристики",

            "products" => "Товары",
            "clearProducts" => "Очистка товаров",

            "categoryParents" => "Дерево категорий",
            "categories" => "Категории",
            "clearCategories" => "Очистка категорий",

            "variations" => "Цены",

            default => $this->type,
        };
    }

    public function getCompletedMoscowAttribute()
    {
        $value = $this->completed_at;
        if (empty($value)) return $value;
        return date_helper()->changeTz($value);
    }
    public function getCompletedHumanAttribute()
    {
        $value = $this->completed_moscow;
        if (empty($value)) return $value;
        return date_helper()->format($value);
    }
}

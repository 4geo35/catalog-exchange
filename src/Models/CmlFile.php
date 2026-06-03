<?php

namespace GIS\CatalogExchange\Models;

use GIS\CatalogExchange\Facades\ExchangeLoadFileActions;
use GIS\CatalogExchange\Interfaces\CmlFileInterface;
use GIS\Fileable\Traits\ShouldFile;
use GIS\TraitsHelpers\Traits\ShouldHumanDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CmlFile extends Model implements CmlFileInterface
{
    use ShouldFile, ShouldHumanDate;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        "original_name",
        "started_at",
        "launched_at",
        "completed_at",
    ];

    public function getTypeAttribute(): string
    {
        return ExchangeLoadFileActions::getFileTypeByName($this->original_name);
    }

    public function getStateAttribute(): string
    {
        $state = "new";
        if ($this->started_at) { $state = "queued"; }
        if ($this->launched_at) { $state = "running"; }
        if ($this->completed_at) { $state = "completed"; }
        return $state;
    }

    public function getCanRunAttribute(): bool
    {
        return in_array($this->state, ["new", "completed"]);
    }

    public function getCanStopAttribute(): bool
    {
        return ! $this->can_run;
    }

    public function getSizeAttribute(): int
    {
        return Storage::size($this->file->path);
    }

    public function getFormatSizeAttribute(): string
    {
        return size2word($this->size);
    }

    public function getStartedMoscowAttribute()
    {
        $value = $this->started_at;
        if (empty($value)) return $value;
        return date_helper()->changeTz($value);
    }
    public function getStartedHumanAttribute()
    {
        $value = $this->started_moscow;
        if (empty($value)) return $value;
        return date_helper()->format($value);
    }

    public function getLaunchedMoscowAttribute()
    {
        $value = $this->launched_at;
        if (empty($value)) return $value;
        return date_helper()->changeTz($value);
    }
    public function getLaunchedHumanAttribute()
    {
        $value = $this->launched_moscow;
        if (empty($value)) return $value;
        return date_helper()->format($value);
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

<?php

namespace Sanlilin\LaravelTheme\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Sanlilin\LaravelTheme\Enums\ThemeStatus;

class InstallTheme extends Model
{
    /**
     * @var array
     */
    public $guarded = [];

    /**
     * @var string[]
     */
    public $casts = [
        'composer' => 'json',
        'status' => 'integer',
    ];

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeEnable(Builder $query): Builder
    {
        return $query->where('status', ThemeStatus::enable());
    }

    public function getStatusAttribute(int $status)
    {
        return ThemeStatus::make(intval($status));
    }

    public function setStatusAttribute(ThemeStatus $value)
    {
        $this->attributes['status'] = $value->value;
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeDisable(Builder $query): Builder
    {
        return $query->where('status', ThemeStatus::disable());
    }

    /**
     * @return string
     */
    public function getLowerNameAttribute(): string
    {
        return strtolower($this->name);
    }
}

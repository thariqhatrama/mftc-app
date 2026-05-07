<?php

namespace App\Models;

class SystemConfig extends BaseModel
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $config = static::query()->where('key', $key)->first();

        return $config?->value ?? $default;
    }
}

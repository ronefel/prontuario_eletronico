<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class File extends Model
{
    protected $fillable = [
        'hash',
        'name',
        'content',
        'size',
        'mime_type',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->hash = Str::orderedUuid();
        });
    }

    public function sizeFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                $precision = 2;
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];

                $bytes = max($this->size, 0);
                $pow = floor(($this->size ? log($this->size) : 0) / log(1024));
                $pow = min($pow, count($units) - 1);

                $bytes /= pow(1024, $pow);

                return round($bytes, $precision) . ' ' . $units[$pow];
            }
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $hash
 * @property string $name
 * @property string $content
 * @property int|null $size
 * @property string|null $mime_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $size_formatted
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

                return round($bytes, $precision).' '.$units[$pow];
            }
        );
    }
}

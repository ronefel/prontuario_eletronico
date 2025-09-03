<?php

namespace App\Models;

use App\Casts\DatetimeWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel query()
 * @mixin \Eloquent
 */
class BaseModel extends Model
{
    use HasFactory;

    public function mergeCasts($casts)
    {
        $this->casts = array_merge(
            array_merge($this->casts, $casts),
            [
                'created_at' => DatetimeWithTimezone::class,
                'updated_at' => DatetimeWithTimezone::class,
            ]
        );

        return $this;
    }
}

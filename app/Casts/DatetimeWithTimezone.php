<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DatetimeWithTimezone implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $date = Carbon::make($value);

        if (Auth::check()) {
            $date->setTimezone(Auth::user()->timezone);
        }

        return $date;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (Auth::check()) {
            return Carbon::parse($value)->setTimezone('UTC');
        }

        return $value;
    }
}

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
        if (is_null($value)) {
            return null;
        }

        $date = Carbon::make($value);

        if ($date && Auth::check() && Auth::user()?->timezone) {
            $date->setTimezone(Auth::user()->timezone);
        }

        return $date;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if (Auth::check() && Auth::user()?->timezone) {
            return Carbon::parse($value)->setTimezone('UTC');
        }

        return $value;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class StatService
{
    const COUNTER_KEY = 'pn:stats:counter:by-server';

    public static function increment($id)
    {
        return Redis::incr(self::COUNTER_KEY . $id);
    }

    public static function get($id)
    {
        return Redis::get(self::COUNTER_KEY . $id);
    }
}

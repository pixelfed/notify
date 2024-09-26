<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class IngestCachePoolService
{
    const POOL_CACHE_KEY = 'pn:notify:pool';

    public static function push($payload)
    {
        return Redis::rpush(self::POOL_CACHE_KEY, json_encode($payload));
    }

    public static function range($start = 0, $stop = 10)
    {
        return Redis::lrange(self::POOL_CACHE_KEY, $start, $stop);
    }

    public static function pop()
    {
        return Redis::lpop(self::POOL_CACHE_KEY);
    }

    public static function flush($confirm = false)
    {
        if (! $confirm) {
            return;
        }

        return Redis::del(self::POOL_CACHE_KEY);
    }
}

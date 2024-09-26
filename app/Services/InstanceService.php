<?php

namespace App\Services;

use App\Models\Instance;
use Cache;
use GuzzleHttp\Exception\RequestException;
use Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;

class InstanceService
{
    const CACHE_NODEINFO_KEY = 'pn:services:instance:nodeinfo:';

    public static function checkServerSupport($domain = false)
    {
        if (! $domain || strlen($domain) > 80) {
            return false;
        }
        $domain = filter_var($domain, FILTER_SANITIZE_URL);

        if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
            return false;
        }

        $nodeinfo = self::nodeinfo($domain);

        if (! $nodeinfo) {
            return false;
        }
    }

    public static function nodeinfo($domain = false)
    {
        if (! $domain || strlen($domain) > 80) {
            return false;
        }
        $domain = filter_var($domain, FILTER_SANITIZE_URL);

        if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
            return false;
        }
        $dk = base64_encode($domain);

        return Cache::remember(self::CACHE_NODEINFO_KEY.$dk, 7200, function () use ($domain) {
            return self::fetchNodeinfo($domain);
        });
    }

    public static function fetchNodeinfo($domain = false)
    {
        if (! $domain || strlen($domain) > 80) {
            return false;
        }
        $domain = filter_var($domain, FILTER_SANITIZE_URL);

        if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
            return false;
        }

        if (! checkdnsrr($domain, 'A') && ! checkdnsrr($domain, 'AAAA')) {
            return false;
        }

        $url = 'https://'.$domain.'/api/nodeinfo/2.0.json';

        try {
            $res = Http::get($url)->json();
            if (! $res || ! isset($res['software']) || ! isset($res['software']['name']) || $res['software']['name'] !== 'pixelfed') {
                return false;
            }

            return $res;
        } catch (
            Exception|
            RequestException|
            ConnectionException $e) {
                return false;
            }
    }

    public static function keyGenerator(Instance $instance)
    {
        return 'v1:'.date('ym').str_pad($instance->id, 5, '0', STR_PAD_LEFT).':'.Str::random(32);
    }
}

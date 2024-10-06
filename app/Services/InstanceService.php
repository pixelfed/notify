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

    const CACHE_SECRET_KEY = 'pn:services:instance:secret_keys';

    const CACHE_DOMAINS_KEY = 'pn:services:instance:active-domains';

    const CACHE_DOMAIN_PAIRS_KEY = 'pn:services:instance:active-domains-pairs';

    const CACHE_DOMAIN_PAIRS_ENTITY = 'pn:services:instance:active-domains-pairs:entity:';

    public static function getKeys()
    {
        return Cache::remember(self::CACHE_SECRET_KEY, 86400, function () {
            return Instance::whereNotNull('secret')->whereIsSupported(true)->whereIsAllowed(true)->pluck('secret')->toArray();
        });
    }

    public static function clearKeys()
    {
        Cache::forget(self::CACHE_SECRET_KEY);
        Cache::forget(self::CACHE_DOMAIN_PAIRS_KEY);

        foreach(Instance::get() as $instance) {
            Cache::forget(self::CACHE_DOMAIN_PAIRS_ENTITY . $instance->id);
        }

        return self::getKeys();
    }

    public static function getDomainKeyPairs()
    {
        return Cache::remember(self::CACHE_DOMAIN_PAIRS_KEY, 86400, function() {
            return Instance::whereNotNull('secret')
                ->whereIsSupported(true)
                ->whereIsAllowed(true)
                ->get()
                ->map(function($instance) {
                    if(!$instance->secret || !$instance->domain) {
                        return false;
                    }
                    return [
                        'id' => $instance->id,
                        'domain' => $instance->domain,
                        'token' => $instance->secret
                    ];
                })
                ->filter()
                ->toArray();
        });
    }

    public static function getEntityFromKey($id)
    {
        return Cache::remember(self::CACHE_DOMAIN_PAIRS_ENTITY . $id, 86400, function() use($id) {
            $keys = self::getDomainKeyPairs();
            if(!$keys || !count($keys)) {
                return;
            }
            $col = collect($keys);

            return $col->filter(function($item) use($id) {
                return $item['token'] === $id;
            })->first();
        });
    }

    public static function getActiveDomains($flush = false)
    {
        if ($flush) {
            Cache::forget(self::CACHE_DOMAINS_KEY);
            Cache::forget(self::CACHE_DOMAIN_PAIRS_KEY);
        }

        return Cache::remember(self::CACHE_DOMAINS_KEY, 86400, function () {
            return Instance::whereIsSupported(true)->whereIsAllowed(true)->pluck('domain')->toArray();
        });
    }

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

    public static function idFromKey($key)
    {
        if (preg_match('/v1:\d{4}(0*\d+):/', $key, $matches)) {
            $instanceId = ltrim($matches[1], '0');
            return $instanceId;
        }

        return null;
    }

    public static function keyGenerator(Instance $instance)
    {
        return 'v1:'.date('ym').str_pad($instance->id, 5, '0', STR_PAD_LEFT).':'.Str::random(32);
    }
}

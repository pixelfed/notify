<?php

namespace App\Http\Controllers;

use App\Http\Requests\RelayV1Request;
use App\Services\IngestCachePoolService;
use App\Services\InstanceService;
use App\Services\NotifyMessageService;

class RelayV1Controller extends Controller
{
    public function store(RelayV1Request $request, $secret, $userToken)
    {
        abort_unless(in_array($secret, InstanceService::getKeys(), true), 403, 'Invalid server secret');
        abort_unless($this->isValidExpoPushToken($userToken), 422, 'Invalid push token');

        $payload = [
            'token' => $userToken,
            'message' => NotifyMessageService::get($request->input('type'), $request->input('actor')),
        ];
        IngestCachePoolService::push($payload);

        return response()->json(['status' => 200]);
    }

    protected function isValidExpoPushToken($token)
    {
        $pattern = '/^ExponentPushToken\[[a-zA-Z0-9_]{20,}\]$/';

        return preg_match($pattern, $token) === 1;
    }
}

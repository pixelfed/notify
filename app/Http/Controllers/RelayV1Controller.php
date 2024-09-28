<?php

namespace App\Http\Controllers;

use App\Http\Requests\RelayV1Request;
use App\Services\IngestCachePoolService;
use App\Services\NotifyMessageService;

class RelayV1Controller extends Controller
{
    public function store(RelayV1Request $request)
    {
        abort_unless($this->isValidExpoPushToken($request->token), 422, 'Invalid push token');

        $payload = [
            'token' => $request->token,
            'message' => NotifyMessageService::get($request->input('type'), $request->input('actor')),
        ];
        IngestCachePoolService::push($payload);

        return response()->json(['status' => 200]);
    }

    protected function isValidExpoPushToken($token)
    {
        if (! $token || empty($token)) {
            return false;
        }
        $starts = str_starts_with($token, 'Expo');
        if (! $starts) {
            return false;
        }
        if (! str_contains($token, 'PushToken[') || ! str_contains($token, ']')) {
            return false;
        }
        if (substr($token, -1) !== ']') {
            return false;
        }

        return true;
    }
}

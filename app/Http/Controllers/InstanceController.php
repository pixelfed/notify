<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInstanceRequest;
use App\Services\InstanceService;

class InstanceController extends Controller
{
    public function checkInstance(CheckInstanceRequest $request)
    {
        $allowedDomains = explode(',', config('custom.allowed_domains'));
        $activeDomains = InstanceService::getActiveDomains();

        return [
            'exists' => in_array($request->domain, $activeDomains),
            'allowed' => in_array($request->domain, $allowedDomains),
        ];
    }
}

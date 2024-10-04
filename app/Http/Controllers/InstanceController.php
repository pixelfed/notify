<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInstanceRequest;
use App\Services\InstanceService;

class InstanceController extends Controller
{
    public function checkInstance(CheckInstanceRequest $request)
    {
        $activeDomains = InstanceService::getActiveDomains();

        return [
            'active' => in_array($request->domain, $activeDomains),
        ];
    }
}

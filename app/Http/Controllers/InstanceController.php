<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInstanceRequest;
use App\Models\Instance;

class InstanceController extends Controller
{
    public function checkInstance(CheckInstanceRequest $request)
    {
        return [
            'exists' => Instance::whereDomain($request->domain)->whereIsSupported(true)->whereIsAllowed(true)->exists(),
        ];
    }
}

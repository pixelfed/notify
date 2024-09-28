<?php

use App\Http\Controllers\InstanceController;
use App\Http\Controllers\RelayV1Controller;
use Illuminate\Support\Facades\Route;

Route::get('/v1/instance-check', [InstanceController::class, 'checkInstance']);

Route::post('/v1/relay/{secret}/{userToken}', [RelayV1Controller::class, 'store']);

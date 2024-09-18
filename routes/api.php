<?php

use App\Http\Controllers\InstanceController;
use Illuminate\Support\Facades\Route;

Route::get('/instance/check', [InstanceController::class, 'checkInstance']);

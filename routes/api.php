<?php

use App\Http\Controllers\Api\HikvisionEventController;
use Illuminate\Support\Facades\Route;

Route::post('/hikvision/events', HikvisionEventController::class);

<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index');
    }

    public function hikvisionSetup(): View
    {
        abort_unless(auth()->user()?->canViewAlarmAdmin(), 403);

        return view('settings.hikvision-setup', [
            'alarmEndpoint' => url('/api/hikvision/events'),
            'alarmPath' => '/api/hikvision/events',
            'tokenEnabled' => filled(config('hikvision.alarm_token')),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\Site;
use App\Support\CameraData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CameraLiveStatusController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user(), 401);

        $cameras = Camera::query()
            ->withMonitoringData()
            ->visibleToUser($request->user())
            ->applyMonitoringFilters([
                'organisation' => $request->string('organisation')->toString(),
                'site' => $request->string('site')->toString(),
                'status' => $request->string('status')->toString(),
                'connectivity_type' => $request->string('connectivity_type')->toString(),
                'ownership_type' => $request->string('ownership_type')->toString(),
            ])
            ->orderBy('site_name')
            ->orderBy('name')
            ->get();

        $sites = $cameras
            ->filter(fn (Camera $camera): bool => $camera->site !== null)
            ->groupBy('site_id')
            ->map(function ($siteCameras): Site {
                $site = $siteCameras->first()->site;
                $site->setRelation('cameras', $siteCameras->values());

                return $site;
            })
            ->values();

        return response()->json([
            'cameras' => $cameras->map(fn (Camera $camera) => CameraData::cameraPayload($camera))->values(),
            'sites' => CameraData::siteCollectionPayload($sites),
        ]);
    }

    public function show(Request $request, Camera $camera): JsonResponse
    {
        abort_unless($request->user(), 401);

        $camera->loadMissing('latestHikvisionEvent', 'site.organisation', 'site.cameras');
        abort_unless($request->user()->canViewCamera($camera), 403);

        return response()->json(CameraData::cameraPayload($camera, includeRawPayload: true));
    }
}

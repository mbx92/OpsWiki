<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\StaticExportService;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class WikiExportController extends Controller
{
    public function exportStatic(StaticExportService $exporter, ActivityLogService $activity): HttpResponse
    {
        $content = $exporter->exportWiki();
        $activity->log(request()->user(), 'exported', null, 'Static wiki site');

        return response($content, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="wiki-static.zip"',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Activity/Index', [
            'logs' => ActivityLog::with('user')
                ->latest('created_at')
                ->paginate(30),
        ]);
    }
}

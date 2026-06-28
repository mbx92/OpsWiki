<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Foundation\Application;
use Inertia\Inertia;
use Inertia\Response;

class MarketingController extends Controller
{
    public function welcome(): Response
    {
        return Inertia::render('Welcome', [
            'canLogin' => true,
            'canRegister' => (bool) config('saas.registration_enabled'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
            'legalLinks' => [
                ['slug' => 'terms', 'label' => 'Terms'],
                ['slug' => 'privacy', 'label' => 'Privacy'],
                ['slug' => 'acceptable-use', 'label' => 'Acceptable Use'],
            ],
        ]);
    }
}

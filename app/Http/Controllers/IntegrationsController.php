<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class IntegrationsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Integrations/Index');
    }
}

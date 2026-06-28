<?php

namespace App\Http\Controllers;

use App\Services\PlatformBillingService;
use Inertia\Inertia;
use Inertia\Response;

class PlatformBillingController extends Controller
{
    public function index(PlatformBillingService $billing): Response
    {
        return Inertia::render('Platform/Billing/Index', [
            'stats' => $billing->summary(),
            'recentInvoices' => $billing->recentInvoices(),
            'recentPayments' => $billing->recentPayments(),
            'subscriptions' => $billing->subscriptionRows(),
        ]);
    }
}

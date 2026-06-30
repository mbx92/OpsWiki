<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AiSettingsController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlitchTipSettingsController;
use App\Http\Controllers\IntegrationsController;
use App\Http\Controllers\KnowledgeGraphController;
use App\Http\Controllers\InboxItemController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\MinioSettingsController;
use App\Http\Controllers\PlatformBillingController;
use App\Http\Controllers\PlatformDashboardController;
use App\Http\Controllers\PlatformInvoiceController;
use App\Http\Controllers\PlatformPlanController;
use App\Http\Controllers\PlatformPolicyController;
use App\Http\Controllers\PlatformSubscriptionController;
use App\Http\Controllers\PlatformSuperAdminController;
use App\Http\Controllers\PlatformTenantController;
use App\Http\Controllers\WorkspaceBillingController;
use App\Http\Controllers\WorkspaceSettingsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PageImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\PublicShareController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SnippetController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\SopImportController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\TroubleshootingController;
use App\Http\Controllers\TroubleshootingImportController;
use App\Http\Controllers\UpgradeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WikiExportController;
use App\Models\InboxItem;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketingController::class, 'welcome'])->name('home');

Route::get('/legal/{slug}', [LegalController::class, 'show'])->name('legal.show');

Route::get('/portal', [PublicPortalController::class, 'index'])->name('portal.index');
Route::get('/w/{tenant:slug}', [PublicPortalController::class, 'indexForTenant'])->name('portal.tenant');

Route::middleware('guest')->group(function () {
    if (config('saas.registration_enabled')) {
        Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('register', [RegisteredUserController::class, 'store'])
            ->middleware('throttle:register');
    }
});

Route::get('/share/books/{book:slug}', [PublicShareController::class, 'showBook'])
    ->middleware('tenant.context')
    ->name('share.books.show');
Route::get('/share/pages/{page:slug}', [PublicShareController::class, 'showPage'])
    ->middleware('tenant.context')
    ->name('share.pages.show');

Route::middleware(['auth', 'verified', 'active', 'tenant'])->group(function () {
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/query', [SearchController::class, 'query'])->name('search.query');

    Route::get('/upgrade/pro', [UpgradeController::class, 'pro'])->name('upgrade.pro');
    Route::get('/upgrade/team', [UpgradeController::class, 'team'])->name('upgrade.team');

    Route::middleware(['permission:assistant.use', 'plan:assistant'])->group(function () {
        Route::get('/assistant', [AssistantController::class, 'index'])->name('assistant.index');
        Route::post('/assistant/chat', [AssistantController::class, 'chat'])->name('assistant.chat');
    });

    Route::middleware('permission:knowledge.view')->group(function () {
        Route::get('/knowledge-graph', [KnowledgeGraphController::class, 'index'])->name('knowledge.index');
    });

    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::bind('inbox', fn (string $value) => InboxItem::findOrFail($value));

    Route::middleware('permission:inbox.manage')->group(function () {
        Route::get('/inbox/create', [InboxItemController::class, 'create'])->name('inbox.create');
        Route::post('/inbox', [InboxItemController::class, 'store'])->name('inbox.store');
        Route::get('/inbox/{inbox}/edit', [InboxItemController::class, 'edit'])->name('inbox.edit');
        Route::put('/inbox/{inbox}', [InboxItemController::class, 'update'])->name('inbox.update');
        Route::delete('/inbox/{inbox}', [InboxItemController::class, 'destroy'])->name('inbox.destroy');
        Route::post('/inbox/{inbox}/convert/page', [InboxItemController::class, 'convertToPage'])->name('inbox.convert.page');
        Route::post('/inbox/{inbox}/convert/snippet', [InboxItemController::class, 'convertToSnippet'])->name('inbox.convert.snippet');
        Route::post('/inbox/{inbox}/convert/sop', [InboxItemController::class, 'convertToSop'])->name('inbox.convert.sop');
        Route::post('/inbox/{inbox}/convert/troubleshooting', [InboxItemController::class, 'convertToTroubleshooting'])->name('inbox.convert.troubleshooting');
    });

    Route::middleware('permission:inbox.view')->group(function () {
        Route::get('/inbox', [InboxItemController::class, 'index'])->name('inbox.index');
        Route::get('/inbox/{inbox}', [InboxItemController::class, 'show'])->name('inbox.show');
    });

    Route::middleware(['permission:wiki.import', 'plan:wiki.import'])->group(function () {
        Route::get('/wiki/import', [PageImportController::class, 'create'])->name('wiki.import');
        Route::post('/wiki/import', [PageImportController::class, 'store'])->name('wiki.import.store');
    });

    Route::middleware(['permission:books.view', 'plan:books'])->group(function () {
        Route::get('/books', [BookController::class, 'index'])->name('books.index');
        Route::get('/books/{book:slug}', [BookController::class, 'show'])->name('books.show');
    });

    Route::middleware(['permission:books.manage', 'plan:books'])->group(function () {
        Route::patch('/books/{book:slug}/sharing', [BookController::class, 'updateSharing'])->name('books.sharing');
        Route::get('/books/{book:slug}/export', [BookController::class, 'export'])->name('books.export');
        Route::get('/books/{book:slug}/export/static', [BookController::class, 'exportStatic'])->name('books.export.static');
        Route::delete('/books/{book:slug}', [BookController::class, 'destroy'])->name('books.destroy');
        Route::post('/books/{book:slug}/pages', [BookController::class, 'attachPages'])->name('books.pages.attach');
        Route::post('/books/{book:slug}/pages/{page}/move', [BookController::class, 'movePage'])->name('books.pages.move');
    });

    Route::middleware(['permission:wiki.export.static', 'plan:wiki.export'])->group(function () {
        Route::get('/wiki/export/static', [WikiExportController::class, 'exportStatic'])->name('wiki.export.static');
    });

    Route::middleware('permission:wiki.history')->group(function () {
        Route::get('/wiki/{page:slug}/history', [PageController::class, 'history'])->name('wiki.history');
        Route::get('/wiki/{page:slug}/versions/{version}', [PageController::class, 'showVersion'])->name('wiki.versions.show');
    });

    Route::middleware('permission:wiki.create')->group(function () {
        Route::get('/wiki/create', [PageController::class, 'create'])->name('wiki.create');
        Route::post('/wiki', [PageController::class, 'store'])->name('wiki.store');
    });

    Route::middleware('permission:wiki.view')->group(function () {
        Route::get('/wiki', [PageController::class, 'index'])->name('wiki.index');
        Route::get('/wiki/{page:slug}', [PageController::class, 'show'])->name('wiki.show');
        Route::get('/wiki/{page:slug}/export', [PageController::class, 'export'])->name('wiki.export');
    });

    Route::middleware('permission:wiki.edit')->group(function () {
        Route::get('/wiki/{page:slug}/edit', [PageController::class, 'edit'])->name('wiki.edit');
        Route::put('/wiki/{page:slug}', [PageController::class, 'update'])->name('wiki.update');
        Route::post('/wiki/{page:slug}/versions/{version}/restore', [PageController::class, 'restore'])->name('wiki.versions.restore');
    });

    Route::middleware('permission:wiki.share')->group(function () {
        Route::patch('/wiki/{page:slug}/sharing', [PageController::class, 'updateSharing'])->name('wiki.sharing');
    });

    Route::middleware('permission:wiki.delete')->group(function () {
        Route::delete('/wiki/{page:slug}', [PageController::class, 'destroy'])->name('wiki.destroy');
    });

    Route::middleware('permission:snippets.view')->group(function () {
        Route::get('/snippets', [SnippetController::class, 'index'])->name('snippets.index');
    });

    Route::middleware('permission:snippets.manage')->group(function () {
        Route::get('/snippets/create', [SnippetController::class, 'create'])->name('snippets.create');
        Route::post('/snippets', [SnippetController::class, 'store'])->name('snippets.store');
        Route::get('/snippets/{snippet}/edit', [SnippetController::class, 'edit'])->name('snippets.edit');
        Route::put('/snippets/{snippet}', [SnippetController::class, 'update'])->name('snippets.update');
        Route::delete('/snippets/{snippet}', [SnippetController::class, 'destroy'])->name('snippets.destroy');
        Route::post('/snippets/{snippet}/copy', [SnippetController::class, 'copy'])->name('snippets.copy');
        Route::post('/snippets/{snippet}/favorite', [SnippetController::class, 'toggleFavorite'])->name('snippets.favorite');
        Route::post('/tools/{tool:slug}/save-snippet', [ToolController::class, 'saveSnippet'])->name('tools.save-snippet');
    });

    Route::middleware(['permission:sops.manage', 'plan:sops'])->group(function () {
        Route::get('/sops/import', [SopImportController::class, 'create'])->name('sops.import');
        Route::post('/sops/import', [SopImportController::class, 'store'])->name('sops.import.store');
        Route::get('/sops/create', [SopController::class, 'create'])->name('sops.create');
        Route::post('/sops', [SopController::class, 'store'])->name('sops.store');
        Route::get('/sops/{sop:slug}/edit', [SopController::class, 'edit'])->name('sops.edit');
        Route::put('/sops/{sop:slug}', [SopController::class, 'update'])->name('sops.update');
        Route::delete('/sops/{sop:slug}', [SopController::class, 'destroy'])->name('sops.destroy');
    });

    Route::middleware(['permission:sops.view', 'plan:sops'])->group(function () {
        Route::get('/sops', [SopController::class, 'index'])->name('sops.index');
        Route::get('/sops/{sop:slug}', [SopController::class, 'show'])->name('sops.show');
    });

    Route::middleware(['permission:troubleshooting.manage', 'plan:troubleshooting'])->group(function () {
        Route::get('/troubleshooting/import', [TroubleshootingImportController::class, 'create'])->name('troubleshooting.import');
        Route::post('/troubleshooting/import', [TroubleshootingImportController::class, 'store'])->name('troubleshooting.import.store');
        Route::get('/troubleshooting/create', [TroubleshootingController::class, 'create'])->name('troubleshooting.create');
        Route::post('/troubleshooting', [TroubleshootingController::class, 'store'])->name('troubleshooting.store');
        Route::get('/troubleshooting/{troubleshooting:slug}/edit', [TroubleshootingController::class, 'edit'])->name('troubleshooting.edit');
        Route::put('/troubleshooting/{troubleshooting:slug}', [TroubleshootingController::class, 'update'])->name('troubleshooting.update');
        Route::delete('/troubleshooting/{troubleshooting:slug}', [TroubleshootingController::class, 'destroy'])->name('troubleshooting.destroy');
    });

    Route::middleware(['permission:troubleshooting.view', 'plan:troubleshooting'])->group(function () {
        Route::get('/troubleshooting', [TroubleshootingController::class, 'index'])->name('troubleshooting.index');
        Route::get('/troubleshooting/{troubleshooting:slug}', [TroubleshootingController::class, 'show'])->name('troubleshooting.show');
    });

    Route::middleware(['permission:projects.manage', 'plan:projects'])->group(function () {
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project:slug}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project:slug}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project:slug}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });

    Route::middleware(['permission:projects.view', 'plan:projects'])->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
    });

    Route::middleware(['permission:tools.view', 'plan:tools'])->group(function () {
        Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
        Route::get('/tools/{tool:slug}', [ToolController::class, 'show'])->name('tools.show');
    });

    Route::middleware('permission:assets.view')->group(function () {
        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    });

    Route::middleware('permission:assets.manage')->group(function () {
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
        Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    });

    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('/settings/workspace', [WorkspaceSettingsController::class, 'edit'])->name('settings.workspace');
        Route::put('/settings/workspace', [WorkspaceSettingsController::class, 'update'])->name('settings.workspace.update');
        Route::post('/settings/workspace/domains', [WorkspaceSettingsController::class, 'storeDomain'])->name('settings.workspace.domains.store');
        Route::post('/settings/workspace/domains/{domain}/verify', [WorkspaceSettingsController::class, 'verifyDomain'])->name('settings.workspace.domains.verify');
        Route::delete('/settings/workspace/domains/{domain}', [WorkspaceSettingsController::class, 'destroyDomain'])->name('settings.workspace.domains.destroy');
        Route::get('/settings/billing', [WorkspaceBillingController::class, 'index'])->name('settings.billing');
    });

    Route::middleware('permission:settings.categories')->group(function () {
        Route::get('/settings/categories', [CategoryController::class, 'index'])->name('settings.categories');
        Route::post('/settings/categories', [CategoryController::class, 'store'])->name('settings.categories.store');
        Route::delete('/settings/categories/{category}', [CategoryController::class, 'destroy'])->name('settings.categories.destroy');
    });

    Route::middleware('permission:settings.tags')->group(function () {
        Route::get('/settings/tags', [TagController::class, 'index'])->name('settings.tags');
        Route::post('/settings/tags', [TagController::class, 'store'])->name('settings.tags.store');
        Route::delete('/settings/tags/{tag}', [TagController::class, 'destroy'])->name('settings.tags.destroy');
    });

    Route::middleware(['permission:settings.integrations', 'plan:integrations'])->group(function () {
        Route::get('/settings/integrations', [IntegrationsController::class, 'index'])->name('settings.integrations');
        Route::get('/settings/integrations/minio', [MinioSettingsController::class, 'edit'])->name('settings.integrations.minio');
        Route::put('/settings/integrations/minio', [MinioSettingsController::class, 'update'])->name('settings.integrations.minio.update');
        Route::post('/settings/integrations/minio/test', [MinioSettingsController::class, 'test'])->name('settings.integrations.minio.test');
        Route::get('/settings/integrations/glitchtip', [GlitchTipSettingsController::class, 'edit'])->name('settings.integrations.glitchtip');
        Route::put('/settings/integrations/glitchtip', [GlitchTipSettingsController::class, 'update'])->name('settings.integrations.glitchtip.update');
        Route::post('/settings/integrations/glitchtip/test', [GlitchTipSettingsController::class, 'test'])->name('settings.integrations.glitchtip.test');
        Route::get('/settings/integrations/ai', [AiSettingsController::class, 'edit'])->name('settings.integrations.ai');
        Route::put('/settings/integrations/ai', [AiSettingsController::class, 'update'])->name('settings.integrations.ai.update');
    });

    Route::middleware(['permission:activity.view', 'plan:activity'])->group(function () {
        Route::get('/settings/activity', [ActivityLogController::class, 'index'])->name('settings.activity');
    });

    Route::middleware(['permission:users.manage', 'plan:users.manage'])->group(function () {
        Route::get('/settings/users', [UserController::class, 'index'])->name('settings.users.index');
        Route::get('/settings/users/create', [UserController::class, 'create'])->name('settings.users.create');
        Route::post('/settings/users', [UserController::class, 'store'])->name('settings.users.store');
        Route::get('/settings/users/{user}/edit', [UserController::class, 'edit'])->name('settings.users.edit');
        Route::put('/settings/users/{user}', [UserController::class, 'update'])->name('settings.users.update');
        Route::delete('/settings/users/{user}', [UserController::class, 'destroy'])->name('settings.users.destroy');
    });

    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/settings/roles', [RoleController::class, 'index'])->name('settings.roles.index');
    });

    Route::middleware(['permission:roles.manage', 'plan:roles.manage'])->group(function () {
        Route::put('/settings/roles/{role}', [RoleController::class, 'update'])->name('settings.roles.update');
    });

    Route::middleware('super_admin')->prefix('platform')->name('platform.')->group(function () {
        Route::get('/', [PlatformDashboardController::class, 'index'])->name('dashboard');
        Route::get('/tenants', [PlatformTenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/{tenant}', [PlatformTenantController::class, 'show'])->name('tenants.show');
        Route::put('/tenants/{tenant}', [PlatformTenantController::class, 'update'])->name('tenants.update');
        Route::post('/tenants/{tenant}/invoices', [PlatformInvoiceController::class, 'store'])->name('tenants.invoices.store');
        Route::get('/billing', [PlatformBillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/subscriptions', [PlatformSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::put('/billing/subscriptions/{subscription}', [PlatformSubscriptionController::class, 'update'])->name('subscriptions.update');
        Route::get('/billing/invoices', [PlatformInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/billing/invoices/{invoice}', [PlatformInvoiceController::class, 'show'])->name('invoices.show');
        Route::post('/billing/invoices/{invoice}/payments', [PlatformInvoiceController::class, 'recordPayment'])->name('invoices.payments.store');
        Route::post('/billing/invoices/{invoice}/void', [PlatformInvoiceController::class, 'void'])->name('invoices.void');
        Route::get('/plans', [PlatformPlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/{plan}/edit', [PlatformPlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [PlatformPlanController::class, 'update'])->name('plans.update');
        Route::get('/policies', [PlatformPolicyController::class, 'index'])->name('policies.index');
        Route::get('/policies/{document}/edit', [PlatformPolicyController::class, 'edit'])->name('policies.edit');
        Route::put('/policies/{document}', [PlatformPolicyController::class, 'update'])->name('policies.update');
        Route::get('/super-admins', [PlatformSuperAdminController::class, 'index'])->name('super-admins.index');
        Route::post('/super-admins', [PlatformSuperAdminController::class, 'store'])->name('super-admins.store');
        Route::delete('/super-admins/{user}', [PlatformSuperAdminController::class, 'destroy'])->name('super-admins.destroy');
    });
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

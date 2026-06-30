<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WikiPageShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_wiki_show_resolves_page_after_tenant_context_is_set(): void
    {
        config(['saas.super_admin.password' => 'password']);

        $this->seed();

        $user = User::where('email', config('saas.super_admin.email'))->firstOrFail();
        $tenant = Tenant::where('slug', config('saas.default_tenant_slug'))->firstOrFail();

        $page = Page::withoutGlobalScopes()->create([
            'title' => 'SyncGuard — Dokumentasi Sistem',
            'slug' => 'documentation',
            'status' => 'production',
            'visibility' => 'private',
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('wiki.show', $page));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Wiki/Show')
            ->where('page.slug', 'documentation')
        );
    }
}

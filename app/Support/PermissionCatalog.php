<?php

namespace App\Support;

class PermissionCatalog
{
    /**
     * @return array<int, array{name: string, slug: string, group: string}>
     */
    public static function all(): array
    {
        return [
            ['name' => 'View dashboard', 'slug' => 'dashboard.view', 'group' => 'General'],
            ['name' => 'View inbox', 'slug' => 'inbox.view', 'group' => 'Inbox'],
            ['name' => 'Manage inbox', 'slug' => 'inbox.manage', 'group' => 'Inbox'],
            ['name' => 'View wiki', 'slug' => 'wiki.view', 'group' => 'Wiki'],
            ['name' => 'Create wiki pages', 'slug' => 'wiki.create', 'group' => 'Wiki'],
            ['name' => 'Edit wiki pages', 'slug' => 'wiki.edit', 'group' => 'Wiki'],
            ['name' => 'Delete wiki pages', 'slug' => 'wiki.delete', 'group' => 'Wiki'],
            ['name' => 'Import wiki pages', 'slug' => 'wiki.import', 'group' => 'Wiki'],
            ['name' => 'Share wiki pages', 'slug' => 'wiki.share', 'group' => 'Wiki'],
            ['name' => 'View page history', 'slug' => 'wiki.history', 'group' => 'Wiki'],
            ['name' => 'Export static docs', 'slug' => 'wiki.export.static', 'group' => 'Wiki'],
            ['name' => 'View books', 'slug' => 'books.view', 'group' => 'Books'],
            ['name' => 'Manage books', 'slug' => 'books.manage', 'group' => 'Books'],
            ['name' => 'View snippets', 'slug' => 'snippets.view', 'group' => 'Snippets'],
            ['name' => 'Manage snippets', 'slug' => 'snippets.manage', 'group' => 'Snippets'],
            ['name' => 'View SOPs', 'slug' => 'sops.view', 'group' => 'SOPs'],
            ['name' => 'Manage SOPs', 'slug' => 'sops.manage', 'group' => 'SOPs'],
            ['name' => 'View troubleshooting', 'slug' => 'troubleshooting.view', 'group' => 'Troubleshooting'],
            ['name' => 'Manage troubleshooting', 'slug' => 'troubleshooting.manage', 'group' => 'Troubleshooting'],
            ['name' => 'View projects', 'slug' => 'projects.view', 'group' => 'Projects'],
            ['name' => 'Manage projects', 'slug' => 'projects.manage', 'group' => 'Projects'],
            ['name' => 'View tools', 'slug' => 'tools.view', 'group' => 'Tools'],
            ['name' => 'View assets', 'slug' => 'assets.view', 'group' => 'Assets'],
            ['name' => 'Manage assets', 'slug' => 'assets.manage', 'group' => 'Assets'],
            ['name' => 'View settings', 'slug' => 'settings.view', 'group' => 'Settings'],
            ['name' => 'Manage categories', 'slug' => 'settings.categories', 'group' => 'Settings'],
            ['name' => 'Manage tags', 'slug' => 'settings.tags', 'group' => 'Settings'],
            ['name' => 'Manage integrations', 'slug' => 'settings.integrations', 'group' => 'Settings'],
            ['name' => 'View activity log', 'slug' => 'activity.view', 'group' => 'Settings'],
            ['name' => 'Use AI assistant', 'slug' => 'assistant.use', 'group' => 'Assistant'],
            ['name' => 'View knowledge graph', 'slug' => 'knowledge.view', 'group' => 'Knowledge'],
            ['name' => 'Manage users', 'slug' => 'users.manage', 'group' => 'Access'],
            ['name' => 'View roles', 'slug' => 'roles.view', 'group' => 'Access'],
            ['name' => 'Manage roles', 'slug' => 'roles.manage', 'group' => 'Access'],
            ['name' => 'Full access', 'slug' => '*', 'group' => 'Access'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'owner' => ['*'],
            'admin' => [
                'dashboard.view',
                'inbox.view', 'inbox.manage',
                'wiki.view', 'wiki.create', 'wiki.edit', 'wiki.delete', 'wiki.import', 'wiki.share', 'wiki.history', 'wiki.export.static',
                'books.view', 'books.manage',
                'snippets.view', 'snippets.manage',
                'sops.view', 'sops.manage',
                'troubleshooting.view', 'troubleshooting.manage',
                'projects.view', 'projects.manage',
                'tools.view',
                'assets.view', 'assets.manage',
                'settings.view', 'settings.categories', 'settings.tags', 'settings.integrations',
                'activity.view',
                'assistant.use', 'knowledge.view',
                'users.manage', 'roles.view',
            ],
            'user' => [
                'dashboard.view',
                'inbox.view', 'inbox.manage',
                'wiki.view', 'wiki.create', 'wiki.edit', 'wiki.share', 'wiki.history',
                'assistant.use', 'knowledge.view',
                'books.view',
                'snippets.view', 'snippets.manage',
                'sops.view',
                'troubleshooting.view',
                'projects.view',
                'tools.view',
                'assets.view',
            ],
        ];
    }
}

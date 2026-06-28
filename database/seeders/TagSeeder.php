<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'backup', 'restore', 'docker', 'proxmox', 'minio',
            'postgresql', 'cloudflare', 'tailscale', 'iscsi', 'nas',
            'production', 'draft', 'tested',
        ];

        foreach ($tags as $name) {
            Tag::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}

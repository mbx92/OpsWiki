<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Docker', 'Proxmox', 'MinIO', 'PostgreSQL', 'Cloudflare',
            'Tailscale', 'NAS & iSCSI', 'Network', 'Windows', 'Mac',
        ];

        foreach ($categories as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'type' => 'general', 'sort_order' => $index],
            );
        }
    }
}

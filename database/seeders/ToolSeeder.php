<?php

namespace Database\Seeders;

use App\Models\Tool;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            [
                'title' => 'MinIO IAM Generator',
                'slug' => 'minio-iam-generator',
                'description' => 'Generate mc commands to create MinIO bucket, user, and IAM policy.',
                'tool_type' => 'generator',
                'component_name' => 'MinioIamGenerator',
            ],
            [
                'title' => 'PostgreSQL Restore Helper',
                'slug' => 'pg-restore-helper',
                'description' => 'Generate pg_restore commands for PostgreSQL database restore.',
                'tool_type' => 'generator',
                'component_name' => 'PgRestoreHelper',
            ],
            [
                'title' => 'Docker Compose Builder',
                'slug' => 'docker-compose-builder',
                'description' => 'Build a basic docker-compose.yml from service inputs.',
                'tool_type' => 'template',
                'component_name' => 'DockerComposeBuilder',
            ],
            [
                'title' => 'rclone Copy Builder',
                'slug' => 'rclone-copy-builder',
                'description' => 'Generate rclone copy/sync commands for cloud storage.',
                'tool_type' => 'generator',
                'component_name' => 'RcloneCopyBuilder',
            ],
        ];

        foreach ($tools as $tool) {
            Tool::updateOrCreate(
                ['slug' => $tool['slug']],
                array_merge($tool, ['status' => 'active']),
            );
        }
    }
}

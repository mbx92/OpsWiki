<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InboxItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Snippet;
use App\Models\Sop;
use App\Models\Tag;
use App\Models\TroubleshootingCase;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;

class OpswikiDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@opswiki.com')->firstOrFail();
        $tenant = TenantContext::get()
            ?? \App\Models\Tenant::where('slug', config('saas.default_tenant_slug'))->firstOrFail();

        TenantContext::set($tenant);

        $this->seedPages($admin);
        $this->seedCategories($admin);
        $this->seedTags();
        $this->seedSops($admin);
        $this->seedTroubleshooting($admin);
        $this->seedSnippets($admin);
        $this->seedProjects($admin);
        $this->seedInbox($admin);

        TenantContext::set(null);
        $this->command?->info('✅ OpsWiki demo data seeded successfully.');
    }

    private function seedPages(User $admin): void
    {
        $p1 = Page::firstOrCreate(['slug' => 'server-provisioning'], [
            'title' => 'Server Provisioning Guide',
            'slug' => 'server-provisioning',
            'summary' => 'Step-by-step guide for provisioning new Ubuntu servers.',
            'content_markdown' => <<<'MD'
# Server Provisioning Guide

## Prerequisites
- Ubuntu 22.04+
- SSH access
- Root or sudo user

## Steps

### 1. Initial Setup
```bash
apt update && apt upgrade -y
```

### 2. Create User
```bash
adduser deploy
usermod -aG sudo deploy
```

### 3. SSH Hardening
- Disable root login
- Change default SSH port
- Set up key-based auth only

### 4. Firewall
```bash
ufw allow 22/tcp
ufw enable
```
MD,
            'content_html' => '<h1>Server Provisioning Guide</h1>',
            'status' => 'published',
            'visibility' => 'workspace',
            'created_by' => $admin->id,
        ]);

        $p2 = Page::create([
            'title' => 'Database Backup Strategy',
            'slug' => 'database-backup-strategy',
            'summary' => 'Automated PostgreSQL backup strategy with retention policies.',
            'content_markdown' => <<<'MD'
# Database Backup Strategy

## Overview
Daily full backups + WAL archiving for PITR.

## Backup Types
1. **Full Backup** — pg_dump nightly
2. **WAL Archive** — continuous write-ahead log shipping
3. **Logical Backup** — per-table export for critical tables

## Retention
- Daily: 7 days
- Weekly: 4 weeks
- Monthly: 12 months

## Recovery Procedure
```bash
pg_restore -d opswiki latest.dump
```
MD,
            'content_html' => '<h1>Database Backup Strategy</h1>',
            'status' => 'published',
            'visibility' => 'workspace',
            'created_by' => $admin->id,
        ]);

        $p3 = Page::create([
            'title' => 'Nginx Reverse Proxy Setup',
            'slug' => 'nginx-reverse-proxy',
            'summary' => 'Configure Nginx as reverse proxy with SSL termination.',
            'content_markdown' => <<<'MD'
# Nginx Reverse Proxy Setup

## Basic Config
```nginx
server {
    listen 443 ssl;
    server_name example.com;
    location / {
        proxy_pass http://localhost:3000;
    }
}
```

## SSL with Let's Encrypt
```bash
certbot --nginx -d example.com
```

## Rate Limiting
```nginx
limit_req_zone $binary_remote_addr zone=mylimit:10m rate=10r/s;
```
MD,
            'content_html' => '<h1>Nginx Reverse Proxy Setup</h1>',
            'status' => 'published',
            'visibility' => 'workspace',
            'created_by' => $admin->id,
        ]);

        $p4 = Page::create([
            'title' => 'Docker Compose Best Practices',
            'slug' => 'docker-compose-best-practices',
            'summary' => 'Production-ready Docker Compose patterns.',
            'content_markdown' => <<<'MD'
# Docker Compose Best Practices

## Structure
```yaml
services:
  app:
    image: myapp:latest
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:3000/health"]
```

## Secrets Management
Use Docker secrets or .env files — never hardcode credentials.

## Networking
- Use internal networks for inter-service communication
- Expose only necessary ports
MD,
            'content_html' => '<h1>Docker Compose Best Practices</h1>',
            'status' => 'published',
            'visibility' => 'workspace',
            'created_by' => $admin->id,
        ]);

        $p5 = Page::create([
            'title' => 'CI/CD Pipeline with GitHub Actions',
            'slug' => 'cicd-github-actions',
            'summary' => 'Build, test, and deploy workflow using GitHub Actions.',
            'content_markdown' => <<<'MD'
# CI/CD Pipeline

## Workflow
```yaml
name: Deploy
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Deploy
        run: ./deploy.sh
```

## Deployment Strategy
- Blue-green deployment
- Database migrations run first
- Zero-downtime
MD,
            'content_html' => '<h1>CI/CD Pipeline</h1>',
            'status' => 'draft',
            'visibility' => 'workspace',
            'created_by' => $admin->id,
        ]);

        $this->command?->info('Created 5 wiki pages.');
    }

    private function seedCategories(User $admin): void
    {
        $cats = [
            ['name' => 'Infrastructure', 'slug' => 'infrastructure', 'type' => 'page'],
            ['name' => 'Development', 'slug' => 'development', 'type' => 'page'],
            ['name' => 'Security', 'slug' => 'security', 'type' => 'page'],
            ['name' => 'Operations', 'slug' => 'operations', 'type' => 'page'],
            ['name' => 'Onboarding', 'slug' => 'onboarding', 'type' => 'page'],
        ];

        foreach ($cats as $data) {
            Category::firstOrCreate(['slug' => $data['slug']], $data);
        }
        $this->command?->info('Created/verified ' . count($cats) . ' categories.');
    }

    private function seedTags(): void
    {
        $tags = ['linux', 'database', 'nginx', 'docker', 'cicd', 'security', 'monitoring', 'backup', 'deployment'];
        foreach ($tags as $name) {
            Tag::firstOrCreate(['slug' => $name], ['name' => ucfirst($name), 'slug' => $name]);
        }
        $this->command?->info('Created/verified ' . count($tags) . ' tags.');
    }

    private function seedSops(User $admin): void
    {
        Sop::create([
            'title' => 'Incident Response Procedure',
            'slug' => 'incident-response',
            'purpose' => 'Standardized procedure for responding to production incidents',
            'use_case' => 'When a production outage or security incident occurs',
            'steps' => json_encode([
                ['action' => 'Identify', 'detail' => 'Determine scope and severity'],
                ['action' => 'Contain', 'detail' => 'Isolate affected systems'],
                ['action' => 'Investigate', 'detail' => 'Root cause analysis'],
                ['action' => 'Resolve', 'detail' => 'Apply fix and verify'],
                ['action' => 'Post-mortem', 'detail' => 'Document and action items'],
            ]),
            'status' => 'production',
            'created_by' => $admin->id,
        ]);

        Sop::create([
            'title' => 'Monthly Server Patching',
            'slug' => 'monthly-server-patching',
            'purpose' => 'Regular security patching for all Linux servers',
            'use_case' => 'Monthly maintenance window every 2nd Saturday',
            'steps' => json_encode([
                ['action' => 'Schedule', 'detail' => 'Notify stakeholders 48h before'],
                ['action' => 'Snapshot', 'detail' => 'Take VM snapshot'],
                ['action' => 'Update', 'detail' => 'apt update && apt upgrade -y'],
                ['action' => 'Reboot', 'detail' => 'Reboot if kernel updated'],
                ['action' => 'Verify', 'detail' => 'Check all services running'],
            ]),
            'status' => 'production',
            'created_by' => $admin->id,
        ]);

        Sop::create([
            'title' => 'Employee Offboarding',
            'slug' => 'employee-offboarding',
            'purpose' => 'Secure deprovisioning of employee access',
            'use_case' => 'Employee resignation or termination',
            'steps' => json_encode([
                ['action' => 'Notify', 'detail' => 'HR notifies IT of departure'],
                ['action' => 'Revoke Access', 'detail' => 'Disable all accounts'],
                ['action' => 'Backup Data', 'detail' => 'Archive for compliance'],
                ['action' => 'Asset Recovery', 'detail' => 'Collect hardware'],
                ['action' => 'Audit', 'detail' => 'Review access logs 30 days'],
            ]),
            'status' => 'review',
            'created_by' => $admin->id,
        ]);

        $this->command?->info('Created 3 SOPs.');
    }

    private function seedTroubleshooting(User $admin): void
    {
        TroubleshootingCase::create([
            'title' => 'PostgreSQL Connection Pool Exhaustion',
            'slug' => 'pg-connection-pool-exhaustion',
            'symptoms' => "Application returns 500 errors\npsql: FATAL: too many connections",
            'suspected_causes' => 'Connection pool not configured with max_connections limit. Long-running queries holding connections.',
            'diagnosis_steps' => "1. SELECT count(*) FROM pg_stat_activity\n2. Check pg_stat_activity for idle connections\n3. Review application connection pool config",
            'working_solution' => "1. Kill idle connections: SELECT pg_terminate_backend(pid)\n2. Increase max_connections in postgresql.conf\n3. Add connection pooling (PgBouncer)",
            'severity' => 'critical',
            'status' => 'solved',
            'created_by' => $admin->id,
        ]);

        TroubleshootingCase::create([
            'title' => 'Nginx 502 Bad Gateway',
            'slug' => 'nginx-502-bad-gateway',
            'symptoms' => "Browser shows 502\nNginx log: upstream prematurely closed connection",
            'suspected_causes' => 'PHP-FPM pool exhausted or crashed due to OOM killer.',
            'diagnosis_steps' => "1. Check PHP-FPM status: systemctl status php-fpm\n2. Check memory: free -h\n3. Check PHP-FPM logs for errors",
            'working_solution' => "1. Increase pm.max_children in PHP-FPM config\n2. Restart PHP-FPM: systemctl restart php-fpm\n3. Add more RAM or optimize PHP memory usage",
            'severity' => 'high',
            'status' => 'solved',
            'created_by' => $admin->id,
        ]);

        TroubleshootingCase::create([
            'title' => 'Disk Space Full on /var',
            'slug' => 'disk-space-full-var',
            'symptoms' => "Services failing to start\ndf -h shows /var at 100%",
            'suspected_causes' => 'Unrotated log files or Docker overlay2 accumulating old images.',
            'diagnosis_steps' => "1. Find large dirs: du -sh /var/* | sort -rh\n2. Check docker: docker system df\n3. Check journal: journalctl --disk-usage",
            'working_solution' => "1. Rotate logs: logrotate -f /etc/logrotate.conf\n2. Clean Docker: docker system prune -a\n3. Limit journald: set SystemMaxUse in /etc/systemd/journald.conf\n4. Set up disk monitoring alerts",
            'severity' => 'medium',
            'status' => 'workaround',
            'created_by' => $admin->id,
        ]);

        $this->command?->info('Created 3 troubleshooting cases.');
    }

    private function seedSnippets(User $admin): void
    {
        Snippet::create([
            'title' => 'Find and replace in files recursively',
            'command' => "find . -type f -name '*.php' -exec sed -i 's/OLD/NEW/g' {} +",
            'description' => 'Recursively replace text in all PHP files',
            'language' => 'bash',
            'platform' => 'linux',
            'created_by' => $admin->id,
        ]);

        Snippet::create([
            'title' => 'PostgreSQL show running queries',
            'command' => "SELECT pid, age(clock_timestamp(), query_start), usename, query FROM pg_stat_activity WHERE state != 'idle' ORDER BY age;",
            'description' => 'Show all currently running queries with duration',
            'language' => 'sql',
            'platform' => 'postgresql',
            'created_by' => $admin->id,
        ]);

        Snippet::create([
            'title' => 'Git undo last commit (keep changes)',
            'command' => 'git reset --soft HEAD~1',
            'description' => 'Undo last commit but keep changes staged',
            'language' => 'bash',
            'platform' => 'git',
            'created_by' => $admin->id,
        ]);

        Snippet::create([
            'title' => 'Docker remove all unused volumes',
            'command' => 'docker volume prune -f',
            'description' => 'Remove unused Docker volumes to free disk space',
            'language' => 'bash',
            'platform' => 'docker',
            'created_by' => $admin->id,
        ]);

        Snippet::create([
            'title' => 'Check SSL certificate expiry',
            'command' => "echo | openssl s_client -servername example.com -connect example.com:443 2>/dev/null | openssl x509 -noout -dates",
            'description' => 'Check SSL certificate expiration date',
            'language' => 'bash',
            'platform' => 'linux',
            'created_by' => $admin->id,
        ]);

        $this->command?->info('Created 5 snippets.');
    }

    private function seedProjects(User $admin): void
    {
        Project::create([
            'name' => 'Infrastructure Migration 2026',
            'slug' => 'infra-migration-2026',
            'description' => 'Migrate all services from on-premise to cloud infrastructure.',
            'status' => 'active',
            'server_location' => 'AWS ap-southeast-1',
            'repository_url' => 'https://github.com/mbx92/infra-migration',
            'created_by' => $admin->id,
        ]);

        Project::create([
            'name' => 'Monitoring Stack Upgrade',
            'slug' => 'monitoring-stack-upgrade',
            'description' => 'Upgrade Prometheus, Grafana, and AlertManager to latest LTS.',
            'status' => 'planning',
            'created_by' => $admin->id,
        ]);

        Project::create([
            'name' => 'Zero-Trust Security Implementation',
            'slug' => 'zero-trust-security',
            'description' => 'Implement zero-trust architecture with Cloudflare Tunnel and mTLS.',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->command?->info('Created 3 projects.');
    }

    private function seedInbox(User $admin): void
    {
        InboxItem::create([
            'title' => 'Set up monitoring for payment service',
            'content' => 'Need Prometheus metrics and Grafana dashboards for the new payment microservice. Alerts: latency > 500ms, error rate > 1%.',
            'type' => 'task',
            'status' => 'new',
            'created_by' => $admin->id,
        ]);

        InboxItem::create([
            'title' => 'SSL certificate expiring next week',
            'content' => 'Wildcard cert for *.yumalab.my.id expires soon. Need renewal via Cloudflare.',
            'type' => 'reminder',
            'status' => 'new',
            'created_by' => $admin->id,
        ]);

        InboxItem::create([
            'title' => 'Document Redis cluster failover',
            'content' => 'After last outage, need a clear SOP for Redis sentinel failover.',
            'type' => 'idea',
            'status' => 'reviewed',
            'created_by' => $admin->id,
        ]);

        $this->command?->info('Created 3 inbox items.');
    }
}

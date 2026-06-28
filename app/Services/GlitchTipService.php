<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Sentry\ClientBuilder;
use Sentry\Severity;
use Sentry\State\Hub;

class GlitchTipService
{
    public function config(): array
    {
        return Setting::getGlitchtip();
    }

    public function isEnabled(): bool
    {
        $config = $this->config();

        return (bool) ($config['enabled'] ?? false) && filled($config['dsn'] ?? null);
    }

    public function applyConfig(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $config = $this->config();

        if (! ($config['enabled'] ?? false) || empty($config['dsn'])) {
            Config::set('sentry.dsn', null);

            return;
        }

        Config::set('sentry.dsn', $config['dsn']);
        Config::set('sentry.environment', $config['environment'] ?: config('app.env'));
        Config::set('sentry.traces_sample_rate', $config['traces_sample_rate'] ?? 0);
        Config::set('sentry.send_default_pii', (bool) ($config['send_default_pii'] ?? false));
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        $config = $this->config();

        if (empty($config['dsn'])) {
            return ['ok' => false, 'message' => 'DSN is required. Copy it from your GlitchTip project settings.'];
        }

        try {
            $client = ClientBuilder::create([
                'dsn' => $config['dsn'],
                'environment' => $config['environment'] ?: config('app.env'),
            ])->getClient();

            $hub = new Hub($client);
            $eventId = $hub->captureMessage(
                'OpsWiki GlitchTip connection test',
                Severity::info(),
            );

            $client->flush();

            if (! $eventId) {
                return ['ok' => false, 'message' => 'Could not send test event to GlitchTip.'];
            }

            return [
                'ok' => true,
                'message' => 'Test event sent successfully. Check your GlitchTip project issues.',
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}

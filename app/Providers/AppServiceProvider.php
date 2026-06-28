<?php

namespace App\Providers;

use App\Services\GlitchTipService;
use App\Services\MinioArchiveService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->booting(function () {
            try {
                if (Schema::hasTable('settings')) {
                    app(GlitchTipService::class)->applyConfig();
                }
            } catch (\Throwable) {
                //
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureTrustedUrls();
        $this->configureAuthRateLimiting();

        Vite::prefetch(concurrency: 3);

        Password::defaults(function () {
            $rule = Password::min((int) config('saas.password.min', 8));

            if (config('saas.password.letters', true)) {
                $rule = $rule->letters();
            }

            if (config('saas.password.mixed_case', true)) {
                $rule = $rule->mixedCase();
            }

            if (config('saas.password.numbers', true)) {
                $rule = $rule->numbers();
            }

            if (config('saas.password.symbols', false)) {
                $rule = $rule->symbols();
            }

            if (config('saas.password.uncompromised', false)) {
                $rule = $rule->uncompromised();
            }

            return $rule;
        });

        if (Schema::hasTable('settings')) {
            app(MinioArchiveService::class)->registerDisk();
        }
    }

    private function configureTrustedUrls(): void
    {
        $appUrl = (string) config('app.url');

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }

    private function configureAuthRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = Str::transliterate(Str::lower((string) $request->input('email', '')));

            return Limit::perMinutes(
                max(1, (int) ceil(config('saas.rate_limits.login.decay_seconds', 60) / 60)),
                (int) config('saas.rate_limits.login.max_attempts', 5),
            )->by($email.'|'.$request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinutes(
                max(1, (int) ceil(config('saas.rate_limits.register.decay_seconds', 3600) / 60)),
                (int) config('saas.rate_limits.register.max_attempts', 5),
            )->by($request->ip());
        });
    }
}

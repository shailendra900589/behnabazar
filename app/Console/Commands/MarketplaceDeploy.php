<?php

namespace App\Console\Commands;

use App\Support\HostingerPublicSync;
use App\Support\StoragePublicLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class MarketplaceDeploy extends Command
{
    protected $signature = 'marketplace:deploy {--skip-composer : Skip composer install check}';

    protected $description = 'Full live deploy: composer, migrate, seed, clear ALL caches, verify features';

    public function handle(): int
    {
        $this->info('=== Behna Bazar LIVE deploy ===');
        $this->line('Path: '.base_path());
        $this->line('PHP: '.PHP_VERSION);
        $this->line('APP_URL: '.config('app.url'));
        $this->verifyLiveEnv();
        $this->printGitHead();

        if (! $this->option('skip-composer') && ! $this->ensureComposerDeps()) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('1) Clearing old caches (required on live hosting)...');
        $this->callSilent('optimize:clear');
        $this->clearBootstrapCacheFiles();
        if (function_exists('opcache_reset')) {
            @opcache_reset();
            $this->line('   OPcache reset.');
        }

        $this->newLine();
        $this->info('2) Running migrations...');
        $this->call('migrate', ['--force' => true]);
        $this->verifySchema();

        $this->newLine();
        $this->info('3) Marketplace defaults + settings...');
        $this->call('db:seed', ['--class' => 'Database\\Seeders\\MarketplaceDefaultsSeeder', '--force' => true]);
        $this->callSilent('marketplace:fix-demo-images');
        $this->callSilent('marketplace:ensure-catalog');

        $this->newLine();
        $this->info('4) Storage public link...');
        StoragePublicLink::ensure() ? $this->line('   OK') : $this->warn('   '.StoragePublicLink::helpMessage());

        $this->newLine();
        $this->info('5) Hostinger public_html sync (CSS/JS + Laravel bootstrap)...');
        $this->syncHostingerPublicHtml();

        $this->newLine();
        $this->info('6) Final cache clear...');
        $this->callSilent('view:clear');
        $this->callSilent('cache:clear');

        $this->verifyRoutes();
        $this->verifySettings();
        $this->printHostingerNotes();

        $this->newLine();
        $this->info('Deploy finished. Hard-refresh browser (Ctrl+Shift+R) or clear Cloudflare cache.');

        return self::SUCCESS;
    }

    private function verifyLiveEnv(): void
    {
        $envPath = base_path('.env');
        if (! is_file($envPath)) {
            $this->error('   .env MISSING at '.$envPath);
            $this->line('   Copy .env.production.hostinger → .env and set APP_URL, DB_PASSWORD, MAIL_PASSWORD.');

            return;
        }

        $raw = (string) file_get_contents($envPath);
        if (preg_match('/^\s*APP_URL\s*=\s*(.+)\s*$/m', $raw, $m)) {
            $fromFile = trim($m[1], " \t\"'");
            $this->line('   .env APP_URL line: '.$fromFile);
        } else {
            $this->warn('   .env has no APP_URL line — Laravel defaults to http://localhost');
        }

        $url = (string) config('app.url');
        $isLocalhost = str_contains($url, 'localhost') || str_contains($url, '127.0.0.1');

        if ($isLocalhost) {
            $this->newLine();
            $this->error('   LIVE .env is WRONG: APP_URL is still localhost.');
            $this->line('   Fix on server: nano .env  →  APP_URL=https://behnabazar.in');
            $this->line('   Also set: APP_ENV=production  APP_DEBUG=false  FILESYSTEM_DISK=public');
            $this->line('   Then: php artisan optimize:clear && php artisan marketplace:deploy');
        }

        if (config('filesystems.default') !== 'public') {
            $this->warn('   FILESYSTEM_DISK should be "public" on live (product images). Current: '.config('filesystems.default'));
        }
    }

    private function printGitHead(): void
    {
        $head = base_path('.git/HEAD');
        if (! is_file($head)) {
            $this->warn('No .git folder — confirm you pulled latest code into THIS directory.');

            return;
        }
        $ref = trim((string) file_get_contents($head));
        if (str_starts_with($ref, 'ref:')) {
            $refFile = base_path('.git/'.trim(substr($ref, 5)));
            $hash = is_file($refFile) ? trim((string) file_get_contents($refFile)) : '?';
            $this->line('Git: '.$hash.' ('.basename($refFile).')');
        }
    }

    private function ensureComposerDeps(): bool
    {
        $ok = class_exists(\Barryvdh\DomPDF\ServiceProvider::class)
            && class_exists(\Endroid\QrCode\QrCode::class);

        if ($ok) {
            $this->line('Composer packages: OK (dompdf, qr-code)');

            return true;
        }

        $this->warn('Missing vendor packages (PDF invoice, etc.). Running composer install...');

        if (! is_file(base_path('composer.json'))) {
            $this->error('composer.json not found.');

            return false;
        }

        $composer = trim((string) shell_exec('which composer 2>/dev/null') ?: '');
        if ($composer === '' && is_file(base_path('composer.phar'))) {
            $cmd = 'php '.escapeshellarg(base_path('composer.phar')).' install --no-dev --optimize-autoloader --no-interaction 2>&1';
        } elseif ($composer !== '') {
            $cmd = 'composer install --no-dev --optimize-autoloader --no-interaction 2>&1';
        } else {
            $this->error('Composer not found. On your PC run: composer install --no-dev');
            $this->error('Then upload the vendor/ folder OR enable composer on Hostinger SSH.');

            return false;
        }

        passthru($cmd, $code);

        if ($code !== 0 || ! class_exists(\Barryvdh\DomPDF\ServiceProvider::class)) {
            $this->error('composer install failed. Run manually: composer install --no-dev');

            return false;
        }

        $this->info('Composer install OK.');

        return true;
    }

    private function clearBootstrapCacheFiles(): void
    {
        foreach (glob(base_path('bootstrap/cache/*.php')) ?: [] as $file) {
            if (basename($file) !== '.gitignore') {
                @unlink($file);
            }
        }
        $this->line('   bootstrap/cache cleared.');
    }

    private function verifySchema(): void
    {
        $checks = [
            'products.seo_title' => ['products', 'seo_title'],
            'products.compare_at_price' => ['products', 'compare_at_price'],
            'stock_alerts' => ['stock_alerts', 'id'],
            'whatsapp_outbox' => ['whatsapp_outbox', 'id'],
            'notification_logs' => ['notification_logs', 'id'],
        ];

        foreach ($checks as $label => [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                $this->error("   Missing DB: {$label} — run php artisan migrate --force");
            } else {
                $this->line("   DB OK: {$label}");
            }
        }
    }

    private function verifyRoutes(): void
    {
        $routes = ['orders.invoice', 'product.stock-alert', 'sitemap', 'robots', 'home', 'dashboard'];
        foreach ($routes as $name) {
            if (Route::has($name)) {
                $this->line("   Route OK: {$name}");
            } else {
                $this->warn("   Route MISSING: {$name} — run optimize:clear, do not use stale route:cache");
            }
        }
    }

    private function verifySettings(): void
    {
        try {
            $count = DB::table('settings')->whereIn('setting_key', [
                'cod_enabled', 'notify_whatsapp_enabled', 'seo_locality',
            ])->count();
            $this->line("   Settings in DB: {$count}/3 core keys");
        } catch (\Throwable $e) {
            $this->warn('   Could not read settings table.');
        }
    }

    private function syncHostingerPublicHtml(): void
    {
        $detected = HostingerPublicSync::detectPublicHtml();
        if ($detected) {
            $this->line('   Found public_html: '.$detected);
        } else {
            $this->warn('   public_html not auto-detected. Set PUBLIC_HTML_PATH in .env then run: php artisan hostinger:sync-public');
        }

        $result = HostingerPublicSync::sync();
        if ($result['ok'] ?? false) {
            $this->line('   '.$result['message']);
            if (! empty($result['build'])) {
                $this->line('   CSS build stamp: '.$result['build']);
            }
        } else {
            $this->warn('   '.($result['message'] ?? 'Skipped.'));
        }
    }

    private function printHostingerNotes(): void
    {
        $this->newLine();
        $this->comment('--- Hostinger checklist ---');
        $this->line('• Split setup: CSS/JS in domains/.../public_html, Laravel in ~/behnabazar — deploy step 5 syncs both');
        $this->line('• Ideal: document root = '.public_path().' OR symlink public_html → behnabazar/public');
        $this->line('• .env APP_URL=https://behnabazar.in (HTTPS, not http://localhost)');
        $this->line('• Verify CSS: view-source → css/app.css?v= should load (not old behnabazar.min.css only)');
        $this->line('• Admin: Dashboard → ?section=whatsapp | notifications | program');
    }
}

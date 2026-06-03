<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApplyHostingerMailEnvCommand extends Command
{
    protected $signature = 'marketplace:apply-mail-env {--file=deploy/hostinger-mail.env}';

    protected $description = 'Merge Hostinger mailbox settings from deploy/hostinger-mail.env into .env (safe for live server)';

    public function handle(): int
    {
        $snippetPath = base_path((string) $this->option('file'));
        $envPath = base_path('.env');

        if (! is_file($snippetPath)) {
            $this->error('Snippet missing: '.$snippetPath);
            $this->line('Create it from deploy/hostinger-mail.env.example or copy deploy/hostinger-mail.env to the server.');

            return self::FAILURE;
        }

        if (! is_file($envPath)) {
            $this->error('.env missing at '.$envPath);

            return self::FAILURE;
        }

        $lines = file($snippetPath, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            $this->error('Could not read '.$snippetPath);

            return self::FAILURE;
        }

        $mailKeys = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (! str_contains($line, '=')) {
                continue;
            }
            [$key] = explode('=', $line, 2);
            $mailKeys[trim($key)] = $line;
        }

        if ($mailKeys === []) {
            $this->error('No MAIL_* variables found in snippet.');

            return self::FAILURE;
        }

        $envRaw = (string) file_get_contents($envPath);
        $envLines = preg_split("/\r\n|\n|\r/", $envRaw) ?: [];
        $updated = [];
        $seen = [];

        foreach ($envLines as $line) {
            if (preg_match('/^\s*([A-Z0-9_]+)\s*=/', $line, $m)) {
                $key = $m[1];
                if (isset($mailKeys[$key])) {
                    $updated[] = $mailKeys[$key];
                    $seen[$key] = true;

                    continue;
                }
            }
            $updated[] = $line;
        }

        foreach ($mailKeys as $key => $line) {
            if (! isset($seen[$key])) {
                $updated[] = $line;
            }
        }

        $output = implode(PHP_EOL, $updated);
        if (! str_ends_with($output, PHP_EOL)) {
            $output .= PHP_EOL;
        }

        file_put_contents($envPath, $output);

        $this->info('Updated .env mail settings from '.$snippetPath);
        $this->line('Run: php artisan config:clear && php artisan marketplace:mail-test no-reply@behnabazar.in');

        return self::SUCCESS;
    }
}

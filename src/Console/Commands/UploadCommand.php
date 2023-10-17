<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Mockery\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelTheme\Support\CompressTheme;
use Sanlilin\LaravelTheme\Traits\HasMarketTokens;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;

class UploadCommand extends Command
{
    use ThemeCommandTrait, HasMarketTokens;

    protected $name = 'theme:upload';

    protected $description = 'Upload the theme to the server.';

    public function handle(): int
    {
        try {
            $this->ensure_api_token_is_available();

            $theme = $this->argument('theme');
            $this->info("Theme {$theme} starts to compress");
            $compressRes = (new CompressTheme($this->getTheme()))->handle();
            if (! $compressRes) {
                $this->error("Theme {$theme} compression Failed");

                return E_ERROR;
            }
            $this->info("Theme {$theme} compression completed");

            $compressPath = $this->getTheme()->getCompressFilePath();

            $stream = fopen($compressPath, 'r+');

            $size = (int) round(filesize($compressPath) / 1024, 2);

            $progressBar = $this->output->createProgressBar($size);
            $progressBar->setFormat(' %current%KB/%max%KB [%bar%] %percent:3s%% (%remaining:-6s% remaining)');
            $progressBar->start();

            $progressCallback = function ($_, $__, $___, $uploaded) use ($progressBar) {
                $progressBar->setProgress((int) round($uploaded / 1024, 2));
            };
            try {
                app('themes.client')->upload([
                    'body' => $stream,
                    'headers' => ['theme-info' => json_encode($this->getTheme()->json()->getAttributes(), true)],
                    'progress' => $progressCallback,
                ]);
            } catch (\Exception $exception) {
                $this->line('');
                $this->error('Theme upload failed : '.$exception->getMessage());

                return E_ERROR;
            }

            $progressBar->finish();
            $this->laravel['files']->delete($compressPath);
            $this->line('');
            $this->info('Theme upload completed');

            if (is_resource($stream)) {
                fclose($stream);
            }

            return 0;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        }
    }

    protected function getArguments(): array
    {
        return [
            ['theme', InputArgument::REQUIRED, 'The name of theme will be used.'],
        ];
    }
}

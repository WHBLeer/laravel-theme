<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Sanlilin\LaravelTheme\Support\Composer\ComposerInstall;

class ComposerInstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:composer-install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all themes composer package.';

    public function handle(): void
    {
        try {
            ComposerInstall::make()->run();
            $this->info('Composer install complete.');
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}

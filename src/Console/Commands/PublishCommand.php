<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelTheme\Support\Theme;
use Sanlilin\LaravelTheme\Support\Publishing\AssetPublisher;

class PublishCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish a theme\'s assets to the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($name = $this->argument('theme')) {
            $theme = $this->laravel['themes.repository']->findOrFail($name);
            $this->publish($theme);

            return 0;
        }
        $this->publishAll();

        return 0;
    }

    /**
     * Publish assets from all themes.
     */
    public function publishAll(): void
    {
        /** @var Theme $theme */
        foreach ($this->laravel['themes.repository']->allEnabled() as $theme) {
            $this->publish($theme);
        }
    }

    /**
     * Publish assets from the specified theme.
     *
     * @param  Theme  $theme
     */
    public function publish(Theme $theme): void
    {
        with(new AssetPublisher($theme))
            ->setRepository($this->laravel['themes.repository'])
            ->setConsole($this)
            ->publish();

        $this->line("<info>Published</info>: {$theme->getStudlyName()}");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['theme', InputArgument::OPTIONAL, 'The name of theme will be used.'],
        ];
    }
}

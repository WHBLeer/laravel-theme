<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelTheme\Support\Theme;

class EnableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:enable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable the specified theme.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /**
         * check if user entred an argument.
         */
        if ($this->argument('theme') === null) {
            $this->enableAll();

            return 0;
        }

        /** @var Theme $theme */
        $theme = $this->laravel['themes.repository']->findOrFail($this->argument('theme'));

        if ($theme->isDisabled()) {
            $theme->enable();

            $this->info("Theme [{$theme}] enabled successful.");
        } else {
            $this->comment("Theme [{$theme}] has already enabled.");
        }

        return 0;
    }

    /**
     * enableAll.
     *
     * @return void
     */
    public function enableAll(): array
    {
        /** @var Theme $theme */
        $themes = $this->laravel['themes.repository']->all();

        foreach ($themes as $theme) {
            if ($theme->isDisabled()) {
                $theme->enable();
                $this->info("Theme [{$theme}]  enabled successful.");
            } else {
                $this->comment("Theme [{$theme}] has already enabled.");
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['theme', InputArgument::OPTIONAL, 'Theme name.'],
        ];
    }
}

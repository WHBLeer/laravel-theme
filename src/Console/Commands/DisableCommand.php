<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelTheme\Support\Theme;

class DisableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable the specified theme.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /**
         * check if user entred an argument.
         */
        if ($this->argument('theme') === null) {
            $this->disableAll();
        }

        /** @var Theme $theme */
        $theme = $this->laravel['themes.repository']->findOrFail($this->argument('theme'));

        if ($theme->isEnabled()) {
            $theme->disable();

            $this->info("Theme [{$theme}] disabled successful.");
        } else {
            $this->comment("Theme [{$theme}] has already disabled.");
        }

        return 0;
    }

    /**
     * disableAll.
     *
     * @return void
     */
    public function disableAll(): void
    {
        $themes = $this->laravel['themes.repository']->all();
        /** @var Theme $theme */
        foreach ($themes as $theme) {
            if ($theme->isEnabled()) {
                $theme->disable();

                $this->info("Theme [{$theme}] disabled successful.");
            } else {
                $this->comment("Theme [{$theme}] has already disabled.");
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

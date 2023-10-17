<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sanlilin\LaravelTheme\Contracts\ActivatorInterface;
use Sanlilin\LaravelTheme\Support\Generators\ThemeGenerator;

class ThemeMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new theme.';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $names = $this->argument('name');
        $success = true;

        foreach ($names as $name) {
            $code = with(new ThemeGenerator($name))
                ->setFilesystem($this->laravel['files'])
                ->setThemeRepository($this->laravel['themes.repository'])
                ->setConfig($this->laravel['config'])
                ->setActivator($this->laravel[ActivatorInterface::class])
                ->setConsole($this)
                ->setForce($this->option('force'))
                ->setActive(! $this->option('disabled'))
                ->generate();

            if ($code === E_ERROR) {
                $success = false;
            }
        }

        return $success ? 0 : E_ERROR;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::IS_ARRAY, 'The names of theme will be created.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['disabled', 'd', InputOption::VALUE_NONE, 'Do not enable the theme at creation.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when the theme already exists.'],
        ];
    }
}

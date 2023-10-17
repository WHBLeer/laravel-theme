<?php

namespace Sanlilin\LaravelTheme\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Str;
use Sanlilin\LaravelTheme\Console\Commands\ComposerInstallCommand;
use Sanlilin\LaravelTheme\Console\Commands\ComposerRemoveCommand;
use Sanlilin\LaravelTheme\Console\Commands\ComposerRequireCommand;
use Sanlilin\LaravelTheme\Console\Commands\ControllerMakeCommand;
use Sanlilin\LaravelTheme\Console\Commands\DisableCommand;
use Sanlilin\LaravelTheme\Console\Commands\DownLoadCommand;
use Sanlilin\LaravelTheme\Console\Commands\EnableCommand;
use Sanlilin\LaravelTheme\Console\Commands\InstallCommand;
use Sanlilin\LaravelTheme\Console\Commands\ListCommand;
use Sanlilin\LaravelTheme\Console\Commands\LoginCommand;
use Sanlilin\LaravelTheme\Console\Commands\MigrateCommand;
use Sanlilin\LaravelTheme\Console\Commands\MigrationMakeCommand;
use Sanlilin\LaravelTheme\Console\Commands\ModelMakeCommand;
use Sanlilin\LaravelTheme\Console\Commands\ThemeCommand;
use Sanlilin\LaravelTheme\Console\Commands\ThemeDeleteCommand;
use Sanlilin\LaravelTheme\Console\Commands\ThemeMakeCommand;
use Sanlilin\LaravelTheme\Console\Commands\ProviderMakeCommand;
use Sanlilin\LaravelTheme\Console\Commands\PublishCommand;
use Sanlilin\LaravelTheme\Console\Commands\RegisterCommand;
use Sanlilin\LaravelTheme\Console\Commands\RouteProviderMakeCommand;
use Sanlilin\LaravelTheme\Console\Commands\SeedMakeCommand;
use Sanlilin\LaravelTheme\Console\Commands\UploadCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Namespace of the console commands.
     *
     * @var string
     */
    protected string $consoleNamespace = 'Sanlilin\\LaravelTheme\\Console\\Commands';

    /**
     * The available commands.
     *
     * @var array
     */
    protected array $commands = [
        ThemeCommand::class,
        ThemeMakeCommand::class,
        ProviderMakeCommand::class,
        RouteProviderMakeCommand::class,
        ControllerMakeCommand::class,
        ModelMakeCommand::class,
        MigrationMakeCommand::class,
        MigrateCommand::class,
        SeedMakeCommand::class,
        ComposerRequireCommand::class,
        ComposerRemoveCommand::class,
        ComposerInstallCommand::class,
        ListCommand::class,
        DisableCommand::class,
        EnableCommand::class,
        ThemeDeleteCommand::class,
        InstallCommand::class,
        PublishCommand::class,
        RegisterCommand::class,
        LoginCommand::class,
        UploadCommand::class,
        DownLoadCommand::class,

    ];

    /**
     * @return array
     */
    private function resolveCommands(): array
    {
        $commands = [];

        foreach ((config('themes.commands') ?: $this->commands) as $command) {
            $commands[] = Str::contains($command, $this->consoleNamespace) ?
                $command :
                $this->consoleNamespace.'\\'.$command;
        }

        return $commands;
    }

    public function register(): void
    {
        $this->commands($this->resolveCommands());
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return $this->commands;
    }
}

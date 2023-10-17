<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sanlilin\LaravelTheme\Support\Composer\ComposerRequire;
use Sanlilin\LaravelTheme\Support\Json;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;
use Sanlilin\LaravelTheme\ValueObjects\ValRequires;

class ComposerRequireCommand extends Command
{
    use ThemeCommandTrait;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:composer-require';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the theme composer package.';

    public function handle(): void
    {
        try {
            $theme = $this->argument('theme');

            $package = $this->argument('package');

            $themeJson = $this->getTheme()->json();

            $require = $this->option('dev') ? 'require-dev' : 'require';

            $vrs = ValRequires::toValRequires([
                $package => $this->option('v'),
            ]);

            $composerRequire = ComposerRequire::make();

            $this->option('dev') ? $composerRequire->appendThemeDevRequires($theme, $vrs)->run() : $composerRequire->appendThemeRequires($theme, $vrs)->run();

            $composer = $themeJson->get('composer', []);
            $version = data_get(Json::make('composer.json')->setIsCache(false)->get($require), $package);
            $composer[$require][$package] = $version;
            $themeJson->set('composer', $composer)->save();
            $this->info("Package {$vrs}generated successfully.");
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    protected function getArguments(): array
    {
        return [
            ['theme', InputArgument::REQUIRED, 'The name of themes will be used.'],
            ['package', InputArgument::REQUIRED, 'The name of the composer package name.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['dev', null, InputOption::VALUE_NONE, 'Only the composer package of the dev environment exists.'],
            ['v', null, InputOption::VALUE_OPTIONAL, 'The version number of the composer package.'],
        ];
    }
}

<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelTheme\Support\Composer\ComposerRemove;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;
use Sanlilin\LaravelTheme\ValueObjects\ValRequire;
use Sanlilin\LaravelTheme\ValueObjects\ValRequires;

class ComposerRemoveCommand extends Command
{
    use ThemeCommandTrait;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:composer-remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the theme composer package.';

    public function handle(): void
    {
        $packages = $this->argument('packages');
        $theme = $this->argument('theme');
        $themeJson = $this->getTheme()->json();

        try {
            $vrs = ValRequires::make();
            foreach ($packages as $package) {
                $vrs->append(ValRequire::make($package));
            }
            ComposerRemove::make()->appendRemoveThemeRequires($theme, $vrs)->run();
            $composer = $themeJson->get('composer');

            foreach ($packages as $package) {
                Arr::forget($composer, "require.$package");
                Arr::forget($composer, "require-dev.$package");
            }

            $themeJson->set('composer', $composer)->save();
            $this->info("Package {$vrs}remove complete.");
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    protected function getArguments(): array
    {
        return [
            ['theme', InputArgument::REQUIRED, 'The name of themes will be used.'],
            ['packages', InputArgument::IS_ARRAY, 'The name of the composer package name.'],
        ];
    }
}

<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sanlilin\LaravelTheme\Support\Config\GenerateConfigReader;
use Sanlilin\LaravelTheme\Support\Stub;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;

class ProviderMakeCommand extends GeneratorCommand
{
    use ThemeCommandTrait;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected string $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:make-provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service provider class for the specified theme.';

    public function getDefaultNamespace(): string
    {
        $repository = $this->laravel['themes.repository'];

        return $repository->config('paths.generator.provider.namespace') ?: $repository->config('paths.generator.provider.path', 'Providers');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The service provider name.'],
            ['theme', InputArgument::OPTIONAL, 'The name of theme will be used.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['master', null, InputOption::VALUE_NONE, 'Indicates the master service provider', null],
        ];
    }

    /**
     * @return string
     */
    protected function getTemplateContents(): string
    {
        $stub = $this->option('master') ? 'scaffold/provider' : 'provider';

        $theme = $this->getTheme();

        return (new Stub('/'.$stub.'.stub', [
            'NAMESPACE'         => $this->getClassNamespace($theme),
            'CLASS'             => $this->getClass(),
            'LOWER_NAME'        => $theme->getLowerName(),
            'THEME'            => $this->getThemeName(),
            'NAME'              => $this->getFileName(),
            'STUDLY_NAME'       => $theme->getStudlyName(),
            'THEME_NAMESPACE'  => $this->laravel['themes.repository']->config('namespace'),
            'PATH_VIEWS'        => GenerateConfigReader::read('views')->getPath(),
            'PATH_LANG'         => GenerateConfigReader::read('lang')->getPath(),
            'PATH_CONFIG'       => GenerateConfigReader::read('config')->getPath(),
            'MIGRATIONS_PATH'   => GenerateConfigReader::read('migration')->getPath(),
            'FACTORIES_PATH'    => GenerateConfigReader::read('factory')->getPath(),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath(): string
    {
        $path = $this->getTheme()->getPath().'/';

        $generatorPath = GenerateConfigReader::read('provider');

        return $path.$generatorPath->getPath().'/'.$this->getFileName().'.php';
    }

    /**
     * @return string
     */
    private function getFileName(): string
    {
        return Str::studly($this->argument('name'));
    }
}

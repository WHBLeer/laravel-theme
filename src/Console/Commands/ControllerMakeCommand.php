<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelTheme\Support\Config\GenerateConfigReader;
use Sanlilin\LaravelTheme\Support\Stub;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;

class ControllerMakeCommand extends GeneratorCommand
{
    use ThemeCommandTrait;

    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected string $argumentName = 'controller';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:make-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new restful controller for the specified theme.';

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getDestinationFilePath(): string
    {
        $path = $this->getTheme()->getPath().'/';

        $controllerPath = GenerateConfigReader::read('controller');

        return $path.$controllerPath->getPath().'/'.$this->getControllerName().'.php';
    }

    /**
     * @return string
     */
    protected function getTemplateContents(): string
    {
        $theme = $this->getTheme();

        return (new Stub($this->getStubName(), [
            'PLUGINNAME'        => $theme->getStudlyName(),
            'CONTROLLERNAME'    => $this->getControllerName(),
            'NAMESPACE'         => $theme->getStudlyName(),
            'CLASS_NAMESPACE'   => $this->getClassNamespace($theme),
            'CLASS'             => $this->getControllerNameWithoutNamespace(),
	        'LOWER_NAME'        => $theme->getLowerName(),
	        'UPPER_NAME'        => $theme->getUpperName(),
            'PLUGIN'            => $this->getThemeName(),
            'NAME'              => $this->getThemeName(),
            'STUDLY_NAME'       => $theme->getStudlyName(),
            'PLUGIN_NAMESPACE'  => $this->laravel['themes.repository']->config('namespace'),
        ]))->render();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['controller', InputArgument::REQUIRED, 'The name of the controller class.'],
            ['theme', InputArgument::OPTIONAL, 'The name of themes will be used.'],
        ];
    }

    /**
     * @return string
     */
    protected function getControllerName(): string
    {
        $controller = Str::studly($this->argument('controller'));

        if (Str::contains(strtolower($controller), 'controller') === false) {
            $controller .= 'Controller';
        }

        return $controller;
    }

    /**
     * @return array|string
     */
    private function getControllerNameWithoutNamespace()
    {
        return class_basename($this->getControllerName());
    }

    public function getDefaultNamespace(): string
    {
        $repository = $this->laravel['themes.repository'];

        return $repository->config('paths.generator.controller.namespace') ?: $repository->config('paths.generator.controller.path', 'Http/Controllers');
    }

    /**
     * Get the stub file name based on the options.
     *
     * @return string
     */
    protected function getStubName(): string
    {
        return '/controller.stub';
    }
}

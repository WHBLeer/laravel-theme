<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sanlilin\LaravelTheme\Support\Config\GenerateConfigReader;
use Sanlilin\LaravelTheme\Support\Stub;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;

class RouteProviderMakeCommand extends GeneratorCommand
{
    use ThemeCommandTrait;

    protected string $argumentName = 'theme';

    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'theme:route-provider';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Create a new route service provider for the specified theme.';

    /**
     * The command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['theme', InputArgument::OPTIONAL, 'The name of theme will be used.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when the file already exists.'],
        ];
    }

    /**
     * Get template contents.
     *
     * @return string
     */
    protected function getTemplateContents(): string
    {
        $theme = $this->getTheme();

        return (new Stub('/route-provider.stub', [
            'NAMESPACE'            => $this->getClassNamespace($theme),
            'CLASS'                => $this->getFileName(),
            'PLUGIN_NAMESPACE'     => $this->laravel['themes.repository']->config('namespace'),
            'PLUGIN'               => $this->getThemeName(),
            'CONTROLLER_NAMESPACE' => $this->getControllerNameSpace(),
            'WEB_ROUTES_PATH'      => $this->getWebRoutesPath(),
            'API_ROUTES_PATH'      => $this->getApiRoutesPath(),
            'LOWER_NAME'           => $theme->getLowerName(),
	        'UPPER_NAME'        => $theme->getUpperName(),
        ]))->render();
    }

    /**
     * @return string
     */
    private function getFileName(): string
    {
        return 'RouteServiceProvider';
    }

    /**
     * Get the destination file path.
     *
     * @return string
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
    protected function getWebRoutesPath(): string
    {
        return '/'.$this->laravel['themes.repository']->config('stubs.files.routes/web', 'Routes/web.php');
    }

    /**
     * @return string
     */
    protected function getApiRoutesPath(): string
    {
        return '/'.$this->laravel['themes.repository']->config('stubs.files.routes/api', 'Routes/api.php');
    }

    public function getDefaultNamespace(): string
    {
        $repository = $this->laravel['themes.repository'];

        return $repository->config('paths.generator.provider.namespace') ?: $repository->config('paths.generator.provider.path', 'Providers');
    }

    /**
     * @return string
     */
    private function getControllerNameSpace(): string
    {
        $repository = $this->laravel['themes.repository'];

        return str_replace('/', '\\', $repository->config('paths.generator.controller.namespace') ?: $repository->config('paths.generator.controller.path', 'Controller'));
    }
}

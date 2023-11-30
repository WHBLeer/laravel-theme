<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sanlilin\LaravelTheme\Support\Config\GenerateConfigReader;
use Sanlilin\LaravelTheme\Support\Stub;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;

class ModelMakeCommand extends GeneratorCommand
{
    use ThemeCommandTrait;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected string $argumentName = 'model';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:make-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model for the specified theme.';

    public function handle(): int
    {
        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        $this->handleOptionalMigrationOption();

        return 0;
    }

    /**
     * Create a proper migration name:
     * ProductDetail: product_details
     * Product: products.
     *
     * @return string
     */
    private function createMigrationName()
    {
        $pieces = preg_split('/(?=[A-Z])/', $this->argument('model'), -1, PREG_SPLIT_NO_EMPTY);

        $string = '';
        foreach ($pieces as $i => $piece) {
            if ($i + 1 < count($pieces)) {
                $string .= strtolower($piece).'_';
            } else {
                $string .= Str::plural(strtolower($piece));
            }
        }

        return $string;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The name of model will be created.'],
            ['theme', InputArgument::OPTIONAL, 'The name of theme will be used.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['fillable', null, InputOption::VALUE_OPTIONAL, 'The fillable attributes.', null],
            ['migration', 'm', InputOption::VALUE_NONE, 'Flag to create associated migrations', null],
        ];
    }

    /**
     * Create the migration file with the given model if migration flag was used.
     */
    private function handleOptionalMigrationOption()
    {
        if ($this->option('migration') === true) {
            $migrationName = 'create_'.$this->createMigrationName().'_table';
            $this->call('theme:make-migration', ['name' => $migrationName, 'theme' => $this->argument('theme')]);
        }
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents(): string
    {
        $theme = $this->getTheme();

        return (new Stub('/model.stub', [
            'NAME'              => $this->getModelName(),
            'FILLABLE'          => $this->getFillable(),
            'NAMESPACE'         => $this->getClassNamespace($theme),
            'CLASS'             => $this->getClass(),
            'LOWER_NAME'        => $theme->getLowerName(),
	        'UPPER_NAME'        => $theme->getUpperName(),
            'PLUGIN'            => $this->getThemeName(),
            'STUDLY_NAME'       => $theme->getStudlyName(),
            'PLUGIN_NAMESPACE'  => $this->laravel['themes.repository']->config('namespace'),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath(): string
    {
        $path = $this->getTheme()->getPath().'/';

        $modelPath = GenerateConfigReader::read('model');

        return $path.$modelPath->getPath().'/'.$this->getModelName().'.php';
    }

    /**
     * @return mixed|string
     */
    private function getModelName(): string
    {
        return Str::studly($this->argument('model'));
    }

    /**
     * @return string
     */
    private function getFillable(): string
    {
        $fillable = $this->option('fillable');

        if (! is_null($fillable)) {
            $arrays = explode(',', $fillable);

            return json_encode($arrays);
        }

        return '[]';
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        $repository = $this->laravel['themes.repository'];

        return $repository->config('paths.generator.model.namespace') ?: $repository->config('paths.generator.model.path', 'Entities');
    }
}

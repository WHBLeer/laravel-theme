<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sanlilin\LaravelTheme\Support\Config\GenerateConfigReader;
use Sanlilin\LaravelTheme\Support\Stub;
use Sanlilin\LaravelTheme\Traits\CanClearThemesCache;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;

class SeedMakeCommand extends GeneratorCommand
{
    use ThemeCommandTrait;
    use CanClearThemesCache;

    protected string $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:make-seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new seeder for the specified theme.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of seeder will be created.'],
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
            [
                'master',
                null,
                InputOption::VALUE_NONE,
                'Indicates the seeder will created is a master database seeder.',
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getTemplateContents(): string
    {
        $theme = $this->getTheme();

        return (new Stub('/seeder.stub', [
            'NAME'      => $this->getSeederName(),
            'THEME'    => $this->getThemeName(),
            'NAMESPACE' => $this->getClassNamespace($theme),
        ]))->render();
    }

    /**
     * @return \mixed
     */
    protected function getDestinationFilePath(): string
    {
        $this->clearCache();

        $path = $this->getTheme()->getPath().'/';

        $seederPath = GenerateConfigReader::read('seeder');

        return $path.$seederPath->getPath().'/'.$this->getSeederName().'.php';
    }

    /**
     * Get seeder name.
     *
     * @return string
     */
    private function getSeederName(): string
    {
        $end = $this->option('master') ? 'DatabaseSeeder' : 'TableSeeder';

        return Str::studly($this->argument('name')).$end;
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        $repository = $this->laravel['themes.repository'];

        return $repository->config('paths.generator.seeder.namespace') ?: $repository->config('paths.generator.seeder.path', 'Database/Seeders');
    }
}

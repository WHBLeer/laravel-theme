<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sanlilin\LaravelTheme\Support\Config\GenerateConfigReader;
use Sanlilin\LaravelTheme\Support\Migrations\NameParser;
use Sanlilin\LaravelTheme\Support\Migrations\SchemaParser;
use Sanlilin\LaravelTheme\Support\Stub;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;

class MigrationMakeCommand extends GeneratorCommand
{
    use ThemeCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:make-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration for the specified theme.';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The migration name will be created.'],
            ['theme', InputArgument::OPTIONAL, 'The name of theme will be created.'],
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
            ['fields', null, InputOption::VALUE_OPTIONAL, 'The specified fields table.', null],
            ['plain', null, InputOption::VALUE_NONE, 'Create plain migration.'],
        ];
    }

    /**
     * Get schema parser.
     *
     * @return SchemaParser
     */
    public function getSchemaParser(): SchemaParser
    {
        return new SchemaParser($this->option('fields'));
    }

    /**
     * @return \mixed
     *
     * @throws InvalidArgumentException
     */
    protected function getTemplateContents(): string
    {
        $parser = new NameParser($this->argument('name'));
        if ($parser->isCreate()) {
            return Stub::create('/migration/create.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields' => $this->getSchemaParser()->render(),
            ]);
        } elseif ($parser->isAdd()) {
            return Stub::create('/migration/add.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields_up' => $this->getSchemaParser()->up(),
                'fields_down' => $this->getSchemaParser()->down(),
            ]);
        } elseif ($parser->isDelete()) {
            return Stub::create('/migration/delete.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields_down' => $this->getSchemaParser()->up(),
                'fields_up' => $this->getSchemaParser()->down(),
            ]);
        } elseif ($parser->isDrop()) {
            return Stub::create('/migration/drop.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields' => $this->getSchemaParser()->render(),
            ]);
        }

        return Stub::create('/migration/plain.stub', [
            'class' => $this->getClass(),
        ]);
    }

    /**
     * @return string
     */
    protected function getDestinationFilePath(): string
    {
        $theme = $this->getTheme();

        $generatorPath = GenerateConfigReader::read('migration');

        return $theme->getPath().'/'.$generatorPath->getPath().'/'.$this->getFileName().'.php';
    }

    /**
     * @return string
     */
    private function getFileName(): string
    {
        return date('Y_m_d_His_').$this->getSchemaName();
    }

    /**
     * @return array|string
     */
    private function getSchemaName()
    {
        return $this->argument('name');
    }

    /**
     * @return string
     */
    private function getClassName(): string
    {
        return Str::studly($this->argument('name'));
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->getClassName();
    }

    /**
     * Run the command.
     */
    public function handle(): int
    {
        if (parent::handle() === E_ERROR) {
            return E_ERROR;
        }

        if (app()->environment() === 'testing') {
            return 0;
        }

        return 0;
    }
}

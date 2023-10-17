<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Sanlilin\LaravelTheme\Exceptions\FileAlreadyExistException;
use Sanlilin\LaravelTheme\Support\Generators\FileGenerator;
use Sanlilin\LaravelTheme\Support\Theme;

abstract class GeneratorCommand extends Command
{
    /**
     * The name of 'name' argument.
     *
     * @var string
     */
    protected string $argumentName = '';

    /**
     * Get template contents.
     *
     * @return string
     */
    abstract protected function getTemplateContents(): string;

    /**
     * Get the destination file path.
     *
     * @return string
     */
    abstract protected function getDestinationFilePath(): string;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = str_replace('\\', '/', $this->getDestinationFilePath());

        if (! $this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }
        $contents = $this->getTemplateContents();

        try {
            $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;
            (new FileGenerator($path, $contents))->withFileOverwrite($overwriteFile)->generate();

            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File : {$path} already exists.");

            return E_ERROR;
        }

        return 0;
    }

    /**
     * Get class name.
     *
     * @return string
     */
    public function getClass(): string
    {
        return class_basename($this->argument($this->argumentName));
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        return '';
    }

    /**
     * Get class namespace.
     *
     * @param  Theme  $theme
     * @return string
     */
    public function getClassNamespace(Theme $theme): string
    {
        $extra = str_replace($this->getClass(), '', $this->argument($this->argumentName));

        $extra = str_replace('/', '\\', $extra);

        $namespace = $this->laravel['themes.repository']->config('namespace');

        $namespace .= '\\'.$theme->getStudlyName();

        $namespace .= '\\'.$this->getDefaultNamespace();

        $namespace .= '\\'.$extra;

        $namespace = str_replace('/', '\\', $namespace);

        return trim($namespace, '\\');
    }
}

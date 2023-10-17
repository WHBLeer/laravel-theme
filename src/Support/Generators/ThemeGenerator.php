<?php

namespace Sanlilin\LaravelTheme\Support\Generators;

use Exception;
use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Sanlilin\LaravelTheme\Contracts\ActivatorInterface;
use Sanlilin\LaravelTheme\Contracts\GeneratorInterface;
use Sanlilin\LaravelTheme\Support\Config\GenerateConfigReader;
use Sanlilin\LaravelTheme\Support\Repositories\FileRepository;
use Sanlilin\LaravelTheme\Support\Stub;

class ThemeGenerator implements GeneratorInterface
{
    /**
     * The theme name will created.
     *
     * @var string
     */
    protected string $name;

    /**
     * The laravel config instance.
     *
     * @var Config|null
     */
    protected ?Config $config;

    /**
     * The laravel filesystem instance.
     *
     * @var Filesystem|null
     */
    protected ?Filesystem $filesystem;

    /**
     * The laravel console instance.
     *
     * @var Console|null
     */
    protected ?Console $console;

    /**
     * The activator instance.
     *
     * @var ActivatorInterface|null
     */
    protected ?ActivatorInterface $activator;

    /**
     * The theme instance.
     *
     * @var FileRepository|null
     */
    protected ?FileRepository $themeRepository;

    /**
     * Force status.
     *
     * @var bool
     */
    protected bool $force = false;

    /**
     * Enables the theme.
     *
     * @var bool
     */
    protected bool $isActive = false;

    /**
     * The constructor.
     *
     * @param  string  $name
     * @param  FileRepository|null  $themeRepository
     * @param  Config|null  $config
     * @param  Filesystem|null  $filesystem
     * @param  Console|null  $console
     * @param  ActivatorInterface|null  $activator
     */
    public function __construct(
        string $name,
        FileRepository $themeRepository = null,
        Config $config = null,
        Filesystem $filesystem = null,
        Console $console = null,
        ActivatorInterface $activator = null
    ) {
        $this->name = $name;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->console = $console;
        $this->themeRepository = $themeRepository;
        $this->activator = $activator;
    }

    /**
     * Set active flag.
     *
     * @param  bool  $active
     * @return $this
     */
    public function setActive(bool $active): ThemeGenerator
    {
        $this->isActive = $active;

        return $this;
    }

    /**
     * Get the name of theme will created. By default in studly case.
     *
     * @return string
     */
    public function getName(): string
    {
        return Str::studly($this->name);
    }

    /**
     * Get the laravel config instance.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Set the laravel config instance.
     *
     * @param  Config  $config
     * @return $this
     */
    public function setConfig(Config $config): ThemeGenerator
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Set the themes activator.
     *
     * @param  ActivatorInterface  $activator
     * @return $this
     */
    public function setActivator(ActivatorInterface $activator): ThemeGenerator
    {
        $this->activator = $activator;

        return $this;
    }

    /**
     * Get the laravel filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Set the laravel filesystem instance.
     *
     * @param  Filesystem  $filesystem
     * @return $this
     */
    public function setFilesystem(Filesystem $filesystem): ThemeGenerator
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get the laravel console instance.
     *
     * @return Console
     */
    public function getConsole(): Console
    {
        return $this->console;
    }

    /**
     * Set the laravel console instance.
     *
     * @param  Console  $console
     * @return $this
     */
    public function setConsole(Console $console): ThemeGenerator
    {
        $this->console = $console;

        return $this;
    }

    /**
     * Get the FileRepository instance.
     *
     * @return FileRepository $theme
     */
    public function getThemeRepository(): FileRepository
    {
        return $this->themeRepository;
    }

    /**
     * Set the theme instance.
     *
     * @param  FileRepository  $themeRepository
     * @return $this
     */
    public function setThemeRepository(FileRepository $themeRepository): ThemeGenerator
    {
        $this->themeRepository = $themeRepository;

        return $this;
    }

    /**
     * Get the list of folders will created.
     *
     * @return array
     */
    public function getFolders(): array
    {
        return $this->themeRepository->config('paths.generator');
    }

    /**
     * Get the list of files will created.
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->themeRepository->config('stubs.files');
    }

    /**
     * Set force status.
     *
     * @param  bool|int  $force
     * @return $this
     */
    public function setForce(bool $force): ThemeGenerator
    {
        $this->force = $force;

        return $this;
    }

    /**
     * Generate the theme.
     *
     * @throws Exception
     */
    public function generate(): int
    {
        $name = $this->getName();

        if ($this->themeRepository->has($name)) {
            if ($this->force) {
                $this->themeRepository->delete($name);
            } else {
                $this->console->error("Theme [{$name}] already exist!");

                return E_ERROR;
            }
        }

        $this->generateFolders();

        $this->generateThemeJsonFile();

        $this->generateFiles();

        $this->generateResources();

        $this->activator->setActiveByName($name, $this->isActive);

        $this->console->info("Theme [{$name}] created successfully.");

        return 0;
    }

    public function generateResources()
    {
        if (GenerateConfigReader::read('seeder')->generate() === true) {
            $this->console->call('theme:make-seed', [
                'name'     => $this->getName(),
                'theme'   => $this->getName(),
                '--master' => true,
            ]);
        }

        if (GenerateConfigReader::read('provider')->generate() === true) {
            $this->console->call('theme:make-provider', [
                'name'     => $this->getName().'ServiceProvider',
                'theme'   => $this->getName(),
                '--master' => true,
            ]);
            $this->console->call('theme:route-provider', [
                'theme' => $this->getName(),
            ]);
        }

        if (GenerateConfigReader::read('controller')->generate() === true) {
            $this->console->call('theme:make-controller', [
                'controller' => $this->getName().'Controller',
                'theme'     => $this->getName(),
            ]);
        }
    }

    /**
     * Generate the theme.json file.
     *
     * @throws Exception
     */
    private function generateThemeJsonFile()
    {
        $path = $this->themeRepository->getThemePath($this->getName()).'theme.json';

        if (! $this->filesystem->isDirectory($dir = dirname($path))) {
            $this->filesystem->makeDirectory($dir, 0775, true);
        }

        $this->filesystem->put($path, $this->getStubContents('json'));

        $this->console->info("Created : {$path}");
    }

    /**
     * Generate the folders.
     *
     * @throws Exception
     */
    public function generateFolders(): void
    {
        foreach ($this->getFolders() as $key => $folder) {
            $folder = GenerateConfigReader::read($key);

            if ($folder->generate() === false) {
                continue;
            }

            $path = $this->themeRepository->getThemePath($this->getName()).$folder->getPath();

            $this->filesystem->makeDirectory($path, 0755, true);
            if (config('themes.stubs.gitkeep')) {
                $this->generateGitKeep($path);
            }
        }
    }

    /**
     * Generate the files.
     *
     * @throws Exception
     */
    public function generateFiles(): void
    {
        foreach ($this->getFiles() as $stub => $file) {
            $path = $this->themeRepository->getThemePath($this->getName()).$file;

            if (! $this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($path, $this->getStubContents($stub));

            $this->console->info("Created : {$path}");
        }
    }

    /**
     * Generate git keep to the specified path.
     *
     * @param  string  $path
     */
    public function generateGitKeep(string $path): void
    {
        $this->filesystem->put($path.'/.gitkeep', '');
    }

    /**
     * Get the contents of the specified stub file by given stub name.
     *
     * @param  string  $stub
     * @return string
     */
    protected function getStubContents(string $stub): string
    {
        return (new Stub(
            '/'.$stub.'.stub',
            $this->getReplacement($stub)
        )
        )->render();
    }

    /**
     * Get array replacement for the specified stub.
     *
     * @param  string  $stub
     * @return array
     */
    protected function getReplacement(string $stub): array
    {
        $replacements = $this->themeRepository->config('stubs.replacements');

        if (! isset($replacements[$stub])) {
            return [];
        }

        $keys = $replacements[$stub];

        $replaces = [];

        if ($stub === 'json' || $stub === 'composer') {
            if (in_array('PROVIDER_NAMESPACE', $keys, true) === false) {
                $keys[] = 'PROVIDER_NAMESPACE';
            }
        }
        foreach ($keys as $key) {
            if (method_exists($this, $method = 'get'.ucfirst(Str::studly(strtolower($key))).'Replacement')) {
                $replaces[$key] = $this->$method();
            } else {
                $replaces[$key] = null;
            }
        }

        return $replaces;
    }

    /**
     * Get the theme name in lower case.
     *
     * @return string
     */
    protected function getLowerNameReplacement(): string
    {
        return strtolower($this->getName());
    }

    /**
     * Get the theme name in studly case.
     *
     * @return string
     */
    protected function getStudlyNameReplacement(): string
    {
        return $this->getName();
    }

    /**
     * Get replacement for $THEME_NAMESPACE$.
     *
     * @return string
     */
    protected function getThemeNamespaceReplacement(): string
    {
        return str_replace('\\', '\\\\', $this->themeRepository->config('namespace'));
    }

    /**
     * @return string
     */
    protected function getProviderNamespaceReplacement(): string
    {
        return str_replace('\\', '\\\\', GenerateConfigReader::read('provider')->getNamespace());
    }
}

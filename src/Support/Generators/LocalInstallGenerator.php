<?php

namespace Sanlilin\LaravelTheme\Support\Generators;

use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Sanlilin\LaravelTheme\Contracts\ActivatorInterface;
use Sanlilin\LaravelTheme\Contracts\GeneratorInterface;
use Sanlilin\LaravelTheme\Exceptions\LocalPathNotFoundException;
use Sanlilin\LaravelTheme\Exceptions\ThemeAlreadyExistException;
use Sanlilin\LaravelTheme\Support\Composer\ComposerRequire;
use Sanlilin\LaravelTheme\Support\DecompressTheme;
use Sanlilin\LaravelTheme\Support\Json;
use Sanlilin\LaravelTheme\Support\Repositories\FileRepository;

class LocalInstallGenerator implements GeneratorInterface
{
    /**
     * The theme name will created.
     *
     * @var string
     */
    protected string $localPath;

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

    public static function make(): LocalInstallGenerator
    {
        return new LocalInstallGenerator();
    }

    public function setLocalPath(string $localPath): LocalInstallGenerator
    {
        $this->localPath = $localPath;

        return $this;
    }

    public function setThemeRepository(FileRepository $themeRepository): LocalInstallGenerator
    {
        $this->themeRepository = $themeRepository;

        return $this;
    }

    public function setActivator(ActivatorInterface $activator): LocalInstallGenerator
    {
        $this->activator = $activator;

        return $this;
    }

    public function setFilesystem(Filesystem $filesystem): LocalInstallGenerator
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    public function setConsole(Console $console): LocalInstallGenerator
    {
        $this->console = $console;

        return $this;
    }

    public function setActive(bool $active): LocalInstallGenerator
    {
        $this->isActive = $active;

        return $this;
    }

    public function generate(): int
    {
        if ($this->filesystem->isDirectory($this->localPath)) {
            if (! $this->filesystem->exists("{$this->localPath}/theme.json")) {
                throw new LocalPathNotFoundException("Local Path [{$this->localPath}] does not exist!");
            }

            $themeName = Json::make("{$this->localPath}/theme.json")->get('name');

            if ($this->themeRepository->has($themeName)) {
                throw new ThemeAlreadyExistException("Theme [{$themeName}] already exists!");
            }
            $buildThemePath = $this->themeRepository->getThemePath($themeName);

            if (! $this->filesystem->isDirectory($buildThemePath)) {
                $this->filesystem->makeDirectory($buildThemePath, 0775, true);
            }
            $this->filesystem->copyDirectory(
                $this->localPath,
                $buildThemePath
            );
        } elseif ($this->filesystem->isFile($this->localPath) && $this->filesystem->extension($this->localPath) === 'zip') {
            $themeName = (new DecompressTheme($this->localPath))->handle();
        }

        $this->activator->setActiveByName($themeName, $this->isActive);

        $this->console->info("Theme [{$themeName}] created successfully.");

        $theme = $this->themeRepository->findOrFail($themeName);

        ComposerRequire::make()
            ->appendThemeRequires(
                $themeName,
                $theme->getComposerAttr('require')
            )->appendThemeDevRequires(
                $themeName,
                $theme->getComposerAttr('require-dev')
            )->run();

        $theme->fireInstalledEvent();

        return 0;
    }
}

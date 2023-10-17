<?php

namespace Sanlilin\LaravelTheme\Support\Repositories;

use Exception;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\Process\Process;
use Sanlilin\LaravelTheme\Contracts\RepositoryInterface;
use Sanlilin\LaravelTheme\Exceptions\InvalidAssetPath;
use Sanlilin\LaravelTheme\Exceptions\ThemeNotFoundException;
use Sanlilin\LaravelTheme\Support\Collection;
use Sanlilin\LaravelTheme\Support\Json;
use Sanlilin\LaravelTheme\Support\Theme;
use Sanlilin\LaravelTheme\Support\Process\Installer;
use Sanlilin\LaravelTheme\Support\Process\Updater;
use Sanlilin\LaravelTheme\ValueObjects\ValRequires;

class FileRepository implements RepositoryInterface
{
    use Macroable;

    /**
     * @var Application
     */
    protected Application $app;

    /**
     * The theme path.
     *
     * @var string|null
     */
    protected ?string $path;

    /**
     * The scanned paths.
     *
     * @var array
     */
    protected array $paths = [];

    /**
     * @var string
     */
    protected string $stubPath;

    /**
     * @var UrlGenerator
     */
    private UrlGenerator $url;

    /**
     * @var ConfigRepository
     */
    private ConfigRepository $config;

    /**
     * @var Filesystem
     */
    private Filesystem $files;

    /**
     * @var CacheManager
     */
    private CacheManager $cache;

    /**
     * The constructor.
     *
     * @param  Application  $app
     * @param  string|null  $path
     */
    public function __construct(Application $app, string $path = null)
    {
        $this->app = $app;
        $this->path = $path;
        $this->url = $app['url'];
        $this->config = $app['config'];
        $this->files = $app['files'];
        $this->cache = $app['cache'];
    }

    /**
     * @param  mixed  ...$args
     * @return Theme
     */
    protected function createTheme(...$args): Theme
    {
        return new Theme(...$args);
    }

    /**
     * Add other theme location.
     *
     * @param  string  $path
     * @return $this
     */
    public function addLocation($path)
    {
        $this->paths[] = $path;

        return $this;
    }

    /**
     * Get all additional paths.
     *
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Get scanned themes paths.
     *
     * @return array
     */
    public function getScanPaths(): array
    {
        $paths = $this->paths;

        $paths[] = $this->getPath();

        $paths = array_map(function ($path) {
            return Str::endsWith($path, '/*') ? $path : Str::finish($path, '/*');
        }, $paths);

        return $paths;
    }

    /**
     * Get & scan all themes.
     *
     * @return array
     *
     * @throws Exception
     */
    public function scan(): array
    {
        $paths = $this->getScanPaths();

        $themes = [];

        foreach ($paths as $key => $path) {
            $manifests = $this->getFiles()->glob("{$path}/theme.json");

            is_array($manifests) || $manifests = [];

            foreach ($manifests as $manifest) {
                $name = Json::make($manifest)->get('name');

                $themes[$name] = $this->createTheme($this->app, $name, dirname($manifest));
            }
        }

        return $themes;
    }

    /**
     * Get all themes.
     *
     * @return array
     *
     * @throws Exception
     */
    public function all(): array
    {
        if (! $this->config('cache.enabled')) {
            return $this->scan();
        }

        return $this->formatCached($this->getCached());
    }

    /**
     * @param  string|null  $type
     * @return ValRequires
     *
     * @throws Exception
     */
    public function getComposerRequires(?string $type = null): ValRequires
    {
        $valRequires = ValRequires::make();

        return array_reduce($this->all(), function (ValRequires $valRequires, Theme $theme) use ($type) {
            $requires = $type ? $theme->getComposerAttr($type) : $theme->getAllComposerRequires();

            return $valRequires->merge($requires);
        }, $valRequires);
    }

    /**
     * @param $name
     * @param  string|null  $type
     * @return ValRequires
     *
     * @throws Exception
     */
    public function getExceptThemeNameComposerRequires($name, ?string $type = null): ValRequires
    {
        $valRequires = ValRequires::make();

        return collect($this->all())
            ->filter(fn (Theme $theme) => is_array($name) ? ! in_array($theme->getName(), $name) : $theme->getName() !== $name)
            ->reduce(function (ValRequires $valRequires, Theme $theme) use ($type) {
                $requires = $type ? $theme->getComposerAttr($type) : $theme->getAllComposerRequires();

                return $valRequires->merge($requires);
            }, $valRequires);
    }

    /**
     * Format the cached data as array of themes.
     *
     * @param  array  $cached
     * @return array
     */
    protected function formatCached(array $cached): array
    {
        $themes = [];

        foreach ($cached as $name => $theme) {
            $path = $theme['path'];

            $themes[$name] = $this->createTheme($this->app, $name, $path);
        }

        return $themes;
    }

    /**
     * Get cached themes.
     *
     * @return array
     */
    public function getCached(): array
    {
        return $this->cache->remember($this->config('cache.key'), $this->config('cache.lifetime'), function () {
            return $this->toCollection()->toArray();
        });
    }

    /**
     * Get all themes as collection instance.
     *
     * @return Collection
     *
     * @throws Exception
     */
    public function toCollection(): Collection
    {
        return new Collection($this->scan());
    }

    /**
     * Get themes by status.
     *
     * @param  bool  $status
     * @return array
     *
     * @throws Exception
     */
    public function getByStatus(bool $status): array
    {
        $themes = [];

        /** @var Theme $theme */
        foreach ($this->all() as $name => $theme) {
            if ($theme->isStatus($status)) {
                $themes[$name] = $theme;
            }
        }

        return $themes;
    }

    /**
     * Determine whether the given themes exist.
     *
     * @param $name
     * @return bool
     *
     * @throws Exception
     */
    public function has($name): bool
    {
        return array_key_exists($name, $this->all());
    }

    /**
     * Get list of enabled themes.
     *
     * @return array
     *
     * @throws Exception
     */
    public function allEnabled(): array
    {
        return $this->getByStatus(true);
    }

    /**
     * Get list of disabled themes.
     *
     * @return array
     *
     * @throws Exception
     */
    public function allDisabled(): array
    {
        return $this->getByStatus(false);
    }

    /**
     * Get count from all themes.
     *
     * @return int
     *
     * @throws Exception
     */
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * Get all ordered themes.
     *
     * @param  string  $direction
     * @return array
     *
     * @throws Exception
     */
    public function getOrdered($direction = 'asc'): array
    {
        $themes = $this->allEnabled();

        uasort($themes, function (Theme $a, Theme $b) use ($direction) {
            if ($a->get('priority') === $b->get('priority')) {
                return 0;
            }

            if ($direction === 'desc') {
                return $a->get('priority') < $b->get('priority') ? 1 : -1;
            }

            return $a->get('priority') > $b->get('priority') ? 1 : -1;
        });

        return $themes;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path ?: $this->config('paths.themes', base_path('themes'));
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function register(): void
    {
        /** @var Theme $theme */
        foreach ($this->getOrdered() as $theme) {
            $theme->register();
        }
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function boot(): void
    {
        foreach ($this->getOrdered() as $theme) {
            $theme->boot();
        }
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function find(string $name): ?Theme
    {
        foreach ($this->all() as $theme) {
            if ($theme->getLowerName() === strtolower($name)) {
                return $theme;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function findByAlias(string $alias): ?Theme
    {
        foreach ($this->all() as $theme) {
            if ($theme->getAlias() === $alias) {
                return $theme;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function findRequirements($name): array
    {
        $requirements = [];

        $theme = $this->findOrFail($name);

        foreach ($theme->getRequires() as $requirementName) {
            $requirements[] = $this->findByAlias($requirementName);
        }

        return $requirements;
    }

    /**
     * Find a specific theme, if there return that, otherwise throw exception.
     *
     * @param $name
     * @return Theme
     *
     * @throws ThemeNotFoundException
     * @throws Exception
     */
    public function findOrFail(string $name): Theme
    {
        $theme = $this->find($name);

        if ($theme !== null) {
            return $theme;
        }

        throw new ThemeNotFoundException("Theme [{$name}] does not exist!");
    }

    /**
     * Get all theme as laravel collection instance.
     *
     * @param $status
     * @return Collection
     *
     * @throws Exception
     */
    public function collections($status = 1): Collection
    {
        return new Collection($this->getByStatus($status));
    }

    /**
     * Get theme path for a specific theme.
     *
     * @param  string  $themeName
     * @return string
     *
     * @throws Exception
     */
    public function getThemePath(string $themeName): string
    {
        try {
            return $this->findOrFail($themeName)->getPath().'/';
        } catch (ThemeNotFoundException $e) {
            return $this->getPath().'/'.Str::studly($themeName).'/';
        }
    }

    /**
     * @inheritDoc
     */
    public function assetPath(string $theme): string
    {
        return $this->config('paths.assets').'/'.$theme;
    }

    /**
     * @inheritDoc
     */
    public function config(string $key, $default = null)
    {
        return $this->config->get('themes.'.$key, $default);
    }

    /**
     * Get storage path for themes used.
     *
     * @return string
     */
    public function getUsedStoragePath(): string
    {
        $directory = storage_path('app/themes');
        if ($this->getFiles()->exists($directory) === false) {
            $this->getFiles()->makeDirectory($directory, 0777, true);
        }

        $path = storage_path('app/themes/themes.used');
        if (! $this->getFiles()->exists($path)) {
            $this->getFiles()->put($path, '');
        }

        return $path;
    }

    /**
     * Set themes used for cli session.
     *
     * @param $name
     *
     * @throws ThemeNotFoundException
     */
    public function setUsed($name)
    {
        $theme = $this->findOrFail($name);

        $this->getFiles()->put($this->getUsedStoragePath(), $theme);
    }

    /**
     * Forget the themes used for cli session.
     */
    public function forgetUsed()
    {
        if ($this->getFiles()->exists($this->getUsedStoragePath())) {
            $this->getFiles()->delete($this->getUsedStoragePath());
        }
    }

    /**
     * Get themes used for cli session.
     *
     * @return string
     *
     * @throws ThemeNotFoundException|FileNotFoundException
     */
    public function getUsedNow(): string
    {
        return $this->findOrFail($this->getFiles()->get($this->getUsedStoragePath()));
    }

    /**
     * Get laravel filesystem instance.
     *
     * @return Filesystem
     */
    public function getFiles(): Filesystem
    {
        return $this->files;
    }

    /**
     * Get themes assets path.
     *
     * @return string
     */
    public function getAssetsPath(): string
    {
        return $this->config('paths.assets');
    }

    /**
     * Get asset url from a specific themes.
     *
     * @param  string  $asset
     * @return string
     *
     * @throws InvalidAssetPath
     */
    public function asset(string $asset): string
    {
        if (Str::contains($asset, ':') === false) {
            throw InvalidAssetPath::missingThemeName($asset);
        }
        [$name, $url] = explode(':', $asset);

        $baseUrl = str_replace(public_path().DIRECTORY_SEPARATOR, '', $this->getAssetsPath());

        $url = $this->url->asset($baseUrl."/{$name}/".$url);

        return str_replace(['http://', 'https://'], '//', $url);
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(string $name): bool
    {
        return $this->findOrFail($name)->isEnabled();
    }

    /**
     * @inheritDoc
     */
    public function isDisabled(string $name): bool
    {
        return ! $this->isEnabled($name);
    }

    /**
     * Enabling a specific theme.
     *
     * @param  string  $name
     * @return void
     *
     * @throws ThemeNotFoundException
     */
    public function enable(string $name): void
    {
        $this->findOrFail($name)->enable();
    }

    /**
     * Disabling a specific theme.
     *
     * @param  string  $name
     * @return void
     *
     * @throws ThemeNotFoundException
     */
    public function disable(string $name): void
    {
        $this->findOrFail($name)->disable();
    }

    /**
     * @inheritDoc
     */
    public function delete(string $name): bool
    {
        $theme = $this->findOrFail($name);

        return $theme->delete();
    }

    /**
     * Update dependencies for the specified theme.
     *
     * @param  string  $theme
     */
    public function update(string $theme)
    {
        with(new Updater($this))->update($theme);
    }

    /**
     * Install the specified theme.
     *
     * @param  string  $name
     * @param  string  $version
     * @param  string  $type
     * @param  bool  $subtree
     * @return Process
     */
    public function install(string $name, string $version = 'dev-master', string $type = 'composer', bool $subtree = false): Process
    {
        $installer = new Installer($name, $version, $type, $subtree);

        return $installer->run();
    }

    /**
     * Get stub path.
     *
     * @return string|null
     */
    public function getStubPath(): ?string
    {
        if (isset($this->stubPath)) {
            return $this->stubPath;
        }

        if ($this->config('stubs.enabled') === true) {
            return $this->config('stubs.path') ?? __DIR__.'/../../../stubs';
        }

        return optional($this)->stubPath;
    }

    /**
     * Set stub path.
     *
     * @param  string  $stubPath
     * @return $this
     */
    public function setStubPath(string $stubPath): FileRepository
    {
        $this->stubPath = $stubPath;

        return $this;
    }
}

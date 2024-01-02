<?php

namespace Sanlilin\LaravelTheme\Support\Repositories;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Sanlilin\LaravelTheme\Enums\ThemeStatus;
use Sanlilin\LaravelTheme\Exceptions\ThemeNotFoundException;
use Sanlilin\LaravelTheme\Models\InstallTheme;
use Sanlilin\LaravelTheme\Support\Theme;

class MysqlRepository
{
    use Macroable;

    /**
     * @var Application
     */
    protected Application $app;

    /**
     * @var CacheManager
     */
    private CacheManager $cache;

    /**
     * @var ConfigRepository
     */
    private ConfigRepository $config;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $app['config'];
        $this->cache = $app['cache'];
    }

    /**
     * @param \mixed  ...$args
     * @return Theme
     */
    protected function createTheme(...$args): Theme
    {
        return new Theme(...$args);
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return InstallTheme::query();
    }

    /**
     * Get all themes.
     *
     * @return \mixed
     */
    public function all(): Collection
    {
        if (! $this->config('cache.enabled')) {
            return $this->scan();
        }

        return $this->getCached();
    }

    /**
     * Get cached themes.
     *
     * @return array
     */
    public function getCached(): array
    {
        return $this->cache->remember($this->config('cache.key'), $this->config('cache.lifetime'), function () {
            return $this->scan();
        });
    }

    /**
     * Scan & get all available themes.
     *
     * @return Collection
     */
    public function scan(): Collection
    {
        return $this->query()->get();
    }

    /**
     * Get theme as themes collection instance.
     *
     * @return Collection
     */
    public function toCollection(): Collection
    {
        return new Collection([]);
    }

    /**
     * Get scanned paths.
     *
     * @return array
     */
    public function getScanPaths(): array
    {
        return [];
    }

    /**
     * Get list of enabled themes.
     *
     * @return Collection
     */
    public function allEnabled(): Collection
    {
        return $this->getByStatus(ThemeStatus::enable());
    }

    /**
     * Get list of disabled themes.
     *
     * @return \mixed
     */
    public function allDisabled()
    {
    }

    /**
     * Get count from all themes.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->all()->count();
    }

    /**
     * Get all ordered themes.
     *
     * @param  string  $direction
     * @return \mixed
     */
    public function getOrdered($direction = 'asc')
    {
    }

    /**
     * Get themes by the given status.
     *
     * @param  ThemeStatus  $status
     * @return Collection
     */
    public function getByStatus(ThemeStatus $status): Collection
    {
        return $this->all()->filter(fn (InstallTheme $theme) => $status->equals($theme->status));
    }

    /**
     * Find a specific theme.
     *
     * @param  string  $name
     * @return InstallTheme|null
     */
    public function find(string $name): ?InstallTheme
    {
        return $this->all()->first(fn (InstallTheme $theme) => $theme->lower_name === strtolower($name));
    }

    /**
     * Find all themes that are required by a theme. If the theme cannot be found, throw an exception.
     *
     * @param $name
     * @return array
     */
    public function findRequirements($name): array
    {
        return [];
    }

    /**
     * Find a specific theme. If there return that, otherwise throw exception.
     *
     * @param $name
     * @return InstallTheme
     *
     * @throws ThemeNotFoundException
     */
    public function findOrFail(string $name): InstallTheme
    {
        $theme = $this->all()->first(fn (InstallTheme $theme) => $theme->lower_name === strtolower($name));

        if ($theme !== null) {
            return $theme;
        }

        throw new ThemeNotFoundException("Theme [{$name}] does not exist!");
    }

    /**
     * @param  string  $themeName
     * @return string
     */
    public function getThemePath(string $themeName): string
    {
        return '';
    }

    /**
     * @return Filesystem
     */
    public function getFiles(): Filesystem
    {
        return new Filesystem();
    }

    /**
     * Get a specific config data from a configuration file.
     *
     * @param  string  $key
     * @param  string|null  $default
     * @return \mixed
     */
    public function config(string $key, $default = null)
    {
        return $this->config->get('themes.'.$key, $default);
    }

    /**
     * Get a theme path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->config('paths.themes', base_path('themes'));
    }

    /**
     * Find a specific theme by its alias.
     *
     * @param  string  $alias
     * @return InstallTheme|null
     */
    public function findByAlias(string $alias): ?InstallTheme
    {
        return $this->all()->first(fn (InstallTheme $theme) => strtolower($theme->alias) === strtolower($alias));
    }

    /**
     * Boot the themes.
     */
    public function boot(): void
    {
    }

    /**
     * Register the themes.
     */
    public function register(): void
    {
    }

    /**
     * Get asset path for a specific theme.
     *
     * @param  string  $name
     * @return string
     */
    public function assetPath(string $name): string
    {
        return '';
    }

    /**
     * Delete a specific theme.
     *
     * @param  string  $name
     * @return bool
     *
     * @throws ThemeNotFoundException
     */
    public function delete(string $name): bool
    {
        return true;
    }

    /**
     * Determine whether the given theme is activated.
     *
     * @param  string  $name
     * @return bool
     *
     * @throws ThemeNotFoundException
     */
    public function isEnabled(string $name): bool
    {
        return true;
    }

    /**
     * Determine whether the given theme is not activated.
     *
     * @param  string  $name
     * @return bool
     *
     * @throws ThemeNotFoundException
     */
    public function isDisabled(string $name): bool
    {
        return true;
    }
}

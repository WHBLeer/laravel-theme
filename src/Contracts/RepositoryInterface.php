<?php

namespace Sanlilin\LaravelTheme\Contracts;

use Illuminate\Filesystem\Filesystem;
use Sanlilin\LaravelTheme\Exceptions\ThemeNotFoundException;
use Sanlilin\LaravelTheme\Support\Collection;
use Sanlilin\LaravelTheme\Support\Theme;

interface RepositoryInterface
{
    /**
     * Get all themes.
     *
     * @return \mixed
     */
    public function all();

    /**
     * Get cached themes.
     *
     * @return array
     */
    public function getCached(): array;

    /**
     * Scan & get all available themes.
     *
     * @return array
     */
    public function scan();

    /**
     * Get theme as themes collection instance.
     *
     * @return Collection
     */
    public function toCollection(): Collection;

    /**
     * Get scanned paths.
     *
     * @return array
     */
    public function getScanPaths(): array;

    /**
     * Get list of enabled themes.
     *
     * @return \mixed
     */
    public function allEnabled();

    /**
     * Get list of disabled themes.
     *
     * @return \mixed
     */
    public function allDisabled();

    /**
     * Get count from all themes.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get all ordered themes.
     *
     * @param  string  $direction
     * @return \mixed
     */
    public function getOrdered($direction = 'asc');

    /**
     * Get themes by the given status.
     *
     * @param  bool  $status
     * @return array
     */
    public function getByStatus(bool $status): array;

    /**
     * Find a specific theme.
     *
     * @param $name
     * @return Theme|null
     */
    public function find(string $name): ?Theme;

    /**
     * Find all themes that are required by a theme. If the theme cannot be found, throw an exception.
     *
     * @param $name
     * @return array
     *
     * @throws ThemeNotFoundException
     */
    public function findRequirements($name): array;

    /**
     * Find a specific theme. If there return that, otherwise throw exception.
     *
     * @param $name
     * @return Theme
     */
    public function findOrFail(string $name): Theme;

    /**
     * @param  string  $themeName
     * @return string
     */
    public function getThemePath(string $themeName): string;

    /**
     * @return Filesystem
     */
    public function getFiles(): Filesystem;

    /**
     * Get a specific config data from a configuration file.
     *
     * @param  string  $key
     * @param  string|null  $default
     * @return \mixed
     */
    public function config(string $key, $default = null);

    /**
     * Get a theme path.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Find a specific theme by its alias.
     *
     * @param  string  $alias
     * @return Theme|null
     */
    public function findByAlias(string $alias): ?Theme;

    /**
     * Boot the themes.
     */
    public function boot(): void;

    /**
     * Register the themes.
     */
    public function register(): void;

    /**
     * Get asset path for a specific theme.
     *
     * @param  string  $name
     * @return string
     */
    public function assetPath(string $name): string;

    /**
     * Delete a specific theme.
     *
     * @param  string  $name
     * @return bool
     *
     * @throws ThemeNotFoundException
     */
    public function delete(string $name): bool;

    /**
     * Determine whether the given theme is activated.
     *
     * @param  string  $name
     * @return bool
     *
     * @throws ThemeNotFoundException
     */
    public function isEnabled(string $name): bool;

    /**
     * Determine whether the given theme is not activated.
     *
     * @param  string  $name
     * @return bool
     *
     * @throws ThemeNotFoundException
     */
    public function isDisabled(string $name): bool;
}

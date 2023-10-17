<?php

namespace Sanlilin\LaravelTheme\Contracts;

use Sanlilin\LaravelTheme\Support\Theme;

interface ActivatorInterface
{
    /**
     * Enables a Theme.
     *
     * @param  Theme  $theme
     */
    public function enable(Theme $theme): void;

    /**
     * Disables a Theme.
     *
     * @param  Theme  $theme
     */
    public function disable(Theme $theme): void;

    /**
     * Determine whether the given status same with a Theme status.
     *
     * @param  Theme  $theme
     * @param  bool  $status
     * @return bool
     */
    public function hasStatus(Theme $theme, bool $status): bool;

    /**
     * Set active state for a Theme.
     *
     * @param  Theme  $theme
     * @param  bool  $active
     */
    public function setActive(Theme $theme, bool $active): void;

    /**
     * Sets a Theme status by its name.
     *
     * @param  string  $name
     * @param  bool  $active
     */
    public function setActiveByName(string $name, bool $active): void;

    /**
     * Deletes a Theme activation status.
     *
     * @param  Theme  $theme
     */
    public function delete(Theme $theme): void;

    /**
     * Deletes any Theme activation statuses created by this class.
     */
    public function reset(): void;
}

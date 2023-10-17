<?php

if (! function_exists('theme_path')) {
    function theme_path(string $name, string $path = ''): string
    {
        $theme = app('themes.repository')->find($name);

        return $theme->getPath().($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

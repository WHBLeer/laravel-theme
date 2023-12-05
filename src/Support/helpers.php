<?php

if (! function_exists('theme_path')) {
	function theme_path(string $name, string $path = ''): string
	{
		$theme = app('themes.repository')->find($name);

		return $theme->getPath().($path ? DIRECTORY_SEPARATOR.$path : $path);
	}
}

if (! function_exists('theme_view')) {
	function theme_view(string $path): string
	{
		$themes = collect(app('themes.repository')->all());
		$enable = $themes->first(function ($theme) {
			return $theme->isEnabled();
		});
		$theme_name = $enable->getLowerName();
		return $theme_name.'::'.$path;
	}
}

if (! function_exists('theme_asset')) {
	function theme_asset(string $src): string
	{
		$themes = collect(app('themes.repository')->all());
		$enable = $themes->first(function ($theme) {
			return $theme->isEnabled();
		});
		$theme_name = $enable->getLowerName();
		return asset("assets/theme/{$theme_name}/{$src}");
	}
}
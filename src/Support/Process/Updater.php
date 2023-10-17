<?php

namespace Sanlilin\LaravelTheme\Support\Process;

use Sanlilin\LaravelTheme\Support\Theme;

class Updater extends Runner
{
    /**
     * Update the dependencies for the specified theme by given the theme name.
     *
     * @param  string  $name
     */
    public function update(string $name)
    {
        $theme = $this->repository->findOrFail($name);

        chdir(base_path());

        $this->installRequires($theme);
        $this->installDevRequires($theme);
        $this->copyScriptsToMainComposerJson($theme);
    }

    /**
     * Check if composer should output anything.
     *
     * @return string
     */
    private function isComposerSilenced(): string
    {
        return config('themes.composer.composer-output') === false ? ' --quiet' : '';
    }

    /**
     * @param  Theme  $theme
     */
    private function installRequires(Theme $theme)
    {
        $packages = $theme->getComposerAttr('require', []);

        $concatenatedPackages = '';
        foreach ($packages as $name => $version) {
            $concatenatedPackages .= "\"{$name}:{$version}\" ";
        }

        if (! empty($concatenatedPackages)) {
            $this->run("composer require {$concatenatedPackages}{$this->isComposerSilenced()}");
        }
    }

    /**
     * @param  Theme  $theme
     */
    private function installDevRequires(Theme $theme)
    {
        $devPackages = $theme->getComposerAttr('require-dev', []);

        $concatenatedPackages = '';
        foreach ($devPackages as $name => $version) {
            $concatenatedPackages .= "\"{$name}:{$version}\" ";
        }

        if (! empty($concatenatedPackages)) {
            $this->run("composer require --dev {$concatenatedPackages}{$this->isComposerSilenced()}");
        }
    }

    /**
     * @param  Theme  $theme
     */
    private function copyScriptsToMainComposerJson(Theme $theme)
    {
        $scripts = $theme->getComposerAttr('scripts', []);

        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        foreach ($scripts as $key => $script) {
            if (array_key_exists($key, $composer['scripts'])) {
                $composer['scripts'][$key] = array_unique(array_merge($composer['scripts'][$key], $script));
                continue;
            }
            $composer['scripts'] = array_merge($composer['scripts'], [$key => $script]);
        }

        file_put_contents(base_path('composer.json'), json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
}

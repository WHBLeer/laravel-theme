<?php

namespace Sanlilin\LaravelTheme\Support\Composer;

use Sanlilin\LaravelTheme\Exceptions\ComposerException;
use Sanlilin\LaravelTheme\ValueObjects\ValRequires;

class ComposerRequire extends Composer
{
    protected array $themeRequires = [];
    protected array $themeDevRequires = [];

    public function appendThemeRequires($themeName, ValRequires $requires): self
    {
        $this->themeRequires[$themeName] = $requires;

        return $this;
    }

    public function appendThemeDevRequires($themeName, ValRequires $devRequires): self
    {
        $this->themeDevRequires[$themeName] = $devRequires;

        return $this;
    }

    public function getThemeRequires(): array
    {
        return $this->themeRequires;
    }

    public function getThemeDevRequires(): array
    {
        return $this->themeDevRequires;
    }

    public function getRequiresByThemes(): ValRequires
    {
        $valRequires = ValRequires::make();

        return array_reduce($this->getThemeRequires(), fn (ValRequires $valRequires, ValRequires $requires) => $valRequires->merge($requires), $valRequires);
    }

    public function getDevRequiresByThemes(): ValRequires
    {
        $valRequires = ValRequires::make();

        return array_reduce($this->getThemeDevRequires(), fn (ValRequires $valRequires, ValRequires $devRequires) => $valRequires->merge($devRequires), $valRequires);
    }

    public function beforeRun(): void
    {
        if ($this->getThemeRequires()) {
            $this->appendRequires($this->getRequiresByThemes());
        }

        if ($this->getThemeDevRequires()) {
            $this->appendDevRequires($this->getDevRequiresByThemes());
        }
    }

    public function afterRun(): void
    {
        $failedrequires = $this->filterExistRequires($this->getRequires()->merge($this->getDevRequires()));

        if ($failedrequires->notEmpty()) {
            throw new ComposerException("Package {$failedrequires}require failed");
        }
    }
}

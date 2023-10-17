<?php

namespace Sanlilin\LaravelTheme\Support\Composer;

use Sanlilin\LaravelTheme\Exceptions\ComposerException;
use Sanlilin\LaravelTheme\ValueObjects\ValRequires;

class ComposerRemove extends Composer
{
    protected array $removeThemeRequires = [];

    public function appendRemoveThemeRequires($themeName, ValRequires $removeRequires): self
    {
        $currentTheme = $this->repository->findOrFail($themeName);
        $notRemoveRequires = $removeRequires->notIn($currentTheme->getAllComposerRequires());

        if ($notRemoveRequires->notEmpty()) {
            throw new ComposerException("Package $notRemoveRequires is not in the theme $themeName.");
        }

        $this->removeThemeRequires[$currentTheme->getName()] = $removeRequires;

        return $this;
    }

    /**
     * @return array
     */
    public function getRemoveThemeRequires(): array
    {
        return $this->removeThemeRequires;
    }

    /**
     * @return ValRequires
     */
    public function getRemoveRequiresByThemes(): ValRequires
    {
        $themeNames = array_keys($this->getRemoveThemeRequires());

        $valRequires = ValRequires::make();
        $removeThemeRequires = array_reduce($this->getRemoveThemeRequires(), function (ValRequires $valRequires, ValRequires $removeThemeRequires) {
            return $valRequires->merge($removeThemeRequires);
        }, $valRequires);

        if ($relyOtherThemeRemoveRequires = $this->repository->getExceptThemeNameComposerRequires($themeNames)) {
            return $removeThemeRequires->notIn($relyOtherThemeRemoveRequires);
        }

        return $removeThemeRequires;
    }

    public function beforeRun(): void
    {
        if ($this->getRemoveThemeRequires()) {
            $this->appendRemoveRequires($this->getRemoveRequiresByThemes());
        }
    }

    public function afterRun(): void
    {
        $failedrequires = $this->getRemoveRequires()->in($this->getExistRequires())->unique();

        if ($failedrequires->notEmpty()) {
            throw new ComposerException("Package {$failedrequires} remove failed");
        }
    }
}

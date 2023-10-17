<?php

namespace Sanlilin\LaravelTheme\Support;

use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Sanlilin\LaravelTheme\Exceptions\CompressThemeException;
use ZipArchive;

class CompressTheme
{
    /**
     * @var Theme
     */
    protected Theme $theme;

    /**
     * CompressTheme constructor.
     *
     * @param  Theme  $theme
     */
    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return bool
     *
     * @throws CompressThemeException
     */
    public function handle(): bool
    {
        if (! $this->theme->getFiles()->isDirectory($this->theme->getCompressDirectoryPath())) {
            $this->theme->getFiles()->makeDirectory($this->theme->getCompressDirectoryPath(), 0775, true);
        }
        if (PHP_OS == 'Darwin') {
            $this->compressThemeOnMac();

            $this->ensureArchiveIsWithinSizeLimits();

            return true;
        }

        $compressFiles = Finder::create()
            ->in($this->theme->getPath())
            ->files()
            ->ignoreVcs(true)
            ->ignoreDotFiles(false);

        $archive = new ZipArchive();

        $archive->open($this->theme->getCompressFilePath(), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($compressFiles as $compressFile) {
            $relativePathName = str_replace('\\', '/', $compressFile->getRelativePathname());

            $archive->addFile($compressFile->getRealPath(), $relativePathName);

            $archive->setExternalAttributesName(
                $relativePathName,
                ZipArchive::OPSYS_UNIX,
                ($this->getPermissions($compressFile) & 0xFFFF) << 16
            );
        }
        $archive->close();

        $this->ensureArchiveIsWithinSizeLimits();

        return true;
    }

    protected function compressThemeOnMac(): void
    {
        (new Process(['zip', '-r', $this->theme->getCompressFilePath(), '.'], $this->theme->getPath()))->mustRun();
    }

    protected function ensureArchiveIsWithinSizeLimits(): void
    {
        $size = ceil(filesize($this->theme->getCompressFilePath()) / 1048576);

        if ($size > 250) {
            throw new CompressThemeException('Application is greater than 250MB. Your application is '.$size.'MB.');
        }
    }

    /**
     * Get the proper file permissions for the file.
     *
     * @param  SplFileInfo  $file
     * @return int
     */
    protected function getPermissions(SplFileInfo $file): int
    {
        return $file->isDir() || $file->getFilename() == 'php'
            ? 33133  // '-r-xr-xr-x'
            : fileperms($file->getRealPath());
    }
}

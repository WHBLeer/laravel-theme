<?php

namespace Sanlilin\LaravelTheme\Support;

use Illuminate\Filesystem\Filesystem;
use Sanlilin\LaravelTheme\Contracts\RepositoryInterface;
use Sanlilin\LaravelTheme\Exceptions\DecompressThemeException;

class DecompressTheme
{
    /**
     * @var string
     */
    protected string $compressPath;

    protected RepositoryInterface $repository;

    protected string $tmpDecompressPath;

    protected Filesystem $filesystem;

    public function __construct(string $compressPath)
    {
        $this->compressPath = $compressPath;
        $this->repository = app('themes.repository');
        $this->tmpDecompressPath = dirname($this->compressPath).'/.tmp';
        $this->filesystem = app('files');
    }

    public function handle(): ?string
    {
        $archive = new \ZipArchive();

        $op = $archive->open($this->compressPath);

        if ($op !== true) {
            return null;
        }

        $archive->extractTo($this->tmpDecompressPath);

        $archive->close();

        $this->filesystem->moveDirectory($this->tmpDecompressPath, $decompressPath = $this->getDecompressPath(), true);

        return basename($decompressPath);
    }

    public function getDecompressPath(): string
    {
        if (! $this->filesystem->exists("{$this->tmpDecompressPath}/theme.json")) {
            throw new DecompressThemeException('Theme parsing error.');
        }

        $plugName = Json::make("{$this->tmpDecompressPath}/theme.json")->get('name');

        $decompressPath = $this->repository->getThemePath($plugName);

        if (! $this->filesystem->isDirectory($decompressPath)) {
            $this->filesystem->makeDirectory($decompressPath, 0775, true);
        }

        return $decompressPath;
    }

    public function __destruct()
    {
        if ($this->filesystem->isDirectory($this->tmpDecompressPath)) {
            $this->filesystem->delete($this->tmpDecompressPath);
        }
    }
}

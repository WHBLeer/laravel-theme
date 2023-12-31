<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelTheme\Support\Composer\ComposerRemove;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;

class ThemeDeleteCommand extends Command
{
    use ThemeCommandTrait;

    protected $name = 'theme:delete';

    protected $description = 'Delete a theme from the application';

    public function handle(): int
    {
        try {
            ComposerRemove::make()->appendRemoveThemeRequires(
                $this->getThemeName(),
                $this->getTheme()->getAllComposerRequires()
            )->run();

	        // 删除主题创建的资源软链
	        $linkPath = public_path('assets/theme/'.$this->getTheme()->getLowerName());
	        if (file_exists($linkPath) || is_link($linkPath)) {
		        $this->laravel->make('files')->delete($linkPath);
	        }
	        // 删除主题注册
	        $this->laravel['themes.repository']->delete($this->argument('theme'));

	        $this->info("Theme {$this->argument('theme')} has been deleted.");

            return 0;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        }
    }

    protected function getArguments(): array
    {
        return [
            ['theme', InputArgument::REQUIRED, 'The name of theme to delete.'],
        ];
    }
}

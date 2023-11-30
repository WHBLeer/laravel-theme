<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelTheme\Support\Theme;

class EnableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'theme:enable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Enable the specified theme.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		/**
		 * check if user entred an argument.
		 */
		$this->disableAll();

		/** @var Theme $theme */
		$theme = $this->laravel['themes.repository']->findOrFail($this->argument('theme'));
		$theme->enable();

		$this->info("Theme [{$theme}] enabled successful.");

		return 0;
	}

	/**
	 * disableAll.
	 *
	 * @return void
	 */
	public function disableAll(): void
	{
		$themes = $this->laravel['themes.repository']->all();
		/** @var Theme $theme */
		foreach ($themes as $theme) {
			if ($theme->isEnabled()) {
				$theme->disable();

				$this->info("Theme [{$theme}] disabled successful.");
			} else {
				$this->comment("Theme [{$theme}] has already disabled.");
			}
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments(): array
	{
		return [
			['theme', InputArgument::OPTIONAL, 'Theme name.'],
		];
	}
}

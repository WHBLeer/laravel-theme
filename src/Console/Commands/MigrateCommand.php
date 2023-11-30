<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sanlilin\LaravelTheme\Support\Migrations\Migrator;
use Sanlilin\LaravelTheme\Support\Theme;

class MigrateCommand extends Command
{
	/**
	 * @var string
	 */
	protected $name = 'theme:migrate';

	/**
	 * @var string
	 */
	protected $description = 'Migrate the migrations from the specified theme or from all themes.';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		$name = $this->argument('theme');

		if ($name) {
			$theme = $this->laravel['themes.repository']->findOrFail($name);

			$this->migrate($theme);
			$this->seeder($theme);
			return 0;
		}
		/** @var Theme $theme */
		foreach ($this->laravel['themes.repository']->getOrdered($this->option('direction')) as $theme) {
			$this->line('Running for theme: <info>'.$theme->getName().'</info>');

			$this->migrate($theme);
			$this->seeder($theme);
		}

		return 0;
	}

	/**
	 * Run the migrate command.
	 *
	 * @param Theme $theme
	 *
	 * @author: hongbinwang
	 * @time  : 2023/10/30 10:44
	 */
	protected function migrate(Theme $theme): void
	{
		$path = str_replace(base_path(), '', (new Migrator($theme, $this->getLaravel()))->getPath());

		if ($this->option('subpath')) {
			$path = $path.'/'.$this->option('subpath');
		}

		$this->call('migrate', [
			'--path' => $path,
			'--database' => $this->option('database'),
			'--pretend' => $this->option('pretend'),
			'--force' => $this->option('force'),
		]);
	}

	/**
	 * Run the database seeder command.
	 *
	 * @param Theme $theme
	 * @return void
	 */
	protected function seeder(Theme $theme): void
	{
		if ($this->option('seed')) {
			$seederPath = theme_path($theme->getName(), '/Database/Seeders');
			$namespace = '\\Themes\\' . $theme->getName() . '\\Database\\Seeders\\';
			$files = File::allFiles($seederPath);

			foreach ($files as $file) {
				$fileName = str_replace('.php', '', $file->getBasename());
				$class = $namespace . $fileName;
				$this->call('db:seed', [
					'--class' => $class,
					'--database' => $this->option('database'),
					'--force' => $this->option('force'),
				]);
			};
		}
	}

	/**
	 * @return array[]
	 */
	protected function getArguments(): array
	{
		return [
			['theme', InputArgument::OPTIONAL, 'The name of theme will be used.'],
		];
	}

	/**
	 * @return array[]
	 */
	protected function getOptions(): array
	{
		return [
			['direction', 'd', InputOption::VALUE_OPTIONAL, 'The direction of ordering.', 'asc'],
			['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
			['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],
			['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
			['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'],
			['subpath', null, InputOption::VALUE_OPTIONAL, 'Indicate a subpath to run your migrations from'],
		];
	}
}

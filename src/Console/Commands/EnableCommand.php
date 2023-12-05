<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Exception;
use App\Models\Menu;
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
		if ($theme->config()['menu']['status']) {
			$this->reloadMenu($theme);
		}
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


	/**
	 * Reload menu
	 *
	 * @return void
	 * @throws Exception
	 */
	public function reloadMenu($theme)
	{
		Menu::where('source_by',$theme->config()['menu']['source_by'])->delete();
		// Repair tree structure
		Menu::fixTree();

		$menu_file = $theme->getPath().'/'.$theme->config()['menu']['file'];
		$menu_data = json_decode(file_get_contents($menu_file),true);
		self::generateMenuData($menu_data,$theme->config());

		// Repair tree structure
		Menu::fixTree();
	}

	/**
	 * Process the menu recursively
	 * @param $menuItems
	 * @param $config
	 * @param $parent
	 * @param $level
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/4 11:05
	 */
	private static function generateMenuData($menuItems, $config, $parent = null, $level = 0)
	{
		foreach ($menuItems as $item) {
			/**
			 * If the menu in the menu.json file of the plug-in is already in the system, it is not changed, updated, or overwritten
			 */
			$hash = self::MenuHash($item);
			if (!$node = Menu::where('hash',$hash)->first()) {
				$node = new Menu();
				$node->parent_id    = $parent ? $parent->id : null;
				$node->url          = self::GenerateUrl($item['route_name']);
				$node->route_name   = $item['route_name'];
				$node->icon         = $item['icon'] ?? 'icon-product';
				$node->params       = $item['params'] ?? null;
				$node->name         = $item['name'];
				$node->is_show      = $item['is_show'];
				$node->is_menu      = $item['is_menu'];
				$node->sort_id      = 1000 - $level;
				$node->source_by    = $config['menu']['source_by'];
				$node->hash         = $hash;
				$node->save();

				if (!$parent) {
					// All Level-1 nodes must be placed before 'SYSTEM'
					$targetNode = Menu::withDepth()->having('depth', '=', 0)->where('name', 'SYSTEM')->first();
					$node->insertBeforeNode($targetNode);
				} else {
					// Placed by dependency
					$targetNode = Menu::where('parent_id', $parent->id)->first();
					$node->appendToNode($targetNode);
				}
			}
			if (isset($item['children'])) {
				self::generateMenuData($item['children'], $config, $node, $level + 1);
			}
		}
	}

	/**
	 * @param $data
	 * @return string
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/4 10:58
	 */
	private static function MenuHash($data): string
	{
		if (isset($data['children'])) unset($data['children']);
		if (isset($data['icon'])) unset($data['icon']);

		$str = implode('_',$data);
		return hash('md5',$str);
	}

	/**
	 * @param $route
	 * @return string
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/4 10:58
	 */
	private static function GenerateUrl($route): string
	{
		if (!$route) return 'javascript:void(0);';
		return '/'.(str_replace('.','/',$route));
	}

}

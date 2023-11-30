<?php

namespace Sanlilin\LaravelTheme\Providers;

use InvalidArgumentException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Sanlilin\LaravelPlugin\Models\Menu;

class MenuServiceProvider extends ServiceProvider
{
	/**
	 * source by
	 * @var string 
	 */
	private static $source_by = 'laravel_theme';

	/**
	 * Booting the package.
	 */
	public function boot()
	{
		$this->syncMenu('insert');
	}

	/**
	 * Register the service provider.
	 */
	public function register()
	{
		$this->syncMenu('remove');
	}
	/**
	 * Sync menu data.
	 *
	 * @param  string  $type
	 * @return void|bool
	 */
	protected function syncMenu($type)
	{
		if (!Schema::hasTable('menus')) {
			return false;
		}
		switch ($type) {
			case 'insert':
				if (Menu::where('source_by', self::$source_by)->count() == 0) {
					$menu_file = __DIR__ . '/../../routes/menu.json';
					$menu_data = json_decode(file_get_contents($menu_file), true);

					self::generateMenuData($menu_data);
				}
				break;

			case 'remove':
				if (Menu::where('source_by', self::$source_by)->count() > 0) {
					Menu::where('source_by', self::$source_by)->delete();

					// Repair tree structure
					Menu::fixTree();
				}
				break;

			default:
				throw new InvalidArgumentException('Invalid operation type.');
		}
	}





	/**
	 * Process the menu recursively
	 * @param $menuItems
	 * @param $parent
	 * @param $level
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/4 11:05
	 */
	private static function generateMenuData($menuItems, $parent = null, $level = 0)
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
				$node->source_by    = self::$source_by;
				$node->hash         = $hash;
				$node->save();

				if (!$parent) {
					// All Level-1 nodes must be placed before 'SYSTEM'
					$targetNode = Menu::withDepth()->having('depth', '=', 0)->where('name', 'SYSTEM')->first();
					$node->insertBeforeNode($targetNode);
				} else {
					// Placed by dependency
					$targetNode = Menu::where('parent_id', $parent->id)->first();
					if ($targetNode->name!='SYSTEM') {
						$node->appendToNode($targetNode);
					} else {
						$node->prependToNode($targetNode);
					}
				}
			}
			if (isset($item['children'])) {
				self::generateMenuData($item['children'], $node, $level + 1);
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

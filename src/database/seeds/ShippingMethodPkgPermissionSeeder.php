<?php
namespace Abs\ShippingMethodPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class ShippingMethodPkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//FAQ
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'shipping-methods',
				'display_name' => 'Shipping Methods',
			],
			[
				'display_order' => 1,
				'parent' => 'shipping-methods',
				'name' => 'add-shipping-method',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'shipping-methods',
				'name' => 'delete-shipping-method',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'shipping-methods',
				'name' => 'delete-shipping-method',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);
	}
}
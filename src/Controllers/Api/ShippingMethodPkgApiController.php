<?php

namespace Abs\ShippingMethodPkg\Controllers\Api;

use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;
use App\Models\ShippingMethod;

class ShippingMethodPkgApiController extends BaseController {
	use CrudTrait;
	public $model = ShippingMethod::class;
	//
	//public function alterCrudInput($action, &$input) {
	//}
	//
	//public function afterSave($Model, $isNew, $input, ApiResponse $response){
	//	$index = Index::firstOrNew([
	//		'url' => $input['seo_name'],
	//		'page_type_id' => Category::$PAGE_TYPE_CONFIG_ID,
	//		'company_id' => Auth::user()->company_id,
	//	]);
	//	$index->save();
	//}

}

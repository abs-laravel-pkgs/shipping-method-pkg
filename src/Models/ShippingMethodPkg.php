<?php

namespace Abs\ShippingMethodPkg\Models;

use Abs\CompanyPkg\Traits\CompanyableTrait;
use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Input;

class ShippingMethodPkg extends BaseModel {
	use CompanyableTrait;
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'shipping_methods';
	public $timestamps = true;

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
		$this->rules = [
			'name' => [
				'min:3',
				'unique:categories,name,' . Input::get('id'),
			],
			'display_order' => [
			],
			'seo_name' => [
				'required',
				'unique:categories,seo_name,' . Input::get('id'),
			],
		];

	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'delivery_time',
		'charge',
	];

	protected $casts = [
		//'has_free' => 'boolean',
		//'has_free_shipping' => 'boolean',
		//'is_best_selling' => 'boolean',
	];

	public $sortable = [
		'name',
		'display_order',
	];

	public $sortScopes = [
		//'id' => 'orderById',
		//'code' => 'orderCode',
		//'name' => 'orderBytName',
		//'mobile_number' => 'orderByMobileNumber',
		//'email' => 'orderByEmail',
	];

	// Custom attributes specified in this array will be appended to model
	protected $appends = [
		'active',
	];

	//This model's validation rules for input values
	public $rules = [
		//Defined in constructor
	];

	public $fillableRelationships = [
		'company',
		'logo',
	];

	public $relationshipRules = [
		//'image' => [
		//	'required',
		//],
	];

	// Relationships to auto load
	public static function relationships($action = '', $format = ''): array
	{
		$relationships = [];

		if ($action === 'index') {
			$relationships = array_merge($relationships, [
				'logo',
			]);
		}
		else if ($action === 'read') {
			$relationships = array_merge($relationships, [
				'logo',
			]);
		}
		else if ($action === 'save') {
			$relationships = array_merge($relationships, [
			]);
		}
		else if ($action === 'options') {
			$relationships = array_merge($relationships, [
			]);
		}

		return $relationships;
	}

	public static function appendRelationshipCounts($action = '', $format = ''): array
	{
		$relationships = [];

		if ($action === 'index') {
			$relationships = array_merge($relationships, [
			]);
		} else if ($action === 'options') {
			$relationships = array_merge($relationships, [
			]);
		}

		return $relationships;
	}

	// Dynamic Attributes --------------------------------------------------------------
	public function getActiveAttribute(): bool
	{
		return !isset($this->attributes['deleted_at']) || !$this->attributes['deleted_at'];
	}

	// Relationships --------------------------------------------------------------

	public function logo(): BelongsTo {
		return $this->belongsTo('Abs\BasicPkg\Attachment', 'logo_id');
	}

	//--------------------- Query Scopes -------------------------------------------------------
	public function scopeFilterSearch($query, $term): void {
		if ($term !== '') {
			$query->where(function ($query) use ($term) {
				$query->orWhere('name', 'LIKE', '%' . $term . '%');
			});
		}
	}

	public static function createFromObject($record_data, $company = null) {

		$errors = [];
		if (!$company) {
			$company = \App\Models\Company::where('code', $record_data->company_code)->first();
		}
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company_code);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->shipping_method,
		]);
		// $record->display_order = $record_data->display_order;
		$record->delivery_time = $record_data->delivery_time;
		$record->charge = $record_data->charge;
		if ($record_data->status != 1) {
			$record->deleted_at = date('Y-m-d');
		}
		$record->save();
	}
}

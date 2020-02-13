<?php

namespace Abs\ShippingMethodPkg;
use Abs\Basic\Attachment;
use Abs\ShippingMethodPkg\ShippingMethod;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ShippingMethodController extends Controller {

	private $company_id;
	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
		$this->company_id = config('custom.company_id');
	}

	public function getShippingMethods(Request $request) {
		$this->data['shipping_methods'] = ShippingMethod::
			select([
			'shipping_methods.question',
			'shipping_methods.answer',
		])
			->where('shipping_methods.company_id', $this->company_id)
			->orderby('shipping_methods.display_order', 'asc')
			->get()
		;
		$this->data['success'] = true;

		return response()->json($this->data);

	}

	public function getShippingMethodList(Request $request) {
		$shipping_methods = ShippingMethod::withTrashed()
			->select([
				'shipping_methods.*',
				DB::raw('IF(shipping_methods.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('shipping_methods.company_id', $this->company_id)
		/*->where(function ($query) use ($request) {
				if (!empty($request->question)) {
					$query->where('shipping_methods.question', 'LIKE', '%' . $request->question . '%');
				}
			})*/
			->orderby('shipping_methods.id', 'desc');

		return Datatables::of($shipping_methods)
			->addColumn('name', function ($shipping_methods) {
				$status = $shipping_methods->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $shipping_methods->name;
			})
			->addColumn('action', function ($shipping_methods) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/shipping-method-pkg/shipping-method/edit/' . $shipping_methods->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="javascript:;" data-toggle="modal" data-target="#shipping-method-delete-modal" onclick="angular.element(this).scope().deleteShippingMethod(' . $shipping_methods->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
					';
				return $output;
			})
			->make(true);
	}

	public function getShippingMethodFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$shipping_method = new ShippingMethod;
			$attachment = new Attachment;
			$action = 'Add';
		} else {
			$shipping_method = ShippingMethod::withTrashed()->find($id);
			$attachment = Attachment::where('id', $shipping_method->logo_id)->first();
			$action = 'Edit';
		}
		$this->data['shipping_method'] = $shipping_method;
		$this->data['attachment'] = $attachment;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveShippingMethod(Request $request) {
		//dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'delivery_time.required' => 'Delivery Time is Required',
				'charge.required' => 'Charge is Required',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'unique:shipping_methods,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'delivery_time' => 'required',
				'charge' => 'required',
				'logo_id' => 'mimes:jpeg,jpg,png,gif,ico,bmp,svg|nullable|max:10000',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$shipping_method = new ShippingMethod;
				$shipping_method->created_by_id = Auth::user()->id;
				$shipping_method->created_at = Carbon::now();
				$shipping_method->updated_at = NULL;
			} else {
				$shipping_method = ShippingMethod::withTrashed()->find($request->id);
				$shipping_method->updated_by_id = Auth::user()->id;
				$shipping_method->updated_at = Carbon::now();
			}
			$shipping_method->fill($request->all());
			$shipping_method->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$shipping_method->deleted_at = Carbon::now();
				$shipping_method->deleted_by_id = Auth::user()->id;
			} else {
				$shipping_method->deleted_by_id = NULL;
				$shipping_method->deleted_at = NULL;
			}
			$shipping_method->save();

			if (!empty($request->logo_id)) {
				if (!File::exists(public_path() . '/themes/' . config('custom.admin_theme') . '/img/shipping_method_logo')) {
					File::makeDirectory(public_path() . '/themes/' . config('custom.admin_theme') . '/img/shipping_method_logo', 0777, true);
				}

				$attacement = $request->logo_id;
				$remove_previous_attachment = Attachment::where([
					'entity_id' => $request->id,
					'attachment_of_id' => 20,
				])->first();
				if (!empty($remove_previous_attachment)) {
					$remove = $remove_previous_attachment->forceDelete();
					$img_path = public_path() . '/themes/' . config('custom.admin_theme') . '/img/shipping_method_logo/' . $remove_previous_attachment->name;
					if (File::exists($img_path)) {
						File::delete($img_path);
					}
				}
				$random_file_name = $shipping_method->id . '_shipping_method_file_' . rand(0, 1000) . '.';
				$extension = $attacement->getClientOriginalExtension();
				$attacement->move(public_path() . '/themes/' . config('custom.admin_theme') . '/img/shipping_method_logo', $random_file_name . $extension);

				$attachment = new Attachment;
				$attachment->company_id = Auth::user()->company_id;
				$attachment->attachment_of_id = 20; //User
				$attachment->attachment_type_id = 40; //Primary
				$attachment->entity_id = $shipping_method->id;
				$attachment->name = $random_file_name . $extension;
				$attachment->save();
				$shipping_method->logo_id = $attachment->id;
				$shipping_method->save();
			}

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Shipping Method Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Shipping Method Updated Successfully',
				]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}
	}

	public function deleteShippingMethod(Request $request) {
		DB::beginTransaction();
		try {
			$shipping_method = ShippingMethod::withTrashed()->where('id', $request->id)->first();
			if (!is_null($shipping_method->logo_id)) {
				Attachment::where('company_id', Auth::user()->company_id)->where('attachment_of_id', 20)->where('entity_id', $request->id)->forceDelete();
			}
			ShippingMethod::withTrashed()->where('id', $request->id)->forceDelete();

			DB::commit();
			return response()->json(['success' => true, 'message' => 'Shipping Method Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}

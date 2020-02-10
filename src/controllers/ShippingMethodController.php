<?php

namespace Abs\ShippingMethodPkg;
use Abs\ShippingMethodPkg\ShippingMethod;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
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
				'shipping_methods.id',
				'shipping_methods.question',
				DB::raw('shipping_methods.deleted_at as status'),
			])
			->where('shipping_methods.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->question)) {
					$query->where('shipping_methods.question', 'LIKE', '%' . $request->question . '%');
				}
			})
			->orderby('shipping_methods.id', 'desc');

		return Datatables::of($shipping_methods)
			->addColumn('question', function ($shipping_method) {
				$status = $shipping_method->status ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $shipping_method->question;
			})
			->addColumn('action', function ($shipping_method) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img2 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$img2_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/shipping-method-pkg/shipping_method/edit/' . $shipping_method->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="#!/shipping-method-pkg/shipping_method/view/' . $shipping_method->id . '" id = "" ><img src="' . $img2 . '" alt="View" class="img-responsive" onmouseover=this.src="' . $img2_active . '" onmouseout=this.src="' . $img2 . '"></a>
					<a href="javascript:;"  data-toggle="modal" data-target="#shipping_method-delete-modal" onclick="angular.element(this).scope().deleteShippingMethodconfirm(' . $shipping_method->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
					';
				return $output;
			})
			->make(true);
	}

	public function getShippingMethodFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$shipping_method = new ShippingMethod;
			$action = 'Add';
		} else {
			$shipping_method = ShippingMethod::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['shipping_method'] = $shipping_method;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveShippingMethod(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'ShippingMethod Code is Required',
				'code.max' => 'Maximum 255 Characters',
				'code.min' => 'Minimum 3 Characters',
				'code.unique' => 'ShippingMethod Code is already taken',
				'name.required' => 'ShippingMethod Name is Required',
				'name.max' => 'Maximum 255 Characters',
				'name.min' => 'Minimum 3 Characters',
			];
			$validator = Validator::make($request->all(), [
				'question' => [
					'required:true',
					'max:255',
					'min:3',
					'unique:shipping_methods,question,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'answer' => 'required|max:255|min:3',
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

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'FAQ Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'FAQ Updated Successfully',
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

	public function deleteShippingMethod($id) {
		$delete_status = ShippingMethod::withTrashed()->where('id', $id)->forceDelete();
		return response()->json(['success' => true]);
	}
}

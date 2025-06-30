<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\WebController;
use Yajra\DataTables\Facades\DataTables;

use App\Models\Sales;
use App\Models\SalesDetail;
use App\Models\SaleOrder;
use App\Models\SaleOrderDetail;
use App\Models\ProductModel;
use App\Models\SerialNo;
use App\Models\FinancialLogsModel;
use App\Models\Employee;
use DB;
use Ap\Models\Product;
use App\Models\StockModel;
use App\Models\Category;
use App\Models\Account;
use App\Models\Color;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Response;
use Auth;

class DeleteController extends WebController
{

    public $soObj,$soDetailObj;
    public function __construct()
    {
        $this->soObj = new Sales();
        $this->inqDetailObj = new SalesDetail();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

	/*====Delete Sale Bill ====== */
    public function saleBill($id){
		Return 'Permission Denied';
	}

	/*====Delete Sale-Order Bill ====== */
	public function saleOrderBill(){
		Return 'Permission Denied';
	}

	/*====Delete Sale Return ====== */
	public function saleReturnBill(){
		Return 'Permission Denied';
	}


	/*====Delete Sale Bill ====== */
    public function purchaseBill($id){
		Return 'Permission Denied';
	}

	/*====Delete Sale-Order Bill ====== */
	public function purchaseOrderBill(){
		Return 'Permission Denied';
	}

	/*====Delete Sale Return ====== */
	public function purchaseReturnBill(){
		Return 'Permission Denied';
	}

	/*====Delete Sale Bill ====== */
    public function inqueryBill(Request $r){
		if(!Auth::user()->can('sales_inquiry_delete'))
		{	
			return redirect()->back();
		}
		$id=$r->billid;
		$inquiry = \App\Models\Inquery::find($id);

		$user=Auth::user();
		
		$canDeleteItem = (
			$inquiry->salesman_id == $user->id ||
			in_array($user->type, ['admin', 'superadmin'])
		);
		
		if (!$canDeleteItem) {
			Toastr::error('This Inquery is created by Someone else So You are not authorized to Delete this inquiry.', 'Error');
			return redirect()->back()->with('error', 'You are not authorized to delete this inquiry.');
		}

		if(in_array($inquiry->status, [0,1, 2])){
			$inquiry->status = 5;
			$inquiry->cancel_by = Auth::user()->id;
			$inquiry->cancel_date = Carbon::now();
			$inquiry->cancel_note = $r->deleteNote.' : Deleted by '.Auth::user()->name.' | '.Carbon::now()->format('d-m-Y H:i:s');
			$inquiry->save();
			$inquiry->details()->update([
				'status' => 5
			]);
			Toastr::success('Inquiry Deleted Successfully', 'Success');
			return redirect()->back()->with('success','Inquiry Deleted Successfully');
		}
		else{
			Toastr::error('Inquiry Cant Be deleted after order generation', 'Error');
			return redirect()->back()->with('error','Inquiry Cant Be deleted after order generation');
		}
		
	}

	/*====Delete Sale-Order Bill ====== */
	public function newLeadBill(){
		Return 'Permission Denied';
	}

    
}

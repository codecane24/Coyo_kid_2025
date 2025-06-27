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
use App\Models\NewInquiry;
use App\Models\Cart;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Response;
use Auth;

class PrintController extends WebController
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

    public function printSaleBill($id)
	{
		$a['title'] = 'SALE (Estimate Form)';
		$sale= new Sales();
		$a['bill'] = $sale->getSaleDetails($id);
       return view('admin.print_preview.sale_bill')->with($a);
	}


	public function printSalePkSlip($id){

		$a['title'] = 'SALE PACKING SLIP';
        $a['odrDetail']= DB::table('tbl_sale_detail AS sl')
					->join('branches_stocks AS bst', 'bst.stock_id', '=', 'sl.stock_id')
					->join('tbl_products_stock AS st', 'st.id', '=', 'bst.stock_id')
				   ->join('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
				   ->join('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
				   ->join('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
				   ->where('sl.order_id','=',$id)
				   ->where('sl.status','=','active')
				   ->orderBy('pd.name')
				   ->select('sl.*','pd.name as prodName', 'atr.name as attrName','ct.name as catName','st.product_id','st.category_id')->get();


	   	$a['odrCatGroup']= DB::table('tbl_sale_detail AS sl')
					->join('branches_stocks AS bst', 'bst.stock_id', '=', 'sl.stock_id')
					->join('tbl_products_stock AS st', 'st.id', '=', 'bst.stock_id')
				   ->join('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
				   ->join('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
				   ->join('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
				   ->where('sl.order_id','=',$id)
				   ->where('sl.status','=','active')
				   ->orderBy('pd.name')
				   ->select('st.product_id','st.category_id','sl.sRate','sl.sDiscount','sl.taxAmt','sl.taxRate',
				   DB::raw('SUM(sl.sNetAmount) as sNetAmount'),
				   DB::raw('SUM(sl.sQty) as TotalQty'),'pd.name as prodName', 'atr.name as attrName','ct.name as catName','bst.purchase_price as pRate','st.product_id','st.category_id')
				    ->groupBy('st.product_id','st.category_id','sl.sRate')
				   ->get();

		$a['pOrder']=DB::table('tbl_sale AS so')
				   ->leftJoin('tbl_account AS ac', 'ac.id', '=', 'so.account_id')
				   ->leftJoin('users AS sm', 'sm.id', '=', 'so.salesman_id')
					 ->leftJoin('tbl_gift AS gf', 'gf.id', '=', 'so.gift_id')
				   ->where('so.id','=',$id)
				   ->select('so.*','ac.name','ac.email','ac.phone','ac.address','ac.city','ac.term_cond','ac.type as acType','ac.referred_by','sm.name as salesMan','gf.name as giftName','gf.code as giftCode','gf.image as giftImage','gf.description as giftDesc')
				   ->first();


        return view('admin.print_preview.sale_slip')->with($a);
	}

	public function printSaleOrderBill($id){

		$a['title'] = 'SALE-ORDER';
		$a['pOrder']=DB::table('tbl_sale_order AS so')
				   ->leftJoin('tbl_account AS ac', 'ac.id', '=', 'so.account_id')
				   ->leftJoin('users AS sm', 'sm.id', '=', 'so.salesman_id')
					 ->leftJoin('tbl_gift AS gf', 'gf.id', '=', 'so.gift_id')
				   ->where('so.id','=',$id)
				   ->select('so.*','ac.name','ac.email','ac.phone','ac.address','ac.city','ac.term_cond','ac.type as acType','ac.referred_by','sm.name as salesMan','gf.name as giftName','gf.code as giftCode','gf.image as giftImage','gf.description as giftDesc')
				   ->first();

		//$bill= \App\Models\SaleOrder::where('id',$id)->with('account','account:city','account:state')->first();

        $a['odrDetail']= DB::table('tbl_sale_order_detail AS sl')
					->join('branches_stocks AS bst', 'bst.stock_id', '=', 'sl.stock_id')
					->join('tbl_products_stock AS st', 'st.id', '=', 'bst.stock_id')
				   ->leftJoin('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
				   ->leftJoin('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
				   ->leftJoin('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
				   ->where('sl.order_id','=',$id)
				   ->where('sl.status','<=','1')
				   ->orderBy('pd.name')
				   ->select('sl.*','pd.name as prodName', 'atr.name as attrName','ct.name as catName','st.product_id','st.category_id')->get();

		//print_query($a['odrDetail']);

	   	$a['odrCatGroup']= DB::table('tbl_sale_order_detail AS sl')
					->join('branches_stocks AS bst', 'bst.stock_id', '=', 'sl.stock_id')
					->join('tbl_products_stock AS st', 'st.id', '=', 'bst.stock_id')
				   ->leftJoin('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
				   ->leftJoin('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
				   ->leftJoin('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
				   ->where('sl.order_id','=',$id)
				   ->where('sl.status','<=','1')
				   ->orderBy('pd.name')
				   ->select('st.product_id','st.category_id','sl.sRate','sl.sDiscount','sl.taxAmt','sl.taxRate',
				   DB::raw('SUM(sl.sNetAmount) as sNetAmount'),
				   DB::raw('SUM(sl.sQty) as TotalQty'),'pd.name as prodName', 'atr.name as attrName','ct.name as catName','bst.purchase_price as pRate','st.product_id','st.category_id')
				    ->groupBy('st.product_id','st.category_id','sl.sRate')
				   ->get();

        return view('admin.print_preview.sale_order_bill')->with($a);
	}

	public function printSaleOrderImgBill($id){

		$a['title'] = 'SALE-ORDER';
		$a['pOrder']=DB::table('tbl_sale_order AS so')
				   ->leftJoin('tbl_account AS ac', 'ac.id', '=', 'so.account_id')
				   ->leftJoin('users AS sm', 'sm.id', '=', 'so.salesman_id')
					->leftJoin('tbl_gift AS gf', 'gf.id', '=', 'so.gift_id')
				   ->where('so.id','=',$id)
				   ->select('so.*','ac.name','ac.email','ac.phone','ac.address','ac.city','ac.term_cond','ac.type as acType','ac.referred_by','sm.name as salesMan','gf.name as giftName','gf.code as giftCode','gf.image as giftImage','gf.description as giftDesc')
				   ->first();

		//$bill= \App\Models\SaleOrder::where('id',$id)->with('account','account:city','account:state')->first();

          		 $a['odrDetail']= DB::table('tbl_sale_order_detail AS sl')
				   ->join('branches_stocks AS bst', 'bst.stock_id', '=', 'sl.stock_id')
				   ->join('tbl_products_stock AS st', 'st.id', '=', 'bst.stock_id')
				   ->leftJoin('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
				   ->leftJoin('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
				   ->leftJoin('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
				   ->where('sl.order_id','=',$id)
				   ->where('sl.status','=','1')
				   ->orderBy('pd.name')
				   ->select('sl.*','pd.name as prodName', 'atr.name as attrName','ct.name as catName','st.product_id','st.category_id')->get();

		//print_query($a['odrDetail']);

	   			$a['odrCatGroup']= DB::table('tbl_sale_order_detail AS sl')
				   ->join('branches_stocks AS bst', 'bst.stock_id', '=', 'sl.stock_id')
				   ->join('tbl_products_stock AS st', 'st.id', '=', 'bst.stock_id')
				   ->leftJoin('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
				   ->leftJoin('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
				   ->leftJoin('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
				   ->leftJoin('tbl_products_image_gallery as gl', function ($join) {
							$join->on('st.product_id', '=', 'gl.product_id')
								 ->on('st.category_id', '=', 'gl.category_id');
						// ->where('users.status', '=', 'active'); // Additional condition
				})

				   ->where('sl.order_id','=',$id)
				   ->where('sl.status','=','1')
				   ->orderBy('pd.name')
				   ->select(
						'st.product_id',
						'st.category_id',
						'sl.sRate',
						'sl.sDiscount',
						'sl.taxAmt',
						'sl.taxRate',
				   		DB::raw('SUM(sl.sNetAmount) as sNetAmount'),
				   		DB::raw('SUM(sl.sQty) as TotalQty'),
						'pd.name as prodName',
						'pd.image as prodImage',
						'atr.name as attrName',
						'ct.name as catName',
						'bst.purchase_price as pRate',
						'gl.image as prodGlImage')
				    ->groupBy('st.product_id','st.category_id','sl.sRate')
				   	->get();

        return view('admin.print_preview.sale_order_bill_img')->with($a);

	}

	public function printSaleReturnBill($id){

		$a['title'] = 'SALE-Return';
		$a['pOrder']=DB::table('tbl_sale_return AS so')
				   ->leftJoin('tbl_account AS ac', 'ac.id', '=', 'so.account_id')
				   ->leftJoin('users AS sm', 'sm.id', '=', 'so.salesman_id')
					 ->leftJoin('tbl_gift AS gf', 'gf.id', '=', 'so.gift_id')
				   ->where('so.id','=',$id)
				   ->select('so.*','ac.name','ac.email','ac.phone','ac.address','ac.city','ac.term_cond','ac.type as acType','ac.referred_by','sm.name as salesMan','gf.name as giftName','gf.code as giftCode','gf.image as giftImage','gf.description as giftDesc')
				   ->first();

		//$bill= \App\Models\SaleOrder::where('id',$id)->with('account','account:city','account:state')->first();

         	$a['odrDetail']= DB::table('tbl_sale_return_detail AS sl')
					->join('branches_stocks AS bst', 'bst.stock_id', '=', 'sl.stock_id')
					->join('tbl_products_stock AS st', 'st.id', '=', 'bst.stock_id')
				   ->leftJoin('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
				   ->leftJoin('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
				   ->leftJoin('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
				   ->where('sl.order_id','=',$id)
				 //  ->where('sl.status','<=','1')
				   ->orderBy('pd.name')
				   ->select('sl.*','st.product_id','st.category_id','st.attribute_id','pd.name as prodName', 'atr.name as attrName','ct.name as catName')->get();

		//print_query($a['odrDetail']);

	    	$a['odrCatGroup']= DB::table('tbl_sale_return_detail AS sl')
		   			->join('branches_stocks AS bst', 'bst.stock_id', '=', 'sl.stock_id')
					->join('tbl_products_stock AS st', 'st.id', '=', 'bst.stock_id')
				   ->leftJoin('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
				   ->leftJoin('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
				   ->leftJoin('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
				   ->where('sl.order_id','=',$id)
				 //  ->where('sl.status','<=','1')
				   ->orderBy('pd.name')
				   ->select('st.product_id','st.category_id','sl.sRate','sl.sDiscount','sl.taxAmt','sl.taxRate',
				   DB::raw('SUM(sl.sNetAmount) as sNetAmount'),
				   DB::raw('SUM(sl.sQty) as TotalQty'),'pd.name as prodName', 'atr.name as attrName','ct.name as catName','bst.purchase_price as pRate')
				    ->groupBy('st.product_id','st.category_id','sl.sRate')
				   ->get();

        return view('admin.print_preview.sale_return_bill')->with($a);
	}

	//======Print New Lead Bill=======
	public function printNewLeadBill($id){

		$a['title'] = 'Inquiry Print';
		$inqueryModel = new NewInquiry();
		$a['bill'] = $inqueryModel->getInqueryDetails($id);
		

        return view('admin.print_preview.newlead_bill')->with($a);
	}

	public function printInqueryBill33($id)
	{
		$a['title'] = 'SALE PACKING SLIP';
        // Fetch the main bill data with necessary relationships
        $billData = \App\Models\Inquery::with(['account', 'salesman','details.stock.product', 'details.stock.category', 'details.stock.attr'])
            ->findOrFail($id);

        // Group and process sale details by product_id and category_id

		$groupedDetails = $billData->details
        ->groupBy(fn($detail) => $detail->stock->product_id . '-' . $detail->stock->category_id)
        ->map(function ($details) {
            $firstDetail = $details->first();
            $stock = $firstDetail->stock;
            $product = $stock->product;
            $category = $stock->category;

            $variants = $details->map(fn($detail) => [
				'stock_id'=>$detail->stock->id,
                'color' => $detail->stock->attr->first()->name ?? null,
                'qty' => $detail->sQty,
                'amount' => $detail->sNetAmount,
            ]);

            return (object) [
                'product_id' => $product->id,
                'category_id' => $category->id,
                'itemName' => $product->name,
                'categoryName' => $category->name,
                'totalQty' => $details->sum('sQty'),
                'totalAmount' => $details->sum('sNetAmount'),
                'variants' => $variants->toArray(),  // Returning variants as an array within the object
            ];
        });

		$a['billData']=$billData;
		$a['groupedDetails']=$groupedDetails;
		return $a;
		return view('admin.print_preview.inquery_bill')->with($a);
	}

	public function printSaleInqueryBill($id){
		$a['title'] = 'Sales-Inquiry Print';

		$inqueryModel = new \App\Models\Inquery();
		$a['bill'] = $inqueryModel->getInqueryDetails($id);
       
        return view('admin.print_preview.inquery_bill')->with($a);
	}

	public function printStockAdjustmentBill($id){
		$a['title'] = 'Sales-Inquiry Print';

		$stockadjustment = new \App\Models\StockAdjustment();
		$a['bill'] = $stockadjustment->getAdjustmentDetails($id);
       
        return view('admin.print_preview.stock_adjustment')->with($a);
	}

	public function printCartAdmin($id){
		$a['title'] = 'Cart Print';

		$cart = new \App\Models\Cart();
		$a['bill'] = $cart->getCartDetails($id);
       
        return view('admin.print_preview.cart_bill')->with($a);
	}


	//======= Purchase Order Print =========
	public function printPurchaseOrderBill($id){
		$a['title'] = 'PURCHASE-ORDER';
		$bill = new \App\Models\PurchaseOrder();
		$a['bill'] = $bill->getBillCatGroupDetails($id);

        return view('admin.print_preview.purchase_order_bill')->with($a);
	}
	

	//======= Purchase Order Print =========
	public function printPurchaseReturnBill($id){
		$a['title'] = 'PURCHASE-RETURN';
		$bill = new \App\Models\PurchaseReturn();
		$a['bill'] = $bill->getBillCatGroupDetails($id);

        return view('admin.print_preview.purchase_return_bill')->with($a);
	}

	public function printPurchaseBill($id)
	{
		$a['title'] = 'Purchase (Estimate Form)';
		$bill = new \App\Models\Purchase();
		$a['bill'] = $bill->getBillDetails($id);
       return view('admin.print_preview.purchase_bill')->with($a);
	}

	//======Print Dispatch Detail =======
	public function printDispatchBoxItems($id){
		$a['title'] = 'Dispatch Box';
		$a['bill'] = \App\Models\Dispatch::where('id', $id)
    ->with([
        'account',
        'pickedby',
        'details' => function ($query) {
            $query->join('tbl_products_stock', 'dispatch_details.stock_id', '=', 'tbl_products_stock.id')
                 ->join('tbl_products_master', 'tbl_products_stock.product_id', '=', 'tbl_products_master.id')
                 ->orderBy('tbl_products_master.name')
                 ->orderBy('tbl_products_stock.category_id')
                 ->select('dispatch_details.*');
        },
        'details.saleorder.bill'
    ])
    ->first();
		
        return view('admin.print_preview.dispatch_box')->with($a);
	}


	public function printDispatchBoxItemsdddd($dispatchid)
	{
		$a['title'] = 'Dispatch Box';
		 $dispatchDetail=\App\Models\DispatchDetail::where('dispatch_id',$dispatchid)
		->where('status',1)
		->pluck('order_item_id')
		->toArray();
		$dispatchbox=\App\Models\Dispatch::where('id',$dispatchid)->with('account','saleorder')
				->first();
		
		$a['sod']= SaleOrderDetail::whereIn('id',$dispatchDetail)->with('stock')->whereIn('status',[1,2])->get();   
		$a['dispatchItemCount']=$a['sod']->count() ?? 0;
	


		$a['odrDetail']= DB::table('tbl_sale_order_detail AS sl')
					->leftJoin('tbl_products_stock AS st', 'st.id', '=', 'sl.stock_id')
				   ->leftJoin('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
				   ->leftJoin('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
				   ->leftJoin('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
				   ->leftJoin('dispatch_details AS dp', 'dp.order_item_id', '=', 'sl.id')
				   ->whereIn('sl.id',$dispatchDetail)
				   ->orderBy('pd.name')
				   ->select('sl.*','pd.name as prodName', 'atr.name as attrName','ct.name as catName','dp.dispatch_qty as dispQty')
				   ->get();


	   	$a['odrCatGroup']= DB::table('tbl_sale_order_detail AS sl')
					->leftJoin('tbl_products_stock AS st', 'st.id', '=', 'sl.stock_id')
				   ->leftJoin('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
				   ->leftJoin('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
				   ->leftJoin('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
				   ->leftJoin('dispatch_details AS dp', 'dp.order_item_id', '=', 'sl.id')
				   ->whereIn('sl.id',$dispatchDetail)
				  // ->whereIn('sl.status',[1,2])
				   ->orderBy('pd.name')
				   ->select('sl.product_id','sl.category_id','sl.sRate','sl.sDiscount','sl.taxAmt','sl.taxRate','dp.dispatch_qty as dispQty',
				   DB::raw('SUM(sl.sRate * dp.dispatch_qty) as sNetAmount'),
				   DB::raw('SUM(dp.dispatch_qty) as TotalQty'),'pd.name as prodName', 'atr.name as attrName','ct.name as catName','st.purchase_price as pRate')
				    ->groupBy('st.product_id','st.category_id','sl.sRate')
				   ->get();
		
		$a['pOrder']=DB::table('tbl_sale_order AS so')
				   ->leftJoin('tbl_account AS ac', 'ac.id', '=', 'so.account_id')
				   ->leftJoin('users AS sm', 'sm.id', '=', 'so.salesman_id')
					 ->leftJoin('tbl_gift AS gf', 'gf.id', '=', 'so.gift_id')
				   ->where('so.id','=',$dispatchbox->bill_id)
				   ->select('so.*','ac.name','ac.email','ac.phone','ac.address','ac.city','ac.term_cond','ac.type as acType','ac.referred_by','sm.name as salesMan','gf.name as giftName','gf.code as giftCode','gf.image as giftImage','gf.description as giftDesc')
				   ->first();
		$a['dispatchbox']=$dispatchbox;
       return view('admin.print_preview.dispatch_box')->with($a);
	}

}	

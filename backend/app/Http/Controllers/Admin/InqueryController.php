<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Inquery;
use App\Models\InqueryDetail;
use App\Models\NewInquiry;
use App\Models\NewInquiryDetail;
use App\Models\ProductModel;
use App\Models\SerialNo;
use App\Models\FinancialLogsModel;
use App\Models\Employee;
use App\Models\UserModel;
use DB;
use App\Models\BranchStocks;
use App\Models\Category;
use App\Models\Account;
use App\Models\Color;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Response;
use Auth;
use Log;

use App\Rules\FinancialYearDateCheck;


class InqueryController extends WebController
{

    public $inqObj,$inqDetailObj;
    public function __construct()
    {
        $this->inqObj = new Inquery();
        $this->inqDetailObj = new InqueryDetail();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


     public function index(Request $r)
     {
        if (!hasPermission('inquiry_view')) {
            return redirect()->back();
        }

         $a['inquiry'] = $this->inqObj->with('account')->latest()->limit(20)->get();
         $a['title'] = 'Sales Inquery';
         $a['breadcrumb'] = breadcrumb([ 'Inquery' => route('admin.sales-inquiry.index'),]);
         $a['repType']='Inquery';
        $a['salesman'] = Employee::withCount('assignInquery')
                                ->orderBy('name')
                                // ->whereHas('roles', function ($query) {
                                //     $query->whereIn('roles.id', [9, 10,12]); // Filtering by role_id
                                // })
                                ->whereIn('department_id',[1,2,3,4,5])
                                ->get();
         $a['accounts']= Account::where('acGroup','4')->orderBy('name')->get();
        //return $data = $this->inqObj::with('account','cancelby','saleorder','notes')->orderByRaw("CASE WHEN status = '0' THEN 0 ELSE 1 END") // Pending rows first
        // ->orderBy('id', 'desc')->get();
 
         return view('admin.inquiry.index')->with($a);
     }



    public function index11(Request $r)
    {
        if (!hasPermission('inquiry_view')) {
            return redirect()->back();
        }
        $a['inquery'] = $this->inqObj->latest()->get();
        $a['title'] = 'Sales Inquery';
        $a['breadcrumb'] = breadcrumb([ 'Inquery' => route('admin.sales-inquiry.index'),]);
        $a['repType']='Sale Inquery';
        return view('admin.inquiry.index')->with($a);

        //return $rd;
		$a['accounts'] = Account::latest()->where('acGroup','!=',1)->where('acGroup','!=',1)->get();
		$a['salesman'] = Employee::withCount('assignInquery')->whereIn('department_id',[1,2,3,4,5])->get();


		$cond='';
		$tbl='tbl_sale_inquery_detail';
		$tblOdr='tbl_sale_inquery';
		$Title='Billwise Sale-Inquery Report';
		$pageTitle='Sale Inquery Billwise Detail';
		$repGroup='order';
		$newInqCount=0;

		if(isset($_POST['status']) && $_POST['status']!=null)
		{
				if($_POST['status']=='*')
				{
          $cond='1=1';
					$Title.=' (All)';
				}else if($_POST['status']=='2'){
          $cond='odr.billing_status="2"';
					$Title.=' (Cancelled)';
				}else if($_POST['status']=='1'){
          $cond='odr.billing_status="1"';
					$Title.=' (Converted To Order)';
				}else{

				}
		}else{
				$cond='odr.billing_status="0"';
				$Title.=' (Pending)';
			}


        if(isset($_POST['fromdate'],$_POST['todate']) && $_POST['fromdate']!=null && $_POST['todate']!=null)
  			{
  				$cond.=" and (odr.billDate>='".$_POST['fromdate']." 00 00 01' and odr.billDate<='".$_POST['todate']." 23 59 59')";
  				$Title.= '<small>('.$_POST['fromdate'].' - '.$_POST['todate'].')</small>';
  			}

			  if(isset($_POST['AccountID']) && $_POST['AccountID']>=1)
				{
					$cond.=" and odr.supplier_id='".$_POST['AccountID']."'";
					$a = Account::where('id','=',$_POST['AccountID'])->first();
					$Title.=' <br> <small>'.$a->name.'</small> | ';
				}

				if(isset($_POST['salesMan']) && $_POST['salesMan']>=1)
				{
					$cond.=" and odr.salesman_id='".$_POST['salesMan']."'";
					$a = Employee::where('id','=',$_POST['salesMan'])->first();
					$Title.=' <br> <small>'.$a->name.'</small> | ';
				}

			$sod = DB::select( DB::raw("SELECT odr.*,emp.name salesMan,ur.name as userName,ac.name supplierName,cur.name cancelByName from ".$tblOdr." as odr
																left Join employees as emp ON emp.id=odr.salesman_id
																left Join users as ur ON ur.id=odr.user_id
																left Join users as cur ON cur.id=odr.cancel_by
																left Join tbl_account as ac ON ac.id=odr.supplier_id WHERE ".$cond.' order by  CASE WHEN odr.supplier_id = 26 THEN 9999999
																ELSE 1 END desc ,odr.id desc'));

			 $newInqCount = DB::select( DB::raw("SELECT min(odr.id) as minid from ".$tblOdr." as odr
					left Join employees as emp ON emp.id=odr.salesman_id
					left Join users as ur ON ur.id=odr.user_id
					left Join tbl_account as ac ON ac.id=odr.supplier_id WHERE ".$cond.' and odr.salesman_id=26 order by odr.id desc'));

		if(count($sod)>=1)
		{
			foreach($sod as $pod)
			{
				$odrCatGroup= DB::table($tbl.' AS sl')
					   ->leftJoin('tbl_products_stock AS st', 'st.id', '=', 'sl.stock_id')
					   ->leftJoin('tbl_products_master AS pd', 'pd.id', '=', 'st.product_id')
					   ->leftJoin('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
					   ->leftJoin('tbl_color AS atr', 'atr.id', '=', 'st.attribute_id')
					   ->where('sl.order_id','=',$pod->id)
					   ->select('st.product_id','st.category_id','sl.sRate','sl.sDiscount',
					   DB::raw('SUM(sl.sNetAmount) as sNetAmount'),
					   DB::raw('SUM(sl.sQty) as TotalQty'),'pd.name as prodName', 'atr.name as attrName','ct.name as catName')
						->groupBy('sl.product_id','sl.category_id','sl.sRate')
					   ->get();

			 	$std=DB::table($tbl.' AS sd')
					   ->leftJoin('tbl_products_stock AS st', 'st.id', '=', 'sd.stock_id')
					   ->leftJoin('tbl_products_master AS pm', 'pm.id', '=', 'st.product_id')
					   ->leftJoin('tbl_color AS clr', 'clr.id', '=', 'st.attribute_id')
					   ->leftJoin('tbl_categories AS ct','ct.id','=','st.category_id')
					   ->where('sd.order_id','=',$pod->id)
					   ->select('sd.*','pm.name as product_name','pm.code as product_code','clr.name as color_name','ct.name as category_name')
					   ->get();

				$pendingOdr=DB::table('tbl_sale_temp_detail as std')
				->leftJoin('tbl_sale_temp_order AS sto', 'sto.id', '=', 'std.order_id')
				->leftJoin('tbl_account as ac', 'ac.id', '=', 'sto.supplier_id')
				->leftJoin('employees as emp', 'emp.id', '=', 'sto.salesman_id')
				->where('std.status','active')
				->where('sto.supplier_id',$pod->supplier_id)
				->groupBy('std.order_id')
				->orderBy('std.order_id', 'desc')
				->select('sto.*','emp.name as salesMan',DB::raw('count(std.id) as itemCount'))
				->get();


				$pendCart=DB::table('tbl_cart_detail as crd')
				->leftJoin('tbl_cart AS crt', 'crt.id', '=', 'crd.order_id')
				->leftJoin('tbl_account as ac', 'ac.id', '=', 'crt.account_id')
				->leftJoin('employees as emp', 'emp.id', '=', 'crt.salesman_id')
				->where('crt.account_id',$pod->supplier_id)
				->groupBy('crd.order_id')
				->orderBy('crd.order_id', 'desc')
				->select('crt.*','emp.name as salesMan',DB::raw('count(crd.id) as itemCount'))
				->get();

				$salesManInqCount=DB::table('tbl_sale_inquery')->where('salesman_id',$pod->salesman_id)->where('billing_status',0)->count();

			$ac = Account::where('id','=',$pod->supplier_id)->get();

			$pod->acc=$ac;
			$pod->dt=$std;
			$pod->pdCatGr=$odrCatGroup;
			$pod->pendingOdr=$pendingOdr;
			$pod->pendingCrt=$pendCart;
			$pod->salesManInQCount=$salesManInqCount;
			$po[]=$pod;
		  }
		}else{$po='';	}

        return view('admin.sale_inquery.inqueryList-detail', compact('newInqCount','po','accounts','Title','pageTitle','repGroup','salesman','repType'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!hasPermission('inquiry_create')) {
            return redirect()->back();
        }
        $a['title']='Add Sale Inquery';
        $a['bill'] = $this->inqObj;
        $a['action']= 'CreateSaleInquiry';
        $a['nextBill']=getNewSerialNo('sale_inquery');
        $a['breadcrumb']=breadcrumb([
            'Inquery' => route('admin.sales-inquiry.index'),
            ]);
        return view('admin.inquiry.addEditForm')->with($a);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $rd)
    {
        $rd->validate([
            'account_id' => ['required'],
            'bill_amount'=>['required'],
            'billDate'=>['required'],
        ]);
        $return_data = $rd->all();
        DB::beginTransaction();
        try {
                $InvNo=getNewSerialNo('sale_inquery');
                $po = $this->inqObj;
                $po->account_id = $rd->account_id;
                $po->invoice_No = $InvNo;
                $po->billDate = date("Y-m-d",strtotime($rd->billDate)).' '.date('H:i'); 
                $po->gen_discount = $rd->gen_discount;
                $po->soffer_discount = $rd->soffer_discount;
                $po->acc_discount = $rd->acc_discount;
                $po->discount = $rd->totalBillDiscount;
                $po->other_charges = $rd->otherCharges;
                $po->freight = $rd->freight;
                $po->parcels = $rd->parcels;
                
                $po->remark = $rd->remark;
                $po->invoice_amt = $rd->sumNetTotal;
                $po->bill_amount = $rd->bill_amount;
                $po->tax_amount= $rd->sumTaxAmount;
                $po->inquiryFor = $rd->inqfor;
                $po->encrypt_id=md5($InvNo.now());
                $po->billing_mode ='0';
                if($rd->inqfor==2){
                    //=====for Photo ===
                    $po->status = 0;
                    $po->salesman_id =0;
                }else{
                    //=====for ORder ===
                    $po->status = 1;
                    $po->salesman_id = Auth::user()->id;
                }
                
                if($po->save()){

                    $n=0;
                    foreach($rd->stockID as $Atr)
                    {
                        
                        $bst=BranchStocks::where('stock_id',$Atr)->first();

                        $saleProdDt= new InqueryDetail();
                        
                        $saleProdDt->added_by=$rd->AdUserID[$n];
                        $saleProdDt->order_id = $po->id;
                        $saleProdDt->account_id = $rd->account_id;
                        $saleProdDt->stock_id = $bst->stock_id;
                        $saleProdDt->sRate = $rd->AdpRate[$n];
                        $saleProdDt->actualQty = $rd->AdProdQty[$n];
                        $saleProdDt->sQty = $rd->AdProdQty[$n];
                        $saleProdDt->sNetAmount = $rd->AdNetAmt[$n];
                        $saleProdDt->taxRate = $rd->AdTaxRate[$n];
                        $saleProdDt->taxAmt = $rd->AdTaxAmt[$n];
                        $saleProdDt->isOffer = $bst->is_offer;
                        $saleProdDt->actualPrice = $bst->sale_price;
                        $saleProdDt->offer_id = $rd->AdOfferID[$n] ?? 0;
                        $saleProdDt->offer_dis_rate = $rd->AdOfferDisRate[$n] ?? 0;
                        $saleProdDt->offer_dis_amt = $rd->AdOfferDisAmt[$n] ?? 0;

                        $saleProdDt->save();


                        /*===== Cart To sale Order ===== */
                            if($rd->action=='CartToInquiry'){
                                if($rd->oldID[$n]>0){
                                    $carddetail=\App\Models\CartDetail::where('id',$rd->oldID[$n])->delete();
                                    $card=\App\Models\Cart::where('id',$rd->requestID)->first();
                                    $card->bill_amount = $card->bill_amount - $rd->AdNetAmt[$n];
                                    $card->save();
                                }
                            }
                        /** ======= ========== */

                        $n++;

                    }
                    increaseSerialNo('sale_inquery');

                    
                        //====Delete Cart if No item Remails====
                        if($rd->action=='CartToInquiry'){
                            $cardItems=\App\Models\CartDetail::where('order_id',$rd->requestID)->count();
                            if($cardItems==0){
                                \App\Models\Cart::where('id',$rd->requestID)->delete();
                            }
                        }
                        //==== END cart Delete====

                }
                DB::commit();
                Toastr::success('Sale inquery Successfully Created', 'Success!!!');
                return redirect()->route('admin.sales-inquiry.index');


        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return $e->getMessage();
            Toastr::error($e->getMessage(), 'Success!!!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!hasPermission('inquiry_edit')) {
            return redirect()->back();
        }
        $a['title']='Update Inquery';
        $a['breadcrumb']=breadcrumb([
            'Inquery' => route('admin.sales-inquiry.index'),
            ]);
        $a['employees']= Employee::whereIn('department_id',[1,2,3,4,5])
        // ->whereHas('roles', function ($query) {
        //     $query->whereIn('roles.id', [9, 10,12]); // Filtering by role_id
        // })
        ->get();
        $a['bill'] = $this->inqObj::where('id',$id)->with('account.citydata','account.statedata')->first();
        $a['nextBill']=$a['bill']->invoice_No;
        $a['requestID'] =$id;
        $a['action']= 'UpdateInquiry';
        $a['details']= $this->inqDetailObj::where('status','1')
                    ->where('order_id',$id)
                    ->with('stock','user')
                    ->get();

        return view('admin.inquiry.addEditForm')->with($a);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $rd, $id)
    {
        //return $rd;
        $rd->validate([
            'account_id' => ['required'],
            'bill_amount'=>['required'],
            'billDate'=>['required'],
        ]);

        DB::beginTransaction();
        try {
                $po = $this->inqObj->find($id);
                $po->account_id = $rd->account_id;
                $po->billDate = date("Y-m-d",strtotime($rd->billDate)); 
                $po->gen_discount = $rd->gen_discount;
                $po->soffer_discount = $rd->soffer_discount;
                $po->acc_discount = $rd->acc_discount;
                $po->discount = $rd->totalBillDiscount;
                $po->other_charges = $rd->otherCharges;
                $po->freight = $rd->freight;
                $po->parcels = $rd->parcels;
                $po->remark = $rd->remark;
                $po->invoice_amt = $rd->sumNetTotal;
                $po->bill_amount = $rd->bill_amount;
                $po->tax_amount= $rd->sumTaxAmount;
                $po->inquiryFor = $rd->inqfor;
                $po->discount_rate = $rd->discount_rate;

                if($po->save()){

                    $n=0;
                    foreach($rd->stockID as $Atr)
                    {
                        if($rd->oldID[$n]==0){
                            $InqDt=new InqueryDetail();

                        }else{
                            $InqDt=$this->inqDetailObj->find($rd->oldID[$n]);
                            $InqDt->user_id=Auth::user()->id;
                            $InqDt->branch_id=Auth::user()->branch_id;
                        }

                        $bst=BranchStocks::where('stock_id',$Atr)->first();
                        $InqDt->order_id = $po->id;
                        $InqDt->account_id = $rd->account_id;
                        $InqDt->stock_id = $bst->stock_id;
                        $InqDt->sRate = $rd->AdpRate[$n];
                        $InqDt->actualQty = $rd->AdProdQty[$n];
                        $InqDt->sQty = $rd->AdProdQty[$n];
                        $InqDt->sNetAmount = $rd->AdNetAmt[$n];
                        $InqDt->taxRate = $rd->AdTaxRate[$n];
                        $InqDt->taxAmt = $rd->AdTaxAmt[$n];
                        $InqDt->isOffer = $bst->is_offer;
                        $InqDt->actualPrice = $bst->sale_price;
                        $InqDt->offer_id = $rd->AdOfferID[$n] ?? 0;
                        $InqDt->offer_dis_rate = $rd->AdOfferDisRate[$n] ?? 0;
                        $InqDt->offer_dis_amt = $rd->AdOfferDisAmt[$n] ?? 0;
                        $InqDt->save();

                        $n++;
                    }
                    increaseSerialNo('new_inquery');
                }
                DB::commit();
                Toastr::success('Sale inquery Successfully Created', 'Success!!!');
                return redirect()->route('admin.sales-inquiry.index');

        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            Toastr::error($e->getMessage(), 'Success!!!');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Convert New customer Inquery to  Existing Customer Inquery
     * - add new customer account
     * - generate inquery with old data
     *
     */
    public function convertToInquiry($id)
    {
        if (!hasPermission('convert_inquiry_to_sale_inquiry')) {
            return redirect()->back();
        }
        $a['title']='Convert To Inquery';
        $a['breadcrumb']=breadcrumb([
            'Inquery' => route('admin.sales-inquiry.index'),
            ]);
        $a['employees']= Employee::get();
        $a['bill'] = NewInquiry::where('id',$id)->first();
        $a['nextBill']=$a['bill']->invoice_No;
        $a['requestID'] =$id;
        $a['action']= 'ConvertInquiry';
        $a['billdt']= NewInquiryDetail::where('status','1')
                    ->where('order_id',$id)
                    ->with('stock','user')
                    ->get();


        return view('admin.inquiry.convertInquiry')->with($a);

    }

    public function convertInquirySave(Request $rd,$id){

        $phone=$rd->phone;
        $phone2=$rd->phone2;
        $name=$rd->name;
        $accCheck=Account::where(function($q) use ($phone,$phone2,$name){
                                        $q->where('phone',$phone)
                                        ->orWhere('phone2',$phone)
                                        ->orWhere('phone',$phone2)
                                        ->orWhere('phone2',$phone2);
                                    })
                                    ->where('name',$name)->count();
        if($accCheck>=1){
            Toastr::error('Account Already registered with name or phone no.', 'Success!!!');
            return redirect()->back();
        }else{

                DB::beginTransaction();
                try
                {
                    $newInq= NewInquiry::where('id',$id)->first();

                    $ac= new Account;
                    $ac->acCode =getNewSerialNo('account_code');
                    $ac->name =$newInq->name;
                    $ac->phone =$newInq->phone;
                    $ac->phone2 =$newInq->phone2;
                    $ac->email =$newInq->email;
                    $ac->address =$newInq->address;
                    $ac->city =$newInq->city;
                    $ac->state =$newInq->state;
                    $ac->state_id =$newInq->state_id;
                    $ac->country ='india';
                    $ac->contactPerson =$newInq->contactPerson;
                    $ac->acGroup =4;
                    $ac->type =$newInq->customerType;
                    $ac->visit_type =$newInq->inq_type;
                    $ac->referred_by =  $newInq->reference_name;
                    //$ac->GSTN_No = $newInq->gst_no;  
                    $ac->priceGroup = $newInq->priceGroup;
                    $ac->is_approved = 0;
                    $ac->discount_rate = $newInq->discount_rate;
                    
                    if($ac->save())
                    {
                        increaseSerialNo('account_code');
                        $InvNo=getNewSerialNo('sale_inquery');
                        $po = $this->inqObj;
                        $po->account_id = $ac->id;
                        $po->invoice_No = $InvNo;
                        $po->billDate = date("Y-m-d");

                        $po->gen_discount = $newInq->gen_discount;
                        $po->soffer_discount = $newInq->soffer_discount;
                        $po->acc_discount = $newInq->acc_discount;
                        $po->discount = $newInq->discount;
                        $po->other_charges = $newInq->other_charges;

                        $po->freight = $newInq->freight;
                        $po->parcels = $newInq->parcels;
                        $po->salesman_id = $rd->inqfor == 2  ? $newInq->salesman_id : 0;
                        $po->remark = $newInq->remark;
                        $po->invoice_amt = $newInq->invoice_amt;
                        $po->bill_amount = $newInq->bill_amount;
                        $po->tax_amount= $newInq->tax_amount;
                        $po->encrypt_id=md5($InvNo.now());
                        $po->inquiryFor = $newInq->inquiryFor;
                        $po->billing_mode ='0';
                        $po->inq_type = $newInq->inq_type;
                        $po->reference_name = $newInq->reference_name;
                       
                        if($po->save())
                        {

                            $nInqDtl=NewInquiryDetail::where('order_id',$id)->get();
                            $n=0;

                            if($nInqDtl)
                            {
                                foreach($nInqDtl as $nid)
                                {
                                    $saleProdDt=new InqueryDetail;
                                    $saleProdDt->order_id = $po->id;
                                    $saleProdDt->account_id = $po->account_id;
                                    $saleProdDt->stock_id = $nid->stock_id;
                                    $saleProdDt->sRate = $nid->sRate;
                                    $saleProdDt->actualQty = $nid->actualQty;
                                    $saleProdDt->sQty = $nid->sQty;
                                    $saleProdDt->sNetAmount = $nid->sNetAmount;
                                    $saleProdDt->taxRate = $nid->taxRate;
                                    $saleProdDt->taxAmt = $nid->taxAmt;
                                    $saleProdDt->actualPrice = $nid->actualPrice;
                                    $saleProdDt->isOffer = $nid->isOffer;
                                    $saleProdDt->offer_id = $nid->offer_id;
                                    $saleProdDt->offer_dis_rate = $nid->offer_dis_rate;
                                    $saleProdDt->offer_dis_amt = $nid->offer_dis_amt;
                                    $saleProdDt->user_id =$nid->user_id;
                                    $saleProdDt->branch_id =$nid->branch_id;
                                    $saleProdDt->save();
                                    $n++;
                                }
                            }

                            increaseSerialNo('sale_inquery');
                            NewInquiry::where('id',$id)->delete();
                            NewInquiryDetail::where('order_id',$id)->delete();
                        }
                    }else{
                        Toastr::error('Unable to create Account.', 'Success!!!');
                        return redirect()->back();
                    }
                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    return $e->getMessage();
                    Toastr::error($e->getMessage(), 'Success!!!');
                }

                Toastr::success('Sale inquery Successfully Created', 'Success!!!');
                return redirect()->route('admin.sales-inquiry.index');
            }


    }


    //====Status update Cancel Inquery =======
    public function statusUpdate(Request $r){
             // Find the order by ID
             //return $r;
        $id=$r->billid;
        $inq = $this->inqObj->findOrFail($id);

        if($r->status==4)
        {

            if(!empty($inq))
            {
                
                if (!Auth::user()->type=='admin' && $inq->salesman_id !== Auth::id()) {
                    Toastr::error('You are not authorized to cancel this inquiry', 'Authorization Error');
                    return back();
                }

                $inq->status='4';
                $inq->cancel_note=$r->cancelNote;
                $inq->cancel_date=date('Y-m-d h:i');
                $inq->cancel_by=Auth::user()->id;

                $image = $r->file('chatImg');
                if (isset($image))
                {
                    $currentDate = date('ymdhis');
                    $imageName = $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

                    // Ensure the 'product' directory exists
                    if(!Storage::disk('public')->exists('inquiry')){
                        Storage::disk('public')->makeDirectory('inquiry');
                    }

                    // Process the image with Intervention Image
                    $postImage = Image::read($image)
                        ->resize(640, 480, function ($constraint) {
                            $constraint->aspectRatio(); // Maintain aspect ratio
                            $constraint->upsize();     // Prevent stretching
                        })
                        ->encode(); // Convert to stream for storage

                        // Save the processed image to storage
                        Storage::disk('public')->put('inquiry/' . $imageName, $postImage);

                    // Add the image name to the mapped data
                    $inq->chat_image=$imageName;
                }

                if($inq->save())
                {
                    $ac=Account::find($inq->account_id);
                    if($inq->account_id>=1 && $r->has('blockaccount')){
                        
                        $ac->block_status='1';
                    }
                    if($inq->account_id>=1 &&  $r->has('inactiveaccount')){
                        $ac->status='0';
                    }

                    $ac->block_remark= $r->cancelNote;
                    $ac->block_by = Auth::user()->id;
                    $ac->save();
                    Toastr::success('Sale inquery Cancelled', 'Success!!!');
                }else{
                    Toastr::warning('Unable to Cancelled', 'Error!!!');
                }


            }else{
                Toastr::warning('Unable to Cancelled', 'Error!!!');
            }
        }
        return redirect()->back();
    }
    //=== update salesman======
    public function changeSalesMan(Request $r){
       // return $r;
        if($r->billid>0 && $r->salesman_id>0){
            $inq=$this->inqObj->find($r->billid);
            $inq->salesman_id=$r->salesman_id;
            $inq->status=1;
            $inq->save();
        }
        Toastr::success('Inquery assign to sales person successfully.', 'Success!!!');
        return redirect()->back();
    }


    public function checkExistingPhotoInq(Request $r){
        $accountid=$r->acid;
        $photoinq=$this->inqObj->where('inquiryFor','2')
                        ->where('account_id',$accountid)
                        ->whereIn('status',[0,1])
                        ->first();
        
        if ($photoinq) {
            return response()->json([
                'exists' => true,
                'billNo' => $photoinq->invoice_No, 
                'billId' => $photoinq->id, 
                'billDate' => myDateFormat($photoinq->billDate),
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function listing(Request $r)
    {
        $data = $this->inqObj::with('account', 'cancelby', 'saleorder', 'notes')
            ->orderByRaw("CASE WHEN status = '0' THEN 0 ELSE 1 END")
            ->orderBy('id', 'desc');

        if ($r->has('fstatus') && $r->fstatus != '*') {
            $data->where('status', $r->fstatus);
        } else {
            $data->whereIn('status', ['0', '1']);
        }

        if ($r->has('finqfor') && $r->finqfor != '*') {
            $data->where('inquiryFor', $r->finqfor);
        }

        if ($r->has('salesman') && $r->salesman != '') {
            $data->where('salesman_id', $r->salesman);
        }

        if ($r->has('account') && $r->account != '') {
            $data->where('account_id', $r->account);
        }

        if ($r->has('fromdate') && $r->has('todate') && $r->fromdate != '' && $r->todate != '') {
            $data->where('billDate', '>=', date('Y-m-d H:i:s', strtotime($r->fromdate)))
                ->where('billDate', '<=', date('Y-m-d', strtotime($r->todate)) . ' 23:59:59');
        }

        if ($r->has('search.value') && $r->input('search.value') !== '' && strlen($r->input('search.value')) === 3) {
            $search = $r->input('search.value');
            $data->where(function ($query) use ($search) {
                // Search account-related fields
                $query->whereHas('account', function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%".strtolower($search)."%"])
                        ->orWhereRaw('LOWER(phone) LIKE ?', ["%".strtolower($search)."%"]);
                    
                });
                // Search other inquiry fields
                // ->orWhere('invoice_No', 'LIKE', "%{$search}%")
                // ->orWhere('billDate', 'LIKE', "%{$search}%")
                // ->orWhere('bill_amount', 'LIKE', "%{$search}%")
                // // Search related fields for computed columns
                // ->orWhereHas('user', function ($q) use ($search) {
                //     $q->where('name', 'LIKE', "%{$search}%"); // For sales_person (By: user.name)
                // })
                // ->orWhereHas('salesman', function ($q) use ($search) {
                //     $q->where('name', 'LIKE', "%{$search}%"); // For sales_person (To: salesman.name)
                // })
                // ->orWhereHas('notes', function ($q) use ($search) {
                //     $q->where('note', 'LIKE', "%{$search}%"); // For notes in name column
                // })
                // ->orWhereHas('saleorder', function ($q) use ($search) {
                //     $q->where('invoice_No', 'LIKE', "%{$search}%"); // For inqstatus (saleorder.invoice_No)
                // });
            });
        }

      // \Log::info('Search term: ' . print_query($data)); 
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('name', function ($row) {
                $acType=accountType($row?->account?->type);
                $name = $row?->account?->name.' '.$acType;
                if ($row?->account?->statedata) {
                    $name .= '<br><small>' . $row?->account?->statedata?->name;
                }
                if ($row?->account?->citydata) {
                    $name .= ' , ' . $row?->account?->citydata?->name . '</small>';
                }
                if ($row->notes->count() > 0) {
                    $lastNote = $row->notes->first();
                    $lnote = myDateFormat($lastNote->datetime) . '|' . $lastNote?->user?->name . ' | ' . $lastNote->note;
                    $name .= '<span title="' . e($lnote) . '"> 📝<span class="badge bg-danger">' . $row->notes->count() . '</span></span>';
                }

                if($row->status==4){
                    $name.'<br><span title="Calceled">Cancel Note: cancel_date:-'.$row->cancel_note.'</span>';
                }

                if($row->status==5){
                    $name.'<br><span title="Deleted"> Note: cancel_date:-'.$row->cancel_note.'</span>';
                }

                return $name;
            })
            ->addColumn('inquiry_type', function ($row) {
                return match ($row->inquiry_type) {
                    '2' => 'Reference',
                    '1' => 'Online',
                    default => '',
                };
            })
            ->addColumn('inqFor', function ($row) {
                return match ($row->inquiryFor) {
                    '2' => '<span class="badge bg-info">Photo</span>',
                    '1' => '<span class="badge bg-danger">Order</span>',
                    default => '',
                };
            })
            ->addColumn('sales_person', function ($row) {
                return 'By: ' . $row?->user?->name . '<br>To: <strong class="text-success">' . $row?->salesman?->name . '</strong>';
            })
            ->addColumn('inqstatus', function ($row) {
                $status = $row->status;
                $account_approved = $row->account->is_approved;
                $statusText = match ($status) {
                    '0' => '<badge class="badge bg-warning rounded-pill">Pending</badge>',
                    '1' => '<badge class="badge bg-success rounded-pill" title="Assigned">Assigned</badge>',
                    '2' => '<badge class="badge bg-primary rounded-pill">Completed</badge>',
                    '3' => $row->saleorder
                        ? '<a href="' . route('admin.print.sale-order', $row->saleorder->id) . '"><badge class="badge bg-info rounded-pill">Order Generated</badge><br><small>' . $row->saleorder->invoice_No . '</small></a>'
                        : '<badge class="badge bg-info rounded-pill">Order Generated</badge>',
                    '4' => '<badge class="badge bg-secondary rounded-pill">Cancelled</badge><br><span class="text-danger">By: ' . $row?->cancelby?->name . '</span><br>Note: <small class="text-success">' . $row->cancel_note . '</small>' . (!empty($row->chat_image) ? ' <i class="fa fa-image cancledInqImg" role="button" imgsrc="' . Storage::url('inquiry/' . $row->chat_image) . '"></i>' : ''),
                    '5' => '<badge class="badge bg-danger rounded-pill">Deleted</badge><br><span class="text-danger">By: ' . $row?->cancelby?->name . '</span><br>Note: <small class="text-success">' . $row->cancel_note . '</small></badge>',
                    default => '--',
                };
                if ($account_approved == 0) {
                    $statusText .= '<br><span class="badge bg-danger">Account Not Approved</span>';
                }
                return $statusText;
            })
            ->addColumn('billDateTime', function ($row) {
                return myDateFormat($row->billDate);
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="btn-group cstbtn">';
                if (auth()->user()->can('sales_inquiry_edit') && $row->status < 3) {
                    $action .= '<button type="button" class="btn btn-sm btn-outline-primary"><a href="' . route('admin.sales-inquiry.edit', $row->id) . '"><i class="fa fa-edit"></i></a></button>';
                }
                if (auth()->user()->can('sales_inquiry_view')) {
                    $action .= ' <button type="button" class="btn btn-sm btn-outline-primary"><a href="' . route('admin.print.sales-inquiry', $row->id) . '"><i class="fa fa-print"></i></a></button>';
                }
                $action .= '<button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown"><i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i></button><ul class="dropdown-menu" role="menu">';
                if (auth()->user()->can('assign_to_salesperson') && ($row->salesman_id < 1 || Auth::user()->role_id == '1' || $row->status < 3)) {
                    $action .= '<li><a href="#" class="assignSalesman" data-id="' . $row->id . '" title="Assign to Salesman"><i class="fas fa-angle-double-right"></i> Assign</a></li>';
                }
                if (auth()->user()->can('sales_inquiry_update_notes')) {
                    $action .= '<li><a href="#" class="inqueryShow" data-id="' . $row->id . '" title="View the Inquery Details"><i class="fa fa-eye"></i> show/Update Note</a></li>';
                }
                if ($row->status <= 2) {
                    if (auth()->user()->can('convert_sales_inquiry_to_sales_order') && $row->status == 1 && $row->inquiryFor == 1) {
                        $action .= '<li><a href="' . url('admin/convert-inquiry-saleorder/' . $row->id) . '" title="Convert Inquery to Order"><i class="fa fa-copy"></i> Inquery To Order</a></li>';
                    }
                    if (auth()->user()->can('sale_inquiry_fulfill')) {
                        $action .= '<li><a href="' . url('admin/fulfill-status/sale-inquiry/' . $row->id . '/ready') . '" title="Check Fullfill Items"><i class="fa fa-list text-success"></i> Fullfill/Ready</a></li>';
                    }
                    if (auth()->user()->can('sales_inquiry_notready')) {
                        $action .= '<li><a href="' . url('admin/fulfill-status/sale-inquiry/' . $row->id . '/not-ready') . '" title="Check Not-Ready Items"><i class="fa fa-list text-danger"></i> Not-Ready Items</a></li>';
                    }
                    if (auth()->user()->can('sales_inquiry_cancel')) {
                        $action .= '<li><a href="#" class="inqueryCancel" data-id="' . $row->id . '" title="Check Not-Ready Items"><i class="fa fa-window-close text-danger"></i> Cancel</a></li>';
                    }
                    if (auth()->user()->can('sales_inquiry_delete')) {
                        $action .= '<li><a href="#" class="inqDelete" data-id="' . $row->id . '" title="Delete Inquery" class="text-danger"><i class="fa fa-copy"></i> Delete Inquery</a></li>';
                    }
                }
                 $action .= '<li><a href="'.route('admin.account.show',$row->account_id).'"><i class="fa fa-table"></i> Account Ledger</a></li>';
                $action .= '</ul></div>';
                return $action;
            })
            ->rawColumns(['name', 'billDateTime', 'inquiry_type', 'inqFor', 'inqstatus', 'sales_person', 'action'])
            ->make(true);
    }
}
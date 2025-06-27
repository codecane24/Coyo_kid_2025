<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;

use App\Models\Account;
use App\Models\BranchAccounts;
use App\Models\AccountGroup;
use App\Models\FinancialLogsModel;
use App\Models\NewInquiry;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockModel;
use DB;
class ReportOtherController extends Controller
{
    public function ReadyAndNotReady($for,$id,$type=null)
    {
      $status='false';
      $code='0';
      $msg='';
      $tblD='';
      $title='';
  
      if($for == 'sale'){
        $tblD='tbl_sale_detail';
        $tb='tbl_sale';
      }
      else if($for=='sale-order'){
        $tblD='tbl_sale_order_detail';
        $tb='tbl_sale_order';
      }
      else if($for=='sale-inquiry'){
        $tblD='tbl_sale_inquery_detail';
        $tb='tbl_sale_inquery';
      }
      else if($for=='cart'){
        $tblD='tbl_cart_detail';
        $tb='tbl_cart';
      }
      else{}
        $bill=DB::table($tb.' as bill')
            ->leftJoin('tbl_account as ac','bill.account_id','=','ac.id')
            ->leftJoin('users as sm','bill.salesman_id','=','sm.id')
            ->where('bill.id',$id)
            ->select('bill.*','ac.name as acName','ac.block_status','sm.name as salesman')->first();

        // Step 1: Subquery to find product-category combinations with insufficient stock
        $excludedProductCategories = DB::table($tblD . ' as od')
              ->join('tbl_products_stock as ps', 'od.stock_id', '=', 'ps.id')
              ->join('branches_stocks as bs', 'bs.stock_id', '=', 'od.stock_id')
              ->join('tbl_products_master as pm', 'pm.id', '=', 'ps.product_id')
              ->join('tbl_categories as ct', 'ct.id', '=', 'ps.category_id')
              ->where('od.order_id', $id)
              ->whereColumn('bs.current_stock', '<', 'od.sQty')
              ->distinct()
              ->selectRaw('ps.product_id, ps.category_id') // Identify product-category combinations
              ->get();

        // Transform excluded combinations into an array of [product_id, category_id]
        $excludedCombinations = $excludedProductCategories->map(function ($item) {
              return $item->product_id . '-' . $item->category_id;
          })->toArray();

           // Step 2: Main query to get fulfilled product-category combinations
          $billItems = DB::table($tblD . ' as sl')
                    ->join('branches_stocks as bs', 'bs.stock_id', '=', 'sl.stock_id')
                    ->leftJoin('tbl_products_stock as st', 'st.id', '=', 'bs.stock_id')
                    ->leftJoin('tbl_products_master as pd', 'pd.id', '=', 'st.product_id')
                    ->leftJoin('tbl_color as atr', 'atr.id', '=', 'st.attribute_id')
                    ->leftJoin('tbl_categories as ct', 'ct.id', '=', 'st.category_id')
                    ->where('sl.order_id', '=', $id)
                    ->where('sl.status', '=', '1') // Ensure only items with status 1
                    ->select('sl.*', 'pd.name as prodName', 'atr.name as attrName', 'ct.name as catName', 'st.product_id', 'st.category_id','bs.current_stock','bs.last_purchase as lpDate','bs.sale_price','bs.purchase_price',
                    DB::raw('(SELECT count(sod.id) 
                            FROM tbl_sale_order_detail as sod 
                            WHERE sod.stock_id = st.id 
                            AND sod.status = 1) as pendingSodCount'),
                    DB::raw('(SELECT SUM(so.sQty) 
                            FROM tbl_sale_order_detail as so 
                            WHERE so.stock_id = st.id 
                            AND so.status = 1) as pendingSodQty'),
                     DB::raw('(SELECT SUM(dp.dispatch_qty) 
                            FROM dispatch_details as dp 
                            WHERE dp.stock_id = st.id 
                            AND dp.status = 1) as dispatchQty')
                    )->get();

        if($type=='ready'){

          $title='Fullfill Items';
          $billdetails=$billItems->filter(function ($item) use ($excludedCombinations) {
            return !in_array($item->product_id . '-' . $item->category_id, $excludedCombinations);
        });

      }else{
          $title='Not Ready Items';
          // Step 2: Main query to get fulfilled product-category combinations

          $billdetails=$billItems->filter(function ($item) use ($excludedCombinations) {
              return in_array($item->product_id . '-' . $item->category_id, $excludedCombinations);
          });
      }  

      $a['title']=$title;
      $a['breadcrumb']='';
      $a['bill']=$bill;
      $a['billdetails']=$billdetails;
      $a['billtype']=$for;

      return view('admin.report.bill_ready_notready')->with($a);
      
    }


    public function productStockValue111(Request $r)
	{
		    
		 $allParent=DB::table('tbl_categories_parent')->select('id','name')->get();
    	 
    	 $cat=DB::table('tbl_categories as ct')->select('ct.id','ct.name')->where('status',0)->get(); 
    	 $catid='';
    	 $parentid='';

		if($r->input('prodNameLike')!='' ){
			$searchname=$r->input('prodNameLike');
		}else{$searchname='';}


      if($r->stockStatus=='1'){
        $cond="st.status='1'";
        $reptitle='Status:Active';
      }elseif($r->stockStatus=='0'){
        $cond="st.status='0'";
        $reptitle='Status:Inactive';
      }elseif($r->stockStatus=='*'){
        $cond='(st.status=0 || st.status=1)';
        $reptitle='Status:All';
      }else{
        $cond='st.status=1';
        $reptitle='Status:Active';
      }



		if(strlen($searchname)>=3)
		{
			if($searchname=='***'){
			$searchname='***';

		   }else{
            $cond.=" and (pm.name like '%".$searchname."%'|| ct.name like '%".$searchname."%')";
            $reptitle.=' | Name Like("'.$searchname.'")';
		   }
		}



    if($r->stockQty!='*')
    {
      if($r->stockQty=='N'){
        $cond.=" and st.current_stock<0";
      }else if($r->stockQty=='NZ'){
        $cond.=" and st.current_stock<=0";
      }else if($r->stockQty=='Z'){
        $cond.=" and st.current_stock=0";
      }else{
        $cond.=" and st.current_stock>=1";
      }
      $reptitle.=' | Stock :'.$r->stockQty;
    }else{
      $cond.=" and st.current_stock>=1";
      $reptitle.=' | Stock Qty: >=1';
    }

	if($r->verified=='1'){
        $cond.=" and st.verified_stock = 1";
		$reptitle.=' | Verified: Yes';
      }else if($r->verified=='0'){
        $cond.=" and st.verified_stock != 1";
		$reptitle.=' | Verified: NO';
      }else if($r->verified=='*'){
       
      }else{
        
      }
     
    if(isset($_POST['parent']) && $_POST['parent']>=1){

			 $cond.=" and st.category_id IN (select category_id from tbl_category_parent_mapping where parent_id='".$_POST['parent']."')";
			 $parentid=$_POST['parent'];
			 	$reptitle.=' | By Parent Category';
		 }
		 elseif(isset($_POST['catID']) && $_POST['catID']>=1)
		 {
			 $cond.=" and st.category_id='".$_POST['catID']."'";
		
			 $catid=$_POST['catID'];
			 	$reptitle.=' | By Category';
		 }else{} 
      
    
	$cond .= ' order by pm.id';


		$stock=DB::select(DB::raw("select st.*,u.name as userName,pm.name as product_name,pm.code as product_code,clr.name as color_name,ct.name as category_name
		from tbl_products_stock AS st
		INNER JOIN tbl_products_master AS pm ON pm.id=st.product_id
		INNER JOIN tbl_color AS clr ON clr.id=st.attribute_id
		INNER JOIN tbl_categories AS ct ON ct.id=st.category_id 
		LEFT JOIN users AS u ON u.id=st.verified_by
		where ".$cond));
		
    $a['stock']=$stock;
    $a['searchname']=$searchname;
    $a['reptitle']=$reptitle;
    $a['cat']=$cat;
    $a['allParent']=$allParent;
    $a['catid']=$catid;
    $a['parentcatid']=$parentid;
    
    return view('admin.report.product_stock_value')->with($a);
	}



  public function productStockValue(Request $request)
  {
        // Fetch parent categories and categories
        $allParent = \App\Models\ParentCatModel::select('id', 'name')->get();
        $categories = \App\Models\Category::select('id', 'name')->where('status', 0)->get();

        // Initialize filters
        $searchName = $request->input('prodNameLike', '');
        $stockStatus = $request->input('stockStatus', '1'); // Default: Active
        $stockQty = $request->input('stockQty', '*'); // Default: Positive stock
        $verified = $request->input('verified', '*'); // Default: All
        $parentId = $request->input('parent', null);
        $categoryId = $request->input('catID', null);
        $branchId = $request->input('branchID', null); // Branch filter

        // Initialize report title
        $reportTitle = 'Product Stock Report';
        $title = 'Product Stock Value';
        // Build Eloquent query with relationships
        $query = \App\Models\BranchStocks::with([
          'stock.product' => fn($q) => $q->select('id', 'name', 'code'),
          'stock.category' => fn($q) => $q->select('id', 'name'),
          'stock.color' => fn($q) => $q->select('id', 'name'),
        //   'stock.verifiedBy' => fn($q) => $q->select('id', 'name'),
        // 'branch' => fn($q) => $q->select('id', 'name')
        ]);
        
        // Apply branch filter
        if ($branchId && $branchId >= 1) {
            $query->where('branch_id', $branchId);
            $reportTitle .= ' | Branch ID: ' . $branchId;
        }

        // Apply stock status filter
        switch ($stockStatus) {
            case '1':
                $query->whereHas('stock', fn($q) => $q->where('status', 1));
                $reportTitle .= ' | Status: Active';
                break;
            case '0':
                $query->whereHas('stock', fn($q) => $q->where('status', 0));
                $reportTitle .= ' | Status: Inactive';
                break;
            case '*':
                $query->whereHas('stock', fn($q) => $q->whereIn('status', [0, 1]));
                $reportTitle .= ' | Status: All';
                break;
            default:
                $query->whereHas('stock', fn($q) => $q->where('status', 1));
                $reportTitle .= ' | Status: Active';
                break;
        }
        
        // Apply search by product or category name
        if (strlen($searchName) >= 3 && $searchName !== '***') {
            $query->whereHas('stock', fn($q) => $q->where(function ($subQ) use ($searchName) {
                $subQ->whereHas('product', fn($q) => $q->where('name', 'like', "%{$searchName}%"))
                    ->orWhereHas('category', fn($q) => $q->where('name', 'like', "%{$searchName}%"));
            }));
            $reportTitle .= ' | Name Like: ' . $searchName;
        }
       
        // Apply stock quantity filter
      /*  switch ($stockQty) {
            case 'N':
                $query->whereHas('stock', fn($q) => $q->where('current_stock', '<', 0));
                $reportTitle .= ' | Stock: Negative';
                break;
            case 'NZ':
                $query->whereHas('stock', fn($q) => $q->where('current_stock', '<=', 0));
                $reportTitle .= ' | Stock: Non-Positive';
                break;
            case 'Z':
                $query->whereHas('stock', fn($q) => $q->where('current_stock', 0));
                $reportTitle .= ' | Stock: Zero';
                break;
            case '*':
                $query->whereHas('stock', fn($q) => $q->where('current_stock', '>=', 1));
                $reportTitle .= ' | Stock: Positive';
                break;
            default:
                $query->whereHas('stock', fn($q) => $q->where('current_stock', '>=', 1));
                $reportTitle .= ' | Stock: Positive';
                break;
        } */
        
        //return $query->get();
        // Apply verified filter
        if ($verified === '1') {
            $query->whereHas('stock', fn($q) => $q->where('verified_stock', 1));
            $reportTitle .= ' | Verified: Yes';
        } elseif ($verified === '0') {
            $query->whereHas('stock', fn($q) => $q->where('verified_stock', '!=', 1));
            $reportTitle .= ' | Verified: No';
        }
       
        // Apply parent category filter
        if ($parentId && $parentId >= 1) {
            $categoryIds = \App\Models\CategoryMapModel::where('parent_id', $parentId)->pluck('category_id');
            $query->whereHas('stock', fn($q) => $q->whereIn('category_id', $categoryIds));
            $reportTitle .= ' | By Parent Category';
        }

        // Apply category filter
        if ($categoryId && $categoryId >= 1) {
            $query->whereHas('stock', fn($q) => $q->where('category_id', $categoryId));
            $reportTitle .= ' | By Category';
        }

        // Order by product_id (via stock relationship)
        $query->orderBy('stock_id');

        // Execute query
       $branchStocks = $query->get();

        // Prepare data for view
        $data = [
            'title' =>$title,
            'breadcrumb' =>'',
            'stock' => $branchStocks,
            'searchname' => $searchName,
            'reptitle' => $reportTitle,
            'cat' => $categories,
            'allParent' => $allParent,
            'catid' => $categoryId,
            'parentcatid' => $parentId,
            'branchid' => $branchId,
        ];

        return view('admin.report.product-stock-value', $data);
    }

public function accFinancialStatus(Request $request)
{
    // Initialize title parts
    $titleParts = ['Account-all'];

    // Check if any filters are applied
    $hasFilter = $request->filled('ddaccountgroup') ||
                 $request->filled('cityState') ||
                 $request->filled('AccountID') ||
                 $request->filled('acType') || 
                 $request->filled('balType');

    // If no filters, return empty data and message
    if (!$hasFilter) {
        $accounts = collect(); // Empty collection
        $allAccount = collect(); // Or fetch minimal necessary data if needed
        $accGroup = AccountGroup::select('id', 'name')->get();
        $title = '(No filters applied)';
        $breadcrumb = '';

        return view('admin.report.account-financial-status', compact(
            'accounts',
            'allAccount',
            'title',
            'breadcrumb',
            'accGroup'
        ));
    }

    // Base query with eager loading
    $query = BranchAccounts::with([
        'account' => function ($q) {
            $q->with(['acGroupData', 'citydata', 'statedata'])->orderBy('name');
        }
    ]);

    // Apply filters as before
    if ($request->filled('ddaccountgroup') && $request->input('ddaccountgroup') !== '*') {
        $query->whereHas('account', function ($q) use ($request) {
            $q->where('acGroup', $request->input('ddaccountgroup'));
        });
        $accNameGroup = AccountGroup::find($request->input('ddaccountgroup'));
        $titleParts[] = 'Group:' . ($accNameGroup->name ?? 'Unknown');
    } else {
        $titleParts[] = 'Group:All';
    }

    if ($request->filled('cityState')) {
        $query->whereHas('account', function ($q) use ($request) {
            $searchTerm = '%' . $request->input('cityState') . '%';
            $q->where('city', 'like', $searchTerm)
              ->orWhere('state', 'like', $searchTerm);
        });
        $titleParts[] = 'City/State:*' . $request->input('cityState') . '*';
    }

    if ($request->filled('AccountID')) {
        $query->whereHas('account', function ($q) use ($request) {
            $q->where('id', $request->input('AccountID'));
        });
        $titleParts[] = 'Account:' . $request->input('AccountID');
    }

    if ($request->filled('acType')) {
        $query->whereHas('account', function ($q) use ($request) {
            $q->where('type', $request->input('acType'));
        });
        $titleParts[] = 'Acc Type:' . $request->input('acType');
    }

    // Get accounts with calculated closing balances
    $accounts = $query->get()->map(function ($account) {
        $calculations = \App\Http\Controllers\Admin\AccountingController::partyCalculateClosing($account->account_id);
        $account->debitTotal = $calculations['debitTotal'] ?? 0;
        $account->creditTotal = $calculations['creditTotal'] ?? 0;
        $account->closing = $this->calculateClosingBalance($account, $calculations);
        return $account;
    });

    // Fetch filtered account list
    $allAccount = BranchAccounts::with(['account' => function ($q) {
        $q->orderBy('name');
    }])->get();

    $accGroup = AccountGroup::select('id', 'name')->get();
    $title = '(' . implode(', ', $titleParts) . ')';
    $breadcrumb = '';

    return view('admin.report.account-financial-status', compact(
        'accounts',
        'allAccount',
        'title',
        'breadcrumb',
        'accGroup'
    ));
}

  
  private function calculateClosingBalance($account, $totals)
  {
      $closingBalance = 0;
      
          $closingBalance = $account->opening_balance_type == 'Dr'
              ? ($totals['creditTotal'] - $totals['debitTotal']) - $account->opening_balance
              : ($totals['creditTotal'] - $totals['debitTotal']) + $account->opening_balance;

      return $closingBalance;
  }
}


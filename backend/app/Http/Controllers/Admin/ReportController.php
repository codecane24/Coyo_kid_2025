<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Content;
use App\Http\Controllers\WebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;

use App\Models\Sales;
use App\Models\Purchase;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\SaleOrder;
use App\Models\PurchaseOrder;
use App\Models\StockModel;
use App\Models\BranchStocks;
use App\Models\Account;
use App\Models\Employee;
use App\Models\ProductMaster;
use App\Models\ProductImageGallery;
use App\Models\CategoryParentMapping;
use App\Models\Color;


class ReportController extends WebController
{

    public function mainProductCatalogue(Request $rq)
    {

        // Initialize variables with default values
        $minStock = (int) $rq->input('minQty', 0);
        $title = 'Main Product Catalogue';
        $length = (int) $rq->input('length', 20);
        $page = (int) $rq->input('page', 1);
        $priceOrder = $rq->input('priceOrder', 'asc') === 'desc' ? 'desc' : 'asc';
        $status = $rq->input('onlyInactive') === 'Inactive Variants' ? 0 : 1;
        $prodid = $rq->input('prodID','');
        $parentid= $rq->input('parent','');
        $catid= $rq->input('catID','');
        $fromRate = $rq->input('fromRate','');
        $toRate = $rq->input('toRate','');
        $colorid = $rq->input('colorID',[]);

        if ($status === 0) {
            $title .= '<h3 class="text-danger">Inactive Items</h3>';
        }

        // Fetch filters
        $allParent = DB::table('tbl_categories_parent')->select('id', 'name')->get();
        $prod = DB::table('tbl_products_master')->where('status', 1)->select('id', 'name')->orderBy('name')->get();
        $cat = DB::table('tbl_categories')->where('status', 0)->select('id', 'name')->get();
        $color = DB::table('tbl_color')->select('id', 'name')->get();

        // Base query
        $query = DB::table('tbl_products_stock as st')
            ->join('tbl_products_master as p', 'p.id', '=', 'st.product_id')
            ->join('tbl_categories as ct', 'ct.id', '=', 'st.category_id')
            ->select(
                'st.id',
                'st.product_id',
                'st.category_id',
                'st.attribute_id',
                'p.name as prodName',
                'p.code as prodCode',
                'p.image as productImage',
                'ct.name as catName',
                'st.sPriceUpdate_date',
                DB::raw("(SELECT SUM(sQty) FROM tbl_sale_order_detail WHERE stock_id = st.id AND status = 'active') as sod"),
                DB::raw("(SELECT SUM(sQty) FROM tbl_purchase_order_detail WHERE stock_id = st.id AND status = 'active') as pod")
            )
            ->where('st.status', $status)
            ->where('st.current_stock', '>=', $minStock);

    // Apply filters
    if ($rq->filled('prodName')) {
        $query->where('p.name', 'like', '%' . $rq->input('prodName') . '%');
        $title .= ' | <small>Product Name Like %' . htmlspecialchars($rq->input('prodName')) . '%</small> | ';
    }

    if ($rq->filled('prodID')) {
        $query->where('st.product_id', $rq->input('prodID'));
        $product = DB::table('tbl_products_master')->find($rq->input('prodID'));
        $title .= $product ? ' | <small>' . htmlspecialchars($product->name) . '</small> | ' : '';
    }

    if ($rq->filled('parent')) {
        $query->whereIn('st.category_id', function ($subQuery) use ($rq) {
            $subQuery->select('category_id')
                ->from('tbl_category_parent_mapping')
                ->where('parent_id', $rq->input('parent'));
        });
    } elseif ($rq->filled('catID')) {
        $query->where('st.category_id', $rq->input('catID'));
        $category = DB::table('tbl_categories')->find($rq->input('catID'));
        $title .= $category ? ' | <small>' . htmlspecialchars($category->name) . '</small> | ' : '';
    }

    if ($rq->filled('colorID')) {
        $query->whereIn('st.attribute_id', $rq->input('colorID'));
    }

    if ($rq->filled('fromRate') || $rq->filled('toRate')) {
        $fromRate = (float) $rq->input('fromRate', 0);
        $toRate = (float) $rq->input('toRate', PHP_INT_MAX);
        $query->whereBetween('st.sale_price', [$fromRate, $toRate]);
        $title .= " | <small>Price: $fromRate - $toRate</small> | ";
    }

    // Fulfill logic
    if ($rq->input('fulfill') === 'yes')
    {
        $query->where('st.status', 1);

        if ($rq->filled('colorID')) 
        {
            $colorIDs = $rq->input('colorID');
            $subQuery = DB::table('tbl_products_stock')
                ->selectRaw('DISTINCT product_id')
                ->whereIn('attribute_id', $colorIDs)
                ->where('status', 1)
                ->where('current_stock', '>=', $minStock)
                ->groupBy('product_id', 'category_id')
                ->havingRaw('COUNT(attribute_id) >= ?', [count($colorIDs)]);

            if ($rq->filled('parent')) {
                $subQuery->whereIn('category_id', function ($parentSubQuery) use ($rq) {
                    $parentSubQuery->select('category_id')
                        ->from('tbl_category_parent_mapping')
                        ->where('parent_id', $rq->input('parent'));
                });
            }

            $query->whereIn('st.product_id', $subQuery);
        }
    }else{
        $query->groupBy('st.product_id', 'st.category_id');
    }

    // Apply ordering
    $query->orderBy('st.sale_price', $priceOrder);
   // $rr=$query;
    //return print_query($rr);
    // Paginate results
     $results = $query->paginate($length, ['*'], 'page', $page);

    // Fetch additional data for each product
    foreach ($results as $product) {
        $gallery = DB::table('tbl_products_image_gallery')
            ->where('product_id', $product->product_id)
            ->where('category_id', $product->category_id)
            ->orderBy('id', 'desc')
            ->first();

        if($gallery && !empty($gallery->image)) {
            $product->productImage = $gallery->image;
        }

        $product->details = DB::table('tbl_products_stock as st')
            ->leftJoin('tbl_categories as ct', 'ct.id', '=', 'st.category_id')
            ->leftJoin('tbl_color as atr', 'atr.id', '=', 'st.attribute_id')
            ->select(
                'st.id',
                'st.product_id',
                'st.category_id',
                'st.attribute_id',
                'st.current_stock',
                'st.sale_price',
                'st.wholesale_price',
                'st.purchase_price',
                'ct.name as catName',
                'atr.name as atrName',
                'st.sPriceUpdate_date',
                'st.pPriceUpdate_date',
                'st.lastpurchase as lpd',
                DB::raw("(SELECT SUM(sQty) FROM tbl_sale_order_detail WHERE stock_id = st.id AND status = 'active') as sod"),
                DB::raw("(SELECT SUM(sQty) FROM tbl_purchase_order_detail WHERE stock_id = st.id AND status = 'active') as pod")
            )
            ->where('st.product_id', $product->product_id)
            ->where('st.category_id', $product->category_id)
            ->where('st.current_stock', '>=', $minStock)
            ->get();
    }

    //return $results;
    // Prepare response
    return view('admin.catalogue.main-catalogue', [
        'breadcrumb'=>breadcrumb([
            'Catalogue' => '',
        ]),
        'minqty' => $minStock,
        'fromRate' => $fromRate,
        'toRate' => $toRate,
        'stock' => $results,
        'prod' => $prod,
        'parentid' => $parentid,
        'prodid' =>$prodid,
        'colorid' => $colorid,
        'cat' => $cat,
        'catid' => $catid,
        'color' => $color,
        'allParent' => $allParent,
        'title' => $title,
        'length' => $length,
        'page' => $page,
        'priceOrder' => $priceOrder,
    ]);
}

//====Latest Product Catalogue===
public function LatestCatalogue(Request $rq)
 {
   
        // Define defaults
        $title = 'Latest Product Catalogue';
        $pageHead = 'Latest Items';
        $length = $rq->input('length', 20);
        $page = $rq->input('page', 1);
        $stock = [];
        $resultCount = 0;
        $fcatid = null;
        $fpCatid = null;
    
        // Fetch parent categories
        $allParent = \app\Models\ParentCatModel::select('id', 'name')->get();
    
        // Prepare category query
        $categoriesQuery = Category::query()
            ->leftJoin('tbl_category_parent_mapping as cpm', 'cpm.category_id', '=', 'tbl_categories.id')
            ->groupBy('cpm.category_id')
            ->select('tbl_categories.id', 'tbl_categories.name', 'cpm.parent_id')
            ->where('tbl_categories.status', 0);
    
        // Prepare product query
        $productsQuery = ProductStock::with('product', 'category')
            ->where('status', 1)
            ->whereIn(
                'product_id',
                ProductMaster::orderByDesc('id')
                    ->limit(450)
                    ->pluck('id')
            );
    
        // Apply filters
        if ($rq->filled('parent') && $rq->input('parent') >= 1) {
            $parentID = $rq->input('parent');
            $allParentCat = CategoryParentMapping::where('parent_id', $parentID)->pluck('category_id');
            $productsQuery->whereIn('category_id', $allParentCat);
            $fpCatid = $parentID;
        } elseif ($rq->filled('catID') && $rq->input('catID') >= 1) {
            $categoryID = $rq->input('catID');
            $productsQuery->where('category_id', $categoryID);
            $fcatid = $categoryID;
        }
    
        // Fetch categories
        $categories = $categoriesQuery->get();
    
        // Finalize product query
        $products = $productsQuery
            ->where('sale_price', '>', 0)
            ->where('purchase_price', '>', 0)
            ->where('current_stock', '>', 0)
            ->groupBy('product_id', 'category_id')
            ->select(['product_id', 'category_id'])
            ->with(['product:id,name,image'])
            ->orderByDesc('product_id')
            ->get();
    
        // Paginate results
        $collection = collect($products);
        $paginatedProducts = new LengthAwarePaginator(
            $collection->forPage($page, $length),
            $collection->count(),
            $length,
            $page,
            ['path' => $rq->url()]
        );
    
        // Fetch stock details for each product
        foreach ($paginatedProducts as $product) {
            $details = ProductStock::with(['category', 'attribute'])
                ->where('status', 1)
                ->where('product_id', $product->product_id)
                ->where('category_id', $product->category_id)
                ->where('sale_price', '>', 0)
                ->where('purchase_price', '>', 0)
                ->where('current_stock', '>', 0)
                ->select(
                    'id',
                    'product_id',
                    'category_id',
                    'current_stock',
                    'purchase_price',
                    'sale_price',
                    'wholesale_price',
                    'sPriceUpdate_date',
                    'pPriceUpdate_date',
                    'lastpurchase as lpd'
                )
                ->withSum('psod as sod', 'sQty')
                ->withSum('psod as pod', 'sQty')
                ->get();
    
            // Fetch product gallery
            $gallery = ProductImageGallery::where('product_id', $product->product_id)
                ->where('category_id', $product->category_id)
                ->first();
    
            if ($gallery && !empty($gallery->image)) {
                $product->product->image = $gallery->image;
            }
    
            $product->details = $details;
            $stock[] = $product;
            $resultCount++;
        }
    
        // Prepare view data
        $data = [
            'breadcrumb'=>breadcrumb([
            'Latest Product Catalogue' => '',
            ]),
            'Title' => $title,
            'pageHead' => $pageHead,
            'stock' => $stock,
            'cat' => $categories,
            'resultcount' => $resultCount,
            'parentcat' => $allParent,
            'fcatid' => $fcatid,
            'fpCatid' => $fpCatid,
            'length' => $length,
            'page' => $page,
            'page1' => $paginatedProducts,
        ];
    
        // Retain input and return view
        session()->flashInput($rq->input());
        return view('admin.report.common_catalogue', $data);
    
    

 }
    
    	//======= Report order/ Dispatch with detail Report========
        public function allDispatchList(Request $rq)
        {
            
            $title = 'All Dispatch Report';
           
            // Fetch accounts and employees
            $accounts = Account::whereIn('acGroup', [4])->with('acGroupData')->orderBy('name')->get();
            $employees = Employee::orderBy('name')->select('id', 'name')->get();
            
            // Initialize query with relationships
           $query = SaleOrder::whereHas('details',function($a){
                        $a->where('sQty', '>=', 1) // Check detail's current_stock
                        ->whereHas('bstock', function($q) {
                            $q->where('current_stock', '>=', 1); // Check bstock's current_stock
                        });
            })->with(['details' => function($a) {
                    $a->whereHas('bstock', function($q) {
                        $q->where('current_stock', '>=', 1);
                    })->where('sQty', '>=', 1);
                },
                'details.bstock.stock.product:id,name,code',
                'details.bstock.stock.color:id,name',
                'details.bstock.stock.category:id,name',
                'details.bstock' => fn($q) =>$q->withSum('psod as sod', 'sQty'),
                'account:id,name,block_status,type',
                'salesman:id,name'
            ])
            ->whereIn('status', [0,1,2]);
           
            
        
            // Apply filters dynamically
            if ($rq->filled(['fromdate', 'todate'])) {
                $query->whereBetween('billDate', [$rq->fromdate, $rq->todate]);
                $title .= " | {$rq->fromdate} - {$rq->todate}";
            }
        
            if ($rq->filled('partyid')) {
                $query->where('account_id', $rq->partyid);
                $party = Account::find($rq->partyid);
                if ($party) {
                    $title .= " | {$party->name}";
                }
            }
        
            if ($rq->filled('stockid')) {
                $query->whereHas('details', fn($q) => $q->where('stock_id', $rq->stockid));
                $title .= " | Stock ID - {$rq->stockid}";
            }
        
            if ($rq->filled('partyType') && $rq->partyType != '*') {
                $query->whereHas('account', fn($q) => $q->where('type', $rq->partyType));
                
                $title .= " | (".customerTypeName($rq->partyType) ?? 'Other'.")";
            }
        
            if ($rq->filled('orderNo')) {
                $query->where('invoice_No', 'like', "%{$rq->orderNo}");
                $title .= " | Inv No. - {$rq->orderNo}";
            }
        
            if ($rq->filled('salesman')) {
                $query->where('salesman_id', $rq->salesman);
                $title .= " | By Sales Person";
            }
        
            // Filter orders with advance payment
            if ($rq->has('advancePaidAc')) {
                $advancePaidAccounts = $accounts->filter(fn($account) =>
                    $this->partyCalculateClosing($account->id, null, null, 'closingAmt') < 0
                )->pluck('id');
        
                if ($advancePaidAccounts->isNotEmpty()) {
                    $query->whereIn('account_id', $advancePaidAccounts);
                    $title .= " | Order With Advance Payment";
                }
            }
            
        

            // Fetch orders with all related data
           // return print_query($query);
            $orders=$query->get();

            $data = [
                'title' => $title,
                'breadcrumb' => breadcrumb([
                    'Stock Status' => route('admin.stock.stock-status', '*'),
                ]),
                'accounts' => $accounts,
                'employees' => $employees,
                'orders' => $orders,
                'reqType' => 'all',
            ];
        
            return view('admin.report.dispatch-detail')->with($data);
        }
        
        
        //====== Order Fullfil Status ==================
        public function fullfilDispatchList(Request $request)
        {
            // Base query with all necessary relationships
            $query = SaleOrder::with([
                'details' => function($q) {
                    $q->where('sQty', '>=', 1)
                    ->with([
                        'bstock.stock.product:id,name,code',
                        'bstock.stock.color:id,name',
                        'bstock.stock.category:id,name',
                        'bstock' => fn($q) =>$q->withSum('psod as sod', 'sQty'),
                    ]);
                },
                'account:id,name,block_status,type',
                'salesman:id,name'
            ])
            ->whereIn('status', [0,1,2])
            ->whereHas('details', function($q) {
                $q->where('sQty', '>=', 1);
            });

            // Build title and apply filters (same as before)
            $title = 'FullFill Dispatch Report';

            if ($request->filled(['fromdate', 'todate'])) {
                $query->whereBetween('saleDate', [$request->fromdate, $request->todate]);
                $title .= " | {$request->fromdate} | {$request->todate}";
            }

            if ($request->filled('partyid')) {
                $query->where('account_id', $request->partyid);
                $title .= " | " . Account::find($request->partyid)->name;
            }

            if ($request->filled('stockid')) {
                $query->whereHas('details', fn($q) => $q->where('stock_id', $request->stockid));
                $title .= " | Stock-id-{$request->stockid}";
            }

            if ($request->filled('partyType') && $request->partyType != '*') {
                $query->whereHas('account', fn($q) => $q->where('type', $request->partyType));
                $typeName = [1 => 'Distributor', 2 => 'Whole Seller'][$request->partyType] ?? 'Other';
                $title .= " | ({$typeName})";
            }

            if ($request->filled('orderNo')) {
                $query->where('invoice_No', 'like', "%{$request->orderNo}%");
                $title .= " | Inv No.-{$request->orderNo}";
            }

            if ($request->filled('salesman')) {
                $query->where('salesman_id', $request->salesman);
                $title .= " | By Sales Person";
            }

            if ($request->has('advancePaidAc')) {
                $advAc = Account::where('acGroup', 4)
                                ->get()
                                ->filter(fn($ac) => $this->calculateClosingBalance($ac) < 0)
                                ->pluck('id');
                $query->whereIn('account_id', $advAc);
                $title .= " | Order With Advance Payment";
            }

            // Get orders and filter them
            $orders = $query->get()->map(function($order) {
                // First find all problematic combinations (where ANY item in the combination is out of stock)
                $problemCombinations = $order->details
                    ->filter(function ($detail) {
                        return $detail->bstock && $detail->bstock->current_stock < $detail->sQty;
                    })
                    ->map(function($detail) {
                        return $detail->bstock->stock 
                            ? "{$detail->bstock->stock->product_id}-{$detail->bstock->stock->category_id}"
                            : null;
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                // Now filter out ALL details that match any problematic combination
                $order->details = $order->details->filter(function ($detail) use ($problemCombinations) {
                    if (!$detail->bstock || !$detail->bstock->stock) {
                        return false;
                    }
                    
                    $currentCombination = "{$detail->bstock->stock->product_id}-{$detail->bstock->stock->category_id}";
                    return !in_array($currentCombination, $problemCombinations);
                });

                return $order;
            })->filter(function($order) {
                // Only keep orders that have at least one remaining detail
                return $order->details->isNotEmpty();
            });

            // Prepare view data
            $data = [
                'title' => $title,
                'accounts' => Account::where('acGroup', 4)->with('acGroupData')->orderBy('name')->get(),
                'employees' => Employee::orderBy('name')->select('id', 'name')->get(),
                'orders' => $orders,
                'reqType' => 'ready',
                'breadcrumb' => breadcrumb([
                    'Stock Status' => route('admin.stock.stock-status', '*'),
                ])
            ];

            return view('admin.report.dispatch-detail', $data);
        }
                
   
    //===========Bill Register==================
    public function billwisedetails(Request $request, $repType = null)
    {
        // Determine report type from request or parameter
        $repType = $request->filled('reptype') ? $request->input('reptype') : $repType;

        // Map report type to model and title
        $modelMap = [
            'sale' => [Sales::class, 'Sale Register', 'bill-register-detail'],
            'purchase' => [Purchase::class, 'Purchase Register', 'bill-register-detail'],
            'sale-order' => [SaleOrder::class, 'Sale-Order Register', 'order-register-detail'],
            'purchase-order' => [PurchaseOrder::class, 'Purchase Order Register', 'order-register-detail'],
            'sale-return' => [SalesReturn::class, 'Sale Return Register', 'bill-register-detail'],
            'purchase-return' => [PurchaseReturn::class, 'Purchase Return Register', 'bill-register-detail'],
        ];

        // Set query and title based on repType
        [$modelClass, $baseTitle, $viewfile] = $modelMap[$repType] ?? [null, null, null];
        $title = $baseTitle;
        $viewfile = $viewfile ?? 'bill-register-detail';

        // Initialize empty collections
        $orders = collect();
        $accounts = collect();
        $employees = collect();

        // If no valid repType, return empty results with default view
        if (!$modelClass) {
            $data = [
                'title' => 'Invalid Report Type',
                'accounts' => $accounts,
                'employees' => $employees,
                'orders' => $orders,
                'repType' => $repType,
                'fillter' => $request->all(),
                'breadcrumb' => breadcrumb(['Bill Register' => route('admin.report.bill-register', '*')]),
            ];
            return view('admin.report.bill-register')->with($data);
        }

        // Fetch account and salesman IDs for filtering dropdowns (always, by default)
        $baseQuery = $modelClass::query();
        $acIds = $baseQuery->clone()->pluck('account_id')->unique()->toArray();
        $smIds = $baseQuery->clone()->pluck('salesman_id')->unique()->toArray();
        $accounts = Account::whereNotIn('acGroup', [1, 2])->whereIn('id', $acIds)->get();
        $employees = Employee::whereIn('id', $smIds)->get();

        // Check if any meaningful filters are applied
        $hasFilters = $request->filled('AccountID') && $request->AccountID >= 1 ||
                    $request->filled('employeeID') ||
                    $request->filled('acType') && $request->acType !== '*' ||
                    $request->filled('stockid') && $request->stockid >= 1 ||
                    $request->filled(['fromdate', 'todate']);

        if ($hasFilters) {
            // Initialize query with relationships
            $query = $modelClass::query()->with(['account', 'user', 'details', 'details.bstock', 'salesman']);

            // Apply filters
            if ($request->filled('AccountID') && $request->AccountID >= 1) {
                $query->where('account_id', $request->AccountID);
                $title .= ' | <small>' . Account::findOrFail($request->AccountID)->name . '</small>';
            }

            if ($request->filled('employeeID')) {
                $query->where('salesman_id', $request->employeeID);
                $title .= ' | <small>' . Employee::findOrFail($request->employeeID)->name . '</small>';
            }

            if ($request->filled('acType') && $request->acType !== '*') {
                $query->whereHas('account', fn($q) => $q->where('type', $request->acType));
            }

            if ($request->filled('stockid') && $request->stockid >= 1) {
                $query->whereHas('details', fn($q) => $q->where('stock_id', $request->stockid));
            }

            if ($request->filled(['fromdate', 'todate'])) {
                $query->whereBetween('billDate', [
                    $request->fromdate . ' 00:00:01',
                    $request->todate . ' 23:59:59'
                ]);
                $title .= '<small><br>(' . $request->fromdate . ' - ' . $request->todate . ')</small>';
            }

            // Fetch results
            $orders = $query->orderByDesc('id')
                ->orderByDesc('billDate')
                ->get();
        }

        // Prepare view data
        $data = [
            'title' => $title ?? 'Bill Register',
            'accounts' => $accounts,
            'employees' => $employees,
            'orders' => $orders,
            'repType' => $repType,
            'fillter' => $request->all(),
            'breadcrumb' => breadcrumb(['Bill Register' => route('admin.report.bill-register', '*')]),
        ];

        return view('admin.report.' . $viewfile)->with($data);
    }

    //======Sale Order / Purchase Order Manual Clear=========
    public function orderManualClear(Request $r){
        $repType=$r->input('repType');
        $query=false;
        if($repType=='sale-order'){
            $odrtype='sale_order';
            $query=\App\Models\SaleOrderDetail::whereIn('status',[1,2])
                    ->whereIn('id',$r->clearsocheck)
                    ->where('sQty','>=',1)->get();
             $upqry=\App\Models\SaleOrderDetail::whereIn('id',$r->clearsocheck)->where('sQty','>=',1);
        }
        if($repType=='purchase-order'){
            $odrtype='purchase_order';
            $query=\App\Models\PurchaseOrderDetail::whereIn('status',[1,2])
            ->whereIn('id',$r->clearpocheck)
            ->where('sQty','>=',1)->get();

            $upqry=\App\Models\PurchaseOrderDetail::whereIn('id',$r->clearpocheck)->where('sQty','>=',1);
        }
        
        if($query){
            foreach($query as $q){
                $clr=new \App\models\OrderClearLog;
                $clr->order_id=$q->order_id;
                $clr->stock_id=$q->stock_id;
                $clr->order_type=$odrtype;
                $clr->sQty= $q->sQty;
                $clr->remarks='M';
                $clr->user_id=auth()->user()->id;
                $clr->save();
               
            }
            $upqry->update([
                'status'=>0,
                'sQty'=>0,
                'clear_status'=>'M'
                ]);


            return redirect()->back()->with('message','Order cleared successfully.');
        }

        return redirect()->back()->with('message','Unable to perform the action');
    }
    


    //============Billwise Report===============
    public function billRegister(Request $request, $repType = null)
    {
        $title='Bill Retister : '.$repType;
        $fdata=$this->billregisterFilter($request,$repType);
        $accounts = Account::whereNotIn('acGroup', [1, 2])->get();
        $employees = Employee::get();
        //$data = $fdata['bill'];
            // Prepare view data
            $data = [
                'title' => $title,
                'accounts' => $accounts,
                'employees' => $employees,
               // 'orders' => $orders,
                'repType' => $repType,
                'fillter' => $request->all(),
                'breadcrumb' => breadcrumb(['Bill Register' => route('admin.report.bill-register', '*')]),
            ];
    
            // For debugging: uncomment to see the SQL
            // dd($query->toSql(), $query->getBindings());
    
            return view('admin.report.bill-register')->with($data);
    }

    public function billregisterFilter($request, $repType){
        // Determine report type from request or parameter
        $repType = $request->filled('reptype') ? $request->input('reptype') : $repType;
       
        // Map report type to model and title
        $modelMap = [
            'sale' => [Sales::class, 'Sale Register'],
            'purchase' => [Purchase::class, 'Purchase Register'],
            'sale-order' => [SaleOrder::class, 'Sale-Order Register'],
            'purchase-order' => [PurchaseOrder::class, 'Purchase Order Register'],
            'sale-return' => [SalesReturn::class, 'Sale Return Register'],
            'purchase-return' => [PurchaseReturn::class, 'Purchase Return Register'],
        ];

        // Set query and title based on repType
        [$modelClass, $baseTitle] = $modelMap[$repType] ?? [null, null];
        $query = $modelClass ? $modelClass::query() : null;
        $title = $baseTitle;

        // If no valid repType, return empty results
        if (!$query) {
            $data = [
                'title' => 'Invalid Report Type',
                'accounts' => collect(),
                'employees' => collect(),
                'orders' => collect(),
                'repType' => $repType,
                'fillter' => $request->all(),
                'breadcrumb' => breadcrumb(['Bill Register' => route('admin.report.bill-register', '*')]),
            ];
            return view('admin.report.bill-register')->with($data);
        }

        // Fetch account and salesman IDs for filtering dropdowns
        $acIds = $query->clone()->pluck('account_id')->unique()->toArray();
        $smIds = $query->clone()->pluck('salesman_id')->unique()->toArray();
        $accounts = Account::whereNotIn('acGroup', [1, 2])->whereIn('id', $acIds)->get();
        $employees = Employee::whereIn('id', $smIds)->get();

        // Apply filters to the main query
        $query->with(['account', 'user', 'salesman']); // Eager load relationships

        if (in_array($repType, ['sale', 'purchase', 'sale-return', 'purchase-return'])) {
            // $query->where('order_type', $repType); // Uncomment if applicable
        }

        if ($request->filled('AccountID') && $request->AccountID >= 1) {
            $query->where('account_id', $request->AccountID);
            $title .= ' | <small>' . Account::findOrFail($request->AccountID)->name . '</small>';
            // Removed: return $query->toSql(); // This was breaking the query chain
        }

        if ($request->filled('employeeID')) {
            $query->where('salesman_id', $request->employeeID);
            $title .= ' | <small>' . Employee::findOrFail($request->employeeID)->name . '</small>';
        }

        if ($request->filled('acType') && $request->acType !== '*') {
            $query->whereHas('account', fn($q) => $q->where('type', $request->acType));
        }

        if ($request->filled('stockid') && $request->stockid >= 1) {
            $query->whereHas('details', fn($q) => $q->where('stock_id', $request->stockid));
        }

        if ($request->filled(['fromdate', 'todate'])) {
            $query->whereBetween('billDate', [
                $request->fromdate . ' 00:00:01',
                $request->todate . ' 23:59:59'
            ]);
            $title .= '<small><br>(' . $request->fromdate . ' - ' . $request->todate . ')</small>';
        }

        // Fetch results
        $orders = $query->orderByDesc('id')
            ->orderByDesc('billDate');
        return $orders->get();
    }

    public function billRegisterDatatable(Request $request)
    {
        // 1. Model Mapping
        $modelMap = [
            'sale' => [Sales::class, 'Sale Register'],
            'purchase' => [Purchase::class, 'Purchase Register'],
            'sale-order' => [SaleOrder::class, 'Sale-Order Register'],
            'purchase-order' => [PurchaseOrder::class, 'Purchase Order Register'],
            'sale-return' => [SalesReturn::class, 'Sale Return Register'],
            'purchase-return' => [PurchaseReturn::class, 'Purchase Return Register'],
        ];

        $repType = $request->input('repType');
        if (!isset($modelMap[$repType])) {
            return DataTables::of([])->make(true);
        }

        [$modelClass, $baseTitle] = $modelMap[$repType];
        $title = $baseTitle;

        // 2. Base Query with Eager Loading
        $query = $modelClass::query()
            ->with([
                'account.statedata',
                'account.citydata',
                'salesman',
                'details.bstock' // Needed for purchase value calculation
            ]);

        // 3. Apply Filters
        if ($request->filled('AccountID') && $request->AccountID >= 1) {
            $query->where('account_id', $request->AccountID);
            $account = Account::find($request->AccountID);
            $title .= $account ? ' | <small>' . $account->name . '</small>' : '';
        }

        if ($request->filled('employeeID')) {
            $query->where('salesman_id', $request->employeeID);
            $employee = Employee::find($request->employeeID);
            $title .= $employee ? ' | <small>' . $employee->name . '</small>' : '';
        }

        if ($request->filled('acType') && $request->acType !== '*') {
            $query->whereHas('account', fn($q) => $q->where('type', $request->acType));
        }

        if ($request->filled('stockid') && $request->stockid >= 1) {
            $query->whereHas('details', fn($q) => $q->where('stock_id', $request->stockid));
        }

        if ($request->filled('fromdate') && $request->filled('todate')) {
            $query->whereBetween('billDate', [
                Carbon::parse($request->fromdate)->startOfDay(),
                Carbon::parse($request->todate)->endOfDay()
            ]);
            $title .= '<small><br>(' . $request->fromdate . ' - ' . $request->todate . ')</small>';
        }

        // 4. Handle Search
        if ($searchValue = $request->input('search.value')) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('invoice_no', 'like', "%{$searchValue}%")
                ->orWhereHas('account', fn($q) => $q->where('name', 'like', "%{$searchValue}%"))
                ->orWhere('bill_amount', 'like', "%{$searchValue}%")
                ->orWhereHas('salesman', fn($q) => $q->where('name', 'like', "%{$searchValue}%"));
            });
        }

        // 5. Handle Ordering
        if ($request->has('order')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');
            
            $orderColumns = [
                1 => 'invoice_no',
                3 => 'account_id',
                9 => 'bill_amount',
                11 => 'salesman_id'
            ];

            if (isset($orderColumns[$orderColumnIndex])) {
                $column = $orderColumns[$orderColumnIndex];
                if ($column === 'account_id') {
                    $query->orderBy(Account::select('name')->whereColumn('accounts.id', 'account_id'), $orderDirection);
                } elseif ($column === 'salesman_id') {
                    $query->orderBy(Employee::select('name')->whereColumn('users.id', 'salesman_id'), $orderDirection);
                } else {
                    $query->orderBy($column, $orderDirection);
                }
            } else {
                $query->orderBy('id', 'desc')->orderBy('billDate', 'desc');
            }
        } else {
            $query->orderBy('id', 'desc')->orderBy('billDate', 'desc');
        }

        // 6. Get filtered results (for both table and aggregates)
        $results = $query->get();

        // 7. Build DataTable Response
        return DataTables::of($results)
            ->addIndexColumn()
            ->addColumn('accountName', function ($row) {
                $name = optional($row->account)->name ?? '';
                if ($row->account?->statedata) {
                    $name .= '<br><small>' . $row->account->statedata->name;
                    $name .= $row->account->citydata ? ', ' . $row->account->citydata->name : '';
                    $name .= '</small>';
                }
                return $name;
            })
            ->addColumn('billDateTime', fn($row) => myDateFormat($row->billDate))
            ->addColumn('salesPerson', fn($row) => optional($row->salesman)->name ?? '')
            ->addColumn('purchase_value', fn($row) => $row->TotalPurchaseValue ?? 0)
            ->addColumn('profit', function($row) {
                if (!$row->bill_amount || !$row->TotalPurchaseValue) return '-';
                $profitAmount = $row->bill_amount - $row->TotalPurchaseValue;
                $profitRate = (($row->bill_amount - $row->TotalPurchaseValue) * 100 / $row->bill_amount);
                $profitAmount = round($profitAmount, 2);
                if($profitAmount<0){
                    return $profitAmount.' <span class="badge bg-danger">'.round($profitRate) . '%</span>';
                }else{
                    return $profitAmount.' <span class="badge bg-success">'.round($profitRate) . '%</span>';
                }
                
            })
            ->rawColumns(['accountName','profit'])
            ->with([
                'title' => $title,
                'totalOtherCharges' => $results->sum('other_charges'),
                'totalBillAmount' => $results->sum('bill_amount'),
                'totalBillFreight' => $results->sum('freight'),
                'totalBillGst' => $results->sum('tax_amount'),
                'totalBillDiscount' => round($results->sum('discount')),
                'totalPurchaseValue' => $results->sum('TotalPurchaseValue'),
                'totalProfit' => round( $results->sum('bill_amount') - $results->sum('TotalPurchaseValue')),
                'totalProfitPercentage' => $results->sum('bill_amount') > 0 ? 
                    round((($results->sum('bill_amount') - $results->sum('TotalPurchaseValue')) * 100) / $results->sum('bill_amount')) . '' : '0%',
            ])
            ->make(true);
    }

    //======Stock Movement =====
    public function StockMovement(Request $request, ?int $stockId = null)
    {
        // Default financial year start
        $fyearStart = env('APP_YEAR', Carbon::now()->startOfYear()->month(4)->day(1)->toDateString());
        $title = 'Stock Movement';
        $stockData = null;
        $stockMovements = collect(); // Empty collection by default

        // Determine stock ID (POST takes precedence)
        $stockId = $request->isMethod('post') && $request->filled('stockID') && $request->input('stockID') >= 1
            ? (int) $request->input('stockID')
            : $stockId;

        if ($stockId) {
           

            // Fetch stock data
            $stockData = BranchStocks::where('id', $stockId)->with('stock')->first();
            if (!$stockData) {
                Toastr::error('Stock not found.', 'Error');
                return redirect()->back()->with('message', 'Stock not found.');
            }

            // Build conditions
            $conditions = ['status' => 'active', 'stock_id' => $stockId];

            // Add account filter
            $accountName = null;
            if ($request->filled('AccountID') && $request->input('AccountID') >= 1) {
                $conditions['account_id'] = (int) $request->input('AccountID');
                $account = Account::find($conditions['account_id']);
                $accountName = $account->name ?? 'Unknown';
            }

            // Set date range
            $fromDate = $request->input('fromdate', $fyearStart);
            $toDate = $request->input('todate', Carbon::today()->toDateString());

            // Build title
            $title .= $accountName ? " | {$accountName}" : '';
            $title .= " | {$fromDate} - {$toDate}";

            // Fetch stock movements
            try {
                $stockMovements = $this->getStockMovements($conditions, $fromDate, $toDate);
                if ($stockMovements->isEmpty()) {
                    Toastr::info('No stock movements found for the given criteria.', 'Info');
                    //Session::flash('message', 'No stock movements found for the given criteria.');
                }
            } catch (\Exception $e) {
                Toastr::error('Failed to fetch stock movements: ' . $e->getMessage(), 'Error');
                return redirect()->back()->with('message', 'Failed to fetch stock movements.');
            }
        }
        
        // Prepare view data
        $data = [
            'title' => $title, 
            'breadcrumb' => breadcrumb([
                'Stock Status' => route('admin.report.stock-movement', '*'),
            ]),
            'stock' => $stockData,
            'data' => $stockMovements,
            'stockId' => $stockId,
            'fromDate' => $request->input('fromdate', $fyearStart),
            'toDate' => $request->input('todate', Carbon::today()->toDateString()),
            'accountId' => $request->input('AccountID'),
        ];

        return view('admin.report.stock-movement', $data);
    }
    /**
     * Fetch stock movements from sales, purchases, and returns.
     *
     * @param array $conditions
     * @param string $fromDate
     * @param string $toDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getStockMovements(array $conditions, string $fromDate, string $toDate): \Illuminate\Support\Collection
    {
        // Validate inputs
        $validator = Validator::make([
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ], [
            'fromDate' => 'required|date_format:Y-m-d',
            'toDate' => 'required|date_format:Y-m-d|after_or_equal:fromDate',
        ]);
    
        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid date range: ' . implode(', ', $validator->errors()->all()));
        }
    
        // Prepare date range using Carbon
        $startDate = Carbon::parse($fromDate)->startOfDay();
        $endDate = Carbon::parse($toDate)->endOfDay();
        $dateRange = [$startDate, $endDate];
    
        // Common fields to select
        $selectFields = ['id', 'account_id', 'stock_id', 'sQty','sRate', 'created_at', 'order_id'];
    
        // Query all types in parallel (better performance)
        $queries = [
            'Sale' => \App\Models\SalesDetail::class,
            'Purchase' => \App\Models\PurchaseDetail::class,
            'Sale_return' => \App\Models\SalesReturnDetail::class,
            'Purchase_return' => \App\Models\PurchaseReturnDetail::class,
            'Stock_adjustment_in' => \App\Models\StockAdjustmentDetail::class,
            'Stock_adjustment_out' => \App\Models\StockAdjustmentDetail::class,
        ];
    
        $results = collect();
    
        foreach ($queries as $type => $model) {

            $query = $model::where($conditions)
                ->whereBetween('created_at', $dateRange)
                ->with('bill')
                ->select($selectFields)
                ->addSelect(\DB::raw("'$type' as odrType"));
    
            // Special condition for purchases
            if ($type === 'Purchase') {
                $query->whereHas('bill', fn($q) => $q->where('approved_by', '>=', 1));
            }

            // Additional condition for stock adjustments
            if ($type === 'Stock_adjustment_in') {
                $query->where('sQty_type', 1);
            } elseif ($type === 'Stock_adjustment_out') {
                $query->where('sQty_type', 0);
            }
            
            $results = $results->merge($query->get());
        }
    
        return $results->sortBy('created_at')->values();
    }

   
    //=======Productwise Pending  stock  status and pending order =====
    public function productwisePendingOrder(int|string|null $prodid = null, int|string|null $catId = null, int|string|null $stockId = null)//: View
    {   

        // If $id is provided and valid, reset $stockId
        if ($stockId >= 1) {
            $prodid = \App\Models\StockModel::where('id', $stockId)->first()->product_id ?? null;
        }

        $data = [
            'title' => 'Productwise stock Status',
            'breadcrumb' => breadcrumb([
                'Stock Status' => route('admin.report.stock-movement', '*'),
            ]),
            'stock' => collect(),
            'prodid' => '',
            'catid' => '',
            'stockid' => $stockId,
            'product' => \App\Models\Product::where('status', 1)
                        ->whereHas('stock', function($q){
                            $q->where('status', 1);
                        })->get(),
            'category' => $this->getCategories($prodid),
        ];

        // If $id is provided and valid, reset $stockId
        if ($prodid >= 1) {
            $stockId = null;
        }

        if ($prodid || $stockId) {
            $query = BranchStocks::with([
                'stock' => function ($q) {  // <— "stock" is the key, closure is the value
                    $q->withSaleOrderSum()
                        ->withPurchaseOrderSum()
                        ->with(['product', 'category']);
                }
            ])
            ->whereHas('stock', function ($q) {
                $q->where('status',1);
            })
            ->where('status', '1');

            if ($stockId >= 1) {
                $stock = \App\Models\StockModel::where('id', $stockId)
                            ->first();
                if ($stock) {
                    $prodid = $stock->product_id;
                    $query->whereHas('stock', function ($q) use ($prodid) {
                        $q->where('product_id', $prodid);
                    });
                }
            } elseif ($prodid !== '*' && $prodid) {
                $query->whereHas('stock', function ($q) use ($prodid) {
                    $q->where('product_id', $prodid);
                });
                $data['prodid'] = $prodid;
                $data['category'] = $this->getCategories($prodid); // Update categories for specific product
            }

            if ($catId && $catId !== '*') {
                $query->whereHas('stock', function ($q) use ($catId) {
                    $q->where('category_id', $catId);
                });
                $data['catid'] = $catId;
            }

            $data['stock'] = $query->get();
        }

        

        return view('admin.report.productwise-pending-order', $data);
    }

    private function getCategories(?int $productId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = StockModel::where('status','1')
            ->groupBy('product_id', 'category_id');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->get();
    }

    //====Over Order Purchase====
    public function purchaseWithoutOrder(Request $request, $accountId = null)
    {
        // Base query with eager loading
        $query = \App\Models\PurchaseDetail::with('stock', 'bill')
                              ->where('unordered_stock', '>=', 1);

        // Apply filters
        if ($accountId && is_numeric($accountId) && $accountId >= 1) {
            $query->where('account_id', $accountId);
        }

        if ($request->filled('stock_id') && $request->stock_id >= 1) {
            $query->where('stock_id', $request->stock_id);
        }

        if ($request->filled('product_id') && $request->product_id >= 1) {
            $query->whereHas('stock', fn($q) => $q->where('product_id', $request->product_id));
        }

        // Fetch data
        $purchases = $query->orderBy('created_at', 'desc')->get();

        // Get unique account IDs after filtering
        $accountIds = $purchases->pluck('account_id')->unique()->values()->all();
        $accounts = Account::whereIn('id', $accountIds)->get();

        // Prepare view data
        $viewData = [
            'title' => 'Unordered Items',
            'breadcrumb' => breadcrumb([
                'Stock Status' => route('admin.report.stock-movement', '*'),
            ]),
            'accounts' => $accounts,
            'data' => $purchases,
        ];

        return view('admin.report.unordered-items-purchase', $viewData);
    }


    //===== Sale ORder Details ========
    public function saleOrderDetailReport(Request $request)
    {
        // Initialize default filter values
        $filters = [
            'fromdate' => date('Y-m-d', strtotime('-6 month')),
            'todate' => date('Y-m-d'),
            'accountid' => '*',
            'salesman_id' => '*',
            'partyType' => '*',
            'status' => 1, // Changed to '*' to enforce no data by default
        ];
    
        // Initialize empty collections
        $sales = collect();
        $employees = collect();
    
        // Get filtered accounts (acGroup == 4 for sales)
        $accounts = \App\Models\BranchAccounts::getBranchMapped(false, null, null)
            ->filter(fn($account) => $account->acGroup == 4)
            ->values();
    
        // Check if meaningful filters are applied
        $hasFilters = $request->filled('partyType') && $request->partyType !== '*' ||
                      $request->filled(['fromdate', 'todate']) && 
                      ($request->fromdate !== $filters['fromdate'] || $request->todate !== $filters['todate']) ||
                      $request->filled('accountid') && $request->accountid !== '*' ||
                      $request->filled('salesman_id') && $request->salesman_id !== '*' ||
                      $request->filled('status');
    
        if ($hasFilters) {
            // Base query with relationships
            $salesQuery = SaleOrder::with(['account', 'salesman', 'details.stock.product', 'details.stock.category']);
    
            // Get unique salesmen
            $employees = Employee::whereIn('id', 
                $salesQuery->clone()->pluck('salesman_id')->unique()
            )->select('id', 'name')->get();
    
            // Apply filters
            if ($request->filled('partyType') && $request->partyType !== '*') {
                $filters['partyType'] = $request->partyType;
                $salesQuery->whereHas('account', fn($query) => $query->where('type', $filters['partyType']));
            }
    
            if ($request->filled(['fromdate', 'todate'])) {
                $filters['fromdate'] = $request->fromdate.' 00:00:01';
                $filters['todate'] = $request->todate.' 23:59:59';
                $salesQuery->whereBetween('billDate', [$filters['fromdate'], $filters['todate']]);
            }
    
            if ($request->filled('accountid') && $request->accountid !== '*') {
                $filters['accountid'] = $request->accountid;
                $salesQuery->where('account_id', $filters['accountid']);
            }
    
            if ($request->filled('salesman_id') && $request->salesman_id !== '*') {
                $filters['salesman_id'] = $request->salesman_id;
                $salesQuery->where('salesman_id', $filters['salesman_id']);
            }
    
            if ($request->filled('status') && $request->status !== '*') {
                $filters['status'] = $request->status;
                $salesQuery->whereHas('details', function ($query) use ($filters) {
                    if ($filters['status'] == 1) {
                        $query->whereIn('status', [1, 2])->where('sQty', '>=', 1);
                    } else {
                        $query->where('status', 0);
                    }
                });
            } 
    
            // Process results
            $sales = $salesQuery->get()->each(function ($sale) use ($filters) {
                $sale->inqGroupData = $sale->details->groupBy(fn($item) => 
                    $item->stock->product_id . '-' . $item->stock->category_id . '-' . $item->sRate
                );
    
                $sale->inqGroupData->transform(function ($group) use ($filters)
                {
                    $firstItem = $group->first();
                    $stock = $firstItem->stock;
                    $ArvDate='';
                    $arvByName = '';
                    $arvDateMark = '';
                    	$arvQry=\App\Models\PurchaseOrderDetail::whereIn('status', [1, 2])
                                ->orderBy('arrival_date','desc')
                                ->with('stock','arrivalby')
                                ->whereHas('stock', function($q) use($stock){
                                    $q->where('product_id', $stock->product->id)
                                      ->where('category_id', $stock->category->id);
                                })
                                ->first();
                        //$arvDateMark=$arvQry;
                        if(isset($arvQry->arrival_date)){
                            $ArvDate=date('Y-m-d',strtotime($arvQry->arrival_date.' +4 days')); 
                            $arvByName = $arvQry->arrivalby->name ?? '';
                            $arvDateMark='<small class="badge badge-info" title="'.$arvByName.'">Arrival Date: '.$ArvDate.'</small>';
                        }

                    $groupTotal = [
                        'total_sNetAmount' => $group->sum('sNetAmount'),
                        'total_sQty' => $group->sum('sQty'),
                        'total_sDiscount' => $group->sum('sDiscount'),
                        'sRate' => $firstItem->sRate,
                        'taxRate' => $firstItem->taxRate,
                        'product_id' => $stock->product->id,
                        'category_id' => $stock->category->id,
                        'product_name' => $stock->product->name,
                        'category_name' => $stock->category->name,
                        'purArivalDate' => isset($ArvDate) ? $ArvDate : null,
                        'arrival_by' => $arvByName,
                        'arrival_date_mark' => $arvDateMark,
                        'itemid' => $firstItem->id,
                    ];
    
                    $group->groupDetail = $group->map(function ($detail) use ($filters) {
                        $showItem = true;
    
                        if ($filters['status'] !== '*') {
                            if ($filters['status'] == 1 && !in_array($detail->status, [1, 2])) {
                                $showItem = false;
                            } elseif ($filters['status'] == 0 && $detail->status != 0) {
                                $showItem = false;
                            }
                        }
    
                        if ($showItem && in_array($detail->status, [1, 2]) && $detail->sQty < 1) {
                            $showItem = false;
                        }
    
                        return $showItem ? [
                            'id' => $detail->id,
                            'product_id' => $detail->stock->product_id,
                            'category_id' => $detail->stock->category_id,
                            'stock_id' => $detail->stock_id,
                            'size_color' => $detail->stock->size_color_name,
                            'actualQty' => $detail->actualQty,
                            'sQty' => $detail->sQty,
                            'status' => $detail->status,
                            'sRate' => $detail->sRate,
                            'isOffer' => $detail->isOffer,
                            'pRate' => $detail->stock->pRate,
                            'currentStock' => $detail->bstock->current_stock,
                                'total_pod' => $detail->bstock->ppod()->sum('sQty') ?? 0,
                                'total_sod' => $detail->bstock->psod()->sum('sQty') ?? 0,
                        ] : null;
                    })->filter();
    
                    return array_merge($groupTotal, [
                        'groupDetail' => $group->groupDetail,
                        'has_valid_items' => $group->groupDetail->isNotEmpty(),
                    ]);
                });
    
                $sale->inqGroupData = $sale->inqGroupData->filter(fn($group) => $group['has_valid_items']);
            });
        }
      
        return view('admin.report.sale-order-details', [
            'title' => 'Sale Order Detail Report',
            'breadcrumb' => breadcrumb([
                'Stock Status' => route('admin.report.sale-order-details', '*'),
            ]),
            'accounts' => $accounts,
            'employees' => $employees,
            'data' => $sales,
            'fromdate' => $filters['fromdate'],
            'todate' => $filters['todate'],
            'accountid' => $filters['accountid'],
            'salesman_id' => $filters['salesman_id'],
            'partyType' => $filters['partyType'],
            'status' => $filters['status'],
        ]);
    }


    public function purchaseOrderDetailReport(Request $rd)
    {
        // Initialize default filter values
        $filters = [
            'fromdate' => env('APP_YEAR', Carbon::now()->startOfYear()->month(4)->day(1)->toDateString()),
            'todate' => date('Y-m-d'),
            'accountid' => '*',
            'salesman_id' => '*',
            'partyType' => '*',
            'status' => 1 // Changed to '*' to enforce no data by default
        ];

        // Initialize empty collections
        $sales = collect();
        $employees = collect();

        // Get filtered accounts (acGroup == 3 for purchases)
        $accounts = \App\Models\BranchAccounts::getBranchMapped(false, null, null)
            ->filter(fn($account) => $account->acGroup == 3)
            ->values();

        // Check if any meaningful filters are applied
        $hasFilters = $rd->filled('partyType') && $rd->partyType !== '*' ||
                    $rd->filled(['fromdate', 'todate']) && 
                    ($rd->fromdate !== $filters['fromdate'] || $rd->todate !== $filters['todate']) ||
                    $rd->filled('accountid') && $rd->accountid !== '*' ||
                    $rd->filled('salesman_id') && $rd->salesman_id !== '*' ||
                    $rd->filled('status');

        if ($hasFilters) {
            // Base query with relationships
            $salesQuery = PurchaseOrder::with(['account', 'salesman', 'details.stock.product', 'details.stock.category']);

            // Get unique salesmen from the query
            $employees = Employee::whereIn('id', 
                $salesQuery->clone()->pluck('salesman_id')->unique()
            )->select('id', 'name')->get();

            // Apply filters
            if ($rd->filled('partyType') && $rd->partyType !== '*') {
                $filters['partyType'] = $rd->partyType;
                $salesQuery->whereHas('account', fn($query) => $query->where('type', $filters['partyType']));
            }

            if ($rd->filled(['fromdate', 'todate'])) {
                $filters['fromdate'] = $rd->fromdate . ' 00:00:01'; // Fixed: Changed $request to $rd
                $filters['todate'] = $rd->todate . ' 23:59:59';     // Fixed: Changed $request to $rd
                $salesQuery->whereBetween('billDate', [$filters['fromdate'], $filters['todate']]);
            }

            if ($rd->filled('accountid') && $rd->accountid !== '*') {
                $filters['accountid'] = $rd->accountid;
                $salesQuery->where('account_id', $filters['accountid']);
            }

            if ($rd->filled('salesman_id') && $rd->salesman_id !== '*') {
                $filters['salesman_id'] = $rd->salesman_id;
                $salesQuery->where('salesman_id', $filters['salesman_id']);
            }

            if ($rd->filled('status') && $rd->status !== '*') {
                $filters['status'] = $rd->status;
                $salesQuery->whereHas('details', function ($query) use ($filters) {
                    if ($filters['status'] == 1) {
                        $query->whereIn('status', [1, 2])->where('sQty', '>=', 1);
                    } else {
                        $query->where('status', 0);
                    }
                });
            } // Removed else block to prevent default data fetching

            // Process the results
            $sales = $salesQuery->get()->each(function ($sale) use ($filters) {
                $sale->inqGroupData = $sale->details->groupBy(fn($item) => 
                    $item->stock->product_id . '-' . $item->stock->category_id . '-' . $item->sRate
                );

                $sale->inqGroupData->transform(function ($group) use ($filters) {
                    $firstItem = $group->first();
                    $stock = $firstItem->stock;

                    $groupTotal = [
                        'total_sNetAmount' => $group->sum('sNetAmount'),
                        'total_sQty' => $group->sum('sQty'),
                        'total_sDiscount' => $group->sum('sDiscount'),
                        'sRate' => $firstItem->sRate,
                        'taxRate' => $firstItem->taxRate,
                        'product_id' => $stock->product->id,
                        'category_id' => $stock->category->id,
                        'product_name' => $stock->product->name,
                        'category_name' => $stock->category->name,
                        'itemid' => $firstItem->id,
                        'arrival_date' => $firstItem->arrival_date ? date('Y-m-d', strtotime($firstItem->arrival_date)) : null,
                        'arrival_by' => $firstItem->arrivalby ? $firstItem->arrivalby->name : null,   
                    ];

                    $group->groupDetail = $group->map(function ($detail) use ($filters) {
                        $showItem = true;

                        if ($filters['status'] !== '*') {
                            if ($filters['status'] == 1 && !in_array($detail->status, [1, 2])) {
                                $showItem = false;
                            } elseif ($filters['status'] == 0 && $detail->status != 0) {
                                $showItem = false;
                            }
                        }

                        if ($showItem && in_array($detail->status, [1, 2]) && $detail->sQty < 1) {
                            $showItem = false;
                        }

                        return $showItem ? [
                            'id' => $detail->id,
                            'product_id' => $detail->stock->product_id,
                            'category_id' => $detail->stock->category_id,
                            'stock_id' => $detail->stock_id,
                            'size_color' => $detail->stock->size_color_name,
                            'actualQty' => $detail->actualQty,
                            'sQty' => $detail->sQty,
                            'status' => $detail->status,
                            'sRate' => $detail->sRate,
                            'pRate' => $detail->stock->pRate,
                            'show_item' => $showItem,
                            'remarks' => $detail->remarks,
                            'currentStock' => $detail->bstock->current_stock,
                            'total_pod' => $detail->bstock->ppod->sum('sQty'),
                            'total_sod' => $detail->bstock->psod->sum('sQty'),
                            'arrival_date' => $detail->arrival_date ? date('Y-m-d', strtotime($detail->arrival_date)) : null,
                            'arrival_by' => $detail->arrivalby ? $detail->arrivalby->name : null,       
                        ] : null;
                    })->filter();

                    return array_merge($groupTotal, [
                        'groupDetail' => $group->groupDetail,
                        'has_valid_items' => $group->groupDetail->isNotEmpty()
                    ]);
                });

                $sale->inqGroupData = $sale->inqGroupData->filter(fn($group) => $group['has_valid_items']);
            });
        }
      //  return $sales;
        return view('admin.report.purchase-order-details', [
            'title' => 'Purchase Order Detail Report',
            'breadcrumb' => breadcrumb([
                'Stock Status' => route('admin.report.purchase-order-details', '*'),
            ]),
            'accounts' => $accounts,
            'employees' => $employees,
            'data' => $sales,
            'fromdate' => $filters['fromdate'],
            'todate' => $filters['todate'],
            'accountid' => $filters['accountid'],
            'salesman_id' => $filters['salesman_id'],
            'partyType' => $filters['partyType'],
            'status' => $filters['status'],
        ]);
    }

    //=========NEED ORDER REPORT===========================
    public function needOrder(Request $r)
    {
        $query = \App\Models\BranchStocks::query()
        ->whereIn('status', [1])
        ->with(['stock' => function ($query) {
            $query->withSaleOrderSum() // Adds total_sod
                ->withPurchaseOrderSum() // Adds total_pod
                ->withSum(['purchasecart as total_purchase_cart' => function ($query) {
                    $query->whereIn('order_type', [2]);
                }], 'sQty') // Moved scope logic here
                ->with([
                    'product' => fn ($q) => $q->select('id', 'name'),
                    'category' => fn ($q) => $q->select('id', 'name'),
                    'color' => fn ($q) => $q->select('id', 'name')
                ]); // Select necessary fields
        }]);

    $stock = $query->get()
    ->sortBy([
        ['stock.product.name', 'asc'],
        ['stock.category.name', 'asc'],
        ['stock.color.name', 'asc']
    ])
    ->filter(function ($branchStock) {
        $reqStock = $branchStock->current_stock + 
                    ($branchStock->stock->total_pod ?? 0) - 
                    ($branchStock->stock->total_sod ?? 0) ; // Include purchase cart
        return $branchStock->stock && $reqStock < 0;
    });

        $a['title'] = 'Need Order Report';
        $a['breadcrumb'] = breadcrumb([
            'Stock Status' => route('admin.report.stock-movement', '*'),
        ]);
        //$a['accounts'] = \App\Models\BranchAccounts::getBranchMapped(false, null, null);
        $a['stock'] = $stock;

        return view('admin.report.need-order-report', $a);
    }


     //===== Supplier wise Product Catalog========
    public function productCatRelatedAccount(Request $request)
    {
        $Title='Account Related product Category';
        $cat=DB::table('tbl_categories as ct')->select('ct.id','ct.name')->where('status',0)->get();
        $cities=DB::table('tbl_city')->select('id','name','state_id')->where('status',0)->get();
        $states=DB::table('tbl_states')->select('id','name')->get();
        $Pcategory=DB::table('tbl_categories_parent')->select('id','name')->get();
        $categoryid='';
        $factype='';
        $faGroup='';
        $faPg='';
        $fcity='';
        $fstate='';
        $fstatus='*';
        $parentCatId='';
        
        if((isset($_POST['fcatid']) && $_POST['fcatid']>=1) || (isset($_POST['fParentcatid']) && $_POST['fParentcatid']>=1))
        {		
            
            
            $acRefQry=DB::table('tbl_product_assoc_account as pa')
                                ->where('st.status',1)
                                ->join('tbl_products_stock As st','st.id','=','pa.stock_id')
                                ->join('tbl_account as ac','ac.id','=','pa.account_id')
                                ->join('tbl_account_group as ag','ag.id','=','ac.acGroup')
                                ->join('tbl_city as act','act.id','=','ac.city_id')
                                ->join('tbl_states as stt','stt.id','=','ac.state_id');
            
            if(isset($_POST['factype']) && $_POST['factype']>=1){
                $factype=$_POST['factype'];
                $acRefQry->where('ac.type',$_POST['factype']);
            }
            
            if(isset($_POST['fParentcatid']) && $_POST['fParentcatid']>=1){
            
                $pcat=DB::table('tbl_category_parent_mapping')->select('category_id')->where('parent_id',$_POST['fParentcatid'])->pluck('category_id')->toArray();

                    $acRefQry->whereIn('st.category_id',$pcat);
                    $parentCatId=$_POST['fParentcatid'];
                                
            }
            
            if(isset($_POST['fcatid']) && $_POST['fcatid']>=1)
            {
                $acRefQry->where('st.category_id',$_POST['fcatid']);
                $categoryid=$_POST['fcatid'];
            }

            if(isset($_POST['faGroup']) && $_POST['faGroup']>=1)
            {
                $acRefQry->where('ac.acGroup',$_POST['faGroup']);
                $faGroup=$_POST['faGroup'];				
            }

            if(isset($_POST['faPg']) && $_POST['faPg']>=1)
            {
                $acRefQry->where('ac.priceGroup',$_POST['faPg']);
                $faPg=$_POST['faPg'];				
            }

            if(isset($_POST['fcity']) && $_POST['fcity']>=1)
            {
                $acRefQry->where('ac.city_id',$_POST['fcity']);
                $fcity=$_POST['fcity'];				
            }

            if(isset($_POST['fstate']) && $_POST['fstate']>=1)
            {
                $acRefQry->where('ac.state_id',$_POST['fstate']);
                $fstate=$_POST['fstate'];				
            }
            
        

            if(isset($_POST['fstatus']) && $_POST['fstatus']==3)
            {
                $acRefQry->where('ac.block_status',1);
                $fstatus=$_POST['fstatus'];				
            }elseif(isset($_POST['fstatus']) && $_POST['fstatus']!='*')
            {
                $acRefQry->where('ac.status',$_POST['fstatus']);
                $fstatus=$_POST['fstatus'];				
            }else{
                $fstatus='*';
            }

            $acRefQry->join('tbl_products_master as pd','pd.id','=','st.product_id');	
            $refpd=$acRefQry->groupBy('pa.account_id')
                            ->select('st.product_id','pd.name as prodName','pd.image as productImage','account_id','st.category_id','ac.name as acName','ac.acCode as acCode','ac.phone as acPhone','ac.type as acType','ac.priceGroup','ac.status as acStatus','ac.block_status as acBlockStatus','ag.name as acGroupName','act.name as acCityName','stt.name as stateName')
                            ->get();
            if(!empty($refpd))
            {
                $stock=$refpd;
            }else{
                $stock='';
            }
        }else{
            $stock='';
        }
        $a['title']=$Title;
        $a['accounts']=$stock;
        $a['category']=$cat;
        $a['Pcategory']=$Pcategory;
        $a['factype']=$factype;
        $a['faGroup']=$faGroup;
        $a['faPg']=$faPg;
        $a['category_id']=$categoryid;
        $a['cities']=$cities;
        $a['states']=$states;
        $a['fcity']=$fcity;
        $a['fstate']=$fstate;
        $a['fstatus']=$fstatus;
        $a['fParentcatid']=$parentCatId;

        //return $a;
        return view('admin.report.prodcat-related-client')->with($a);
        
    }

}

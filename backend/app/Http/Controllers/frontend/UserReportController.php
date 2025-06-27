<?php
namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\Models\Account;
use App\Models\FinancialLogsModel;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleOrder;
use App\Models\PurchaseOrder;
use Yajra\DataTables\Facades\DataTables;
use App\Models\StockModel;
use App\Models\BranchStocks;
use Carbon\Carbon;
class UserReportController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Display the user's profile information.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function myOrder(Request $request, ?string $repType = null)
    {
        // Validate report type immediately
        $reportConfig = $this->getReportConfiguration($repType);
        if (!$reportConfig) {
            abort(404, 'Invalid report type');
        }

        // Get orders with parameterized query
        $orders = DB::table("{$reportConfig['orderTable']} as odr")
            ->where('odr.account_id', Auth::user()->account_id)
            ->where('odr.status', '<', 4)
            ->whereNotNull('odr.billDate') // Optional: exclude NULLs
            ->orderByDesc(DB::raw('CAST(odr.billDate AS DATETIME)'))
            ->get();
        // return print_query($orders);

        return view('frontend.order.mysaleOrder', [
            'bill' => $orders,
            'title' => $reportConfig['title'],
            'repType' => $repType,
            'pageTitle' => $reportConfig['pageTitle']
        ]);
    }



/**
 * Get configuration for different report types
 */
protected function getReportConfiguration(?string $repType): ?array
{
    $configurations = [
        'purchase' => [
            'detailTable' => 'tbl_sale_detail',
            'orderTable' => 'tbl_sale',
            'title' => 'Purchase Report',
            'pageTitle' => 'Sale Billwise Detail',
            'printType' => 'purchase'
        ],
        'sale' => [
            'detailTable' => 'tbl_purchase_detail',
            'orderTable' => 'tbl_purchase',
            'title' => 'Sale Report',
            'pageTitle' => 'Sale Billwise Detail',
            'printType' => 'sale'
        ],
        'purchase-order' => [
            'detailTable' => 'tbl_sale_order_detail',
            'orderTable' => 'tbl_sale_order',
            'title' => 'Purchase-Order Report',
            'pageTitle' => 'Purchase Order Billwise Detail',
            'printType' => 'purchase-order'
        ],
        'sale-order' => [
            'detailTable' => 'tbl_purchase_order_detail',
            'orderTable' => 'tbl_purchase_order',
            'title' => 'Sale-Order Report',
            'pageTitle' => 'Sale Order Billwise Detail',
            'printType' => 'sale-order'
        ],
        'purchase-return' => [
            'detailTable' => 'tbl_sale_return_detail',
            'orderTable' => 'tbl_sale_return',
            'title' => 'Purchase Return',
            'pageTitle' => 'Purchase-Return Billwise Detail',
            'printType' => 'purchase-return'
        ],
        'sale-return' => [
            'detailTable' => 'tbl_purchase_return_detail',
            'orderTable' => 'tbl_purchase_return',
            'title' => 'Sale Return',
            'pageTitle' => 'Sale Return Billwise Detail',
            'printType' => 'sale-return'
        ]
    ];

    return $configurations[$repType] ?? null;
}




public function listing(Request $request,$reqType)
{   
    //return 'hi';
    $reportConfig = $this->getReportConfiguration($reqType);
    if (!$reportConfig) {
        abort(404, 'Invalid report type');
    }

    // Get orders with parameterized query
   $data = DB::table("{$reportConfig['orderTable']} as odr")
            ->where('odr.account_id', Auth::user()->account_id)
            ->where('odr.status', '<', 4)
            ->whereNotNull('odr.billDate') // Optional: exclude NULLs
            ->orderByDesc(DB::raw('CAST(odr.billDate AS DATETIME)'))
            ->get();
   // return print_query($data); 
   $printType = $reportConfig['printType'];
    return DataTables::of($data)
        ->addIndexColumn()
        
        ->addColumn('billDate', function ($row) {
            return  myDateFormat($row->billDate);
        })
        
        // ->addColumn('salesPerson', function ($row) {
        //     return $row?->salesman?->name;
        // })
        ->addColumn('orderstatus', function ($row) use ($printType){
            
            //'0:In-process| 1:Delivered
            $status=$row->status;
            $statusText='';
            $status = $row->status;
            switch ($status) {
                case 0:
                    $statusText = '<badge class="badge bg-success rounded-pill">In Process</badge>';
                    
                    break;
                case 1:
                    $statusText = '<badge class="badge bg-info rounded-pill">Delivered</badge>';
                    
                    break;
                case 2:
                    $statusText = '<badge class="badge bg-warning rounded-pill">Ready to bill</badge>';
                    break;
                case 3:
                   
                        $statusText = '<a href="'.route('user.print.'.$printType,encrypt($row->id)).'">
                                        <badge class="badge bg-danger rounded-pill">Bill Generated</badge>
                                        <br><small class="text-danger">'.$row->invoice_No.'</small></a>';
                    
                    break;
                case 4:
                    $statusText = '<badge class="badge bg-secondary rounded-pill">Cancelled</badge>';
                    break;
                case 5:
                    $statusText = '<badge class="badge bg-danger rounded-pill">Deleted</badge>';
                    break;
                default:
                    $statusText = '--';
            }
                    return  $statusText;
        })
        ->addColumn('action',function ($row) use ($printType){
        
            $action='<div class="btn-group cstbtn">';
            $action.='<button type="button" class="btn btn-sm btn-outline-primary">
                    <a href="'.route('user.print.'.$printType,encrypt($row->id)).'">
                        <i class="fa fa-print"></i>
                    </a>
                </button>';

                // <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
                //     <i class="fa fa-chevron-down d-none d-xl-inline-block"></i>
                // </button>
                // <ul class="dropdown-menu" role="menu">
                //     <li><a href="'.route('user.bill.print-bill-image', ['sale-order', encrypt($row->id)]).'">
                //     <i class="fa fa-copy"></i> Print With Image</a></li>
                //     <li class="divider"></li>
                // </ul>
            $action.='</div>';
            return $action;
        })
        ->rawColumns(['orderstatus',"billDate","status", "action"])
        ->make(true);
}






    public function myClearOrder()
    {
        return view('frontend.blank');
    }

    public function myPendingOrder()
    {
        return view('frontend.blank');
    }

       
    //===Supplier pending order details
    public function supplierOrderDetail(Request $request)
    {
        return view('frontend.order.mySaleOrder');
    }

    //===== Sale ORder Details ========
    public function myPurchaseOrderDetailReport(Request $request)
    {
        // Initialize default filter values
        $filters = [
            'fromdate' => date('Y-m-d', strtotime('-6 month')), // Default to 6 months ago
            'todate' => date('Y-m-d'),
            'salesman_id' => '*',
            'partyType' => '*',
            'status' => 1, // Changed to '*' to enforce no data by default
        ];
    
        // Initialize empty collections
        $sales = collect();
        $accountid=Auth::user()->account_id;
        // Check if meaningful filters are applied
        $hasFilters = $request->filled('partyType') && $request->partyType !== '*' ||
                        $request->filled(['fromdate', 'todate']) && 
                        ($request->fromdate !== $filters['fromdate'] || $request->todate !== $filters['todate']) ||
                        $request->filled('salesman_id') && $request->salesman_id !== '*' ||
                        $request->filled('status');
    
        if ($hasFilters) {
            // Base query with relationships
            $salesQuery = SaleOrder::with(['account', 'salesman', 'details.stock.product', 'details.stock.category']);
            $salesQuery->where('account_id', $accountid);
            // Get unique salesmen
            
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
            } else {
                $filters['status'] = '*'; // Ensure no default status filter
            }
    
            // Process results
            $sales = $salesQuery->get()->each(function ($sale) use ($filters) {
                $sale->inqGroupData = $sale->details->groupBy(fn($item) => 
                    $item->stock->product_id . '-' . $item->stock->category_id . '-' . $item->sRate
                );
    
                $sale->inqGroupData->transform(function ($group) use ($filters) {
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
                            $arvDateMark='<small class="badge badge-info" title="By Abbangles">Arrival Date: '.$ArvDate.'</small>';
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
                            'pRate' => $detail->stock->pRate,
                            'currentStock' => $detail->bstock->current_stock,
                            'total_pod' => $detail->bstock->ppod()->sum('sQty') ?? 0,
                            'total_sod' => $detail->bstock->psod()->sum('sQty') ?? 0,
                            'isOffer' => $detail->isOffer,
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
    
        return view('frontend.frontend.my_so_detail', [
            'Title' => 'Purchase Order Detail Report',
            'data' => $sales,
            'fromdate' => $filters['fromdate'],
            'todate' => $filters['todate'],
            'salesman_id' => $filters['salesman_id'],
            'partyType' => $filters['partyType'],
            'status' => $filters['status'],
        ]);
    }


    public function mySaleOrderDetailReport(Request $rd)
    {
       
        // Initialize default filter values
        $filters = [
            'fromdate' => date('Y-m-d', strtotime('-6 month')), // Default to 6 month
            'todate' => date('Y-m-d'),
            'accountid' => '*',
            'salesman_id' => '*',
            'partyType' => '*',
            'status' => 1 // Changed to '*' to enforce no data by default
        ];

        // Initialize empty collections
        $sales = collect();
        $accountid=Auth::user()->account_id;
       // Check if any meaningful filters are applied
        $hasFilters = $rd->filled('partyType') && $rd->partyType !== '*' ||
                    $rd->filled(['fromdate', 'todate']) && 
                    ($rd->fromdate !== $filters['fromdate'] || $rd->todate !== $filters['todate']) ||
                    $rd->filled('salesman_id') && $rd->salesman_id !== '*' ||
                    $rd->filled('status') && $rd->status !== '*';

        if ($hasFilters) {
            // Base query with relationships
            $salesQuery = PurchaseOrder::with(['account', 'salesman', 'details.stock.product', 'details.stock.category','details.arrivalby','details.bstock']);
            $salesQuery->where('account_id', $accountid);
            // Get unique salesmen from the query
            $employees = \App\Models\Employee::whereIn('id', 
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

            $salesQuery->get();
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
        
        return view('frontend.frontend.my_po_detail', [
            'Title' => 'Sale Order Detail Report',
            'breadcrumb' => breadcrumb([
                'Stock Status' => route('admin.report.purchase-order-details', '*'),
            ]),
            'data' => $sales,
            'fromdate' => $filters['fromdate'],
            'todate' => $filters['todate'],
            'partyType' => $filters['partyType'],
            'status' => $filters['status'],
        ]);
    }
    

    //===Customer pending order details
    public function customerOrderDetail(Request $request)
    {

        return view('frontend.order.mySaleOrder');
    }



    public function myOrderDetail(Request $request)
    {
        return view('frontend.order.mySaleOrder');
    }

    //======Customer/Supplier My products list =======
    public function myProducts(Request $request)
    {
        $a['Title'] = 'My Products';
        $minStock = 0;
        $accountId = Auth::user()->account_id;
        $titleParts = ['My Products'];
        $post = $request->anyFilled(['prodID', 'catID']);
        $mycartType = Auth::user()->acGroup == '3' ? '4' : '3'; // Supplier (acGroup=3) -> type 4, Customer -> type 3
        $mycartData = \App\Models\CartDetail::where('account_id', $accountId)
            ->where('order_type', $mycartType)
            ->get();

        // Get stock IDs associated with the account
        $stockIds = \App\Models\AccountAssocProd::where('account_id', $accountId)
            ->where('status', 1)
            ->pluck('stock_id')
            ->toArray();

        // Get all product IDs from those stocks
        $productIds = \App\Models\StockModel::whereIn('id', $stockIds)
            ->where('status', 1)
            ->pluck('product_id')
            ->unique()
            ->toArray();

        // Get products (only those associated with account)
        $prod = \App\Models\Product::where('status', 1)
            ->whereIn('id', $productIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get categories (only those with products associated with account)
        $cat = \App\Models\Category::where('status', 1)
            ->whereIn('id', function ($query) use ($productIds) {
                $query->select('category_id')
                    ->from((new \App\Models\StockModel)->getTable())
                    ->whereIn('product_id', $productIds)
                    ->where('status', 1);
            })
            ->select('id', 'name')
            ->get();

        // Initialize results
        $results = collect();

        if (!empty($stockIds)) {
            $query = \App\Models\BranchStocks::with(['stock' => function ($q) {
                $q->select('id', 'product_id', 'category_id', 'attribute_id', 'size_id', 'pair_id');
            }])
                ->withSum(['psod as mysod' => function ($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                }], 'sQty')
                ->withSum(['ppod as mypod' => function ($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                }], 'sQty')
                ->withSum('psod as total_sod', 'sQty')
                ->withSum('ppod as total_pod', 'sQty')
                ->where('status', 1)
                ->whereHas('stock', function ($q) {
                    $q->where('status', 1);
                })
                ->whereIn('stock_id', $stockIds);

            // Apply product ID filter
            if ($request->filled('prodID')) {
                $prodID = $request->input('prodID');
                $query->whereHas('stock', function ($q) use ($prodID) {
                    $q->where('product_id', $prodID);
                });
                $product = \App\Models\Product::find($prodID);
                if ($product) {
                    $titleParts[] = '<small>' . e($product->name) . '</small>';
                }
            }

            // Apply category ID filter
            if ($request->filled('catID')) {
                $catID = $request->input('catID');
                $query->whereHas('stock', function ($q) use ($catID) {
                    $q->where('category_id', $catID);
                });
                $category = \App\Models\Category::find($catID);
                if ($category) {
                    $titleParts[] = '<small>' . e($category->name) . '</small>';
                }
            }

            $results = $query->get();
        }

        // Prepare title
        $title = implode(' | ', array_filter($titleParts));

        // Prepare response
        return view('frontend.frontend.myproduct_list', [
            'breadcrumb' => breadcrumb(['My Products' => '']),
            'stock' => $results,
            'prod' => $prod,
            'prodid' => $request->input('prodID', ''),
            'cat' => $cat,
            'catid' => $request->input('catID', ''),
            'Title' => $title,
            'priceOrder' => '',
            'post' => $post ? 'true' : 'false',
            'resultcount' => !empty($results) ? $results->count() : 0,
            'mycartData' => $mycartData,
        ]);
    }
    //======Customer/Supplier Replated items===============
    public function myProductsCatalogue(Request $request)
    {
        $a['Title'] = 'Products related with us';
        // Initialize variables
            $priceOrder = $request->input('priceOrder') ?? '';
            $minStock=0;
            $status = $request->input('onlyInactive') === 'Inactive Variants' ? 0 : 1;
            $accountId = Auth::user()->account_id; // Use authenticated user's account_id
            $titleParts = ['Main Product Catalogue'];
            $post = $request->anyFilled([
                'prodID', 'catID', 'submit', 'priceOrder', 'prodName', 'pairID'
            ]);
            
            $mycartType= Auth::user()->type=='customer' ? '3' : '4';
            $mycartData = \App\Models\CartDetail::where('account_id', $accountId)
                ->where('order_type', $mycartType)
                ->get();

            if ($status === 0) {
                $titleParts[] = '<span class="text-danger">Inactive Items</span>';
            }
            
            // Get stock IDs associated with the account
            $stockIds = \App\Models\AccountAssocProd::where('account_id', $accountId)
                ->pluck('stock_id')
                ->toArray();

            // Get all product IDs from those stocks
            $productIds = \App\Models\StockModel::whereIn('id', $stockIds)
                ->pluck('product_id')
                ->unique()
                ->toArray();

            // Get products (only those associated with account)
            $prod = \App\Models\Product::where('status', 1)
                ->whereIn('id', $productIds)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            // Get categories (only those with products associated with account)
            $cat = \App\Models\Category::where('status', 1)
                ->whereIn('id', function($query) use ($productIds) {
                    $query->select('category_id')
                        ->from((new \App\Models\StockModel)->getTable())
                        ->whereIn('product_id', $productIds);
                })
                ->select('id', 'name')
                ->get();
                
            // Initialize results
            $results = collect(); // Empty collection if no post or no stock IDs
        
            if ($post && !empty($stockIds)) {
                $st = (new \App\Models\StockModel)->getTable();
                $bst = (new \App\Models\BranchStocks)->getTable();
        
                // Build query with eager loading
                $query = \App\Models\BranchStocks::with(['stock' => function ($q) {
                        $q->select('id', 'product_id', 'category_id', 'attribute_id', 'size_id', 'pair_id');
                    }])
                    ->join($st, "{$st}.id", '=', "{$bst}.stock_id")
                    ->where("{$bst}.status", $status)
                    ->where("{$bst}.current_stock", '>=', $minStock)
                  //  ->whereIn("{$bst}.stock_id", $stockIds) // Filter by account-associated stock IDs
                    ->whereHas('stock', function ($q) use ($productIds) {
                        $q->whereIn('product_id',$productIds);
                    })
                    ->groupBy(DB::raw("{$st}.product_id"), DB::raw("{$st}.category_id"));
        
                // Apply product name filter
                if ($request->filled('prodName')) {
                    $prodName = trim($request->input('prodName'));
                    $query->whereHas('stock', function ($q) use ($prodName) {
                        $q->whereHas('product', function ($a) use ($prodName) {
                            $a->where('name', 'like', '%' . $prodName . '%');
                        });
                    });
                    $titleParts[] = '<small>Product Name Like %' . e($prodName) . '%</small>';
                }
        
                // Apply other filters
                if ($request->filled('prodID')) {
                    $prodID = $request->input('prodID');
                    $query->where("{$st}.product_id", $prodID);
                    $product = \App\Models\Product::find($prodID);
                    if ($product) {
                        $titleParts[] = '<small>' . e($product->name) . '</small>';
                    }
                }
        
                if ($request->filled('catID')) {
                    $catID = $request->input('catID');
                    $query->where("{$st}.category_id", $catID);
                    $category = \App\Models\Category::find($catID);
                    if ($category) {
                        $titleParts[] = '<small>' . e($category->name) . '</small>';
                    }
                }
        
               
               
                // Apply ordering
                if ($priceOrder == 'desc' || $priceOrder == 'asc') {
                    $query->orderBy("{$bst}.sale_price", $priceOrder);
                }
        
                // Paginate results
                $results = $query->get();
        
                // Fetch additional data and financial information
                foreach ($results as $product) {
                    // Fetch gallery image
                    $gallery = \App\Models\Gallery::where('product_id', $product->stock->product_id)
                        ->where('category_id', $product->stock->category_id)
                        ->orderBy('id', 'desc')
                        ->first();
        
                    if ($gallery && !empty($gallery->image)) {
                        $product->stock->productImage = $gallery->image;
                    } else {
                        $product->stock->productImage = $product->stock->product->image;
                    }
        
                    // Fetch stock details
                    $detailQuery = \App\Models\BranchStocks::whereHas('stock', function ($a) use ($product, $minStock, $request, $productIds) {
                        $a->where('product_id', $product->stock->product_id)
                            ->where('category_id', $product->stock->category_id)
                            ->whereIn('product_id', $productIds); // Ensure details are account-associated
                        if ($request->filled('colorID')) {
                            $a->whereIn('attribute_id', (array) $request->input('colorID'));
                        }
                    })->where('current_stock', '>=', $minStock);
        
                  
                    if ($priceOrder == 'desc' || $priceOrder == 'asc') {
                        $detailQuery->orderBy('sale_price', $priceOrder);
                    }
        
                    $product->details = $detailQuery->with([
                        'stock' => function ($q) {
                            $q->withSaleOrderSum()
                                ->withPurchaseOrderSum();
                        }
                    ])->get();
                }
            }
        
            // Prepare title
            $title = implode(' | ', array_filter($titleParts));
        
            // Prepare response
            return view('frontend.frontend.myproduct_catelogue', [
                'breadcrumb' => breadcrumb(['Catalogue' => '']),
                'stock' => $results,
                'prod' => $prod,
                'prodid' => $request->input('prodID', ''),
                'cat' => $cat,
                'catid' => $request->input('catID', ''),
                'Title' => $title,
                'priceOrder' => $priceOrder,
                'post' => $post ? 'true' : 'false',
                'resultcount' => !empty($results) ? $results->count() : 0,
                'mycartData' => $mycartData,
                
            ]);
    }


    public function financialLogsModel()
    {
        $id = Auth::user()->account_id;
        $ac = Account::where('id', $id)->first();
        $fncl = financialLogsModel::where('party_id', $id)->with('payaccount')->get();

        $a['Title'] = 'My ledger';
        $a['ac'] = $ac;
        $a['fromDate'] = '';
        $a['toDate'] = '';
        $a['flData'] = $fncl;
        return view('frontend.mylegder')->with($a);
    }

    //=========NEED ORDER REPORT===========================
    public function supplierNeed()
    {
        // Get the authenticated user's account_id
       $accountId = Auth::user()->account_id;

        // Get stock IDs associated with the account_id
        $stockIds = \App\Models\AccountAssocProd::where('account_id', $accountId)
            ->pluck('stock_id')
            ->toArray();

        // Build the query for BranchStocks
        $query = \App\Models\BranchStocks::query()
            ->whereIn('status', [1])
            ->whereIn('stock_id', $stockIds) // Filter by associated stock IDs
            ->with(['stock' => function ($query) {
                $query->withSaleOrderSum() // Adds total_sod
                    ->withPurchaseOrderSum() // Adds total_pod
                    ->withSum(['purchasecart as total_purchase_cart' => function ($query) {
                        $query->whereIn('order_type', [2, 3]);
                    }], 'sQty')
                    ->with([
                        'product' => fn ($q) => $q->select('id', 'name'),
                        'category' => fn ($q) => $q->select('id', 'name'),
                        'color' => fn ($q) => $q->select('id', 'name')
                    ]);
            }]);

        // Get and filter the stock
       
        $stock = $query->get()
            ->sortBy([
                ['stock.product.name', 'asc'],
                ['stock.category.name', 'asc'],
                ['stock.color.name', 'asc']
            ])
            ->filter(function ($branchStock) {
                $reqStock = $branchStock->current_stock +
                            ($branchStock->stock->total_pod ?? 0) -
                            ($branchStock->stock->total_sod ?? 0);
                return $branchStock->stock && $reqStock < 0;
            });

        // Prepare view data
        $data = [
            'Title' => 'Need Order Report',
            'stock' => $stock,
        ];

        return view('frontend.frontend.urgent-requirements', $data);
    }
    

    public function productMainCatalogue(Request $request)
    {   
        // Initialize variables
        $minStock = (int) $request->input('minQty', 0);
        $fromRate = (int) $request->input('fromRate', 0) ?? 0;
        $toRate = (int) $request->input('toRate') ?? 99999;
        $length = (int) $request->input('length');
        $page = (int) $request->input('page', 1);
        $priceOrder = $request->input('priceOrder') ?? '';
        $status = $request->input('onlyInactive') === 'Inactive Variants' ? 0 : 1;
        $accountId = $request->input('account_id');
        $titleParts = ['Main Product Catalogue'];
        $post = $request->anyFilled([
            'prodID', 'catID', 'parent', 'fromRate', 'toRate', 'colorID',
            'minQty', 'length', 'priceOrder', 'onlyInactive', 'prodName', 'pairID', 'account_id'
        ]);
        $mycartData = \App\Models\CartDetail::where('account_id', Auth::user()->account_id)
                ->where('order_type', 3)
                ->get();
        $myOdrData = \App\Models\SaleOrderDetail::where('account_id', Auth::user()->account_id)
                ->where('sQty','>', 0)
                ->whereIn('status', [1,2])
                ->get();

        if ($status === 0) {
            $titleParts[] = '<span class="text-danger">Inactive Items</span>';
        }

        // Fetch filter options
        $allParent = DB::table('tbl_categories_parent')->select('id', 'name')->get();
        $prod = \App\Models\Product::where('status', 1)
                            ->whereHas('stock', function($q) use ($status) {
                                $q->where('status', $status);
                            })
                            ->select('id', 'name')
                            ->orderBy('name')
                            ->get();

        $cat = \App\Models\Category::where('status', 1)
                                    ->whereHas('stock', function($q) use ($status) {
                                        $q->where('status', $status);
                                    })
                                    ->select('id', 'name')->get();

        $color = \App\Models\Color::select('id', 'name')
                                    ->whereHas('stock', function($q) use ($status) {
                                        $q->where('status', $status);
                                    })->get();
        $pair = \App\Models\Pair::select('id', 'name')->get();

        // Initialize results
        $results = null;

        if ($post) {

        $st = (new StockModel)->getTable();
        $bst = (new BranchStocks)->getTable();
            // Build query with eager loading
            $query = BranchStocks::with(['stock' => function ($q) {
                    $q->select('id', 'product_id', 'category_id', 'attribute_id', 'size_id', 'pair_id');
                }])
                ->join($st, "{$st}.id", '=', "{$bst}.stock_id")
                ->where("{$bst}.status", $status)
                ->where("{$st}.status", $status)
                ->where("{$bst}.current_stock", '>=', $minStock)
                
                ->groupBy(DB::raw("{$st}.product_id"), DB::raw("{$st}.category_id"));
           // Apply product name filter
            if ($request->filled('prodName')) {
                $prodName = trim($request->input('prodName')); // Trim to avoid whitespace issues

                $query->whereHas('stock', function($q) use($prodName) {
                    $q->whereHas('product', function($a) use($prodName) {
                        $a->where('name', 'like', '%' . $prodName . '%');
                    });
                });
                $titleParts[] = '<small>Product Name Like %' . e($prodName) . '%</small>';
            }
            
            // Apply other filters
            if ($request->filled('prodID')) {
                $prodID=$request->input('prodID');
                $query->where("{$st}.product_id",$prodID);
                
                $product = Product::find($request->input('prodID'));
                if ($product) {
                    $titleParts[] = '<small>' . e($product->name) . '</small>';
                }
            }
            
            
            if ($request->filled('parent')) {
                $parentCatid = $request->input('parent');
                $query->whereIn("{$st}.category_id", function($subQuery) use ($parentCatid) {
                        $subQuery->select('category_id')
                                 ->from('tbl_category_parent_mapping')
                                 ->where('parent_id', $parentCatid);
                    });
            } elseif ($request->filled('catID')) {
                $catID=$request->input('catID');
                $query->where("{$st}.category_id",$catID);
                
                $category = Category::find($catID);
                if ($category) {
                    $titleParts[] = '<small>' . e($category->name) . '</small>';
                }
            }
            
            if ($request->filled('colorID')) {
                $query->whereIn("{$st}.attribute_id", (array) $request->input('colorID'));
            }

            if($fromRate >0 && $toRate >0 ){
                $query->whereBetween("{$bst}.sale_price", [$fromRate, $toRate]);
                $titleParts[] = "<small>Price: $fromRate - $toRate</small>";
            }else if($fromRate >0){
                $query->where("{$bst}.sale_price", '>=', $fromRate);
                $titleParts[] = "<small>Price: $fromRate - </small>";
            }else if($toRate >0 ){
                $query->where("{$bst}.sale_price", '<=', $toRate);
                $titleParts[] = "<small>Price: - $toRate</small>";
            }else{

            }
            
            //return print_query($query);
            if ($request->filled('pairID')) {
                $pairId=$request->input('pairID');
                $query->whereHas('stock', function($q) use($pairId){
                    $q->where('pair_id',$pairId);
                });   
                $pairName = Pair::find($request->input('pairID'));
                if ($pairName) {
                    $titleParts[] = '<small>' . e($pairName->name) . '</small>';
                }
            }
            
            // Fulfill logic
            if ($request->input('fulfill') === 'yes') {
                
                if ($request->filled('colorID')) {
                    $colorIDs = (array) $request->input('colorID');
                    $subQuery = BranchStocks::join($st, "{$st}.id", '=', "{$bst}.stock_id")->select('product_id')
                        ->whereIn("{$st}.attribute_id", $colorIDs)
                        ->where("{$st}.status", 1)
                        ->where("{$bst}.current_stock",'>=', $minStock)
                        ->groupBy("{$st}.product_id", "{$st}.category_id")
                        ->havingRaw("COUNT(DISTINCT {$st}.attribute_id) >= ?", [count($colorIDs)]);

                    if ($request->filled('parent')) {
                        $subQuery->whereIn("{$st}.category_id", fn($parentSubQuery) => $parentSubQuery
                            ->select("{$st}.category_id")
                            ->from('tbl_category_parent_mapping')
                            ->where('parent_id', $request->input('parent'))
                        );
                    }
                    
                    $query->whereIn("{$st}.product_id", $subQuery->pluck('product_id'));
                    //$subQuery->pluck('product_id')->toArray();
                }
            } else {
                
            }
          // return print_query($query);
           // Apply ordering
           if($priceOrder == 'desc' || $priceOrder == 'asc'){
                $query->orderBy("{$bst}.sale_price", $priceOrder);
           }

            // Paginate results
            $results = $query->get();

            // Fetch additional data and financial information
            foreach ($results as $product) {
                // Fetch gallery image
                $gallery = \App\Models\Gallery::where('product_id', $product->stock->product_id)
                    ->where('category_id', $product->stock->category_id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($gallery && !empty($gallery->image)) {
                    $product->stock->productImage = $gallery->image;
                }else{
                    $product->stock->productImage=$product->stock->product->image;
                }

                // Fetch stock details
                $detailQuery = BranchStocks::whereHas('stock',function($a) use($product,$minStock,$request){
                    $a->where('product_id', $product->stock->product_id)
                    ->where('category_id', $product->stock->category_id);
                    if ($request->filled('colorID')) {
                        $a->whereIn('attribute_id', (array) $request->input('colorID'));
                    }
                    if ($request->filled('fulfill') && $request->input('fulfill') === 'yes') {
                        $a->whereIn('product_id', $subQuery->pluck('product_id'));
                    }
                })->where('current_stock', '>=', $minStock);

                if($fromRate >0 && $toRate >0 ){
                    $detailQuery->whereBetween('sale_price', [$fromRate, $toRate]);
                }else if($fromRate >0){
                    $detailQuery->where('sale_price', '>=', $fromRate);
                }else if($toRate >0 ){
                    $detailQuery->where('sale_price', '<=', $toRate);
                }else{
    
                }

               
    
                
                
                if($priceOrder == 'desc' || $priceOrder == 'asc'){
                    $detailQuery->orderBy('sale_price', $priceOrder);
                }

                $product->details=$detailQuery->with([
                    'stock' => function ($q) {  // <— "stock" is the key, closure is the value
                        $q->withSaleOrderSum()
                            ->withPurchaseOrderSum();
                    }
                ])->get();
             
            }
        }
        // Prepare title
        $title = implode(' | ', array_filter($titleParts));
       
        // Prepare response
        return view('frontend.frontend.product_catelogue', [
            'breadcrumb' => breadcrumb(['Catalogue' => '']),
            'minqty' => $minStock,
            'fromRate' => $request->input('fromRate', ''),
            'toRate' => $request->input('toRate', ''),
            'stock' => $results,
            'prod' => $prod,
            'parentid' => $request->input('parent', ''),
            'prodid' => $request->input('prodID', ''),
            'colorid' => (array) $request->input('colorID', []),
            'cat' => $cat,
            'catid' => $request->input('catID', ''),
            'pairid' => $request->input('pairID', ''),
            'color' => $color,
            'allParent' => $allParent,
            'pair' => $pair,
            'Title' => $title,
            'length' => $length,
            'page' => $page,
            'priceOrder' => $priceOrder,
            'post' => $post ? 'true' : 'false',
            'account_id' => $accountId,
            'resultcount' => !empty($results) ? $results->count() : 0,
            'mycartData' => $mycartData,
            'myOdrData' => $myOdrData,
        ]);
    }
    //======IMAGE catalogue==============
   
    public function printPreview(Request $request)
    {
        $request->validate([
            'data' => 'required|string',
        ]);

        $a['htmlContent'] = $request->input('data');
        $a['title']='Print Preview';
        // Render the view with the HTML content
        return view('frontend.frontend.printpage')->with($a);
    }





    //====Account Associalte product Catalogue====
    public function accountRelatedCatalogue(Request $request)
    {
        // Initialize variables
        $minStock = (int) $request->input('minQty', 0);
        $fromRate = (int) $request->input('fromRate', 0) ?? 0;
        $toRate = (int) $request->input('toRate') ?? 99999;
        $length = (int) $request->input('length');
        $page = (int) $request->input('page', 1);
        $priceOrder = $request->input('priceOrder') ?? '';
        $status = $request->input('onlyInactive') === 'Inactive Variants' ? 0 : 1;
        $accountId = Auth::user()->account_id; // Use authenticated user's account_id
        $titleParts = ['Main Product Catalogue'];
        $post = $request->anyFilled([
            'prodID', 'catID', 'parent', 'fromRate', 'toRate', 'colorID',
            'minQty', 'length', 'priceOrder', 'onlyInactive', 'prodName', 'pairID'
        ]);

        if ($status === 0) {
            $titleParts[] = '<span class="text-danger">Inactive Items</span>';
        }

        // Fetch filter options
        $allParent = DB::table('tbl_categories_parent')->select('id', 'name')->get();
        $prod = \App\Models\Product::where('status', 1)->select('id', 'name')->orderBy('name')->get();
        $cat = \App\Models\Category::where('status', 1)->select('id', 'name')->get();
        $color = \App\Models\Color::select('id', 'name')->get();
        $pair = \App\Models\Pair::select('id', 'name')->get();

        // Get stock IDs associated with the account_id
        $stockIds = \App\Models\AccountAssocProd::where('account_id', $accountId)
            ->pluck('stock_id')
            ->toArray();

        // Initialize results
        $results = collect(); // Empty collection if no post or no stock IDs

        if ($post && !empty($stockIds)) {
            $st = (new \App\Models\StockModel)->getTable();
            $bst = (new \App\Models\BranchStocks)->getTable();

            // Build query with eager loading
            $query = \App\Models\BranchStocks::with(['stock' => function ($q) {
                    $q->select('id', 'product_id', 'category_id', 'attribute_id', 'size_id', 'pair_id');
                }])
                ->join($st, "{$st}.id", '=', "{$bst}.stock_id")
                ->where("{$bst}.status", $status)
                ->where("{$bst}.current_stock", '>=', $minStock)
                ->whereIn("{$bst}.stock_id", $stockIds) // Filter by account-associated stock IDs
                ->groupBy(DB::raw("{$st}.product_id"), DB::raw("{$st}.category_id"));

            // Apply product name filter
            if ($request->filled('prodName')) {
                $prodName = trim($request->input('prodName'));
                $query->whereHas('stock', function ($q) use ($prodName) {
                    $q->whereHas('product', function ($a) use ($prodName) {
                        $a->where('name', 'like', '%' . $prodName . '%');
                    });
                });
                $titleParts[] = '<small>Product Name Like %' . e($prodName) . '%</small>';
            }

            // Apply other filters
            if ($request->filled('prodID')) {
                $prodID = $request->input('prodID');
                $query->where("{$st}.product_id", $prodID);
                $product = \App\Models\Product::find($prodID);
                if ($product) {
                    $titleParts[] = '<small>' . e($product->name) . '</small>';
                }
            }

            if ($request->filled('parent')) {
                $parentCatid = $request->input('parent');
                $query->whereIn("{$st}.category_id", function ($subQuery) use ($parentCatid) {
                    $subQuery->select('category_id')
                        ->from('tbl_category_parent_mapping')
                        ->where('parent_id', $parentCatid);
                });
            } elseif ($request->filled('catID')) {
                $catID = $request->input('catID');
                $query->where("{$st}.category_id", $catID);
                $category = \App\Models\Category::find($catID);
                if ($category) {
                    $titleParts[] = '<small>' . e($category->name) . '</small>';
                }
            }

            if ($request->filled('colorID')) {
                $query->whereIn("{$st}.attribute_id", (array) $request->input('colorID'));
            }

            if ($fromRate > 0 && $toRate > 0) {
                $query->whereBetween("{$bst}.sale_price", [$fromRate, $toRate]);
                $titleParts[] = "<small>Price: $fromRate - $toRate</small>";
            } elseif ($fromRate > 0) {
                $query->where("{$bst}.sale_price", '>=', $fromRate);
                $titleParts[] = "<small>Price: $fromRate - </small>";
            } elseif ($toRate > 0) {
                $query->where("{$bst}.sale_price", '<=', $toRate);
                $titleParts[] = "<small>Price: - $toRate</small>";
            }

            if ($request->filled('pairID')) {
                $pairId = $request->input('pairID');
                $query->whereHas('stock', function ($q) use ($pairId) {
                    $q->where('pair_id', $pairId);
                });
                $pairName = \App\Models\Pair::find($pairId);
                if ($pairName) {
                    $titleParts[] = '<small>' . e($pairName->name) . '</small>';
                }
            }

            // Fulfill logic
            if ($request->input('fulfill') === 'yes' && $request->filled('colorID')) {
                $colorIDs = (array) $request->input('colorID');
                $subQuery = \App\Models\BranchStocks::join($st, "{$st}.id", '=', "{$bst}.stock_id")
                    ->select('product_id')
                    ->whereIn("{$st}.attribute_id", $colorIDs)
                    ->where("{$st}.status", 1)
                    ->where("{$bst}.current_stock", '>=', $minStock)
                    ->whereIn("{$bst}.stock_id", $stockIds) // Apply account filter to subquery
                    ->groupBy("{$st}.product_id", "{$st}.category_id")
                    ->havingRaw("COUNT(DISTINCT {$st}.attribute_id) >= ?", [count($colorIDs)]);

                if ($request->filled('parent')) {
                    $subQuery->whereIn("{$st}.category_id", fn ($parentSubQuery) => $parentSubQuery
                        ->select("{$st}.category_id")
                        ->from('tbl_category_parent_mapping')
                        ->where('parent_id', $request->input('parent'))
                    );
                }

                $query->whereIn("{$st}.product_id", $subQuery->pluck('product_id'));
            }

            // Apply ordering
            if ($priceOrder == 'desc' || $priceOrder == 'asc') {
                $query->orderBy("{$bst}.sale_price", $priceOrder);
            }

            // Paginate results
            $results = $query->get();

            // Fetch additional data and financial information
            foreach ($results as $product) {
                // Fetch gallery image
                $gallery = \App\Models\Gallery::where('product_id', $product->stock->product_id)
                    ->where('category_id', $product->stock->category_id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($gallery && !empty($gallery->image)) {
                    $product->stock->productImage = $gallery->image;
                } else {
                    $product->stock->productImage = $product->stock->product->image;
                }

                // Fetch stock details
                $detailQuery = \App\Models\BranchStocks::whereHas('stock', function ($a) use ($product, $minStock, $request, $stockIds) {
                    $a->where('product_id', $product->stock->product_id)
                        ->where('category_id', $product->stock->category_id)
                        ->whereIn('id', $stockIds); // Ensure details are account-associated
                    if ($request->filled('colorID')) {
                        $a->whereIn('attribute_id', (array) $request->input('colorID'));
                    }
                })->where('current_stock', '>=', $minStock);

                if ($fromRate > 0 && $toRate > 0) {
                    $detailQuery->whereBetween('sale_price', [$fromRate, $toRate]);
                } elseif ($fromRate > 0) {
                    $detailQuery->where('sale_price', '>=', $fromRate);
                } elseif ($toRate > 0) {
                    $detailQuery->where('sale_price', '<=', $toRate);
                }

                if ($priceOrder == 'desc' || $priceOrder == 'asc') {
                    $detailQuery->orderBy('sale_price', $priceOrder);
                }

                $product->details = $detailQuery->with([
                    'stock' => function ($q) {
                        $q->withSaleOrderSum()
                            ->withPurchaseOrderSum();
                    }
                ])->get();

                // Add financial data for the account
                try {
                    $financials = $this->partyClosingService->calculateClosing(
                        $accountId,
                        $request->input('from_date'),
                        $request->input('to_date')
                    );
                    $product->accountFinancials = $financials;
                } catch (\Exception $e) {
                    $product->accountFinancials = [
                        'opening' => 0,
                        'debitTotal' => 0,
                        'creditTotal' => 0,
                        'closing' => 0,
                    ];
                }
            }
        }

        // Prepare title
        $title = implode(' | ', array_filter($titleParts));

        // Prepare response
        return view('frontend.frontend.product_catelogue', [
            'breadcrumb' => breadcrumb(['Catalogue' => '']),
            'minqty' => $minStock,
            'fromRate' => $request->input('fromRate', ''),
            'toRate' => $request->input('toRate', ''),
            'stock' => $results,
            'prod' => $prod,
            'parentid' => $request->input('parent', ''),
            'prodid' => $request->input('prodID', ''),
            'colorid' => (array) $request->input('colorID', []),
            'cat' => $cat,
            'catid' => $request->input('catID', ''),
            'pairid' => $request->input('pairID', ''),
            'color' => $color,
            'allParent' => $allParent,
            'pair' => $pair,
            'Title' => $title,
            'length' => $length,
            'page' => $page,
            'priceOrder' => $priceOrder,
            'post' => $post ? 'true' : 'false',
            'account_id' => $accountId,
            'resultcount' => !empty($results) ? $results->count() : 0,
        ]);
    }



    //==== Catalogue Block IMAGES ====
    public function productBlockImage(Request $request)
    {
        if (Auth::user()->user_type == 103) {
            return redirect()->back();
        }
        $minStock = 1;
        $i = 0;
        $Title = 'Catalogue';
        $allParent = DB::table('tbl_categories_parent')->select('id', 'name')->get();
        $prod = DB::table('tbl_products_master as p')->where('p.status', 1)->select('p.id', 'p.name')->get();
        $cat = DB::table('tbl_categories as ct')->select('ct.id', 'ct.name')->where('status', 0)->get();
        $color = DB::table('tbl_color as cl')->select('cl.id', 'cl.name')->get();

        if (isset($_POST['prodID']) || isset($_POST['catID']) || isset($_POST['colorID']) || isset($_POST['minQty']) && isset($_POST['fulfill'])) {
            $cond = "st.status='1'";
            if (isset($_POST['minQty']) && !empty($_POST['minQty'])) {
                $minStock = $_POST['minQty'];
            }
            $cond .= " and st.current_stock>='" . $minStock . "'";

            //=====Filter By Product ID==============================
            if (isset($_POST['prodID']) && $_POST['prodID'] >= 1) {
                $cond .= " and st.product_id='" . $_POST['prodID'] . "'";
                $p = Product::where('id', $_POST['prodID'])->select('id', 'name')->first();
                $Title .= ' | <small>' . $p->name . '</small> | ';
            }

            //=====Filter By Parent Category Or Category ID===========
            if (isset($_POST['parent']) && $_POST['parent'] >= 1) {
                $cond .= " and st.category_id IN (select category_id from tbl_category_parent_mapping where parent_id='" . $_POST['parent'] . "')";
                $pcatName = DB::table('tbl_categories_parent')->where('id', $_POST['parent'])->select('id', 'name')->first();
                $Title .= ' | <small>Parent: ' . $pcatName->name . '</small> | ';
            } elseif (isset($_POST['catID']) && $_POST['catID'] >= 1) {
                $cond .= " and st.category_id='" . $_POST['catID'] . "'";
                $c = Category::where('id', $_POST['catID'])->select('id', 'name')->first();
                $Title .= ' | <small>' . $c->name . '</small> | ';
            } else {
            }

            //============Filter By Attribute/Color ID================
            if (isset($_POST['colorID']) && $_POST['colorID'] >= 1) {
                $cond .= " and st.attribute_id IN (" . implode(',', $_POST['colorID']) . ")";
            }

            if (isset($_POST['fromRate']) || isset($_POST['toRate']) && ($_POST['fromRate'] >= 1 || $_POST['toRate'] >= 1)) {
                if (isset($_POST['fromRate'], $_POST['toRate']) && ($_POST['fromRate'] >= 1 && $_POST['toRate'] >= 1)) {
                    $cond .= " and (st.sale_price>='" . $_POST['fromRate'] . "' and st.sale_price<='" . $_POST['toRate'] . "')";
                    $Title .= ' | <small> Price:' . $_POST['fromRate'] . ' - ' . $_POST['toRate'] . '</small> | ';
                } else if (isset($_POST['fromRate']) && $_POST['fromRate'] >= 1) {
                    $cond .= " and st.sale_price>='" . $_POST['fromRate'] . "'";
                    $Title .= ' | <small> Price: >=' . $_POST['fromRate'] . '</small> | ';
                } else if (isset($_POST['toRate']) && $_POST['toRate'] >= 1) {
                    $cond .= " and st.sale_price<='" . $_POST['toRate'] . "'";
                    $Title .= ' | <small> Price: <=' . $_POST['toRate'] . '</small> | ';
                } else {
                }
            }

            if (isset($_POST['latestproduct'])) {
                $subqry = " INNER JOIN (SELECT pp.id FROM tbl_products_master as pp where pp.status=1 ORDER BY pp.id DESC LIMIT 300) as pv ON st.product_id = pv.id";
                $Title .= ' | <small>Lastest: 100</small> | ';

                $odrby = ' order by st.product_id desc';
            } else {
                $subqry = '';
                $odrby = '';
            }

            //====FULLFILL=========
            if (isset($_POST['fulfill']) && $_POST['fulfill'] == 'yes') {
                $cond = "st.status='1'";
                if (isset($_POST['colorID']) && $_POST['colorID'] >= 1) {
                    if (isset($_POST['parent']) && $_POST['parent'] >= 1) {
                        $cond .= " and st.product_id IN (SELECT distinct product_id FROM tbl_products_stock where attribute_id In (" . implode(',', $_POST['colorID']) . ") and status=1 and current_stock >='" . $minStock . "' and category_id IN (select category_id from tbl_category_parent_mapping where parent_id='" . $_POST['parent'] . "') group by product_id,category_id HAVING count(attribute_id)>=" . count($_POST['colorID']) . ")";
                    } else {
                        $cond .= " and st.product_id IN (SELECT distinct product_id FROM tbl_products_stock where attribute_id In (" . implode(',', $_POST['colorID']) . ") and status=1 and current_stock >='" . $minStock . "' group by product_id,category_id HAVING count(attribute_id)>=" . count($_POST['colorID']) . ")";
                    }

                }

            }
            //======FULLFILL End ======


            $sod = DB::select(DB::raw("SELECT st.id,st.product_id,st.category_id,st.attribute_id,p.name as prodName,p.code prodCode,p.image productImage,st.sPriceUpdate_date,(select sum(sQty) from tbl_sale_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as sod,(select sum(sQty) from tbl_purchase_temp_detail as ptd WHERE ptd.stock_id=st.id and ptd.status='active') as pod FROM tbl_products_stock as st LEFT JOIN tbl_products_master AS p ON p.id=st.product_id" . $subqry . " WHERE " . $cond . " group by st.product_id " . $odrby));


            if ($sod) {
                foreach ($sod as $pd) {
                    //=====Detail filter By Parent Category =========
                    if (isset($_POST['parent']) && $_POST['parent'] >= 1) {
                        $dtcond = " and st.category_id IN (select category_id from tbl_category_parent_mapping where parent_id='" . $_POST['parent'] . "')";
                    } elseif (isset($_POST['catID']) && $_POST['catID'] >= 1) {
                        $dtcond = " and st.category_id='" . $_POST['catID'] . "'";
                    } else {
                        $dtcond = ' and st.status=1';
                    }

                    //=====Detail Filter by Fullfil and Parent ============
                    if (isset($_POST['fulfill']) && isset($_POST['colorID'])) {
                        //$cond.=" and st.attribute_id IN (".implode(',',$_POST['colorID']).")";
                        if (isset($_POST['parent']) && $_POST['parent'] >= 1) {
                            $dtcond .= " and st.category_id IN (SELECT distinct category_id FROM tbl_products_stock where product_id='" . $pd->product_id . "' and  attribute_id In (" . implode(',', $_POST['colorID']) . ") and status=1 and current_stock >='" . $minStock . "' and category_id IN (select category_id from tbl_category_parent_mapping where parent_id='" . $_POST['parent'] . "') group by product_id,category_id HAVING count(attribute_id)>=" . count($_POST['colorID']) . ")";
                        } else {
                            $dtcond .= " and st.category_id IN (SELECT distinct category_id FROM tbl_products_stock where product_id='" . $pd->product_id . "' and  attribute_id In (" . implode(',', $_POST['colorID']) . ") and status=1 and current_stock >='" . $minStock . "'group by product_id,category_id HAVING count(attribute_id)>=" . count($_POST['colorID']) . ")";
                        }
                        $dtcond .= " and st.attribute_id IN (" . implode(',', $_POST['colorID']) . ")";
                    } elseif (isset($_POST['colorID']) && !empty($_POST['colorID'])) {
                        $dtcond .= " and st.attribute_id IN (" . implode(',', $_POST['colorID']) . ")";
                    } else {
                        $dtcond .= '';
                    }

                    $std = DB::select(DB::raw("SELECT st.id,st.product_id,st.category_id,st.attribute_id,st.current_stock,st.purchase_price,st.sale_price,st.wholesale_price,ct.name catName,atr.name atrName,st.sPriceUpdate_date,
						(select sum(sQty) from tbl_sale_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as sod,
						(select sum(sQty) from tbl_purchase_temp_detail as ptd WHERE ptd.stock_id=st.id and ptd.status='active') as pod,
						(select created_at from tbl_purchase_detail as lp WHERE lp.stock_id=st.id and lp.status='active' order by lp.id desc limit 1) as lpd
						FROM tbl_products_stock as st LEFT JOIN tbl_categories as ct ON ct.id=st.category_id LEFT JOIN tbl_color as atr ON atr.id=st.attribute_id WHERE st.product_id='" . $pd->product_id . "' and current_stock >='" . $minStock . "'" . $dtcond . " group by st.category_id"));


                    if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
                        $pGallery = DB::table('tbl_products_image_gallery')
                            ->where('product_id', $pd->product_id)
                            ->where('category_id', $pd->category_id)
                            ->first();

                        if ($pGallery) {
                            if (!empty($pGallery->image)) {
                                $pd->productImage = $pGallery->image;
                            }
                        }
                    }
                    $pd->dt = $std;
                    $stock[] = $pd;
                    $i++;
                }
            } else {
                $stock = '';
            }
        } else {
            $stock = '';
        }


        $a['stock'] = $stock;
        $a['prod'] = $prod;
        $a['cat'] = $cat;
        $a['color'] = $color;
        $a['Title'] = $Title;
        $a['resultcount'] = $i;
        $a['allParent'] = $allParent;

        session()->flashInput($request->input());
        return view('frontend.product_catelogue_block')->with($a);

    }


    //==== Catalogue Block IMAGES ====
    public function categoryBlockImage(Request $request)
    {
        if (Auth::user()->user_type == 103) {
            return redirect()->back();
        }
        $minStock = 1;
        $i = 0;
        $Title = 'Catalogue';
        $st = DB::table('tbl_products_stock')
            ->where('status', 1)
            ->where('current_stock', '>=', 1)
            ->groupBy('category_id')
            ->orderBy('product_id', 'desc')
            ->pluck('category_id')->toArray();

        $latestPosts = DB::table('tbl_products_image_gallery')
            ->select(DB::raw('MAX(id) as id'))
            ->whereIn('category_id', $st)
            ->groupBy('category_id');

        $stock = DB::table('tbl_products_image_gallery as pg')
            ->leftJoin('tbl_categories as ct', 'ct.id', '=', 'pg.category_id')
            ->joinSub($latestPosts, 'latest_posts', function ($join) {
                $join->on('pg.id', '=', 'latest_posts.id');
            })
            ->select('pg.*', 'ct.name as catName')
            ->get();



        $a['stock'] = $stock;
        $a['Title'] = $Title;

        session()->flashInput($request->input());
        return view('frontend.category_catelogue_block')->with($a);

    }


    //======IMAGE catalogue==============
    public function myProductImage(Request $request)
    {
        if (Auth::user()->user_type == 104) {
            return redirect()->back();
        }

        $Title = 'Supplier Product Catalog';
        $cat = DB::table('tbl_categories as ct')->select('ct.id', 'ct.name')->where('status', 0)->get();
        $supplier_id = '';
        $i = 1;

        $supplier_id = Auth::user()->account_id;
        $acRefQry = DB::table('tbl_product_assoc_account')->where('st.status', 1)
            ->join('tbl_products_stock As st', 'st.id', '=', 'tbl_product_assoc_account.stock_id');


        $acRefQry->where('account_id', $supplier_id);


        if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
            $acRefQry->where('st.category_id', $_POST['catID']);

        }
        $acRefQry->join('tbl_products_master as pd', 'pd.id', '=', 'st.product_id');
        $refpd = $acRefQry->groupBy('st.product_id')
            ->select('st.product_id', 'pd.name as prodName', 'pd.image as productImage', 'account_id', 'st.category_id')->get();
        if (!empty($refpd)) {
            foreach ($refpd as $pd) {
                $dt = DB::table('tbl_product_assoc_account')->where('st.status', 1)
                    ->join('tbl_products_stock As st', 'st.id', '=', 'tbl_product_assoc_account.stock_id')
                    ->join('tbl_categories as ct', 'ct.id', '=', 'st.category_id')
                    ->join('tbl_color as clr', 'clr.id', '=', 'st.attribute_id')
                    ->where('account_id', $pd->account_id)
                    // ->where('st.category_id',$pd->category_id)
                    ->where('st.product_id', $pd->product_id)
                    ->select('st.id', 'product_id', 'st.category_id', 'st.current_stock', 'st.purchase_price', 'st.sale_price', 'st.wholesale_price', 'st.sPriceUpdate_date', 'st.pPriceUpdate_date', 'ct.name as catName', 'clr.name as atrName', DB::raw("(select sum(sQty) from tbl_sale_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as sod"), DB::raw("(select sum(sQty) from tbl_purchase_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as pod"), DB::raw("(select created_at from tbl_purchase_detail as lp WHERE lp.stock_id=st.id and lp.status='active' order by lp.id desc limit 1) as lpd"))->get();

                if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
                    $pGallery = DB::table('tbl_products_image_gallery')
                        ->where('product_id', $pd->product_id)
                        ->where('category_id', $_POST['catID'])
                        ->first();
                    if ($pGallery) {
                        if (!empty($pGallery->image)) {
                            $pd->productImage = $pGallery->image;
                        }
                    }
                }
                $pd->dt = $dt;
                $stock[] = $pd;
                $i++;
            }
        } else {
            $stock = '';
        }

        $a['Title'] = $Title;
        $a['stock'] = $stock;
        $a['cat'] = $cat;
        $a['supplier_id'] = $supplier_id;
        $a['resultcount'] = $i;

        session()->flashInput($request->input());
        return view('frontend.supplierProduct_catelogue')->with($a);

    }

    //======OFFER catalogue==============
    public function OfferProductCatalogue(Request $request)
    {
        if (Auth::user()->user_type == 103) {
            return redirect()->back();
        }

        $Title = 'Offer Product Catalogue';
        $cat = DB::table('tbl_categories as ct')->select('ct.id', 'ct.name')->where('status', 0)->get();
        $i = 1;


        $acRefQry = DB::table('tbl_products_stock AS st')->where('st.status', 1)->whereRaw('st.sale_price <= st.purchase_price')->whereRaw('st.current_stock > 0');

        if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
            $acRefQry->where('st.category_id', $_POST['catID']);
        }

        $acRefQry->join('tbl_products_master as pd', 'pd.id', '=', 'st.product_id');
        $refpd = $acRefQry->groupBy('st.product_id')
            ->select('st.product_id', 'pd.name as prodName', 'pd.image as productImage')->get();

        if (count($refpd) > 0) {
            foreach ($refpd as $pd) {
                $dt = DB::table('tbl_products_stock as st')->where('st.status', 1)
                    ->join('tbl_categories as ct', 'ct.id', '=', 'st.category_id')
                    ->join('tbl_color as clr', 'clr.id', '=', 'st.attribute_id')
                    ->where('st.product_id', $pd->product_id)
                    ->whereRaw('st.sale_price <= st.purchase_price')
                    ->whereRaw('st.current_stock > 0')
                    ->select('st.id', 'product_id', 'st.category_id', 'st.current_stock', 'st.purchase_price', 'st.sale_price', 'st.wholesale_price', 'st.sPriceUpdate_date', 'st.pPriceUpdate_date', 'ct.name as catName', 'clr.name as atrName', DB::raw("(select sum(sQty) from tbl_sale_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as sod"), DB::raw("(select sum(sQty) from tbl_purchase_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as pod"), DB::raw("(select created_at from tbl_purchase_detail as lp WHERE lp.stock_id=st.id and lp.status='active' order by lp.id desc limit 1) as lpd"))->get();

                if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
                    $pGallery = DB::table('tbl_products_image_gallery')
                        ->where('product_id', $pd->product_id)
                        ->where('category_id', $_POST['catID'])
                        ->first();
                    if ($pGallery) {
                        if (!empty($pGallery->image)) {
                            $pd->productImage = $pGallery->image;
                        }
                    }
                }
                $pd->dt = $dt;
                $stock[] = $pd;
                $i++;
            }
        } else {

            $stock = '';
        }

        $a['Title'] = $Title;
        $a['stock'] = $stock;
        $a['cat'] = $cat;
        $a['resultcount'] = $i;

        session()->flashInput($request->input());
        return view('frontend.offer_catelogue')->with($a);

    }

    //====Latest Arrival CAtalogue=====
    public function LatestProductCatalogue(Request $request)
    {
        if (Auth::user()->user_type == 103) {
            return redirect()->back();
        }

        $Title = 'Latest Arrival Catalogue';
        $cat = DB::table('tbl_categories as ct')->select('ct.id', 'ct.name')->where('status', 0)->get();
        $i = 1;


        $acRefQry = DB::table('tbl_products_stock AS st')->where('st.status', 1)->whereRaw('st.product_id  IN (select id from (select id from tbl_products_master order by id desc limit 350) as prodmaster )');

        if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
            $acRefQry->where('st.category_id', $_POST['catID']);
        }

        $acRefQry->join('tbl_products_master as pd', 'pd.id', '=', 'st.product_id');
        $refpd = $acRefQry->groupBy('st.product_id')
            ->groupBy('st.category_id')
            ->where('st.sale_price', '>', 0)
            ->where('st.purchase_price', '>', 0)
            ->where('st.current_stock', '>', 0)
            ->select('st.product_id', 'st.category_id', 'pd.name as prodName', 'pd.image as productImage')->orderBy('pd.id', 'desc')->get();

        if (count($refpd) > 0) {
            foreach ($refpd as $pd) {
                $dt = DB::table('tbl_products_stock as st')->where('st.status', 1)
                    ->join('tbl_categories as ct', 'ct.id', '=', 'st.category_id')
                    ->join('tbl_color as clr', 'clr.id', '=', 'st.attribute_id')
                    ->where('st.product_id', $pd->product_id)
                    ->where('st.category_id', $pd->category_id)
                    ->where('st.sale_price', '>', 0)
                    ->where('st.purchase_price', '>', 0)
                    ->where('st.current_stock', '>', 0)
                    //->whereIn('st.id',$latestProdODr)
                    //->orWhere('st.current_stock','>',0)
                    ->select('st.id', 'product_id', 'st.category_id', 'st.current_stock', 'st.purchase_price', 'st.sale_price', 'st.wholesale_price', 'st.sPriceUpdate_date', 'st.pPriceUpdate_date', 'ct.name as catName', 'clr.name as atrName', DB::raw("(select sum(sQty) from tbl_sale_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as sod"), DB::raw("(select sum(sQty) from tbl_purchase_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as pod"), DB::raw("(select created_at from tbl_purchase_detail as lp WHERE lp.stock_id=st.id and lp.status='active' order by lp.id desc limit 1) as lpd"))->get();

                //   if(isset($_POST['catID']) && $_POST['catID']>=1)
                //   {
                $pGallery = DB::table('tbl_products_image_gallery')
                    ->where('product_id', $pd->product_id)
                    ->where('category_id', $pd->category_id)
                    ->first();
                if ($pGallery) {
                    if (!empty($pGallery->image)) {
                        $pd->productImage = $pGallery->image;
                    }
                }
                //   }
                $pd->dt = $dt;
                $stock[] = $pd;
                $i++;
            }
        } else {

            $stock = '';
        }

        $a['Title'] = $Title;
        $a['stock'] = $stock;
        $a['cat'] = $cat;
        $a['resultcount'] = $i;

        session()->flashInput($request->input());
        return view('frontend.offer_catelogue')->with($a);

    }

    //====Upcoming Catalogue=====
    public function UpcomingProductCatalogue(Request $request)
    {
        if (Auth::user()->user_type == 103) {
            return redirect()->back();
        }

        $Title = 'Upcoming Product Catalogue';
        $cat = DB::table('tbl_categories as ct')->select('ct.id', 'ct.name')->where('status', 0)->get();
        $i = 1;


        $acRefQry = DB::table('tbl_products_stock AS st')->where('st.status', 1)->whereRaw('st.product_id  IN (select id from (select id from tbl_products_master order by id desc limit 250) as prodmaster )');

        if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
            $acRefQry->where('st.category_id', $_POST['catID']);
        }

        $acRefQry->join('tbl_products_master as pd', 'pd.id', '=', 'st.product_id');
        $refpd = $acRefQry->groupBy('st.product_id')
            ->groupBy('st.category_id')
            ->where('st.current_stock', '=', 0)
            ->where('st.sale_price', '>', 0)
            ->where('st.purchase_price', '>', 0)
            ->select('st.product_id', 'st.category_id', 'pd.name as prodName', 'pd.image as productImage')
            ->orderBy('pd.id', 'desc')
            ->get();

        if (count($refpd) > 0) {
            foreach ($refpd as $pd) {
                $dt = DB::table('tbl_products_stock as st')->where('st.status', 1)
                    ->join('tbl_categories as ct', 'ct.id', '=', 'st.category_id')
                    ->join('tbl_color as clr', 'clr.id', '=', 'st.attribute_id')
                    ->where('st.product_id', $pd->product_id)
                    ->where('st.category_id', $pd->category_id)
                    ->where('st.current_stock', 0)
                    ->where('st.sale_price', '>', 0)
                    ->where('st.purchase_price', '>', 0)
                    //->whereIn('st.id',$latestProdODr)
                    //->orWhere('st.current_stock','>',0)
                    ->select('st.id', 'product_id', 'st.category_id', 'st.current_stock', 'st.purchase_price', 'st.sale_price', 'st.wholesale_price', 'st.sPriceUpdate_date', 'st.pPriceUpdate_date', 'ct.name as catName', 'clr.name as atrName', DB::raw("(select sum(sQty) from tbl_sale_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as sod"), DB::raw("(select sum(sQty) from tbl_purchase_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as pod"), DB::raw("(select created_at from tbl_purchase_detail as lp WHERE lp.stock_id=st.id and lp.status='active' order by lp.id desc limit 1) as lpd"))->get();

                //   if(isset($_POST['catID']) && $_POST['catID']>=1)
                //   {
                $pGallery = DB::table('tbl_products_image_gallery')
                    ->where('product_id', $pd->product_id)
                    ->where('category_id', $pd->category_id)
                    ->first();
                if ($pGallery) {
                    if (!empty($pGallery->image)) {
                        $pd->productImage = $pGallery->image;
                    }
                }
                //   }
                $pd->dt = $dt;
                $stock[] = $pd;
                $i++;
            }
        } else {

            $stock = '';
        }

        $a['Title'] = $Title;
        $a['stock'] = $stock;
        $a['cat'] = $cat;
        $a['resultcount'] = $i;

        session()->flashInput($request->input());
        return view('frontend.offer_catelogue')->with($a);

    }


    //===== Trending Product Catalogue =========
    public function TrendingItemCatelogue(Request $r)
    {
        $minStock = 0;
        $stockCount = 0;
        $Title = 'Trending Products Catalogue';
        $allParent = DB::table('tbl_categories_parent')->select('id', 'name')->get();
        $allState = DB::table('tbl_states')->select('id', 'name')->get();
        $prod = DB::table('tbl_products_master as p')->where('p.status', 1)->select('p.id', 'p.name')->get();
        $cat = DB::table('tbl_categories as ct')->select('ct.id', 'ct.name')->where('status', 0)->get();
        $color = DB::table('tbl_color as cl')->select('cl.id', 'cl.name')->get();
        $stateid = '';
        $ptr = '';
        $i = 0;


        $cond = 'status=1';
        $ps = StockModel::where('status', 1);
        $pd = StockModel::with('product', 'category')
            ->withSum('sales as salesum', 'sQty')
            ->where('status', 1)
            ->havingRaw('salesum > 0')
            ->groupBy('product_id')
            ->groupBy('category_id')
            ->orderBy('salesum', 'desc');

        $ffpd = StockModel::where('status', 1);

        if (isset($_POST['stateid']) && $_POST['stateid'] >= 1) {
            $stateId = $_POST['stateid'];
            $pd->whereHas('sales.account', function ($query) use ($stateId) {
                $query->where('state_id', $stateId)->where('acGroup', 4);

            });

        } else {
            $pd->whereHas('sales.account', function ($query) {
                $query->where('acGroup', 4);
            });
            $pd->limit(250);
        }


        if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
            $ps->where('category_id', $_POST['catID']);
            $pd->where('category_id', $_POST['catID']);
            $c = Category::where('id', $_POST['catID'])->select('id', 'name')->first();
            $Title .= ' | <small>cat : ' . $c->name . '</small> | ';
            $cond .= ' and category_id = ' . $_POST['catID'];
        }

        $pds = $pd->get();
        $ps2 = $ps->with('category', 'attr');
        $ptr = '<table id="example1" class="table table-bordered table-striped text-center table-responsive-xl">';
        foreach ($pds as $p) {

            $pss = StockModel::whereRaw($cond)->where('product_id', $p->product_id)->where('category_id', $p->category_id)->get();

            if (!empty($p->product)) {
                $i++;
                $ptr .= '<tr><td colspan="9" class="prodHead">' . $p->product->name . ': <small>' . $p->category->name . '</small><span class="float-right">
                                                        <a href="javascript:void(0)" class="text-white">
                                                            <span class="float-right catalogueByCat" catid="3" title="Click To show to product catalogue related to this category">
                                                                <i class="fa fa-external-link-square p-1"></i>
                                                            </span>
                                                        </a></td></tr>';
                $ptr .= '<tr><td colspan="9">';

                //================ Main or Category Image =============
                $img = 'no-product-image.png';
                if (!empty($p->product->image)) {
                    $img = $p->product->image;
                }
                $pGallery = DB::table('tbl_products_image_gallery')
                    ->where('product_id', $p->product_id)
                    ->where('category_id', $p->category_id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($pGallery) {
                    if (!empty($pGallery->image)) {
                        $img = $pGallery->image;
                    }
                }
                //=================Emd Prod Image ======================

                $ptr .= '<center><img class="pdImage" style="width:auto;max-height:400px;max-width:100%" src="' . url('storage/app/public/product/' . $img) . '"></cener>';
                $ptr .= '</td></tr>';
                $ptr .= '<tr>
        								<td>Type</td>
                                         <td>Size</td>
                                         <td class="cqty">Stock</td>
                                         <td class="price">Price/offer_price</td>
                                         <td class="priceUpdate hide">Updated On</td>
                                         <td class="myrate hide">Rate</td>
        							</tr>';
                $ptrD = '';

                foreach ($pss as $s) {
                    $sod = DB::table('tbl_sale_temp_detail')->where('stock_id', $s->id)->where('status', 'active')->sum('sQty');
                    $pod = DB::table('tbl_purchase_temp_detail')->where('stock_id', $s->id)->where('status', 'active')->sum('sQty');

                    if ($s->purchase_price >= $s->sale_price) {
                        $offer = '<span class="badge badge-danger">Offer price</span>';
                    } else {
                        $offer = '';
                    }

                    if (!empty($s->lastpurchase)) {
                        $e = explode(' | ', $s->lastpurchase);
                        $lpd = date('d-M-y', strtotime($e[1]));
                        $lpdTitle = 'Last Purchase (Qty:' . $e[0] . ' / Date : ' . $e[1] . '/ Supplier : ' . $e[2] . ')';
                    } else {
                        $lpd = '';
                        $lpdTitle = '';
                    }

                    if (session()->has('accountid') && session()->get('pricegroup') == 2 && $s->wholesale_price > 0) {
                        $price = '<span class="text-danger">' . number_format($s->wholesale_price, 2) . '</span>';
                    } else {
                        $price = number_format($s->sale_price, 2);
                    }

                    if (!empty($s->sPriceUpdate_date)) {
                        $sPriceUpdate = 'SP: ' . date('d-M-y', strtotime($s->sPriceUpdate_date));
                    } else {
                        $sPriceUpdate = '';
                    }

                    if (!empty($s->pPriceUpdate_date)) {
                        $pPriceUpdate = 'PP: ' . date('d-M-y', strtotime($s->pPriceUpdate_date));
                    } else {
                        $pPriceUpdate = '';
                    }
                    if (Auth::user()->user_type == 104) {

                        $btn = '<span  onclick="catRelatedProductStock(' . $s->category_id . ')" class="bg-success border  pull-right" style="cursor:pointer" title="click to see more products related to this category(' . $s->catName . ')">
                                           <i class="glyphicon glyphicon-link"></i>
                                         </span>';
                    } else {
                        $btn = '';
                    }

                    $ptr .= '<tr>
        					            <td class="text-left imgpp text-danger" pd="' . $s->product_id . '" ct="' . $s->category_id . '" title="Click to show the product Image" style="cursor:pointer">' . $s->category->name . $btn . '

                                       </td>
                                       <td class="atc">' . $s->attr->name . '
        								</td>
                            <td class="cqty" title="' . $s->current_stock . '+' . $pod . '-' . $sod . '">' . ($s->current_stock + $pod - $sod) . '</td>
                            <td class="price" title="Purchase P :' . $s->purchase_price . '">' . $price . '</td>


        								<td class="priceUpdate hide" title="Sale and Purchase Price Update Date">' . $sPriceUpdate . '</td>
        								<td class="myrate hide"><input type="text" name="ourprice" class="form-control" value="" style="border:none !important;max-width:120px"></td>

        								</tr>';
                }
                $pss = '';
            }
        }
        $ptr .= '</table>';
        $stockCount = $i;

        $stock = $ptr;
        return view('frontend.trending_catalogue', compact('Title', 'stockCount', 'stock', 'allState', 'stateid', 'cat'));

    }
    //=====ZeroStock== Catalogoue====
    public function zeroStockCatalogue(Request $request)
    {
        if (Auth::user()->user_type == 103) {
            return redirect()->back();
        }

        $Title = 'Zero Stock Catalogue';
        $cat = DB::table('tbl_categories as ct')->select('ct.id', 'ct.name')->where('status', 0)->get();
        $i = 1;

        $acRefQry = DB::table('tbl_products_stock AS st')
            ->where('st.status', 1)
            ->whereRaw('st.current_stock <= 0');

        if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
            $acRefQry->where('st.category_id', $_POST['catID']);
        }

        $acRefQry->join('tbl_products_master as pd', 'pd.id', '=', 'st.product_id');
        $refpd = $acRefQry->groupBy('st.product_id')
            ->groupBy('st.category_id')
            ->select('st.product_id', 'st.category_id', 'pd.name as prodName', 'pd.image as productImage')->get();

        if (count($refpd) > 0) {
            foreach ($refpd as $pd) {
                $dt = DB::table('tbl_products_stock as st')->where('st.status', 1)
                    ->join('tbl_categories as ct', 'ct.id', '=', 'st.category_id')
                    ->join('tbl_color as clr', 'clr.id', '=', 'st.attribute_id')
                    ->where('st.product_id', $pd->product_id)
                    ->where('st.category_id', $pd->category_id)
                    ->whereRaw('st.current_stock <= 0')
                    ->select('st.id', 'product_id', 'st.category_id', 'st.current_stock', 'st.purchase_price', 'st.sale_price', 'st.wholesale_price', 'st.sPriceUpdate_date', 'st.pPriceUpdate_date', 'ct.name as catName', 'clr.name as atrName', DB::raw("(select sum(sQty) from tbl_sale_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as sod"), DB::raw("(select sum(sQty) from tbl_purchase_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as pod"), DB::raw("(select created_at from tbl_purchase_detail as lp WHERE lp.stock_id=st.id and lp.status='active' order by lp.id desc limit 1) as lpd"))->get();

                //   if(isset($_POST['catID']) && $_POST['catID']>=1)
                //   {
                $pGallery = DB::table('tbl_products_image_gallery')
                    ->where('product_id', $pd->product_id)
                    ->where('category_id', $pd->category_id)
                    ->first();
                if ($pGallery) {
                    if (!empty($pGallery->image)) {
                        $pd->productImage = $pGallery->image;
                    }
                }
                //   }
                $pd->dt = $dt;
                $stock[] = $pd;
                $i++;
            }
        } else {

            $stock = '';
        }

        $a['Title'] = $Title;
        $a['stock'] = $stock;
        $a['cat'] = $cat;
        $a['resultcount'] = $i;

        session()->flashInput($request->input());
        return view('frontend.offer_catelogue')->with($a);

    }

    public function productImage__0000000(Request $request)
    {
        $minStock = 1;
        $i = 0;
        $Title = 'Catalogue';
        $prod = DB::table('tbl_products_master as p')->where('p.status', 1)->select('p.id', 'p.name')->get();
        $cat = DB::table('tbl_categories as ct')->select('ct.id', 'ct.name')->where('status', 0)->get();
        $color = DB::table('tbl_color as cl')->select('cl.id', 'cl.name')->get();

        if (isset($_POST['prodID']) || isset($_POST['catID']) || isset($_POST['colorID']) || isset($_POST['minQty']) && isset($_POST['fulfill'])) {
            $cond = "st.status='1'";
            if (isset($_POST['minQty']) && !empty($_POST['minQty'])) {
                $minStock = $_POST['minQty'];
            }
            $cond .= " and st.current_stock>='" . $minStock . "'";

            if (isset($_POST['prodID']) && $_POST['prodID'] >= 1) {
                $cond .= " and st.product_id='" . $_POST['prodID'] . "'";
                $p = Product::where('id', $_POST['prodID'])->select('id', 'name')->first();
                $Title .= ' | <small>' . $p->name . '</small> | ';
            }

            if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
                $cond .= " and st.category_id='" . $_POST['catID'] . "'";
                $c = Category::where('id', $_POST['catID'])->select('id', 'name')->first();
                $Title .= ' | <small>' . $c->name . '</small> | ';
            }

            if (isset($_POST['colorID']) && $_POST['colorID'] >= 1) {
                $cond .= " and st.attribute_id IN (" . implode(',', $_POST['colorID']) . ")";
                // $cl = Color::get()->first();
                // $Title.=' | <small>'.$cl->name.'</small> | ';
            }

            if (isset($_POST['fromRate']) || isset($_POST['toRate']) && ($_POST['fromRate'] >= 1 || $_POST['toRate'] >= 1)) {
                if (isset($_POST['fromRate'], $_POST['toRate']) && ($_POST['fromRate'] >= 1 && $_POST['toRate'] >= 1)) {
                    $cond .= " and (st.sale_price>='" . $_POST['fromRate'] . "' and st.sale_price<='" . $_POST['toRate'] . "')";
                    $Title .= ' | <small> Price:' . $_POST['fromRate'] . ' - ' . $_POST['toRate'] . '</small> | ';
                } else if (isset($_POST['fromRate']) && $_POST['fromRate'] >= 1) {
                    $cond .= " and st.sale_price>='" . $_POST['fromRate'] . "'";
                    $Title .= ' | <small> Price: >=' . $_POST['fromRate'] . '</small> | ';
                } else if (isset($_POST['toRate']) && $_POST['toRate'] >= 1) {
                    $cond .= " and st.sale_price<='" . $_POST['toRate'] . "'";
                    $Title .= ' | <small> Price: <=' . $_POST['toRate'] . '</small> | ';
                } else {
                }
            }

            if (isset($_POST['latestproduct'])) {

                $subqry = " INNER JOIN (SELECT pp.id FROM tbl_products_master as pp where pp.status=1 ORDER BY pp.id DESC LIMIT 100) as pv ON st.product_id = pv.id";

                $Title .= ' | <small>Lastest: 100</small> | ';

            } else {
                $subqry = '';
            }

            $sod = DB::select(DB::raw("SELECT st.id,st.product_id,st.category_id,st.attribute_id,p.name as prodName,p.code prodCode,p.image productImage,(select sum(sQty) from tbl_sale_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as sod,(select sum(sQty) from tbl_purchase_temp_detail as ptd WHERE ptd.stock_id=st.id and ptd.status='active') as pod FROM tbl_products_stock as st LEFT JOIN tbl_products_master AS p ON p.id=st.product_id " . $subqry . " WHERE " . $cond . " group by st.product_id"));



            if ($sod) {

                foreach ($sod as $pd) {

                    if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
                        $dtcond = " and st.category_id='" . $pd->category_id . "'";
                    } else {
                        $dtcond = '';
                    }

                    if (isset($_POST['colorID']) && !empty($_POST['colorID'])) {
                        $dtcond .= " and st.attribute_id IN (" . implode(',', $_POST['colorID']) . ")";
                    } else {
                        $dtcond .= '';
                    }

                    $std = DB::select(DB::raw("SELECT st.id,st.product_id,st.category_id,st.attribute_id,st.current_stock,st.sale_price,ct.name catName,atr.name atrName,
   					(select sum(sQty) from tbl_sale_temp_detail as std WHERE std.stock_id=st.id and std.status='active')  as sod,
   					(select sum(sQty) from tbl_purchase_temp_detail as ptd WHERE ptd.stock_id=st.id and ptd.status='active') as pod
   					FROM tbl_products_stock as st LEFT JOIN tbl_categories as ct ON ct.id=st.category_id LEFT JOIN tbl_color as atr ON atr.id=st.attribute_id WHERE st.product_id='" . $pd->product_id . "' and current_stock >='" . $minStock . "'" . $dtcond));


                    if (isset($_POST['catID']) && $_POST['catID'] >= 1) {
                        $pGallery = DB::table('tbl_products_image_gallery')
                            ->where('product_id', $pd->product_id)
                            ->where('category_id', $pd->category_id)
                            ->first();
                        if ($pGallery) {
                            if (!empty($pGallery->image)) {
                                $pd->productImage = $pGallery->image;
                            }
                        }
                    }
                    $pd->dt = $std;
                    $stock[] = $pd;
                    $i++;

                }
            } else {
                $stock = '';
            }
        } else {
            $stock = '';
        }

        $a['stock'] = $stock;
        $a['prod'] = $prod;
        $a['cat'] = $cat;
        $a['color'] = $color;
        $a['Title'] = $Title;
        $a['resultcount'] = $i;

        session()->flashInput($request->input());

        return view('frontend.product_catelogue')->with($a);
    }

    public function printCatalogue(Request $r)
    {
        //return $r;
        $a['data'] = $r->data;
        //	$a['title']='Print Catalogue';
        if (!empty($r->type == 'admin')) {

            return view('layouts.backend.print_html')->with($a);
        } else {
            return view('layouts.frontend.print_html')->with($a);
        }
    }


    //=========Party wise product Order====================
    public function productStockStatus($id = null, $catid = null)
    {
        $supplierId = Auth::user()->account_id;
        $a['Title'] = 'My product stock status';
        $a['stock'] = '';
        $a['prodid'] = '';
        $a['catid'] = '';
        $a['product'] = DB::table('tbl_product_assoc_account as myp')
            ->join('tbl_products_stock AS st', function ($join) {
                $join->on('st.id', '=', 'myp.stock_id')->where('st.status', '1');
            })
            ->join('tbl_products_master AS pd', function ($join) {
                $join->on('pd.id', '=', 'st.product_id');
            })
            ->groupBy('st.product_id')
            ->select('pd.id as id', 'pd.name', 'pd.code')
            ->get();

        $a['category'] = DB::table('tbl_product_assoc_account as myp')
            ->join('tbl_products_stock AS st', function ($join) {
                $join->on('st.id', '=', 'myp.stock_id')->where('st.status', '1');
            })
            ->join('tbl_categories AS pd', function ($join) {
                $join->on('pd.id', '=', 'st.category_id');
            })
            ->groupBy('st.category_id')
            ->select('pd.id as id', 'pd.name')
            ->get();


        if ($id != '*' and !empty($id)) {
            $cond = 'st.status=1 and st.product_id="' . $id . '"';
            $a['prodid'] = $id;
            $a['category'] = DB::table('tbl_product_assoc_account as myp')
                ->join('tbl_products_stock AS st', function ($join) {
                    $join->on('st.id', '=', 'myp.stock_id')->where('st.status', '1');
                })
                ->join('tbl_categories AS ct', function ($join) {
                    $join->on('ct.id', '=', 'st.category_id');
                })
                ->groupBy('st.category_id')
                ->where('st.product_id', $id)
                ->select('st.id', 'st.category_id', 'ct.name as catname', 'ct.id as catid')
                ->get();

        } else {
            $cond = 'st.status=1 and myp.account_id="' . Auth::user()->account_id . '"';
        }

        if ($catid != null && $catid != '*') {
            $cond .= ' and st.category_id="' . $catid . '"';
            $a['catid'] = $catid;
        }
        //	return $cond;
        $a['stock'] = DB::select(DB::raw("select st.*,pm.name as product_name,pm.code as product_code,clr.name as color_name,ct.name as category_name,
				(select sum(sQty) from tbl_sale_temp_detail where stock_id=st.id and status='active') as sale_order,
				(select sum(sQty) from tbl_purchase_temp_detail  where stock_id=st.id and status='active') as purchase_order,
				st.lastpurchase lpDate
				from tbl_products_stock AS st
				INNER JOIN tbl_product_assoc_account AS myp ON myp.stock_id=st.id
				INNER JOIN tbl_products_master AS pm ON pm.id=st.product_id
				INNER JOIN tbl_color AS clr ON clr.id=st.attribute_id
				INNER JOIN tbl_categories AS ct ON ct.id=st.category_id where " . $cond . " group by myp.stock_id order by ct.name asc,clr.name asc"));

        return view('frontend.myprod_stock')->with($a);

    }

    //=========NEED ORDER REPORT===========================
    public function needOrderForSupplier($reqType = null)
    {
        $supplierId = Auth::user()->account_id;
        $cond = "std.status='active'";
        $stock = [];
        $count = '0';
        $cond .= " and std.account_id='" . $supplierId . "'";

        $sod = DB::table('tbl_product_assoc_account as std')
            ->join('tbl_products_stock AS st', 'st.id', '=', 'std.stock_id')
            ->join('tbl_products_master AS pm', 'pm.id', '=', 'st.product_id')
            ->join('tbl_color AS clr', 'clr.id', '=', 'st.attribute_id')
            ->join('tbl_categories AS ct', 'ct.id', '=', 'st.category_id')
            ->where('std.account_id', $supplierId)
            ->where('st.status', '1')
            ->groupBy('std.stock_id')
            ->orderBy('st.product_id')
            ->select('st.id', 'st.product_id', 'st.category_id', 'pm.name as product_name', 'pm.code as product_code', 'clr.name as color_name', 'ct.name as category_name', 'st.current_stock', 'st.purchase_price')
            ->get();

        if ($sod->count() >= 1) {
            foreach ($sod as $sd) {
                $sd->purchase_order = DB::table('tbl_purchase_temp_detail')->where('stock_id', $sd->id)->where('status', 'active')->sum('sQty');
                $sd->sale_order = DB::table('tbl_sale_temp_detail')->where('stock_id', $sd->id)->where('status', 'active')->sum('sQty');

                $sd->reqStock = ($sd->current_stock + $sd->purchase_order) - $sd->sale_order;
                if ($sd->reqStock < 0 && $sd->sale_order >= 1) {
                    $stock[] = $sd;
                    $count++;
                }
            }
        }
        if ($reqType != null) {
            return $count;
        }
        return view('frontend.ab_needreport', compact('stock'));
    }

}

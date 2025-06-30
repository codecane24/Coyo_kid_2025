<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\StockModel;
use App\Models\Branch;
use App\Models\BranchAccounts;
use App\Models\BranchStocks;
use App\Models\SerialNo;
use App\Http\Controllers\WebController;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Auth;


class BranchController extends WebController
{

    public $branch_obj;
    public function __construct()
    {
        $this->branch_obj = new Branch();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!hasPermission('branch_view')) {
            return redirect()->back();
        }

        return view('admin.branch.index', [
            'title' => 'Branch',
            'breadcrumb' => breadcrumb([
                'Branch' => route('admin.branch.index'),
            ]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!hasPermission('branch_create')) {
            return redirect()->back();
        }
        return view('admin.branch.create', [
            'title' => "Add Branch",
            'breadcrumb' => breadcrumb([
                'Branch' => route('admin.branch.index')
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:255'],
            'address'=>['required']
        ]);
        $return_data = $request->all();
        $branch = $this->branch_obj->saveBranch($return_data);
        if (isset($branch) && !empty($branch)) {
            success_session('Branch created successfully');
        } else {
            error_session('Branch not created');
        }
        return redirect()->route('admin.branch.index');
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

    //status
    public function status_update($id = 0)
    {
        $data = ['status' => 0, 'message' => 'Branch Not Found'];
        $find = $this->branch_obj::find($id);
        if ($find) {
            $find->update(['status' => ($find->status == "inactive") ? "active" : "inactive"]);
            $data['status'] = 1;
            $data['message'] = 'Branch status updated';
        }
        return $data;
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        if (!hasPermission('branch_edit')) {
            return redirect()->back();
        }

        try {
            $decryptedId = Crypt::decrypt($id); // Decrypt the ID safely
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return back()->with('error', 'Invalid account ID.');
        }

        $data = $this->branch_obj->find($decryptedId);
        if (isset($data) && !empty($data))
        {   
           
                               
            $serialNoMaster = SerialNo::withoutGlobalScope('fyear_branch_filter')
                                    ->where('branch_id', 1)
                                    ->where('fyid', 1)
                                    ->where('type', 'transaction')
                                    //->whereNotIn('name', $MyserialArray)
                                    ->orderBy('name')
                                    ->get()
                                    ->each(function ($query) use ($decryptedId) { 
                                        // Use each() instead of map()
                                        $my = SerialNo::withoutGlobalScope('fyear_branch_filter')
                                            ->where('type', 'transaction')
                                            ->where('name', $query->name)
                                            ->where('branch_id', $decryptedId)
                                            ->where('fyid', Session::get('fyear.id'))
                                            ->first();
                                
                                        $query->my_prefix = $my->prefix ?? "";
                                        $query->my_fyear = $my->financialYear ?? "";
                                    });
                                    
        

           
            return view('admin.branch.create', [
                'title' => 'Branch Update',
                'serialNoMaster' =>$serialNoMaster,
                'breadcrumb' => breadcrumb([
                    'Branch' => route('admin.branch.index'),
                    'edit' => route('admin.branch.edit', $id),
                ]),
            ])->with(compact('data'));
        }
        return redirect()->route('admin.branch.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => ['required','max:255'],
            'address' => ['required']
        ]);
        $branch = $this->branch_obj->find($id);
        if(isset($branch) && !empty($branch)){
            $mappedData = [
                'name' =>$request->name,
                'address' =>$request->address
            ];

            
            // $return_data['branch'] = !empty($request->branch) ?  $request->branch : 0;
            $this->branch_obj->saveBranch($mappedData,$id,$branch);
            //return $request;
            if(!empty($request->keyname)){
                foreach($request->keyname as $key => $value){
                    $serialNo = SerialNo::withoutGlobalScope('fyear_branch_filter')
                                ->where('name', $value)
                                ->where('branch_id', $id)
                                ->where('type', 'transaction')
                                ->where('fyid', Session::get('fyear.id'))
                                ->first();

                    if($serialNo){
                        $serialNo->update([
                            'prefix' => $request->prefix[$key],
                            'financialYear' => $request->financialYear[$key],
                        ]);
                    }else{
                        SerialNo::create([
                            'name' => $value,
                            'prefix' => $request->prefix[$key],
                            'financialYear' => $request->financialYear[$key],
                            'length' => $request->length[$key],
                            'branch_id' => $id,
                            'type' => 'transaction',
                            'next_number' => 1,
                            'fyid' => Session::get('fyear.id')
                        ]);
                    }
                }
            }
            
            success_session('Branch updated successfully');
        }
        else{
            error_session('Branch not found');
        }
        return redirect()->route('admin.branch.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!hasPermission('branch_delete')) {
            return redirect()->back();
        }
        $data = $this->branch_obj::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('branch deleted successfully');
        } else {
            error_session('branch not found');
        }
        return redirect()->route('admin.branch.index');
    }


    public function listing(Request $request)
    {
        $data = $this->branch_obj::all();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'status' => route('admin.branch.status_update', $row->id),
                    ],
                    'checked' => ($row->status == 'active') ? 'checked' : ''
                ];
                return  $this->generate_switch($param);
            })
            ->addColumn('description', function ($row) {
                return "-";
            })
            ->addColumn('action', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'delete' => route('admin.branch.destroy', $row->id),
                        'edit' => route('admin.branch.edit', \Crypt::encrypt($row->id)),
                        // 'view' => route('admin.news.show', $row->id),
                    ]
                ];
                return $this->generate_actions_buttons($param);
            })
            ->rawColumns(["status", "action"])
            ->make(true);
    }

    //==== Branch Account Mapping ====
    public function branchAccountMapping(Request $request, $branchId = null)
    {
        $branches = Branch::all();
        $accounts = Account::all();
        $mapingstatus='all';
       //return  $request->mapping_status;
        if ($branchId) {
            // Get mapped accounts for the selected branch 
            $mappedAccounts = BranchAccounts::getBranchMapped(true, null, $branchId); // Active only, optional branchId
           // BranchAccounts::getBranchMapped(true,$branchId); // Active only
           $mappedIds = $mappedAccounts->pluck('account_id')->toArray();
           if($request->mapping_status == 'mapped'){
                $mapingstatus='mapped';
                $unmappedAccounts=[];
             }else if($request->mapping_status == 'unmapped'){
                $mapingstatus='unmapped';
                $unmappedAccounts = $accounts->filter(function ($account) use ($mappedIds) {
                    return !in_array($account->id, $mappedIds);
                });
                $mappedAccounts=[];
             }else{
                $unmappedAccounts = $accounts->filter(function ($account) use ($mappedIds) {
                    return !in_array($account->id, $mappedIds);
                });
             }
            

            return view('admin.branch.branch_account_mapping', [
                'branches' => $branches,
                'accounts' => $accounts,
                'mappedAccounts' => $mappedAccounts,
                'unmappedAccounts' => $unmappedAccounts,
                'selectedBranchId' => $branchId,
                'mapingstatus' => $mapingstatus,
                'title' => 'Branch Account Linking',
                'breadcrumb' => breadcrumb([
                'Branch' => route('admin.branch.index'),
                ]),
            ]);
        } else {
            return view('admin.branch.branch_account_mapping', [
                'branches' => $branches,
                'accounts' => $accounts,
                'mappedAccounts' => [],
                'unmappedAccounts' => $accounts,
                'mapingstatus' => $mapingstatus,
                'selectedBranchId' => null,
                'title' => 'Branch Account Linking',
                'breadcrumb' => breadcrumb([
                'Branch' => route('admin.branch.index'),
                ]),
            ]);
        }
    }

    //===== Save Branch Account Mapping =====
    public function branchAccountMappingSave(Request $request)
    {
        
        $branchId = $request->input('branch_id');
        $accountIds = $request->input('account_ids', []); // Default to empty array if not provided
        $fyid = session('fyear.id'); // Get fyid from session
        $userId = auth()->id(); // Get user_id from auth
        foreach ($accountIds as $accountId) {
            // Find or create the mapping
            $mapping = BranchAccounts::where('fyid', $fyid)
                ->where('branch_id', $branchId)
                ->where('account_id', $accountId)
                ->first();

            if ($mapping) {
                // If exists and status is 0, update to status 1
                if ($mapping->status == 0) {
                    $mapping->update([
                        'status' => 1,
                        'user_id' => $userId, // Update user_id as well
                    ]);
                }else{
                    
                        $mapping->update([
                            'status' => 0,
                            'user_id' => $userId, // Update user_id as well
                        ]);
                    
                }
                // If status is already 1, no action needed
            } else {
                // Create new mapping with status 1
                BranchAccounts::create([
                    'fyid' => $fyid,
                    'branch_id' => $branchId,
                    'account_id' => $accountId,
                    'user_id' => $userId,
                    'status' => 1,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Mappings updated successfully.');
    }


    public function branchStockMapping(Request $request, $branchId = null)
    {
        $branches = Branch::all();
        $stocks = StockModel::all();
        $produdctid=$request->product_id ?? 0;
        $stocks = StockModel::where('product_id',$produdctid)->get();
        $products = \App\Models\Product::all();
        $mapingstatus='all';
       //return  $request->mapping_status;
        if ($branchId) {
            // Get mapped Stocks for the selected branch 
            $mappedStocks = BranchStocks::getBranchMapped(true, null, $branchId,500); // Active only, optional branchId
           // BranchAccounts::getBranchMapped(true,$branchId); // Active only
           $mappedIds = $mappedStocks->pluck('stock_id')->toArray();
           if($request->mapping_status == 'mapped'){
                $mapingstatus='mapped';
                $unmappedStocks=[];
             }else if($request->mapping_status == 'unmapped'){
                $mapingstatus='unmapped';
                $unmappedStocks = $stocks->filter(function ($stock) use ($mappedIds) {
                    return !in_array($stock->id, $mappedIds);
                });
                $mappedStocks=[];
             }else{
                $unmappedStocks = $stocks->filter(function ($stock) use ($mappedIds) {
                    return !in_array($stock->id, $mappedIds);
                });
             }
            

            return view('admin.branch.branch_stock_mapping', [
                'branches' => $branches,
                'stocks' => $stocks,
                'products' => $products,
                'productid'=>$produdctid,
                'mappedStocks' => $mappedStocks,
                'unmappedStocks' => $unmappedStocks,
                'selectedBranchId' => $branchId,
                'mapingstatus' => $mapingstatus,
                'title' => 'Branch Stock Linking',
                'breadcrumb' => breadcrumb([
                'Branch' => route('admin.branch.index'),
                ]),
            ]);
        } else {
            return view('admin.branch.branch_stock_mapping', [
                'branches' => $branches,
                'stocks' => $stocks,
                'products' => $products,
                'productid'=>$produdctid,
                'mappedStocks' => [],
                'unmappedStocks' => $stocks,
                'mapingstatus' => $mapingstatus,
                'selectedBranchId' => null,
                'title' => 'Branch Stock Linking',
                'breadcrumb' => breadcrumb([
                'Branch' => route('admin.branch.index'),
                ]),
            ]);
        }
    }

     
    //===== Save Branch Account Mapping =====
    public function branchStockMappingSave(Request $request)
    {
        
        $branchId = $request->input('branch_id');
        $stockIds = $request->input('stock_ids', []); // Default to empty array if not provided
        $fyid = session('fyear.id'); // Get fyid from session
        $userId = auth()->id(); // Get user_id from auth
        foreach ($stockIds as $stockId) {
            // Find or create the mapping
            $mapping = BranchStocks::where('fyid', $fyid)
                ->where('branch_id', $branchId)
                ->where('stock_id', $stockId)
                ->first();

            if ($mapping) {
                // If exists and status is 0, update to status 1
                if ($mapping->status == 0) {
                        $mapping->update([
                            'status' => 1,
                            'user_id' => $userId, // Update user_id as well
                        ]);
                }else{
                        $mapping->update([
                            'status' => 0,
                            'user_id' => $userId, // Update user_id as well
                        ]);
                    
                }
                // If status is already 1, no action needed
            } else {
                
                // Create new mapping with status 1
                BranchStocks::create([
                    'fyid' => $fyid,
                    'branch_id' => $branchId,
                    'stock_id' => $stockId,
                    'user_id' => $userId,
                    'status' => 1,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Mappings updated successfully.');
    }

}

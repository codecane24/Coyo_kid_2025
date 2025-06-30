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


use App\Models\StockModel;
use App\Models\BranchStocks;
use App\Models\BranchAccounts;
use App\Models\Account;

    use App\Models\FinancialLogsModel;
    use App\Models\AccountGroup;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Redirect;


class DashboardController extends WebController
{

    public function taskDashboard(){
        $inq=\App\Models\Inquery::whereIn('status',[0,1]);
        $a['inq']['total']=$inq->count();
        $a['inq']['total_OdrInq']=(clone $inq)->where('inquiryFor',1)->count();//==Inq For Order
        $a['inq']['total_PhotoInq']=(clone $inq)->where('inquiryFor',2)->count(); // Inq For Photo



       $partyreq=\App\Models\ClientRequest::where('status',0);
        $a['req']['total']=$partyreq->count();
        $a['req']['total_supplier']=(clone $partyreq)->where('order_type',2)->count();// Supplier Sale Req
        $a['req']['total_customer']=(clone $partyreq)->where('order_type',1)->count(); // Customer Purchase Req
        
        $dispatch=\App\Models\Dispatch::whereIn('status',[0,1,2]);
        $a['disp']['total']=$dispatch->count();
        $a['disp']['total_new']=(clone $dispatch)->where('status',0)->count();// Unpicked ..New
        $a['disp']['total_picked']=(clone $dispatch)->where('status',1)->count(); // Picked Req
        $a['disp']['total_completed']=(clone $dispatch)->where('status',2)->count(); // completed Req


        $purchase=\App\Models\Purchase::where('status',0);
        $a['pur']['total']=$purchase->count();
        

        // Today: Inquiries created today
        $todayInq = (clone $inq)->whereDate('created_at', now()->toDateString());
        $a['inqtoday']['total'] = $todayInq->count();
        $a['inqtoday']['order'] = (clone $todayInq)->where('inquiryFor', 1)->count();
        $a['inqtoday']['photo'] = (clone $todayInq)->where('inquiryFor', 2)->count();
        $a['inqtoday']['unassigned'] = (clone $todayInq)->where('salesman_id',0)->count();

        // Pending: Inquiries '
        $pendingInq = (clone $inq)->whereIn('status', [0]);
        $a['inqPending']['total'] = $pendingInq->count();
        $a['inqPending']['order'] = (clone $pendingInq)->where('inquiryFor', 1)->count();
        $a['inqPending']['photo'] = (clone $pendingInq)->where('inquiryFor', 2)->count();
        $a['inqPending']['unassigned'] = (clone $pendingInq)->where('salesman_id',0)->count();

        // Old Accounts: Approved Accounts
        $oldAccountsInq = (clone $inq)->whereHas('account', function ($query) {
            $query->where('is_approved', '1');
        });
        $a['inqOldAc']['total'] = $oldAccountsInq->count();
        $a['inqOldAc']['order'] = (clone $oldAccountsInq)->where('inquiryFor', 1)->count();
        $a['inqOldAc']['photo'] = (clone $oldAccountsInq)->where('inquiryFor', 2)->count();
        $a['inqOldAc']['unassigned'] = (clone $oldAccountsInq)->where('salesman_id',0)->count();

        // New Accounts: Not approved Account
        $newAccountsInq = (clone $inq)->whereHas('account', function ($query) {
            $query->where('is_approved', '0');
        });
        $a['inqNewAc']['total'] = $newAccountsInq->count();
        $a['inqNewAc']['order'] = (clone $newAccountsInq)->where('inquiryFor', 1)->count();
        $a['inqNewAc']['photo'] = (clone $newAccountsInq)->where('inquiryFor', 2)->count();
        $a['inqNewAc']['unassigned'] = (clone $newAccountsInq)->where('salesman_id',0)->count();

        
        $AssignedInq=\App\Models\Inquery::whereIn('status',[1])->where('salesman_id','>',0)->with('notes');
        //=====Assigned Photo Inquery======
        $a['inqAsgPhoto']['total'] = $AssignedInq->where('inquiryFor', 2)->count();
        $a['inqAsgPhoto']['followed'] = (clone $AssignedInq)->where('inquiryFor', 2)
                                        ->whereHas('notes', function ($q) {
                                            $q->whereBetween('datetime', [now()->startOfDay(), now()->endOfDay()]);
                                        })->count();
        $a['inqAsgPhoto']['notfollowed'] = (clone $AssignedInq)->where('inquiryFor', 2)
                                            ->whereDoesntHave('notes', function ($q) {
                                                $q->whereDate('datetime', now()->toDateString());
                                            })->count();

        $a['inqAsgPhoto']['delayed'] =(clone $AssignedInq)->where('inquiryFor', 2)
                                            ->whereDoesntHave('notes', function ($q) {
                                                $q->whereDate('datetime', now()->toDateString());
                                            })->count();

        //=== Assigned Order Inquery =======                                    
        $a['inqAsgOdr']['total'] = $AssignedInq->where('inquiryFor', 1)->count();
        $a['inqAsgOdr']['followed'] = (clone $AssignedInq)->where('inquiryFor', 1)
                                        ->whereHas('notes', function ($q) {
                                            $q->whereBetween('datetime', [now()->startOfDay(), now()->endOfDay()]);
                                        })->count();
        $a['inqAsgOdr']['notfollowed'] = (clone $AssignedInq)->where('inquiryFor', 1)
                                            ->whereDoesntHave('notes', function ($q) {
                                                $q->whereDate('datetime', now()->toDateString());
                                            })->count();

        $a['inqAsgOdr']['delayed'] =(clone $AssignedInq)->where('inquiryFor', 1)
                                            ->whereDoesntHave('notes', function ($q) {
                                                $q->whereBetween('datetime', [now()->startOfDay()->subDays(7), now()->endOfDay()]);
                                            })->count();



        //=== Account Related Data ===
            // Customer Queries (acGroup = 4)
            $ca = Account::where('acGroup', 4);

            // Supplier Queries (acGroup = 3)
            $sa = Account::where('acGroup', 3);
            
             // Customer Account  Total Counts
            $caTotal=(clone $ca);
            $a['caTotal']['total'] = $caTotal->count();
            $a['caTotal']['ws'] = (clone $caTotal)->where('type', '1')->count();
            $a['caTotal']['dist'] = (clone $caTotal)->where('type', '2')->count();
            $a['caTotal']['ret'] = (clone $caTotal)->where('type', '3')->count();
            $a['caTotal']['wsp'] = (clone $caTotal)->where('priceGroup', '2')->count();
            $a['caTotal']['rsp'] = (clone $caTotal)->where('priceGroup', '1')->count(); 

            // Customer Active Status Counts
            $caActive=(clone $ca)->where('status',1);
            $a['caActive']['total'] = $caActive->count();
            $a['caActive']['ws'] = (clone $caActive)->where('type', '1')->count();
            $a['caActive']['dist'] = (clone $caActive)->where('type', '2')->count();
            $a['caActive']['ret'] = (clone $caActive)->where('type', '3')->count();
            $a['caActive']['wsp'] = (clone $caActive)->where('priceGroup', '2')->count();
            $a['caActive']['rsp'] = (clone $caActive)->where('priceGroup', '1')->count();  
            
            // Customer Inactive Account  Counts
             $caInActive=(clone $ca)->where('status',0);
            $a['caInActive']['total'] = $caInActive->count();
            $a['caInActive']['ws'] = (clone $caInActive)->where('type', '1')->count();
            $a['caInActive']['dist'] = (clone $caInActive)->where('type', '2')->count();
            $a['caInActive']['ret'] = (clone $caInActive)->where('type', '3')->count();
            $a['caInActive']['wsp'] = (clone $caInActive)->where('priceGroup', '2')->count();
            $a['caInActive']['rsp'] = (clone $caInActive)->where('priceGroup', '1')->count();  

            // Customer Blocked Account  Counts
             $caBlocked=(clone $ca)->where('block_status',1);
            $a['caBlocked']['total'] = $caBlocked->count();
            $a['caBlocked']['ws'] = (clone $caBlocked)->where('type', '1')->count();
            $a['caBlocked']['dist'] = (clone $caBlocked)->where('type', '2')->count();
            $a['caBlocked']['ret'] = (clone $caBlocked)->where('type', '3')->count();
            $a['caBlocked']['wsp'] = (clone $caBlocked)->where('priceGroup', '2')->count();
            $a['caBlocked']['rsp'] = (clone $caBlocked)->where('priceGroup', '1')->count();  


             // Customer New Not Approved  Counts
            $caNa=(clone $ca)->where('is_approved',0);
            $a['caNa']['total'] = $caNa->count();
            $a['caNa']['ws'] = (clone $caNa)->where('type', '1')->count();
            $a['caNa']['dist'] = (clone $caNa)->where('type', '2')->count();
            $a['caNa']['ret'] = (clone $caNa)->where('type', '3')->count();
            $a['caNa']['wsp'] = (clone $caNa)->where('priceGroup', '2')->count();
            $a['caNa']['rsp'] = (clone $caNa)->where('priceGroup', '1')->count();  

            // Customer Created Today ===
             $caNewToday=(clone $ca)->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);;
            $a['caNewToday']['total'] = $caNewToday->count();
            $a['caNewToday']['ws'] = (clone $caNewToday)->where('type', '1')->count();
            $a['caNewToday']['dist'] = (clone $caNewToday)->where('type', '2')->count();
            $a['caNewToday']['ret'] = (clone $caNewToday)->where('type', '3')->count();
            $a['caNewToday']['wsp'] = (clone $caNewToday)->where('priceGroup', '2')->count();
            $a['caNewToday']['rsp'] = (clone $caNewToday)->where('priceGroup', '1')->count();  
            
            
           

        return view('admin.dashboard.taskdashboard', [
            'title' => 'Task Dashboard',
            'breadcrumb' => breadcrumb([
                'Stock Status' => route('admin.task-dashboard', '*'),
            ]),
            'data' => $a,
        ]);
    }


   
    public function financialDashboard()
    {
        // Redirect if user_type > 1
        if (Auth::user()->user_type > 1) {
            return Redirect::to('admin/dashboard');
        }

        $today = Carbon::today('Asia/Kolkata');
        $yesterday = $today->copy()->subDay();
        $currentMonth = $today->month;
        $previousMonth = $today->copy()->subMonth()->month;
        $currentYear = $today->year;
        $previousYear = $today->copy()->subYear()->year;

        $data = [];

        // Sales Data
        $salesQuery = FinancialLogsModel::where('reference_type', 'sale');
        $data['today'] = $salesQuery->whereDate('txn_date', $today)->get();
        $data['yesterday'] = $salesQuery->whereDate('txn_date', $yesterday)->get();
        $data['month'] = $salesQuery->whereMonth('txn_date', $currentMonth)->get();
        $data['previous_month'] = $salesQuery->whereMonth('txn_date', $previousMonth)->get();
        $data['year'] = FinancialLogsModel::whereYear('txn_date', $currentYear)->get();
        $data['previous_year'] = $salesQuery->whereYear('txn_date', $previousYear)->get();
        $data['sales'] = '0';

        // Current Stock
        $data['stockCurrent'] = BranchStocks::selectRaw('
            SUM(current_stock * sale_price) AS stSaleValue,
            SUM(current_stock * purchase_price) AS stPurValue,
            SUM(opening_stock * sale_price) AS opStSaleValue
        ')->first();

        // Payable (Suppliers)
        $supplier = $customer = BranchAccounts::whereHas('account',function($q){
                                    $q->where('acGroup', 3);
                                })->get();
        $payable = 0;
        $payableCount = 0;
         $i=0;
        if($supplier)
        {
            foreach($supplier as $ac){
                $d=partyCalculateClosing($ac->account_id,$from=null,$to=null);

                if($ac->opening_balance_type=='Cr'){
                $closingBalance=($d['debitTotal'] - $d['creditTotal']) - $ac->opening_balance;
                }else{
                $closingBalance=($d['debitTotal']- $d['creditTotal']) + $ac->opening_balance;
                }

                $payable+=$closingBalance;
                $i++;
                }
                $a['currentPayables']=$payable;
        }
        $a['payableCount']=$i;

        
        $data['currentPayables'] = $payable;
        $data['payableCount'] = $payableCount;

        // Receivable (Customers)
        $customer = BranchAccounts::whereHas('account',function($q){
            $q->where('acGroup', 4);
        })->get();

        $receivable = 0;
        $dataarray=[];
        $i=0;
        if($customer){
          foreach($customer as $ac){
            $d=partyCalculateClosing($ac->account_id,$from=null,$to=null);

              if($ac->opening_balance_type=='Cr'){
                $closingBalance=($d['creditTotal']- $d['debitTotal']) + $ac->opening_balance;
               }else{
                $closingBalance=($d['creditTotal'] - $d['debitTotal']) - $ac->opening_balance;
               }


            // $dataarray[]=$r;
              $receivable+=$closingBalance;
               $i++;
            }
             // return $dataarray;
           $a['currentReceivables']=$receivable;
        }
        $a['ReceivableCount']=$i;
        
        $data['currentReceivables'] = $receivable;
       

        // Expenses Today
        $expenseAccountIds = Account::whereIn('acGroup', [6, 7])->pluck('id')->toArray();
        $data['expensesToday'] = FinancialLogsModel::whereDate('txn_date', $today)
            ->whereIn('reference_type', ['payment', 'expense'])
            ->whereIn('party_id', $expenseAccountIds)
            ->sum('txn_amount');

        // Payment and Receipt Today
        $data['paymentToday'] = FinancialLogsModel::whereDate('txn_date', $today)
            ->where('reference_type', 'payment')
            ->whereNotIn('party_id', $expenseAccountIds)
            ->where('txn_type', 'credit')
            ->sum('txn_amount');
        $data['receiveToday'] = FinancialLogsModel::whereDate('txn_date', $today)
            ->where('reference_type', 'receipt')
            ->where('txn_type', 'credit')
            ->sum('txn_amount');

        // Sale Data
        $data['saleToday'] = FinancialLogsModel::whereDate('txn_date', $today)->where('reference_type', 'sale')->get();
        $data['saleYesterday'] = FinancialLogsModel::whereDate('txn_date', $yesterday)->where('reference_type', 'sale')->get();
        $data['saleMonthWise'] = FinancialLogsModel::selectRaw('
            YEAR(txn_date) year,
            MONTHNAME(txn_date) month,
            MONTH(txn_date) monthNum,
            SUM(txn_amount) txn_amount
        ')
            ->where('reference_type', 'sale')
            ->groupBy('year', 'monthNum', 'month')
            ->orderByDesc('year')
            ->orderByDesc('monthNum')
            ->get();

        // Purchase Data
        $data['purchaseToday'] = FinancialLogsModel::whereDate('txn_date', $today)->where('reference_type', 'purchase')->get();
        $data['purchaseYesterday'] = FinancialLogsModel::whereDate('txn_date', $yesterday)->where('reference_type', 'purchase')->get();
        $data['purchaseMonthWise'] = FinancialLogsModel::selectRaw('
            YEAR(txn_date) year,
            MONTHNAME(txn_date) month,
            MONTH(txn_date) monthNum,
            SUM(txn_amount) txn_amount
        ')
            ->where('reference_type', 'purchase')
            ->groupBy('year', 'monthNum', 'month')
            ->orderByDesc('year')
            ->orderByDesc('monthNum')
            ->get();

        // Payment Data
        $data['payToday'] = FinancialLogsModel::whereDate('txn_date', $today)
            ->where('reference_type', 'payment')
            ->whereNotIn('party_id', $expenseAccountIds)
            ->get();
        $data['payYesterday'] = FinancialLogsModel::whereDate('txn_date', $yesterday)
            ->where('reference_type', 'payment')
            ->whereNotIn('party_id', $expenseAccountIds)
            ->get();
        $data['payMonthWise'] = FinancialLogsModel::selectRaw('
            YEAR(txn_date) year,
            MONTHNAME(txn_date) month,
            MONTH(txn_date) monthNum,
            SUM(txn_amount) txn_amount
        ')
            ->where('reference_type', 'payment')
            ->where('txn_type', 'debit')
            ->whereNotIn('party_id', $expenseAccountIds)
            ->groupBy('year', 'monthNum', 'month')
            ->orderByDesc('year')
            ->orderByDesc('monthNum')
            ->get();

        // Receipt Data
        $data['recToday'] = FinancialLogsModel::whereDate('txn_date', $today)
            ->where('reference_type', 'receipt')
            ->where('txn_type', 'credit')
            ->get();
        $data['recYesterday'] = FinancialLogsModel::whereDate('txn_date', $yesterday)
            ->where('reference_type', 'receipt')
            ->where('txn_type', 'credit')
            ->get();
        $data['recMonthWise'] = FinancialLogsModel::selectRaw('
            YEAR(txn_date) year,
            MONTHNAME(txn_date) month,
            MONTH(txn_date) monthNum,
            SUM(txn_amount) txn_amount
        ')
            ->where('reference_type', 'receipt')
            ->where('txn_type', 'credit')
            ->groupBy('year', 'monthNum', 'month')
            ->orderByDesc('year')
            ->orderByDesc('monthNum')
            ->get();

        // Today's Logins
        $data['todaylogin'] = DB::select("
            SELECT u.*, ag.name as groupname, ac.type as actype
            FROM users u
            LEFT JOIN tbl_account ac ON ac.id = u.account_id
            LEFT JOIN tbl_account_group ag ON ag.id = ac.acGroup
            WHERE u.last_login >= ? ORDER BY u.last_login DESC
        ", [$today->startOfDay()->toDateTimeString()]);

        // Bank and Cash Account Closing Balance
        $bankCashGroups = AccountGroup::whereIn('id', [1, 2, 8])
            ->select('id', 'name')
            ->with(['account' => function ($query) {
                $query->select('id', 'acCode', 'name', 'acGroup');
            }])
            ->get();

        $bankCashData = [];
       
        $data['bank_cash'] = $bankCashData;
        $data['title']='Financial Dashboard';
        $data['breadcrumb'] = breadcrumb([
                'Stock Status' => route('admin.task-dashboard', '*'),
        ]);
        return view('admin.dashboard.financialdashboard', $data);
    }
    
     public function clientDashboard(){

        $cust = \App\Models\BranchAccounts::whereHas('account', function($q){
                        $q->whereIn('acGroup',[4]);
                    })->with('account');
        $sup = \App\Models\BranchAccounts::whereHas('account', function($q){
                        $q->whereIn('acGroup',[3]);
                    })->with('account');

        //====Active Customer ==============
        $actCust = (clone $cust)->whereHas('account',function($q){
                        $q->where('status',1);
                    });
        $a['actCust']['title'] = 'Active Customer';            
        $a['actCust']['total']=$actCust->count(); // Total Active Customer
        $a['actCust']['total_la']=(clone $actCust)->whereHas('account', function($q){
                                                        $q->where('allow_login','Y');
                                                    })->count(); //===  Customer login allow
        $a['actCust']['total_laWs']=(clone $actCust)->whereHas('account', function($q){
                                                        $q->where('allow_login','Y')
                                                        ->where('type',2);
                                                    })->count(); //===  Ws Customer login allow
        $a['actCust']['total_laDs']=(clone $actCust)->whereHas('account', function($q){
                                                        $q->where('allow_login','Y')
                                                        ->whereIn('type',[1,3]);
                                                    })->count(); //===  Ws Customer login allow
    
        //====Active Supplier ==============                                            
        $actSup = (clone $sup)->whereHas('account',function($q){
                        $q->where('status',1);
                    });
        $a['actSup']['title'] = 'Active Supplier';            
        $a['actSup']['total']=$actSup->count(); // Total Active Customer
        $a['actSup']['total_la']=(clone $actSup)->whereHas('account', function($q){
                                                        $q->where('allow_login','Y');
                                                    })->count(); //===  Customer login allow
        $a['actSup']['total_laWs']=(clone $actSup)->whereHas('account', function($q){
                                                        $q->where('allow_login','Y')
                                                            ->where('type',2);
                                                    })->count(); //===  Ws Customer login allow
        $a['actSup']['total_laDs']=(clone $actSup)->whereHas('account', function($q){
                                                        $q->where('allow_login','Y')
                                                            ->whereIn('type',[1,3]);
                                                    })->count(); //===  Ws Customer login allow 
        
        //====Active Login Today ==============                                            
        $client = \App\Models\UserModel::whereIn('type',['customer','supplier'])
                    ->whereBetween('last_login',[now()->startOfDay(), now()->endOfDay()]);
        
        
        $a['clientLT']['title'] = 'Login Today';            
        $a['clientLT']['total']=$client->count(); // Total Active Customer
        $a['clientLT']['total_custLT']=(clone $client)->where('type','customer')->count(); //===  Customer login Today
        $a['clientLT']['total_supLT']=(clone $client)->where('type','supplier')->count(); //===  Supplier login Today


         //====Order Total ==============                                            
         $clientReq = \App\Models\ClientRequest::whereIn('order_type',[1,2]);
        
        $a['creq']['title'] = 'Client Req';            
        $a['creq']['total']= $clientReq->count(); // Total Active Customer
        $a['creq']['total_custreq']=(clone $clientReq)->where('order_type',1)->count(); //===  Customer purchase req
        $a['creq']['total_supreq']=(clone $clientReq)->where('order_type',2)->count(); //===  Supplier sale req
        
        //===== Customer Req Summary ======
        $a['custsummary']['title'] = 'Customer Req Summary';

        //====Customer Login Today List ====
        $a['custLoginTdy']['title']= 'Customer Login Today';
        
        $a['custLoginTdy']['data']=(clone $client)->with('account')
                                    ->where('type','customer')
                                    ->orderBy('last_login','desc')->get();

         //===== Vendor Req Summary ======
        $a['vendorsummary']['title'] = 'Vendor Req Summary';

        //====Vendor Login Today List ====
        $a['vendorLoginTdy']['title']= 'Vendor Login Today';
        $a['vendorLoginTdy']['data']=(clone $client)->with('account')
                                                    ->where('type','supplier')
                                                    ->orderBy('last_login','desc')->get();



        

        //=== Account Related Data ===
            // Customer Queries (acGroup = 4)
            $ca = Account::where('acGroup', 4);

            // Supplier Queries (acGroup = 3)
            $sa = Account::where('acGroup', 3);
            
             // Customer Account  Total Counts
            $caTotal=(clone $ca);
            $a['caTotal']['total'] = $caTotal->count();
            $a['caTotal']['ws'] = (clone $caTotal)->where('type', '1')->count();
            $a['caTotal']['dist'] = (clone $caTotal)->where('type', '2')->count();
            $a['caTotal']['ret'] = (clone $caTotal)->where('type', '3')->count();
            $a['caTotal']['wsp'] = (clone $caTotal)->where('priceGroup', '2')->count();
            $a['caTotal']['rsp'] = (clone $caTotal)->where('priceGroup', '1')->count(); 

            // Customer Active Status Counts
            $caActive=(clone $ca)->where('status',1);
            $a['caActive']['total'] = $caActive->count();
            $a['caActive']['ws'] = (clone $caActive)->where('type', '1')->count();
            $a['caActive']['dist'] = (clone $caActive)->where('type', '2')->count();
            $a['caActive']['ret'] = (clone $caActive)->where('type', '3')->count();
            $a['caActive']['wsp'] = (clone $caActive)->where('priceGroup', '2')->count();
            $a['caActive']['rsp'] = (clone $caActive)->where('priceGroup', '1')->count();  
            
            // Customer Inactive Account  Counts
             $caInActive=(clone $ca)->where('status',0);
            $a['caInActive']['total'] = $caInActive->count();
            $a['caInActive']['ws'] = (clone $caInActive)->where('type', '1')->count();
            $a['caInActive']['dist'] = (clone $caInActive)->where('type', '2')->count();
            $a['caInActive']['ret'] = (clone $caInActive)->where('type', '3')->count();
            $a['caInActive']['wsp'] = (clone $caInActive)->where('priceGroup', '2')->count();
            $a['caInActive']['rsp'] = (clone $caInActive)->where('priceGroup', '1')->count();  

            // Customer Blocked Account  Counts
             $caBlocked=(clone $ca)->where('block_status',1);
            $a['caBlocked']['total'] = $caBlocked->count();
            $a['caBlocked']['ws'] = (clone $caBlocked)->where('type', '1')->count();
            $a['caBlocked']['dist'] = (clone $caBlocked)->where('type', '2')->count();
            $a['caBlocked']['ret'] = (clone $caBlocked)->where('type', '3')->count();
            $a['caBlocked']['wsp'] = (clone $caBlocked)->where('priceGroup', '2')->count();
            $a['caBlocked']['rsp'] = (clone $caBlocked)->where('priceGroup', '1')->count();  


             // Customer New Not Approved  Counts
            $caNa=(clone $ca)->where('is_approved',0);
            $a['caNa']['total'] = $caNa->count();
            $a['caNa']['ws'] = (clone $caNa)->where('type', '1')->count();
            $a['caNa']['dist'] = (clone $caNa)->where('type', '2')->count();
            $a['caNa']['ret'] = (clone $caNa)->where('type', '3')->count();
            $a['caNa']['wsp'] = (clone $caNa)->where('priceGroup', '2')->count();
            $a['caNa']['rsp'] = (clone $caNa)->where('priceGroup', '1')->count();  

            // Customer Created Today ===
             $caNewToday=(clone $ca)->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);;
            $a['caNewToday']['total'] = $caNewToday->count();
            $a['caNewToday']['ws'] = (clone $caNewToday)->where('type', '1')->count();
            $a['caNewToday']['dist'] = (clone $caNewToday)->where('type', '2')->count();
            $a['caNewToday']['ret'] = (clone $caNewToday)->where('type', '3')->count();
            $a['caNewToday']['wsp'] = (clone $caNewToday)->where('priceGroup', '2')->count();
            $a['caNewToday']['rsp'] = (clone $caNewToday)->where('priceGroup', '1')->count();  


            //==== Supplier/Supplier========================= 

             // Vendor Account  Total Counts
            $saTotal=(clone $sa);
            $a['saTotal']['total'] = $saTotal->count();
            $a['saTotal']['ws'] = (clone $saTotal)->where('type', '1')->count();
            $a['saTotal']['dist'] = (clone $saTotal)->where('type', '2')->count();
            
            // Vendor Active Status Counts
            $saActive=(clone $sa)->where('status',1);
            $a['saActive']['total'] = $saActive->count();
            $a['saActive']['ws'] = (clone $saActive)->where('type', '1')->count();
            $a['saActive']['dist'] = (clone $saActive)->where('type', '2')->count();
            
            // Vendor Inactive Account  Counts
             $saInActive=(clone $sa)->where('status',0);
            $a['saInActive']['total'] = $saInActive->count();
            $a['saInActive']['ws'] = (clone $saInActive)->where('type', '1')->count();
            $a['saInActive']['dist'] = (clone $saInActive)->where('type', '2')->count();
         
            // Vendor Blocked Account  Counts
             $saBlocked=(clone $sa)->where('block_status',1);
            $a['saBlocked']['total'] = $saBlocked->count();
            $a['saBlocked']['ws'] = (clone $saBlocked)->where('type', '1')->count();
            $a['saBlocked']['dist'] = (clone $saBlocked)->where('type', '2')->count();
           

             // Vendor New Not Approved  Counts
            $saNa=(clone $sa)->where('is_approved',0);
            $a['saNa']['total'] = $saNa->count();
            $a['saNa']['ws'] = (clone $saNa)->where('type', '1')->count();
            $a['saNa']['dist'] = (clone $saNa)->where('type', '2')->count();
            
            // Vendor Created Today ===
             $saNewToday=(clone $sa)->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);;
            $a['saNewToday']['total'] = $saNewToday->count();
            $a['saNewToday']['ws'] = (clone $saNewToday)->where('type', '1')->count();
            $a['saNewToday']['dist'] = (clone $saNewToday)->where('type', '2')->count();
            
            
           

        return view('admin.dashboard.clientdashboard', [
            'title' => 'Client Dashboard',
            'breadcrumb' => breadcrumb([
                'Stock Status' => route('admin.client-dashboard', '*'),
            ]),
            'data' => $a,
        ]);
    }


}


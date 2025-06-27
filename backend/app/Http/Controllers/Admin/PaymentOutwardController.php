<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use App\Models\Expense;
use App\Models\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\WebController;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Brian2694\Toastr\Facades\Toastr;
use Auth;
use session;
class PaymentOutwardController extends WebController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public $payment_obj, $account_obj;
    public function __construct()
    {
        $this->payment_obj = new Payment();
        $this->account_obj = new Account();
    }

    public function index()
    {
        if (!hasPermission('payment_outward_view')) {
            return redirect()->back();
        }
        $data=$this->payment_obj::where('reference_type', 'payment')->whereNull('reference_id')->with('account','payaccount')->latest()->get();
        return view('admin.payment.outward.index', [
            'title' => 'Outward Payment',
            'breadcrumb' => breadcrumb([
                'Outward Payment' => route('admin.payment.outward.index'),
            ]),
            'txn' =>$data,
        ]);
    }

    public function listing()
    {
         $data = $this->payment_obj::where('reference_type', 'payment')
                                    ->whereNull('reference_id')
                                    ->with('account','payaccount')
                                    ->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('accountName', function($row){
               return $name=$row?->account_name.'<br><small>'.$row?->account->acCode.'</small>';
            })
            ->addColumn('payerName', function($row){
                return $name=$row?->payaccount->name.'<br><small>'.$row?->payaccount->acCode.'</small>';
             })
            ->addColumn('billDate' ,function($row){
                return myDateFormat($row->txn_date);
            })
           
            ->addColumn('billType', function ($row) {
                return "-";
            })

            ->addColumn('action', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'delete' => route('admin.product.destroy', $row->id),
                        'edit' => route('admin.product.edit', $row->id),
                        // 'view' => route('admin.news.show', $row->id),
                    ]
                ];
                return $this->generate_actions_buttons($param);
            })

            ->addColumn('action2',function ($row){
                return '<div class="btn-group cstbtn">
                    <button type="button" class="btn btn-sm btn-outline-primary">
                        <a href="'.route('admin.payment.outward.edit', $row->id).'">
                            <i class="fa fa-edit"></i>
                        </a>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary">
                        <a href="'.url('admin/print-receipt/'.$row->id).'">
                            <i class="fa fa-print"></i>
                        </a>
                    </button>
                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
                        <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li class="divider"></li>
                       
                    </ul>
                    </div>';
            })
            ->rawColumns(["billDate","billType","accountName",'payerName', "action",'action2'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!hasPermission('payment_outward_create')) {
            return redirect()->back();
        }
        $a['allAccount'] = $this->account_obj->orderBy('name')->where('status', 'active')->get();
        $a['bank'] = $this->account_obj->orderBy('name')->where('status', 'active')->get();
        $a['bank'] = Account::latest()->whereIn('acGroup',[1,2,8])->where('status','1')->get();
		$a['payment'] =new Payment();
        $a['nextBill']=getNewSerialNo('payment_receipt');
        $a['title']='Add Outward Payment';
        $a['breadcrumb']=breadcrumb([
            'Payment Outward' => route('admin.payment.outward.index')
        ]);
        return view('admin.payment.outward.addEditPayment')->with($a);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $rd
     * @return \Illuminate\Http\Response
     */
    public function store(Request $rd)
    {
        $inputs = $rd->except('_token');
        $rules = [
          'txn_date' => 'required',
          'account_id' => 'required',
          'txn_amount'=>'required'
        ];

        $validator = Validator::make($inputs, $rules);
        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput();
        }

    try {
            $Acc=$this->account_obj->where('id',$rd->input('account_id'))->select('current_balance')->first();
            $BnkAc=$this->account_obj->where('id',$rd->input('bankAcID'))->select('current_balance')->first();

            $balance=($Acc->current_balance - $rd->input('txn_amount'));
            $Bankbalance=($BnkAc->current_balance + $rd->input('txn_amount'));
            $payType='payment';
            $txnType='debit';
            $txnBankType='credit';

            $accountId = $rd->input('account_id');
            $bankId = $rd->input('bankAcID');
            $amount = $rd->input('txn_amount'); 

            $date = Carbon::now();
            $sNo=getNewSerialNo('payment_receipt');

            $fLogs=$this->payment_obj;
            $fLogs->txn_date=general_date($rd->input('txn_date'));
            //$fLogs->reference_id = '';
            $fLogs->reference_no=$sNo;
            $fLogs->txn_method=$rd->input('txn_method');
            $fLogs->reference_type =$payType;
            $fLogs->txn_type=$txnType;
            $fLogs->txn_amount = $amount;
            $fLogs->party_id = $accountId;
            $fLogs->payment_bank_id = $bankId;
            $fLogs->payment_referrence_no=$rd->input('payment_referrence_no');
            $fLogs->party_prevBal = $Acc->current_balance;
            $fLogs->party_currentBal = $balance;
            $fLogs->remark =$rd->input('remark');

            if($fLogs->save())
            {
                //===== Reversal Enter for Bank====
                $fRlog=new Payment;
                $fRlog->txn_date=general_date($rd->input('txn_date'));
                $fRlog->reference_id =$fLogs->id;
                $fRlog->reference_no=$sNo;
                $fRlog->txn_method=$rd->input('txn_method');
                $fRlog->reference_type =$payType;
                $fRlog->txn_type=$txnBankType;
                $fRlog->txn_amount = $amount;
                //$fRlog->payment_bank_id=$rd->input('bankAcID');
                $fRlog->payment_bank_id = $accountId;
                $fRlog->party_id=$bankId;
                $fRlog->payment_referrence_no=$rd->input('payment_referrence_no');
                $fRlog->party_prevBal = $BnkAc->current_balance;
                $fRlog->party_currentBal = $Bankbalance;
                $fRlog->remark =$rd->input('remark');
                $fRlog->save();

                //======Update Branch Maped CLIENT account======
                \App\Models\BranchAccounts::updateBalance($accountId, $amount, $txnType);

                //======Update Branch Maped BANK account======
                \App\Models\BranchAccounts::updateBalance($bankId, $amount, $txnBankType);


                //======Increase Serial No======
                increaseSerialNo('payment_receipt');

                Toastr::success('Payment done successfully', 'Success');
                //success_session('Payment done successfully');
                return redirect()->route('admin.payment.outward.index');
            }else{

            }
        } catch (\Exception $e) {
            //success_error($e->getMessage());
            return $e;
            return redirect()->back();
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

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!hasPermission('payment_outward_edit')) {
            return redirect()->back();
        }
        $a['title'] = 'Edit Outward Payment';
        $a['breadcrumb'] = breadcrumb([
                    'Payment' => route('admin.payment.outward.index'),
                    'edit' => route('admin.payment.outward.edit', $id),
                ]);
        $a['bank'] = $this->account_obj->orderBy('name')->whereIn('acGroup',[1,2,8])->get();
	    $a['payment'] = $this->payment_obj->where('id',$id)->with('account')->latest()->first();
        $a['nextBill']=$a['payment']->reference_no;
        Toastr::success('Please update carefully', 'Success');
        return view('admin.payment.outward.addEditPayment')->with($a);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $rd
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function update(Request $rd, $id)
     {
         $inputs = $rd->except('_token', '_method'); // Exclude _method for PUT requests
         $rules = [
             'txn_date' => 'required',
             'account_id' => 'required',
             'txn_amount' => 'required'
         ];
 
         $validator = Validator::make($inputs, $rules);
         if ($validator->fails()) {
             return redirect()->back()->withErrors($validator)->withInput();
         }
 
         try {
             // Find the existing payment record
             $fLogs = $this->payment_obj->findOrFail($id);
 
             // Get current balances
             $Acc = $this->account_obj->where('id', $rd->input('account_id'))->select('current_balance')->first();
             $BnkAc = $this->account_obj->where('id', $rd->input('bankAcID'))->select('current_balance')->first();
 
             // Reverse previous transaction effects
             $previousAmount = $fLogs->txn_amount;
             $previousAccountId = $fLogs->party_id;
             $previousBankId = $fLogs->payment_bank_id;
             $previousTxnType = $fLogs->txn_type;
             $previousBankTxnType = 'credit'; // Reversal entry was credit for bank
 
             // Restore previous balances
             \App\Models\BranchAccounts::updateBalance($previousAccountId, $previousAmount, $previousTxnType == 'debit' ? 'credit' : 'debit');
             \App\Models\BranchAccounts::updateBalance($previousBankId, $previousAmount, $previousBankTxnType == 'credit' ? 'debit' : 'credit');
 
             // Calculate new balances
             $balance = $Acc->current_balance - $rd->input('txn_amount');
             $Bankbalance = $BnkAc->current_balance + $rd->input('txn_amount');
             $payType = 'payment';
             $txnType = 'debit'; // Party
             $txnBankType = 'credit'; // Bank
 
             $accountId = $rd->input('account_id');
             $bankId = $rd->input('bankAcID');
             $amount = $rd->input('txn_amount');
 
             // Update the payment record
             $fLogs->txn_date = general_date($rd->input('txn_date'));
             $fLogs->txn_method = $rd->input('txn_method');
             $fLogs->reference_type = $payType;
             $fLogs->txn_type = $txnType;
             $fLogs->txn_amount = $amount;
             $fLogs->party_id = $accountId;
             $fLogs->payment_bank_id = $bankId;
             $fLogs->payment_referrence_no = $rd->input('payment_referrence_no');
             $fLogs->party_prevBal = $Acc->current_balance;
             $fLogs->party_currentBal = $balance;
             $fLogs->remark = $rd->input('remark');
 
             if ($fLogs->save()) {
                 // Find and update the reversal entry for the bank
                 $fRlog = Payment::where('reference_id', $fLogs->id)
                                    ->where('reference_type',$payType)
                                    ->first();
                 if ($fRlog) {
                     $fRlog->txn_date = general_date($rd->input('txn_date'));
                     $fRlog->txn_method = $rd->input('txn_method');
                     $fRlog->reference_type = $payType;
                     $fRlog->txn_type = $txnBankType;
                     $fRlog->txn_amount = $amount;
                     $fRlog->payment_bank_id = $accountId;
                     $fRlog->party_id = $bankId;
                     $fRlog->payment_referrence_no = $rd->input('payment_referrence_no');
                     $fRlog->party_prevBal = $BnkAc->current_balance;
                     $fRlog->party_currentBal = $Bankbalance;
                     $fRlog->remark = $rd->input('remark');
                     $fRlog->save();
                 }
 
                 // Update party account balance
                 \App\Models\BranchAccounts::updateBalance($accountId, $amount, $txnType);
 
                 // Update bank account balance
                 \App\Models\BranchAccounts::updateBalance($bankId, $amount, $txnBankType);
 
                 Toastr::success('Payment updated successfully', 'Success');
                 return redirect()->route('admin.payment.outward.index');
             } else {
                 Toastr::error('Failed to update payment', 'Error');
                 return redirect()->back();
             }
         } catch (\Exception $e) {
             Toastr::error($e->getMessage(), 'Error');
             return redirect()->back();
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
}

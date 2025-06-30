<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait BranchAccountFilter
{
    public function scopeBranchMapped(Builder $query, $includeMappingData = true, $branchId = null)
    {
        $branchId = $branchId ?? Auth::user()->branch_id;
        $fyid = Session::get('fyear.id');

        if ($includeMappingData) {
            return $query->where('branch_id', $branchId)
                         ->where('fyid', $fyid)
                         ->select('id', 'branch_id', 'account_id', 'status', 'user_id')
                         ->with(['account' => function ($q) {
                             $q->select('id', 'name', 'acCode as code', 'email', 'phone', 'phone2', 
                                        'city_id', 'state_id', 'type', 'acGroup', 'priceGroup', 
                                        'contactPerson', 'GSTN_No', 'creditDays', 'credit_limit', 
                                        'discount_rate', 'block_status', 'block_remark', 'status');
                         }, 'account.stateData', 'account.cityData', 'account.acGroupData', 'user' => function ($q) {
                             $q->select('id', 'name', 'code');
                         }]);
        }

        return \App\Models\Account::whereIn('id', function ($subQuery) use ($branchId, $fyid) {
            $subQuery->select('account_id')
                     ->from('branches_accounts')
                     ->where('branch_id', $branchId)
                     ->where('fyid', $fyid);
        })->with(['stateData', 'cityData', 'acGroupData'])
          ->select('id', 'name', 'acCode as code', 'email', 'phone', 'phone2', 'city_id', 
                   'state_id', 'type', 'acGroup', 'priceGroup', 'contactPerson', 'GSTN_No', 
                   'creditDays', 'credit_limit', 'discount_rate', 'block_status', 'block_remark', 'status');
    }

    public static function getBranchMapped($includeMappingData = true, $status = null, $branchId = null)
    {
        $branchId = $branchId ?? Auth::user()->branch_id;
        $query = static::branchMapped($includeMappingData, $branchId);
        if ($includeMappingData && !is_null($status)) {
            $query->where('status', $status);
        }
        return $query->get();
    }

    public static function getUnmapped($branchId = null)
    {
        $branchId = $branchId ?? Auth::user()->branch_id;
        $fyid = Session::get('fyear.id');

        $mappedIds = static::where('branch_id', $branchId)
            ->where('fyid', $fyid)
            ->pluck('account_id');

        return \App\Models\Account::whereNotIn('id', $mappedIds)->get();
    }

    /**
     * Get a single account with mapped data if mapped, or base account data if unmapped, in one query.
     */
    public static function getSingleWithMapping($accountId, $branchId = null)
    {
        $branchId = $branchId ?? Auth::user()->branch_id;
        $fyid = Session::get('fyear.id');

        return \App\Models\Account::select('accounts.id as account_id', 'accounts.name', 
                                           'branches_accounts.status', 'branches_accounts.user_id')
            ->leftJoin('branches_accounts', function ($join) use ($branchId, $fyid, $accountId) {
                $join->on('accounts.id', '=', 'branches_accounts.account_id')
                     ->where('branches_accounts.branch_id', $branchId)
                     ->where('branches_accounts.fyid', $fyid)
                     ->where('branches_accounts.account_id', $accountId);
            })
            ->where('accounts.id', $accountId)
            ->first();
    }

    public static function updateBalance($accountId, $amount, $type, $isCancellation = false)
    {
        $branchId = Auth::user()->branch_id;
        $fyid = Session::get('fyear.id');

        $mapping = static::where('branch_id', $branchId)
            ->where('fyid', $fyid)
            ->where('account_id', $accountId)
            ->first();

        if (!$mapping) {
            return false; // No mapping exists to update
        }

        // Adjust amount based on transaction type
        $transactionAmount = $type === 'credit' ? -$amount : $amount;

        // Calculate new balance based on current balance type
        if ($mapping->current_balance_type === 'Dr') {
            $newBalance = $mapping->current_balance + $transactionAmount;
        } else { // 'Cr'
            $newBalance = $mapping->current_balance - $transactionAmount;
        }

        // Determine new balance type and ensure balance is positive
        $balanceType = $newBalance >= 0 ? 'Dr' : 'Cr';
        $newBalance = abs($newBalance);

        // Update credit/debit totals
        if ($type === 'credit') {
            $newCreditTotal = $isCancellation ? max(0, $mapping->total_credit_amt - $amount) : $mapping->total_credit_amt + $amount;
            $newDebitTotal = $mapping->total_debit_amt; // Keep debit total unchanged
        } else {
            $newDebitTotal = $isCancellation ? max(0, $mapping->total_debit_amt - $amount) : $mapping->total_debit_amt + $amount;
            $newCreditTotal = $mapping->total_credit_amt; // Keep credit total unchanged
        }

        // Update the mapping
        $mapping->update([
            'current_balance' => $newBalance,
            'current_balance_type' => $balanceType,
            'total_credit_amt' => $newCreditTotal,
            'total_debit_amt' => $newDebitTotal,
        ]);

        return true;
    }
}
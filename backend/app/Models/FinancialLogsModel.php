<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FyearBranchFilter;
use App\Traits\ValidatesFinancialYear;

class FinancialLogsModel extends Model
{
    use HasFactory, FyearBranchFilter, ValidatesFinancialYear;
    
    protected $table = 'tbl_financial_logs';
    protected $fillable = [
        'party_id',
        'txn_date',
        'txn_type',
        'txn_amount',
        'reference_type',
        'reference_no',
        'reference_id',
        'payment_bank_id',
        'remark',
        'status',
    ];
    protected $casts = [
        'txn_date' => 'date',
        'txn_amount' => 'float',
        'status' => 'integer',
    ];
    protected $appends = ['payment_bank_name', 'paid_status'];
    
    public function party()
    {
        return $this->belongsTo(Account::class, 'party_id');
    }

    public function payeraccount()
    {
        return $this->belongsTo(Account::class, 'payment_bank_id');
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class, 'reference_no', 'invoice_No');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'reference_no', 'invoice_No');
    }

    public function getPaymentBankNameAttribute(): ?string
    {
        return $this->payeraccount ? $this->payeraccount->name : null;
    }

    public function getPaidStatusAttribute(): string
    {
        if ($this->reference_type === 'sale' && $this->sales && $this->sales->payment_status === '1') {
            return 'paid';
        }

        if ($this->reference_type === 'purchase' && $this->purchase && $this->purchase->payment_status === '1') {
            return 'paid';
        }

        return 'unpaid';
    }

    /**
     * Get the bill relationship based on reference type.
     *
     * @return BelongsTo|MorphTo|null
     */
    public function bill()
    {
        return match ($this->reference_type) {
            'sale' => $this->belongsTo(Sales::class, 'reference_id'),
            'purchase' => $this->belongsTo(Purchase::class, 'reference_id'),
            'sale-return' => $this->belongsTo(SalesReturn::class, 'reference_id'),
            'purchase-return' => $this->belongsTo(PurchaseReturn::class, 'reference_id'),
            'transfer' => $this->belongsTo(Transfer::class, 'reference_id'),
            default => null, // Return null instead of false
        };
    }
}
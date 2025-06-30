<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FyearBranchFilter;
use App\Traits\ValidatesFinancialYear;


class Payment extends Model
{
    use HasFactory, FyearBranchFilter, ValidatesFinancialYear;
    protected $table ='tbl_financial_logs';

    public function account(){
      return $this->belongsTo(Account::class,'party_id');
    }
    public function payaccount(){
      return $this->belongsTo(Account::class,'payment_bank_id','id');
    }

    public function getAccountNameAttribute(){
      return $this->account ? $this->account->name : '-';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FyearBranchFilter;
use App\Traits\ValidatesFinancialYear;


class Transfer extends Model
{
  use HasFactory, FyearBranchFilter, ValidatesFinancialYear; 
  protected $table ='tbl_transfer_logs';

  public function payer(){
    return $this->belongsTo(Account::class,'payer_party_id');
  }

  public function receiver(){
    return $this->belongsTo(Account::class,'receiver_party_id');
  }
  
  public function createdBy(){
    return $this->belongsTo(User::class,'user_id');
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FyearBranchFilter;
use App\Traits\ValidatesFinancialYear;

class InqueryDetail extends Model
{
   use HasFactory, FyearBranchFilter, ValidatesFinancialYear;

   protected $table = 'tbl_sale_inquery_detail';

   
   public function stock(){
		return $this->belongsTo(StockModel::class,'stock_id','id');
	}

   public function bstock(){
		return $this->belongsTo(BranchStocks::class,'stock_id','stock_id');
	}

   public function user(){
      return $this->belongsTo(UserModel::class,'user_id','id');
   }

   public function addedby(){
      return $this->belongsTo(UserModel::class,'added_by','id');
   }

   
}


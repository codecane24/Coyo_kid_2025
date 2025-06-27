<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FyearBranchFilter;
use App\Traits\ValidatesFinancialYear;

class SerialNo extends Model
{
  use HasFactory, FyearBranchFilter, ValidatesFinancialYear;  
	protected $table = 'tbl_serialnumber';
  protected $fillable = [
    'name', // Add 'name' here
    'prefix',
    'financialYear',
    'next_number',
    'type',
    'branch_id',
    'fyid',
  ];

}

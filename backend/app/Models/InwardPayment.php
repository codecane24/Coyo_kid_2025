<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FyearBranchFilter;
use App\Traits\ValidatesFinancialYear;


class InwardPayment extends Model
{

    use HasFactory, FyearBranchFilter, ValidatesFinancialYear;

    
}

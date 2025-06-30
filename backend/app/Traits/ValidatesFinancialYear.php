<?php 
// app/Traits/ValidatesFinancialYear.php

namespace App\Traits;

use App\Models\FinancialYear;
use Illuminate\Support\Facades\Session;

trait ValidatesFinancialYear
{
    protected static function bootValidatesFinancialYear()
    {
        static::creating(function ($model) {
            static::validateFinancialYear($model);
        });

        static::updating(function ($model) {
            static::validateFinancialYear($model);
        });
    }

    protected static function validateFinancialYear($model)
    {
        $financialYearId = Session::get('fyid') ?? Session::get('fyear.id');

        if ($financialYearId) {
            $financialYear = FinancialYear::find($financialYearId);

            if ($financialYear && $financialYear->status === 2) {
                throw new \Exception('Cannot create/update records in a closed financial year.');
            }

            if ($financialYear && isset($model->billDate)) {
                $dateToCheck = strtotime($model->billDate);
                $startDate = strtotime($financialYear->start_date);
                $endDate = strtotime($financialYear->end_date);

                if (!($dateToCheck >= $startDate && $dateToCheck <= $endDate)) {
                    throw new \Exception('Bill date is not within the open financial year range.');
                }
            }
        }
    }
}
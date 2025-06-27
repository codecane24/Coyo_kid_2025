<?php 
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


trait FyearBranchFilter
{
    protected static $currentUserId = null;
    protected static $currentBranchId = null;

    /**
     * Set the user context for the trait (optional)
     */
    public static function setFilterContext(?int $userId = null, ?int $branchId = null)
    {
        static::$currentUserId = $userId;
        static::$currentBranchId = $branchId;
    }

    protected static function bootFyearBranchFilter()
    {
        static::addGlobalScope('fyear_branch_filter', function (Builder $builder) {
            // Use provided branch_id or fall back to authenticated user
            $branchId = static::$currentBranchId ?? (Auth::check() ? Auth::user()->branch_id : null);
            
            if ($branchId) {
                $builder->where('branch_id', $branchId);
            }

            if (Session::has('fyear')) {
                $builder->where('fyid', Session::get('fyear.id'));
            }
        });

        // For new records
        static::creating(function ($model) {
            // Use provided user_id or fall back to authenticated user
            $userId = static::$currentUserId ?? (Auth::check() ? Auth::user()->id : null);
            
            if ($userId) {
                $model->user_id = $model->user_id ?? $userId;
                $model->branch_id = static::$currentBranchId ?? (Auth::user()->branch_id ?? null);
            }

            if (Session::has('fyear')) {
                $model->fyid = Session::get('fyear.id');
            }
        });
    }
}
/*
trait FyearBranchFilter
{
    protected static function bootFyearBranchFilter()
    {
        static::addGlobalScope('fyear_branch_filter', function (Builder $builder) {
            if (Auth::check() && Auth::user()->branch_id) {
                $builder->where('branch_id', Auth::user()->branch_id);
            }
            if (Session::has('fyear')) {
                $builder->where('fyid', Session::get('fyear.id'));
            }
        });

        // For new records
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->user_id = $model->user_id ?? Auth::user()->id;
                $model->branch_id = Auth::user()->branch_id ?? null;
            }
            if (Session::has('fyear')) {
                $model->fyid = Session::get('fyear.id');
            }
        });

        // For updates
        // static::updating(function ($model) {
        //     if (Auth::check()) {
        //         $model->user_id = Auth::user()->id;
        //         $model->branch_id = Auth::user()->branch_id ?? null;
        //     }
        //     if (Session::has('fyid')) {
        //         $model->fyid = Session::get('fyid');
        //     }
        // }); 
    }
} 
*/
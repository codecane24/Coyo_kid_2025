<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait BranchStockFilter
{
    public function scopeBranchMapped(Builder $query, $includeMappingData = true, $branchId = null)
    {
        $branchId = $branchId ?? Auth::user()->branch_id;
        $fyid = Session::get('fyear.id');

        if ($includeMappingData) {
            return $query->where('branch_id', $branchId)
                         ->where('fyid', $fyid)
                         ->select('id', 'branch_id', 'stock_id', 'current_stock', 'status', 'user_id')
                         ->with(['stock' => function ($q) {
                             $q->select('id', 'product_id','category_id', 'pair_id', 'attribute_id', 
                                        'size_id', 'tax_rate', 'purchase_price','sale_price','wholesale_price','status');
                         }, 'user' => function ($q) {
                             $q->select('id', 'name', 'code');
                         }]);
        }

        return \App\Models\StockModel::whereIn('id', function ($subQuery) use ($branchId, $fyid) {
            $subQuery->select('stock_id')
                     ->from('branches_stocks')
                     ->where('branch_id', $branchId)
                     ->where('fyid', $fyid);
        })->with(['stock' => function ($q) {
            $q->select('id', 'product_id','category_id', 'pair_id', 'attribute_id', 
                       'size_id', 'tax_rate', 'purchase_price','sale_price','wholesale_price','status');}])
          ->select('*');
    }

    public static function getBranchMapped($includeMappingData = true, $status = null, $branchId = null, $chunkSize = 1000)
    {
        $branchId = $branchId ?? Auth::user()->branch_id;
        $results = [];

        $query = static::branchMapped($includeMappingData, $branchId);
        if ($includeMappingData && !is_null($status)) {
            $query->where('status', $status);
        }

        $query->chunk($chunkSize, function ($chunk) use (&$results) {
            $results = array_merge($results, $chunk->all());
        });

        return collect($results); // Return as a collection for consistency
    }

    public static function getUnmapped($branchId = null, $chunkSize = 1000)
    {
        $branchId = $branchId ?? Auth::user()->branch_id;
        $fyid = Session::get('fyear.id');
        $results = [];

        $mappedIds = static::where('branch_id', $branchId)
            ->where('fyid', $fyid)
            ->pluck('stock_id');

        \App\Models\StockModel::whereNotIn('id', $mappedIds)
            ->chunk($chunkSize, function ($chunk) use (&$results) {
                $results = array_merge($results, $chunk->all());
            });

        return collect($results); // Return as a collection
    }
    /**
     * Get a single stock with mapped data if mapped, or base stock data if unmapped, in one query.
     */
    public static function getSingleWithMapping($stockId, $branchId = null)
    {
        $branchId = $branchId ?? Auth::user()->branch_id;
        $fyid = Session::get('fyear.id');

        return \App\Models\StockModel::select('stocks.id as stock_id', 'stocks.name', 'stocks.code', 
                                         'branch_stocks.quantity', 'branch_stocks.status', 'branch_stocks.user_id')
            ->leftJoin('branch_stocks', function ($join) use ($branchId, $fyid, $stockId) {
                $join->on('stocks.id', '=', 'branch_stocks.stock_id')
                     ->where('branch_stocks.branch_id', $branchId)
                     ->where('branch_stocks.fyid', $fyid)
                     ->where('branch_stocks.stock_id', $stockId);
            })
            ->where('stocks.id', $stockId)
            ->first();
    }
    
}
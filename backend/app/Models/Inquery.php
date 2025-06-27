<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FyearBranchFilter;
use App\Traits\ValidatesFinancialYear;

class Inquery extends Model
{

    use HasFactory, FyearBranchFilter, ValidatesFinancialYear;
    
	protected $table ='tbl_sale_inquery';
    protected $hidden = ['created_at', 'updated_at'];
    protected $guarded = [];

	public function account()
    {
 		return $this->belongsTo(Account::class, 'account_id');
    }
    
    public function salesman()
    {
        return $this->belongsTo(UserModel::class, 'salesman_id');
    }

    public function details()
    {
        return $this->hasMany(InqueryDetail::class, 'order_id');
    }

    public function user(){
        return $this->belongsTo(UserModel::class,'user_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function cancelby(){
        return $this->belongsTo(UserModel::class,'cancel_by');
    }

    public function saleorder(){
        return $this->belongsTo(saleorder::class,'order_id');
    }

    public function notes()
    {
        return $this->hasMany(Notes::class, 'bill_id')->where('bill_type', 4)->orderBy('id', 'desc');
    }

    

    public function getInqueryDetails($billid)
    {
        
        $bill = $this->with([
            'account',
            'salesman', 
            'details.stock.product',
            'details.stock.category',
            'details.stock.size',
            'details.stock.color',
        ])
        ->findOrFail($billid);

        
        // Group by product_id, category_id, and sRate
        $bill->inqGroupData = $bill->details->groupBy(function ($item) {
            return $item->stock->product_id . '-' . $item->stock->category_id . '-' . $item->sRate;
        });

        // Transform each group and add sum of sNetAmount, sQty, and other fields
        $bill->inqGroupData->transform(function ($group) {
            // Add group totals for sNetAmount, sQty, and additional fields
            $groupTotal = [
                'total_sNetAmount' => $group->sum('sNetAmount'),
                'total_sQty' => $group->sum('sQty'),
                'total_sDiscount' => $group->sum('sDiscount'),
                'sRate' => $group->first()->sRate,
                'taxRate' => $group->first()->taxRate,  // Assuming all items in group have the same taxRate
                'product_name' => $group->first()->stock->product->name, // Assuming all items in group have the same productName
                'category_name' => $group->first()->stock->category->name, // Assuming all items in group have the same category name
                'product_image' => $group->first()->stock->product->image,
				'prod_cat_image'=>$group->first()->stock->getStockImage(),

            ];

            // Include group details for each item
            $group->groupDetail = $group->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'stock_id' => $detail->stock_id,
                    'size_color' => $detail->stock->size_color_name,
                    'sQty' => $detail->sQty,
                    'sRate' => $detail->sRate,
                    'pRate' => $detail->stock->pRate,
                    'isOffer' => $detail->isOffer,
                    
                ];
            });

            // Merge total and group details into a single array
            return array_merge($groupTotal, [
                'groupDetail' => $group->groupDetail
            ]);
        });

        return $bill;
    
    }

}

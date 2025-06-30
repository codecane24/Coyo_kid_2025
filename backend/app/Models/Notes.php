<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notes extends Model
{
	protected $table = 'client_interactions';

	public function bill()
{	
    /* 
        1: sale | 
        2: sale-order | 
        3: sale-return | 
        4: sale-inquiry | 
        5: Inquiry |
        6: purchase | 
        7: purchase-order |
        8: purchase-return 
    */
    $relationshipMap = [
        1 => Sales::class,
        2 => SaleOrder::class,
        3 => SaleReturn::class,
        4 => Inquery::class,
        5 => NewInquiry::class,
        6 => Purchase::class,
        7 => PurchaseOrder::class,
        8 => PurchaseReturn::class,
    ];

    if (isset($relationshipMap[$this->bill_type])) {
        return $this->belongsTo($relationshipMap[$this->bill_type], 'bill_id');
    }

    // Handle invalid or undefined $bill_type
    return null;
}


    public function billdd()
    {	
		/* 	1: sale | 
			2: sale-order | 
			3: sale-return | 
			4: sale-inquiry | 
			5: Inquiry |
			6: purchase | 
			7: purchase-order |
			8: purchase-retun 
		*/

        switch ($this->bill_type) {
            case 1:
                return $this->belongsTo(Sales::class, 'bill_id');
            case 2:
                return $this->belongsTo(SaleOrder::class, 'bill_id');
            case 3:
                return $this->belongsTo(SaleReturn::class, 'bill_id');
            case 4:
                return $this->belongsTo(Inquery::class, 'bill_id');
            case 5:
                return $this->belongsTo(NewInquiry::class, 'bill_id');
            case 6:
                return $this->belongsTo(Purchase::class, 'bill_id');
            case 7:
                return $this->belongsTo(PurchaseOrder::class, 'bill_id');
            case 8:
                return $this->belongsTo(PurchaseReturn::class, 'bill_id');
            default:
                return null;
        }
    }

	public function user(){
		return $this->belongsTo(UserModel::class,'user_id','id');
	}
}

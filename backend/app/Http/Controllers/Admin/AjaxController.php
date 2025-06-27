<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use Illuminate\Support\Facades\Validator;

use App\Models\Account;
use App\Models\NewInquiry;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockModel;
use App\Models\Notes;
use DB;
use Auth;
use App\Models\BranchAccounts;
use App\Models\BranchStocks;
use Illuminate\Http\JsonResponse;

class AjaxController extends Controller
{
    //=====01===Party/Account/Ledger list===============
    public function AccountList(string $group, string $name)//: JsonResponse
    {
       
        $mappedQuery = BranchAccounts::getBranchMapped(false, null, null)->pluck('id');

        // Step 2: Build Account query
        $accounts = Account::query()
            ->whereIn('id', $mappedQuery);
        
        // Apply Group Filter Only If It's Not '*' or 'null'
        if ($group !== '*' && $group !== 'null')
        {
            if ($group === 'sc') {
                $accounts->whereIn('acGroup', ['3', '4'])->where('status', 1);
            } else {
                $accounts->where('acGroup', $group);
            }
        }

        // Apply Name Filter Only If It Has At Least 2 Characters
        if (strlen($name) >= 2) {
            $accounts->where(function ($query) use ($name) {
                $query->where('name', 'LIKE', "%$name%")
                      ->orWhere('acCode', 'LIKE', "%$name%")
                      ->orWhere('email', 'LIKE', "%$name%");
            });
        }
       // return print_query($accounts);
        // Fetch All Matching Records
       $result = $accounts->get();

        return response()->json([
            'status'  => true,
            'message' => 'Account list fetched successfully.',
            'data'    => $result,
        ]);
    }

    //=====02====Account/Ledger/Party Details===========
    public function AccountDetail($id)
    {
      $acData=Account::where('id',$id)->with('statedata','citydata')->first();
	    $currentBalance=partyCalculateClosing($id,null,null);
	    $acData['current_balance']=$currentBalance['closing'];
      $acData['showbalance']=$currentBalance['showbalance'];
  		return Response::json($acData);

    }

    //======03===Partywise Previous Sale / Purchase======
    public function lastItemSalePrice($type,$partyID,$itemids)
    {
      if($type=='sale'){$OdrDt='tbl_sale_detail';$Odr='tbl_sale_order';}else if($type=='purchase'){ $OdrDt='tbl_purchase_detail';$Odr='tbl_purchase_order';}else{}
            $ids=explode('|',$itemids);
            $data=DB::table($OdrDt.' AS sd')
        ->join($Odr.' AS so', function($join) use($partyID,$ids){$join->on('so.id', '=', 'sd.order_id')->where('so.supplier_id','=',$partyID);})
        ->where('sd.product_id',$ids[1])->where('sd.category_id',$ids[2])->where('sd.attribute_id',$ids[3])
        ->select('sd.order_id','sd.sRate','sd.sQty',DB::raw("DATE_FORMAT(so.saleDate, '%d-%b-%Y') as sDate"))
        ->orderBy('so.id','desc')->limit(5)
        ->get();

      return Response::json($data);
    }

    //======04===Partywise Previous Financial Transection ======
    public function accPrevPayment($type,$partyID,$itemids)
    {
      $reftype=explode('|',$itemids);
      $data=DB::table('tbl_financial_logs AS fl')
        ->leftjoin('tbl_account AS ac', function($join){$join->on('ac.id', '=', 'fl.party_id');})
        ->where('fl.party_id',$partyID)->whereIn('reference_type',$reftype)
        ->select('fl.*','ac.name as AcName',DB::raw("DATE_FORMAT(fl.created_at, '%d-%b-%Y') as sDate"))
        ->orderBy('fl.id','desc')
        ->limit(10)
        ->get();
    
      return Response::json($data);
    }

    //======05===Account Bills======
    public function AccountBills($type,$partyID,$itemids)
    {
      if($type=='sale')
      {
        $OdrDt='tbl_sale_detail';
        $Odr='tbl_sale_order';
      }else if($type=='purchase'){ 
        $OdrDt='tbl_purchase_detail';
        $Odr='tbl_purchase_order';
      }else{}

            $ids=explode('|',$itemids);
            $data=DB::table($OdrDt.' AS sd')
        ->join($Odr.' AS so', function($join) use($partyID,$ids){$join->on('so.id', '=', 'sd.order_id')->where('so.supplier_id','=',$partyID);})
        ->where('sd.product_id',$ids[1])->where('sd.category_id',$ids[2])->where('sd.attribute_id',$ids[3])
        ->select('sd.order_id','sd.sRate','sd.sQty',DB::raw("DATE_FORMAT(so.saleDate, '%d-%b-%Y') as sDate"))
        ->orderBy('so.id','desc')->limit(5)
        ->get();

      return Response::json($data);
    }

    

    //====07 Account Search for New Inquery============
    public function AccNewInqSearch($group=null,$keyword){
      $status=false;
      $html='';
      $html2='';
     $Acc=Account::query();

     //===Branched Mapped Account ===
      $mappedQuery = BranchAccounts::getBranchMapped(false, null, null)->pluck('id');
      $Acc->whereIn('id', $mappedQuery);
      //====End ========

    $Acc->where(function ($query) use ($keyword) {
        $query->where('name', 'LIKE', '%' . $keyword . '%')
              ->orWhere('acCode', 'LIKE', '%' . $keyword . '%')
              ->orWhere('phone', 'LIKE', '%' . $keyword . '%')
              ->orWhere('phone2', 'LIKE', '%' . $keyword . '%')
              ->orWhere('contactPerson', 'LIKE', '%' . $keyword . '%')
              ->orWhere('address', 'LIKE', '%' . $keyword . '%');
    })
    ->with(['statedata', 'citydata', 'acGroupData']);

      if($group>=1){
        $Acc->where('acGroup',$group);
      }
      $account=$Acc->get();
      $newInq= NewInquiry::where('name','LIKE','%'.$keyword.'%')
      ->orWhere('phone','LIKE','%'.$keyword.'%')
      ->orWhere('phone2','LIKE','%'.$keyword.'%')
      ->orWhere('contactPerson','LIKE','%'.$keyword.'%')
      ->orWhere('address','LIKE','%'.$keyword.'%')
      ->with('state')
      ->get();
                  
      if($account){
        $status=true;
        $html='<h3> Registered Customer</h3><table class="w-100 table table-info table-hover">
                  <thead class="table-dark">
                      <tr>
                          <th>Sno</th>
                          <th>Sup/Cust</th>
                          <th>Name </th>
                          <th>Phone No </th>
                          <th>State/City </th>
                          <th>Address </th>
                      </tr>
                  </thead>';
         $i=1; 
         $tr='';          
        foreach($account as $ac)
        {
          $state='';
          $city='';
          if(!empty($ac->statedata)){
            $state=$ac->statedata->name;
          }
          if(!empty($ac->citydata)){
            $city=$ac->citydata->name;
          }
          
          $tr.='<tr class="accinfo" role="button" acid="'.$ac->id.'">
                  <td>'.$i.'</td>
                  <td>'.$ac->account_group_name.'</td>
                  <td>'.$ac->name.accountType($ac->type).'</td>
                  <td>'.$ac->phone.'<br>'.$ac->phone2.'</td>
                  <td>'.$state.' | '.$city.'</td>
                  <td>'.$ac->address.'</td>
                </tr>';
                $i++;
        }
        $html.='<tbody>'.$tr.'</tbody>';
      }
      /*====New Inquery === */
      if($newInq){
        $status=true;
        $html2='<h3> Previous Inquery </h3>
        <table class="w-100 table table-warning table-hover">
                  <thead class="table-dark">
                      <tr>
                          <th>Inq No</th>
                          <th>Name </th>
                          <th>Phone No </th>
                          <th>State/City </th>
                          <th>Address </th>
                      </tr>
                  </thead>';
        $i=1; 
        $tr='';          
        foreach($newInq as $ac)
        {
          $state='';
          $city=$ac->city;
          if(!empty($ac->state)){
            $state=$ac->state->name;
          }
          if(!empty($ac->citydata)){
            $city=$ac->citydata->name;
          }
          $inqDate= date('d-M-y',strtotime($ac->saleDate));
          $tr.='<tr>
                  <td><a href="'.route('admin.inquiry-new.edit', $ac->id).'">'.$ac->invoice_No.'</a><br><small>'.$inqDate.'</small></td>
                  <td>'.$ac->name.accountType($ac->customerType).'</td>
                  <td>'.$ac->phone.'<br>'.$ac->phone2.'</td>
                  <td>'.$state.' | '.$city.'</td>
                  <td>'.$ac->address.'</td>
                </tr>';
                $i++;
        }
        $html2.='<tbody>'.$tr.'</tbody>';
      }
    $a['acc']=$html;
    $a['inq']=$html2;  
    $a['status']=$status;  
    return $a;

    }
    

  //=====08 ====Product  Pending order========
  public function saleProductPendingOrder($pdStockID)
  {
    $stock=\App\Models\SaleOrderDetail::where('stock_id',$pdStockID)
                                          ->with('stock','bill','bill.account','stock.product','stock.category','stock.color')
                                          ->whereIn('status',[1,2])
                                          ->where('sQty','>',0)
                                          ->get();
    return Response::json($stock);
  }


  //=====09 ====Product  Pending Purchase order========
  public function purchaseProductPendingOrder($pdStockID)
  {
    $stock=\App\Models\PurchaseOrderDetail::where('stock_id',$pdStockID)
                                          ->with('stock','bill','bill.account','stock.product','stock.category','stock.color')
                                          ->whereIn('status',[1,2])
                                          ->where('sQty','>',0)
                                          ->get();
    return Response::json($stock);
  }

  //=====10 ====Product  Pending In  Dispatch Box ========
  public function dispatchProductPending($pdStockID)
  {
    $stock=\App\Models\DispatchDetail::where('stock_id',$pdStockID)
                                      ->with('stock','bill','bill.account','stock.product','stock.category','stock.color')
                                      ->where('status',1)
                                      ->where('dispatch_qty','>',0)
                                      ->get();
      return Response::json($stock);
  }

  //========Search Product By Name ========
  public function searchProdName($name)
  {
      $Acc= DB::table('tbl_products_master AS p')
          ->join('tbl_products_stock AS ps', function($join)
          {
            $join->on('ps.product_id', '=', 'p.id');
            $join->where('ps.status','=', 1);
          })
          ->join('tbl_categories AS pc', function($join)
          {
            $join->on('pc.id', '=', 'ps.category_id');
          })
          ->where('p.name','LIKE','%'.$name.'%')
          ->where('ps.status',1)
          ->orWhere('p.code','LIKE','%'.$name.'%')
          ->where('ps.status',1)->where('p.status',1)
          ->select('p.id','p.code','p.name','ps.id AS stockID','ps.category_id AS catID','pc.name AS catName','ps.current_stock')
          ->groupBy('ps.category_id','ps.product_id')
          ->orderBy('p.id')
          ->get();

          return Response::json($Acc);
    }

  //========Search Product By Name ========
  public function searchProdQrCode($qrcode,$catall=null,$actype=null)
  {     
    $html=''; 
    $qrdata='' ;
    //$qrstockid=\App\Models\QrCode::Where('qrcode',$qrcode)->first();
    $qrstockid=\App\Models\StockModel::Where('qrcode',$qrcode)->first();
    
    if(!empty($qrstockid))
      {
        $qrdata=\App\Models\StockModel::where('id',$qrstockid->stock_id)->with('product','category')->first();
        
        if(!empty($catall)){
          $stock=\App\Models\StockModel::where('product_id',$qrdata->product_id)->where('category_id',$qrdata->category_id)->where('status',1)->get();      
        }else{
          $stock=\App\Models\StockModel::where('id',$qrstockid->stock_id)->with('product','attr','category')->get();
        }

      if($stock->count()>0)
      {   
          foreach($stock as $pd){

            $html.='<tr id="'.$pd->id.'" class="variantsRow">
                    <td class="itemId">'.$pd->id.'
                    <input type="hidden" name="stTaxRate" class="stTaxRate" value="'.$pd->tax_rate.'">
                    </td>
                    <td class="itemAtr">'.$pd->attr?->name.'</td>
                    <td class="itemAstock">'.$pd->current_stock.'</td>
                    <td>'.$pd->sale_price.'</td>
                    <td width="50"><input type="number" name="AdQty[]" class="inpt AdQty"></td>
                    <td width="50" class="hide"><input type="number" name="AdSprice[]" class="inpt AdSprice" value="'.$pd->sale_price.'" disabled></td>
                    <td width="50"><input type="number" name="AdNet[]" class="inpt AdNet" disabled></td>
                    <td class="itmStatus"></td>;
            </tr>';
 
          }
          $html.='<tr>
                    <td colspan="100%" class="text-center p-2 AdBtn">
                      <span class="btn btn-success rounded btn-sm hide" id="addItemBtn" onclick="addItemToBill()">
                      Add Items
                      </span>
                    </td>
                  </tr>';  
       }
      }
      $a['data']=$html;
      $a['prodinfo']=$qrdata;
       return $a;
    }   


    /*======Search Product Variant ===== */
    /*
    public function categoryAssocProduct($catid){
      $prod=StockModel::where('category_id',$catid)->with('attr')->where('status','1')->where('isOffer','1')->get();
    }

    public function searchProdVariants($prodid, $catid,$billtype=null,$account=null,){
      $prod=StockModel::where('product_id',$prodid)->where('category_id',$catid)->with('attr')->get();
      $html='<tr><td colspan="100%">--- No data found---</td></tr>'; 
      $accountData='';
      $effectivePrice='';

      if($prod->count()>0)
      {   
        $html='';
        $sof['offerIndicator']=null;

          foreach($prod as $pd){
            if(!is_null($account)){
                $accountData=\App\Models\Account::where('id',$account)->first();
            }

            if(in_array($billType,['sale','sale-order','sale-return','inquery','salecart']))
            {
                //======wholesale Price=======
                if(!is_null($accountData) && $accountData->priceGroup=='2' ){
                    $effectivePrice=$pd->wholesale_price;
                  }else{
                    $effectivePrice=$pd->sale_price;
                  }
            }elseif(in_array($billType,['purchase','purchase-order','purchase-return,purchasecart'])){
               //======wholesale Price=======
                if(!is_null($accountData) && $accountData->priceGroup=='2' ){
                    $effectivePrice=$pd->purchase_price;
                }else{
                  $effectivePrice=$pd->purchase_price;
                }
            }else{
              $effectivePrice=$pd->purchase_price;
            }

            //====Special Offer=====
            if(in_array($billType,['sale','sale-order','sale-return','inquery','salecart']))
            {
              $sof=productOffer($pd->id,$account=null);
              if($pd->isOffer==1){
                $effectivePrice=$pd->offerSalePrice;
              }
            }

            
            $html.='<tr id="'.$pd->id.'" class="variantsRow">
                    <td class="itemId">'.$pd->id.' '.$sof['offerIndicator'].'
                    <input type="hidden" name="stTaxRate" class="stTaxRate" value="'.$pd->tax_rate.'">
                    <input type="hidden" name="sofRate" value="'.$sof['offerRate'].'">
                    <input type="hidden" name="sofId" value="'.$sof['offerId'].'">
                    <input type="hidden" name="sofType" value="'.$sof['offerType'].'">
                    </td>
                    <td class="itemAtr">'.$pd->attr?->name.'</td>
                    <td class="itemAstock">'.$pd->current_stock.'</td>
                    <td>'.$saleprice.'</td>
                    <td width="50"><input type="number" name="AdQty[]" class="inpt AdQty"></td>
                    <td width="50" class="hide"><input type="number" name="AdSprice[]" class="inpt AdSprice" value="'.$pd->sale_price.'" disabled></td>
                    <td width="50"><input type="number" name="AdNet[]" class="inpt AdNet" disabled></td>
                    <td class="itmStatus"></td>;
                    </tr>';

          }
          $html.='<tr>
                    <td colspan="100%" class="text-center p-2 AdBtn"><span class="btn btn-success rounded btn-sm hide" id="addItemBtn" onclick="addItemToBill()">Add Items</span></td>
                  </tr>';  
       }
       return $html;
    } 
*/

/**
 * Search Product Variants.
 *
 * @param int $prodId Product ID
 * @param int $catId Category ID
 * @param string|null $billType Bill type (e.g., sale, purchase)
 * @param int|null $account Account ID
 * @return string HTML table rows
 */

public function searchProdVariants(int $prodId, int $catId, ?string $billType = null, ?int $account = null, ?string $billMode = null )
{
    /* 
      Request : prodid | catid | billtype (sale,purchase) | accountid | billMode= offline/online
    */
    $query = BranchStocks::whereHas('stock', function ($q) use ($prodId, $catId){
                            $q->where('product_id', $prodId)
                              ->where('category_id', $catId);
                        })
                        ->with('stock.attr','stock.psod','stock.ppod');
      $products = $query->get();

    // Default HTML when no data is found
    if ($products->isEmpty()){
        return '<tr><td colspan="100%">--- No data found ---</td></tr>';
    }

    $html = '';
    $accountData = $account ? \App\Models\Account::find($account) : null;
    foreach ($products as $product)
    {
        // Determine effective price based on account and bill type
        $effectivePrice = $this->getEffectivePrice($product, $billType, $accountData,$priceGroup=null);
       //$effectivePrice=$product->sale_price;
        // Handle special offers
        $offer = $this->getProductOffer($product->id, $account, $billType);
        if ($offer['isOffer'] && $billType && in_array($billType, ['sale', 'sale-order', 'sale-return', 'inquiry', 'salecart'])) {
              $effectivePrice = $offer['offerSalePrice'];
        }

        //===== counter Stock==
          $psod=$product->stock->psod ? $product->stock->psod->sum('sQty'):0;
          $ppod=$product->stock->ppod ? $product->stock->ppod->sum('sQty'):0;
          $cstock = $product->current_stock - $psod;
          $rstock = $product->current_stock - $psod + $ppod;
          

          //====For Purchase Only=====
          if($billType=='purchase' && $accountData){
            $acPendingOdr=\App\Models\PurchaseOrderDetail::where('account_id',$accountData->id)
                          ->where('stock_id',$product->stock_id)
                          ->whereIn('status',[1,2])
                          ->where('sQty','>',0)
                          ->sum('sQty');
          }

        // Generate HTML row for each product variant
        $html .= '<tr id="' . $product->stock_id . '" class="variantsRow">
                    <td class=""><span class="offer">'.($offer['offerIndicator'] ?? '') . '</span>
                        <input type="hidden"  class="itemId" name="stockid" value="'.$product->stock_id.'">
                        <input type="hidden" name="stTaxRate" class="stTaxRate" value="' . $product->tax_rate . '">
                        <input type="hidden" name="UserId" value="'.auth()->user()->id.'">
                        <input type="hidden" name="BranchId" value="'.auth()->user()->branch_id.'">
                        <input type="hidden" name="SofRate" value="' . $offer['offerRate'] . '">
                        <input type="hidden" name="SofId" value="' . $offer['offerId'] . '">
                        <input type="hidden" name="SofType" value="' . $offer['offerType'] . '">
                    </td>';

        $html .='<td class="itemAtr">' . ($product->stock->size_color_name ?? 'N/A') . '</td>
                    <td class="itemAstock" title="current Stock">' . $product->current_stock . '</td>
                    <td class="itemCstock" title="Counter Stock :(stock)'.$product->current_stock.'  - (so)'.$psod.'='.($product->current_stock - $psod).'">'.($product->current_stock - $psod).'</td>
                    <td class="itemCstock" title="Request Stock :(stock)'.$product->current_stock.' + (PO) '.$ppod.'  - (so)'.$psod.' ='.($product->current_stock + $ppod - $psod).'">'.($product->current_stock + $ppod - $psod).'</td>
                    <td>'. number_format($effectivePrice,2).'</td>
                    <td class="ofr">'.$offer['offerRate'].'</td>';

        if($billType=='purchase' && $accountData){
          if($acPendingOdr<1){
            $ovrOrdCheck='checked';
            $odrColor='';
          }else{
            $ovrOrdCheck='';
            $odrColor='badge bg-danger';
          }
          $html .='<td>
                    <span class="acPpod '.$odrColor.'">'.$acPendingOdr .'</span>
                    <input type="checkbox" name="overOrderItem" class="ovrOdrMark" value="'.$product->stock_id.'" '.$ovrOrdCheck.'>
                  </td>';
        }
       
        $html .='<td width="50"><input type="number" name="AdQty[]" class="inpt AdQty"></td>';
        
        if($billType=='stock-adjustment'){
          $html.='<td><input type="number" name="AdOutQty[]" class="inpt AdOutQty"></td>';
        }
        $html .='<td width="50" class="hide"><input type="number" name="AdSprice[]" class="inpt AdSprice" value="' . $effectivePrice . '" disabled></td>
                    <td width="50"><input type="number" name="AdNet[]" class="inpt AdNet" disabled></td>
                    <td class="itmStatus"></td>
                </tr>';
    }

    // Add "Add Items" button row
    $html .= '<tr>
                <td colspan="100%" class="text-center p-2 AdBtn">
                    <button class="btn btn-success rounded btn-sm" id="addItemBtn" onclick="addItemToBill(event)">Add Items</button>
                </td>
              </tr>';

    // return $offer;
    return $html;
}

    /**
     * Get the effective price based on bill type and account data.
     */
    private function getEffectivePrice($product, ?string $billType, $account,$priceGroup=null)
    {
        $isWholesale = $account?->priceGroup === '2';
        if (in_array($billType, ['sale', 'sale-order', 'sale-return', 'inquiry', 'salecart'])) {
            if($isWholesale && $product->wholesale_price>0){
              return $product->wholesale_price;
            }else{
              return $product->sale_price;
            }
            return $isWholesale ? $product->wholesale_price : $product->sale_price;
        }

        if (in_array($billType, ['purchase', 'purchase-order', 'purchase-return', 'purchasecart'])) {
            return $isWholesale ? $product->purchase_price : $product->purchase_price;
        }

        return $product->purchase_price;
    }

    /**
     * Get product offer details.
    */
    private function getProductOffer(int $productId, ?int $accountId, ?string $billType): array
    {
        // Only process offers for specific bill types
        if (!in_array($billType, ['sale', 'sale-order', 'sale-return', 'inquiry', 'salecart']))
        {
              // Assuming `productOffer` is a helper function
              return productOffer($productId, $accountId) ?? [
                'isOffer' => false,
                'offerIndicator' => null,
                'offerRate' => null,
                'offerId' => null,
                'offerType' => null,
                'offerSalePrice' => null,
              ];
        }

        // Assuming `productOffer` is a helper function
        return productOffer($productId, $accountId) ?? [
            'isOffer' => false,
            'offerIndicator' => null,
            'offerRate' => null,
            'offerId' => null,
            'offerType' => null,
            'offerSalePrice' => null,
        ];
    }

    /*====== Product Associated with Category====== */
    public function categoryAssocProduct($catid)
    {
        $stock=StockModel::with('product')
                ->where('category_id',$catid)
                ->orderBy(Product::select('name')
                          ->whereColumn('id', 'tbl_products_stock.product_id'))
                ->groupBy('product_id')->get();

        $html='';
        $status=0;
    
        if($stock->count())
        {
          foreach($stock as $st){
            $html.='<div class="col-md-4 prodRow">
              <input type="checkbox" class="prodCheck" value="'.$st->product_id.'" prodname="'.$st?->product?->name.'" id="cpid'.$st->product_id.'"">
              <label for="cpid'.$st->product_id.'" role="button">'.$st?->product?->name.'</label>
            </div>';
            $status=1;
          }
        }else{
            $html.='<div class="col-md-12">-- No product found associated with the category</div>';
        }
        $a['status']=$status;
        $a['data']=$html;
        
      return $a;
    }

    //=====Generate Qr Code whose qrcode is note generated ========
    public function GenerateQrString(){
      generateQRCodesForStock();
      return 'Qr created successfully';

    }

    public function multiAction($action,$id)
    {
      $status='false';
      $code='0';
      $msg='';
      $tblD='';
      if($action=='stock_enable'){
        DB::table('tbl_products_stock')->where('id',$id)->update(array('status'=>'1',));
        $msg='Data updated successfully';
        $status='true';
        $code=100;
      }else if($action=='stock_disable'){
        DB::table('tbl_products_stock')->where('id',$id)->update(array('status'=>'0',));
        $msg='Data updated successfully';
        $status='true';
        $code=100;
      }else if($action=='purchase-order-itemRemove'){$tblD='tbl_purchase_order_detail';}
      else if($action=='purchase-itemRemove' || $action=='purchase-return-itemRemove'){$tblD='tbl_purchase_detail';}
      else if($action=='sale-order-itemRemove'){$tblD='tbl_sale_order_detail';}
      else if($action=='sale-itemRemove' || $action=='sale-return-itemRemove'){$tblD='tbl_sale_detail';}
      else if($action=='sale-inquery-itemRemove'){$tblD='tbl_sale_inquery_detail';}
      else if($action=='sale-new-inquery-itemRemove'){$tblD='tbl_new_inquery_detail';}
      else{}
      if($tblD!=''){

          if($action=='purchase-order-itemRemove' || $action=='sale-order-itemRemove' || $action=='sale-inquery-itemRemove' || $action=='sale-new-inquery-itemRemove')
          {
                DB::table($tblD)->where('id',$id)->delete();
                $msg=$action.' updated successfully '.$tblD;
                $status='true';
                $code=101;
          }else{
                DB::table($tblD)->where('id',$id)->update(array('status'=>'3'));
                $msg=$action.' updated successfully '.$tblD;
                $status='true';
                $code=101;
          }
      }
  
      $data['code']=$code;
      $data['message']=$msg;
      $data['status']=$status;
      return Response::json($data);
    }


    /* ======Show Bill data ===== */
    public function showBillData($reqType,$id){

      if($reqType=='sales-inquiry'){
        $tbl="tbl_sale_inquery";
        $tblDt="tbl_sale_inquery_detail";
      }
      
      $inq = \App\Models\Inquery::where('id',$id)->with('account.citydata','account.statedata','details.stock')->first();    
		  if($inq){
        $i=1;
        $html='<table class="table table-sm table-striped border">
                <thead class="table-info">
                  <tr>
                    <th>S.no<th>
                    <th>Product Name & Cat<th>
                    <th>size</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Total</th>
                    <th>#</th>
                  </tr>
                <thead>
                <tbody>
                ';
        foreach($inq->details as $inq){
          $html.='<tr>
                    <td>'.$i.'<td>
                    <td>'.$inq?->stock?->product?->name.'<small>( '.$inq?->stock?->category?->name.' )</small><td>
                    <td>'.$inq?->stock?->attr?->name.'</td>
                    <td>'.$inq->sQty.'</td>
                    <td>'.$inq->sRate.'</td>
                    <td>'.$inq->sQty *$inq->sRate .'</td>
                    <td>#</td>
                  </tr>';

                  $i++;
        }
        $html.='</tbody></table>';
      }
      return $html;
    }


    public function StoreNotes(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'billid'   => 'required|integer',
            'noteType' => 'required|string',
            'datetime' => 'required|date',
            'note'     => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Save the note (example code - replace with your database logic)
        $note=new Notes();
        $note->bill_id   = $request->billid;
        $note->bill_type = $request->noteType;
        $note->datetime  = $request->datetime;
        $note->user_id   = Auth::user()->id;
        $note->branch_id = Auth::user()->branch_id;
        $note->note      = $request->note;    
       
        if ($note->save()) {
            return response()->json([
                'success' => true,
                'note'    => [
                    'datetime' => date('d-M-y H:i',strtotime($note->created_at)),
                    'inqdate' => $note->datetime,
                    'note'     => $note->note,
                    'username'     => Auth::user()->name,
                ],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save the note.',
            ], 500);
        }
    
        return response()->json([
          'success' => true,
          'note'    => [
              'datetime' => '$note->datetime',
              'note'     => '$note->note',
              'user'     => 'Auth::user()->name',
          ],
      ]);
    }
    
    public function getNotes($billtype,$billid,$viewtype=null)
    {
        $note=Notes::where('bill_type',$billtype)->where('bill_id',$billid)
                  ->with('user')
                  ->orderBy('id','desc')
                  ->get(); 
        $html=''; 
        $status=false;               
        if($note){
          $status=true;
          if($viewtype=='html'){
            foreach($note as $note){
              $html.= '<div class="">
                        <em>'.date('d-M-y H:i',strtotime($note->created_at)).' | 
                        <i class="fa fa-user"> '.$note?->user?->name.'</i> </em> 
                        <div class="noteitem">
                            <span class="inqdate">'.date('d-M-y H:i',strtotime($note->datetime)).' </span>
                            <span class="inqnote">'.$note->note.'</span>
                        </div>
                      </div>';
            }
          
          }
        }else{
          $html.='<div class=""><em>.. No Data found..</em></div>';  
        }
        $a['status']=$status;
        $a['note']=$note;
        $a['html']=$html;

        return $a;
    }

    //======GET City By State ID========
    public function getCities($stateId) {
      $cities = \App\Models\City::where('state_id', $stateId)->orderBy('name','asc')->get(['id', 'name']);
      return response()->json($cities);
    }
    

    public function productImage($proid, $catid = null, $pairid = null)
    {
        // Fetch product with gallery and related category
        $product = Product::with([
            'gallery' => fn($query) => $query->when($catid, fn($q) => $q->where('category_id', $catid))
                ->when($pairid, fn($q) => $q->where('pair_id', $pairid))
                ->with(['category' => fn($q) => $q->select('id', 'name')])
                ->select('id', 'product_id', 'category_id', 'pair_id', 'image')
        ])
        ->where('id', $proid)
        ->select('id', 'name', 'image')
        ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found','imgtype'=>'0'], 404);
        }

        $gallery = $product->gallery->first();

        // Case 1: Gallery exists with product_id, category_id, and pair_id
        if ($gallery && $catid && $pairid) {
            return response()->json([
                'imgtype' => '3',
                'image' => $gallery->image,
                'product_name' => $product->name,
                'category_name' => $gallery->category->name ?? null,
                'pair_name' => $gallery->pair ? $gallery->pair->name : null // Adjust based on Pair model
            ]);
        }

        // Case 2: Gallery exists with product_id and category_id
        if ($gallery && $catid) {
            return response()->json([
                'imgtype' => '2',
                'image' => $gallery->image,
                'product_name' => $product->name,
                'category_name' => $gallery->category->name ?? null
            ]);
        }

        // Case 3: No matching gallery, return product data
        return response()->json([
            'imgtype' => '1',
            'image' => $product->image,
            'product_name' => $product->name
        ]);
    }

    public function productStockStatus($prod,$cat){
          $query = BranchStocks::whereHas('stock', function ($q) use ($prod, $cat){
                                $q->where('product_id', $prod)
                                  ->where('category_id', $cat);
                            })
                            ->with('stock.attr','stock.psod','stock.ppod');
          $products = $query->get();

        // Default HTML when no data is found
        if ($products->isEmpty()){
            return '<tr><td colspan="100%">--- No data found ---</td></tr>';
        }
        $html='';
        $i=1;
        foreach($products as $prod){
            $psod=$prod->psod ? $prod->psod->sum('sQty'):'-';
            $ppod=$prod->ppod ? $prod->ppod->sum('sQty'):'-';
            $cstock = $prod->current_stock - $psod;
            $rstock = $prod->current_stock - $psod + $ppod;

            $html.= '<tr>
                        <td>'.$i.'</td>
                        <td>'.$prod->stock->color_name.'</td>
                        <td>'.$prod->current_stock.'</td>
                        <td>'.$psod.'</td>
                        <td>'.$ppod.'</td>
                        <td>'.$rstock.'</td>
                      </tr>';
                      $i++;
        }
        return $html;

    }

    public function getUnpaidBills($accountId)
    {
        $bills = DB::table('bills')
            ->where('party_id', $accountId)
            ->where('pending_amount', '>', 0)
            ->select('id', 'bill_no', 'bill_date', 'bill_type', 'total_amount', 'pending_amount')
            ->get();

        return response()->json($bills);
    }
    
}


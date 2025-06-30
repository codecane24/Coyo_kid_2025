<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\WebController;
use Yajra\DataTables\Facades\DataTables;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\QrModel;
use App\Models\Product;
use App\Models\StockModel;
use Auth;
use Response;

class QRCodeController extends WebController
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index($qrstring)
    {
        return QrCode::format('svg')->size(300)->generate($qrstring);
    }

    public function qrListing(Request $rd)
    {
        
        $qrcode = $rd->qrcode ?? null;
        $bsid = $rd->bsid ?? 0; //branch stockid
        $stockid = $rd->stockid ?? 0; //branch stockid
        $fromqrid =  $rd->fromqrid ?? 0;
        $toqrid = $rd->toqrid ?? 0;
        $filter=false;

        $a['title']='Product Qr Code';
        $a['breadcrumb']=breadcrumb([
            'Qr Code' => route('admin.product-qr-list')
        ]);
        $qrdata = QrModel::with('brstock.stock','brstock.stock.product','brstock.stock.category')
                        ->where('branch_id',Auth::user()->branch_id);
                               // ->groupBy('stock_id')
        if($qrcode){
            $qrdata->where('qrcode',$qrcode);
            $filter=true;
        }

        if($bsid > 0){
            $qrdata->where('bsid',$bsid);
            $filter=true;
        }

        if($stockid > 0){
            $qrdata->where('stock_id',$stockid);
            $filter=true;
        }

        if($fromqrid >0 &&  $toqrid){
            $qrdata->where('id','>=',$fromqrid);
            $qrdata->where('id','<=',$toqrid);
            $filter=true;
        }
        if($filter== false){
            $qrdata->where('id','=','a');
        }

        $a['qrdata'] =$qrdata->get();
        $a['qrcode']= $qrcode;
        $a['bsid'] = $bsid;
        $a['stockid'] = $stockid;
        $a['fromqrid'] = $fromqrid;
        $a['toqrid'] = $toqrid;

        return view('admin.qrcode.index')->with($a);
    }

    public function branchOpeningStockQrGenerate(Request $r)
    {
        $a['status']=false;
        $stockid= $r->stockid;
        if($stockid >0){

            $st = \App\Models\BranchStocks::with('stock')
                ->where('osqr_generated', 0)
                ->where('current_stock', '>', 0)
                ->where('stock_id', $stockid)
                ->first();

            $qty = $st ? $st->current_stock : 0;
            
            $qdata=[
                'reqtype' => 'opening_stock',
                'stockid'=> $stockid,
                'qty' =>$qty
            ];
           return generateQRCodesForStock($qdata);
        
        }
        return $a;
    }
    
    public function printQr($bsid=null,$fromid=null,$toid=null){
        $qrs=QrModel::where('status',1);
        if($stockid>1){
            $qrs->where('stock_id',$stockid);
        }

        if($fromid>=1 && $toid>=1){
            $qrs->where('id','>=',$fromid)
                ->where('id','<=',$toid);
        }
        $qrcodes=$qrs->get();


    }

    public function getQrinfo($qr,$reqtype=null){
        $d= \App\Models\QrModel::where('qrcode',$qr)->with('brstock.stock')->first();
        $a['status']=false;
        if($d){
            $a['status']=true;
            $a['qrinfo']=$d;   
        }
        return Response::json($a);
    }

}


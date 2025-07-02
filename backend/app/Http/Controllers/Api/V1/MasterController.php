<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\ClassesMaster;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    /**
     * Display a listing of the classes.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function classmasterList()
    {
        $list = ClassesMaster::select('id','code','name','status')->get();
        return response()->json($list);
    }

   
}
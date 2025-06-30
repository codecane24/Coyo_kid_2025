<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Sections;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of the Sections.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Sections = Sections::select('id','name','status')->get();
        return response()->json($Sections);
    }

    /**
     * Display the specified class.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $class = Sections::select('id','name','status')->findOrFail($id);
        return response()->json($class);
    }
}
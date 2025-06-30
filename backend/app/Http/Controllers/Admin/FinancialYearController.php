<?php

namespace App\Http\Controllers\Admin;
use App\models\FinancialYear;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\WebController;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use Brian2694\Toastr\Facades\Toastr;

class FinancialYearController extends WebController
{

    public $fyObj,$fyTableName;
    public function __construct()
    {
        $this->fyObj = new FinancialYear();
        $this->fyTableName = (new FinancialYear)->getTable();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!hasPermission('fyear_view')) {
            return redirect()->back();
        }
        $fyear = $this->fyObj::with('parentyear')->latest()->get();
        return view('admin.fyear.index',
                [
                    'title' => 'Financial Year',
                    'breadcrumb' => breadcrumb([
                        'Financial Year' => route('admin.fyear.index'),
                    ]),
                    'fyear' => $fyear,
                ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function create()
    {
        if (!hasPermission('fyear_create')) {
            return redirect()->back();
        }
        $fyear=$this->fyObj;
        return view('admin.fyear.create', [
            'title' => "Add Financial Year",
            'breadcrumb' => breadcrumb([
                'FYear' => route('admin.fyear.index')
            ]),
            'fyear' => $fyear,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $rd
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        
        $request->validate([
            'name' => 'required|unique:financial_years',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Deactivate previous active year if necessary
        // if ($request->status) {
        //     FinancialYear::where('status', true)->update(['status' => false]);
        // }

        FinancialYear::create([
            'code' => getNewSerialNo('fyear_code'),
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status ?? false,
            'description' => $request->input('description'),

        ]);
        increaseSerialNo('fyear_code');
        return redirect()->route('admin.fyear.index')->with('success', 'Financial year created.');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!hasPermission('fyear_edit')) {
            return redirect()->back();
        }
        $a['title'] ='Financial Year Update';
        $a['breadcrumb']=breadcrumb([
            'fyear' => route('admin.fyear.index'),
            'edit' => route('admin.fyear.edit', $id),
        ]);

        $a['data'] = $this->fyObj->orderBy('name')->where('id', $id)->first();
        return view('admin.fyear.create')->with($a);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $rd
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

            $inputs = $request->except('_token');
           // return $request;
            // Validation rules
            $request->validate([
                'name' => 'required|unique:financial_years,name,' . $id,
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);
    

            // Find the fyear entry
            $fyear = $this->fyObj::findOrFail($id); // Fetch the fyear by ID or fail

            
            $fyear->name = $request->input('name');
            $fyear->description = $request->input('description');
            $fyear->start_date = $request->input('start_date');
            $fyear->end_date = $request->input('end_date');
            
            // Save the updated fyear
            if ($fyear->save()) {
                success_session('fyear updated successfully');
                Toastr::success('fyear successfully updated', 'Success');
            } else {
                Toastr::error('An error occurred while updating the fyear', 'Error');
                success_session('fyear not updated');
            }

            return redirect()->route('admin.fyear.index');

    }



    public function changeFinancialYear(Request $request,$id)
    {
        // Validate the input
        $request->validate([
           // 'fyid' => 'required|exists:'.$this->fyTableName.',id',
        ]);
        $fydata=$this->fyObj::find($id);
        if($fydata)
        session(['fyear' => $fydata]);
        if (auth()->check()) {
            auth()->user()->setAttribute('fyear', $fydata);
        }
        success_session('Financial year Changed successfully');
        return redirect()->back()->with('success', 'Financial year updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function statusUpdate($id){
        FinancialYear::where('status', true)->update(['status' => 0]);
        $fydata=$this->fyObj::find($id);
        $fydata->status=1;
        if($fydata->save()){
            success_session('Financial year Status updated successfully');
        }
        return redirect()->back()->with('success', 'Financial year status updated successfully.');
    }

    public function listing(Request $rd)
    {
        $data = $this->fyObj::latest()->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('name_parent',function ($row){
                return $row->code.' : '.$row->name.' <br><small>('.$row->parentyear_name.')</small>';
            })
            ->addColumn('dateRange',function ($row){
                $date='<span class="badge bg-info">'.myDateFormat($row->start_date).'</span>';
                $date.=' To <span class="badge bg-danger">'.myDateFormat($row->end_date).'</span>';
                return $date;
            })
            ->addColumn('status',function ($row){
                if($row->status==1){
                    $status='<span class="badge bg-success">Active</span>';
                }else{
                    
                    $status='<a href="'.route('admin.fyear.status_update', $row->id).'"><span class="badge bg-danger">Inactive</span></a>'; 
                }
               return $status;
            })
            ->addColumn('statusd', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'status' => route('admin.fyear.status_update', $row->id),
                    ],
                    'checked' => ($row->status == '1') ? 'checked' : ''
                ];
                return  $this->generate_switch($param);
            })
            ->addColumn('action', function ($row) {
                return '<div class="btn-group cstbtn">
                            <button type="button" class="btn btn-sm btn-outline-primary">
                                <a href="'.route('admin.fyear.edit' , $row->id).'"><i class="fa fa-edit"></i></a>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
                                <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="'.route('admin.fyear.destroy' , $row->id).'"><i class="fa fa-image"></i> Delete</a></li>
                                <li class="divider"></li>

                            </ul>
                        </div>';
            })
            ->rawColumns(["name_parent","dateRange","status", "action",'statusd',"points"])
            ->make(true);
    }


    //=======Financial Year Data import  ========
    public function importPage(Request $request,$id)
    {
       $data = $this->fyObj::find($id);
        return view('admin.fyear.import', [
            'title' => 'Import Financial Year Data' ,
            'breadcrumb' => breadcrumb([
                'Financial Year' => route('admin.fyear.index'),
                'Import' => route('admin.fyear.dataimport',[$id]),
            ]),
            'data' => $data,
        ]);
       
        
    }

}

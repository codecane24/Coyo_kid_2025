<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transporter;
use App\Http\Controllers\WebController;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class TransporterController extends WebController
{

    public $tranportObj;
    public function __construct()
    {
        $this->tranportObj = new Transporter();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.transporter.index', [
            'title' => 'Transporter',
            'breadcrumb' => breadcrumb([
                'Transporter' => route('admin.transporters.index'),
            ]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.transporter.create', [
            'title' => "Add Transporter",
            'breadcrumb' => breadcrumb([
                'Transporter' => route('admin.transporters.index')
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $rd)
    {
        $rd->validate([
            'name' => ['required', 'max:255'],
            'address'=>['required']
        ]);
        $return_data = $rd->all();  
        //$Transporter = $this->tranportObj->saveTransporter($return_data);
        $trns=$this->tranportObj;
        $trns->name=$rd->name;
        $trns->contact_person=$rd->contact_person;
        $trns->phone_no=$rd->phone_no;
        $trns->phone_no2=$rd->phone_no2;
        $trns->email=$rd->email;
        $trns->address=$rd->address;
        $trns->vehicle_number=$rd->vehicle_number;
        $trns->gst_number=$rd->gst_number;
        $trns->license_number=$rd->license_number;
        
        if ($trns->save()) {
            success_session('Transporter created successfully');
        } else {
            error_session('Transporter not created');
        }
        return redirect()->route('admin.transporters.index');
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

    //status 
    public function status_update($id = 0)
    {
        $data = ['status' => 0, 'message' => 'Transporter Not Found'];
        $find = $this->tranportObj::find($id);
        if ($find) {
            $find->update(['status' => ($find->status == "inactive") ? "active" : "inactive"]);
            $data['status'] = 1;
            $data['message'] = 'Transporter status updated';
            success_session('Transporter Status Updated successfully');
        }
        return $data;
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        $data = $this->tranportObj->find($id);
        if (isset($data) && !empty($data))
        {
            return view('admin.transporter.create', [
                'title' => 'Transporter Update',
                'breadcrumb' => breadcrumb([
                    'Transporter' => route('admin.transporters.index'),
                    'edit' => route('admin.transporters.edit', $id),
                ]),
            ])->with(compact('data'));
        }
        return redirect()->route('admin.transporters.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $rd, string $id)
    {
        $rd->validate([
            'name' => ['required','max:255'],
            'address' => ['required']
        ]);
        $trns = $this->tranportObj->find($id);
        $trns->name=$rd->name;
        $trns->contact_person=$rd->contact_person;
        $trns->phone_no=$rd->phone_no;
        $trns->phone_no2=$rd->phone_no2;
        $trns->email=$rd->email;
        $trns->address=$rd->address;
        $trns->vehicle_number=$rd->vehicle_number;
        $trns->gst_number=$rd->gst_number;
        $trns->license_number=$rd->license_number;

        if($trns->save()){
            success_session('Transporter updated successfully');
        }
        else{
            error_session('Transporter not found');
        }

        return redirect()->route('admin.transporters.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = $this->tranportObj::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('Transporter deleted successfully');
        } else {
            error_session('Transporter not found');
        }
        return redirect()->route('admin.transporter.index');
    }


    public function listing(Request $request)
    {
        $data = $this->tranportObj::all();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('phoneno', function ($row) {
                return $row->phone_no.'<br>'.$row->phone_no2.'<br>'.$row->email;
            })
            ->addColumn('action', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'delete' => route('admin.transporters.destroy', $row->id),
                        'edit' => route('admin.transporters.edit', $row->id),
                        // 'view' => route('admin.news.show', $row->id),
                    ]
                ];
                return $this->generate_actions_buttons($param);
            })
            ->rawColumns(["status","phoneno", "action"])
            ->make(true);
    }
}

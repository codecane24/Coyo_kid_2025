<?php

namespace App\Http\Controllers\Admin;
use App\models\Gst;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\WebController;
use Yajra\DataTables\Facades\DataTables;

class GstController extends WebController
{

    public $gstObj;
    public function __construct()
    {
        $this->gstObj = new Gst();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!hasPermission('gst_view')) {
            return redirect()->back();
        }
       // return $data = $this->gstObj::with('categories')->withCount('categories')->latest()->get();
        return view('admin.gst.index', [
            'title' => 'Gst',
            'breadcrumb' => breadcrumb([
                'Gst' => route('admin.gst.index'),
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
        if (!hasPermission('gst_create')) {
            return redirect()->back();
        }
        return view('admin.gst.create', [
            'title' => "Add Gst",
            'breadcrumb' => breadcrumb([
                'Gst' => route('admin.gst.index')
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $rd
     * @return \Illuminate\Http\Response
     */
    public function store(Request $rd)
    {
        $rd->validate([
            'name'=>['required'],
        ]);
        $gst = $this->gstObj;
        $gst->name = $rd->name;
        $gst->rate = $rd->rate;
        $gst->remark = $rd->description;
        if ($gst->save()) {
            success_session('Gst created successfully');
        } else {
            error_session('Gst not created');
        }
        return redirect()->route('admin.gst.index');
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
        if (!hasPermission('gst_edit')) {
            return redirect()->back();
        }
        $a['title'] ='Gst Update';
        $a['breadcrumb']=breadcrumb([
            'Gst' => route('admin.gst.index'),
            'edit' => route('admin.gst.edit', $id),
        ]);
        $a['data'] = $this->gstObj->orderBy('name')->where('id', $id)->first();
        return view('admin.gst.create')->with($a);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $rd
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $rd, $id)
    {
        $rd->validate([
            'name'=>['required'],
        ]);
        $gst=$this->gstObj->find($id);
        $gst->name = $rd->name;
        $gst->rate = $rd->rate;
        $gst->remark = $rd->description;
        if ($gst->save()) {
            success_session('Gst created successfully');
        } else {
            error_session('Gst not created');
        }
        return redirect()->back();
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

    public function listing(Request $rd)
    {
        $data = $this->gstObj::with('categories')->withCount('categories')->latest()->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'status' => route('admin.gst.status_update', $row->id),
                    ],
                    'checked' => ($row->status == 'active') ? 'checked' : ''
                ];
                return  $this->generate_switch($param);
            })
            ->addColumn('action2', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'delete' => route('admin.gst.destroy', $row->id),
                        'edit' => route('admin.gst.edit', $row->id),
                        // 'view' => route('admin.news.show', $row->id),
                    ]
                ];
                return $this->generate_actions_buttons($param);
            })
            ->addColumn('action', function ($row) {
                return '<div class="btn-group cstbtn">
                            <button type="button" class="btn btn-sm btn-outline-primary">
                                <a href="'.route('admin.gst.edit' , $row->id).'"><i class="fa fa-edit"></i></a>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
                                <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="'.route('admin.gst.destroy' , $row->id).'"><i class="fa fa-image"></i> Delete</a></li>
                                <li class="divider"></li>

                            </ul>
                        </div>';
            })
            ->rawColumns(["status", "action"])
            ->make(true);
    }
}

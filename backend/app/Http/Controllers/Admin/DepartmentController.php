<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\WebController;

class DepartmentController extends WebController
{

    public function index()
    {
        return view('admin.department.index', [
            'title' => 'Departments',
            'breadcrumb' => breadcrumb([
                'Department' => route('admin.department.index'),
            ]),
        ]);
    }

    public function listing()
    {
        $datatable_filter = datatable_filters();
        $offset = $datatable_filter['offset'];
        $search = $datatable_filter['search'];
        $return_data = array(
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        $main = Department::query();
        $return_data['recordsTotal'] = $main->count();
        if (!empty($search)) {
            $main->where(function ($query) use ($search) {
                $query->AdminSearch($search);
            });
        }
        $return_data['recordsFiltered'] = $main->count();
        $all_data = $main->orderBy($datatable_filter['sort'], $datatable_filter['order'])
            ->offset($offset)
            ->limit($datatable_filter['limit'])
            ->get();
        if (!empty($all_data)) {
            foreach ($all_data as $key => $value) {
                $param = [
                    'id' => $value->id,
                    'url' => [
                        // 'edit' => auth()->user()->hasPermissionTo('department_edit')
                        //     ? route('admin.user.edit', $value->id)
                        //     : null,
                        // 'delete' => auth()->user()->hasPermissionTo('department_delete')
                        //     ? route('admin.user.destroy', $value->id)
                        //     : null,
                        'delete' => route('admin.department.destroy', $value->id)
                    ],
                ];
                $return_data['data'][] = array(
                    'id' => $offset + $key + 1,
                    'name' => $value->name,
                    'description' => $value->description,
                    'action' => $this->generate_actions_buttons($param),
                );
            }
        }
        return $return_data;
    }

    public function create()
    {
        return view('admin.department.create');
    }


    public function save(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        // Save department
        Department::create([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        return response()->json(['message' => 'Department created successfully!'], 200);
    }

    public function destroy($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json(['error' => 'Department not found!'], 404);
        }

        $department->delete();

        return redirect()->route('admin.department.index')->with('success', 'Department deleted successfully!');
    }

}

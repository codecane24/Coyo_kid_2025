<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserActivityLog;
use App\User;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class LogsController extends Controller
{
    public function index()
    {

        if (!hasPermission('user_logs_view')) {
            return redirect()->back();
        }
        $logs = UserActivityLog::with('user')->latest()->paginate(10);
        $users = User::all();
        return view('admin.logs.index', compact('users'));
    }

    public function logsAjax(Request $request)
    {
        // Fetch logs, you may want to paginate them depending on your needs
        $query = UserActivityLog::query(); // Adjust with your model and logic

        if ($request->ajax()) {
            if ($request->date_filter === 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($request->date_filter === 'yesterday') {
                $query->whereDate('created_at', Carbon::yesterday());
            } elseif ($request->date_filter === 'custom' && $request->start_date && $request->end_date) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            }

            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            return DataTables::of($query)
                ->addColumn('user_name', function ($log) {
                    return $log->user->name ?? 'Guest';
                })
                ->make(true);
        }

        return view('admin.logs.index'); // Your view to show the logs
    }
}

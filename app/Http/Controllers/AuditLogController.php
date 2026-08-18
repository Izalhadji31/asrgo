<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $logs = AuditLog::with('user')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }
}

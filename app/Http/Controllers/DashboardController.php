<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role->name;

        $stats = [];

        if ($role === 'staff') {
            $mine = Submission::where('user_id', $user->id);
            $stats = [
                'total' => (clone $mine)->count(),
                'waiting' => (clone $mine)->whereIn('status', ['submitted', 'waiting_spv', 'waiting_manager', 'waiting_director', 'waiting_finance'])->count(),
                'paid' => (clone $mine)->where('status', 'paid')->count(),
                'rejected' => (clone $mine)->where('status', 'rejected')->count(),
            ];
        } elseif (in_array($role, ['spv', 'manager', 'direktur'], true)) {
            $statusMap = [
                'spv' => 'waiting_spv',
                'manager' => 'waiting_manager',
                'direktur' => 'waiting_director',
            ];
            $stats = [
                'pending' => Submission::where('status', $statusMap[$role])->count(),
                'approved_by_me' => \App\Models\Approval::where('approver_user_id', $user->id)->where('status', 'approved')->count(),
                'rejected_by_me' => \App\Models\Approval::where('approver_user_id', $user->id)->where('status', 'rejected')->count(),
            ];
        } elseif ($role === 'finance') {
            $stats = [
                'waiting_finance' => Submission::where('status', 'waiting_finance')->count(),
                'paid' => Submission::where('status', 'paid')->count(),
                'rejected' => Submission::where('status', 'rejected')->count(),
            ];
        }

        return view('dashboard', compact('stats', 'role'));
    }
}

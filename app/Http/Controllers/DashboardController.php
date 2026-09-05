<?php

namespace App\Http\Controllers;

use App\Services\DashboardCounts;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardCounts $counts): View
    {
        $user = $request->user();

        return view('dashboard', [
            'user' => $user,
            'counts' => $counts->forUser($user),
            'tasks' => $user->tasks()->latest('updated_at')->orderByDesc('id')->limit(5)->get(),
            'report' => $user->reports()->latest('id')->first(),
        ]);
    }
}

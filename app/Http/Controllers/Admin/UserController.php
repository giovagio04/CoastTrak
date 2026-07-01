<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_trails')) {
            $minTrails = $request->min_trails;
            $query->whereHas('digitalCredentials', function ($q) {
                $q->whereHas('outing', function ($oq) {
                    $oq->where('is_full_trail', true);
                });
            }, '>=', $minTrails);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        
        return view('admin.users.index', compact('users'));
    }
}

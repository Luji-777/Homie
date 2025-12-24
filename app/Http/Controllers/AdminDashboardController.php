<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
{
    // كل المستخدمين
    $allUsers = User::all();

    // المنتظرين الموافقة فقط
    $pendingUsers = User::where('is_verified', false)->get();

    // المقبولين فقط
    $approvedUsers = User::where('is_verified', true)->get();

    return view('dashboard', compact('allUsers', 'pendingUsers', 'approvedUsers'));
}

    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->is_verified = true;
        $user->save();

        return redirect('/admin')->with('success', 'تم الموافقة على المستخدم!');
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect('/admin')->with('success', 'تم حذف المستخدم!');
    }
}

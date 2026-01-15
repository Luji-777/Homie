<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Apartment;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // جلب كلمة البحث من الـ query parameter
        $search = $request->query('search');

        // بناء الـ query الأساسي
        $query = User::query();

        // إذا كان في بحث، نضيف شرط البحث بالاسم أو رقم الموبايل
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // جلب كل المستخدمين بعد تطبيق الفلتر (إن وجد)
        $allUsers = $query->get();

        // المنتظرين الموافقة (نفلترهم من النتايج المفلترة)
        $pendingUsers = $allUsers->where('is_verified', false);

        // المقبولين (اختياري، لكن موجود عندك في الـ view)
        $approvedUsers = $allUsers->where('is_verified', true);


        // كل الشقق
        $allApartments = Apartment::with('owner')->get();

        // الشقق المنتظرة الموافقة
        $pendingApartments = $allApartments->where('is_approved', false);

        // الشقق المقبولة
        $approvedApartments = $allApartments->where('is_approved', true);

        return view('dashboard', compact(
            'allUsers',
            'pendingUsers',
            'approvedUsers',
            'allApartments',
            'pendingApartments',
            'approvedApartments'
        ));
    }





    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->is_verified = true;
        $user->save();

        return redirect('/admin')->with('success', 'تم الموافقة على المستخدم!');
    }

    public function approveAllUsers()
    {
        User::where('is_verified', false)->update(['is_verified' => true]);
        return redirect()->back()->with('success', 'تمت الموافقة على جميع المستخدمين!');
    }




    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect('/admin')->with('success', 'تم حذف المستخدم!');
    }







    public function approveApartment($id)
    {
        $apartment = Apartment::findOrFail($id);
        $apartment->is_approved = true;
        $apartment->save();

        return redirect()->route('admin.dashboard')->with('success', 'تمت الموافقة على الشقة بنجاح!');
    }


    public function approveAllApartments()
    {
        // تحديث كل الشقق اللي is_approved = false
        Apartment::where('is_approved', false)->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'تمت الموافقة على جميع الشقق!');
    }



    public function deleteApartment($id)
    {
        $apartment = Apartment::findOrFail($id);
        $apartment->delete();

        return redirect()->route('admin.dashboard')->with('success', 'تم حذف الشقة بنجاح!');
    }











    public function apartmentDetails($id)
    {
        $apartment = Apartment::with(['owner', 'apartment_image', 'area.city'])->findOrFail($id);
        return view('admin.apartment-details', compact('apartment'));
    }
}

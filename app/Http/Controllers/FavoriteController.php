<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    //
    public function myFavorites( )
    {
        $user = FacadesAuth::user();

        $favorites = $user->favorites()->with('apartment')->get();

        return response()->json([
            'status' => 'success',
            'data' => $favorites
        ]);
    }

    public function addFavorite(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
        ]);

        $user = FacadesAuth::user();

        // تحقق إذا الشقة مضافة بالفعل للمفضلة
        if ($user->favorites()->where('apartment_id', $validated['apartment_id'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'الشقة مضافة بالفعل للمفضلة'
            ], 400);
        }

        // أضف الشقة للمفضلة
        $user->favorites()->create([
            'apartment_id' => $validated['apartment_id'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إضافة الشقة إلى المفضلة بنجاح'
        ]);
    }


    public function removeFavorite(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
        ]);

        $user = FacadesAuth::user();

        // تحقق إذا الشقة موجودة في المفضلة
        $favorite = $user->favorites()->where('apartment_id', $validated['apartment_id'])->first();

        if (!$favorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'الشقة غير موجودة في المفضلة'
            ], 400);
        }

        // احذف الشقة من المفضلة
        $favorite->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إزالة الشقة من المفضلة بنجاح'
        ]);
    }

    public function isFavorite(int $id)
    {
        $user = FacadesAuth::user();

        $isFavorite = $user->favorites()->where('apartment_id', $id)->exists();

        return response()->json([
            'status' => 'success',
            'is_favorite' => $isFavorite
        ]);
    }

    public function toggleFavorite(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
        ]);

        $user = FacadesAuth::user();

        $favorite = $user->favorites()->where('apartment_id', $validated['apartment_id'])->first();

        if ($favorite) {
            // إذا موجود، نحذفه
            $favorite->delete();
            $message = 'تمت إزالة الشقة من المفضلة بنجاح';
            $isFavorite = false;
        } else {
            // إذا مش موجود، نضيفه
            $user->favorites()->create([
                'apartment_id' => $validated['apartment_id'],
            ]);
            $message = 'تمت إضافة الشقة إلى المفضلة بنجاح';
            $isFavorite = true;
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'is_favorite' => $isFavorite
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    //
    public function myFavorites()
    {
        $user = FacadesAuth::user();

        $favorites = $user->favoriteApartments()
            ->with([
                'isCover',
                'owner.profile',
                'area.city',
                'review'
            ])
            ->get()
            ->map(function ($apartment) {

                $averageRating = $apartment->review->avg('rating');

                return [
                    'apartment' => [
                        'id'             => $apartment->id,
                        'title'          => $apartment->title,
                        'price'          => $apartment->price_per_month,
                        'cover_image'    => $apartment->isCover
                            ? asset('storage/' . $apartment->isCover->image_path)
                            : null,
                        'space'          => (float) $apartment->space,
                        'bedrooms'       => $apartment->bedrooms,
                        'bathrooms'      => $apartment->bathrooms,
                        'rooms'          => $apartment->rooms ?? null,
                        'address'        => $apartment->area->city->name . '، ' . $apartment->area->name,
                        'rental_type'    => $apartment->rent_type,
                        'apartment_type' => $apartment->type,
                        'average_rating'         => round($averageRating, 1),
                    ],
                    'owner' => [
                        'id'            => $apartment->owner->id,
                        'profile_image' => $apartment->owner->profile->profile_photo ?? null,
                        'full_name'     => $apartment->owner->profile->first_name . ' ' . $apartment->owner->profile->last_name,
                        'phone_number'  => $apartment->owner->phone_number,
                        'bio'           => null, // مؤقتاً
                    ]
                ];
            });

        return response()->json([
            'status' => 'success',
            // 'data' => $favorites->map(fn ($apt) => ApartmentController::format($apt))
            'data'   => $favorites
        ]);
    }



    public function addFavorite(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
        ]);

        $user = FacadesAuth::user();

        if ($user->favoriteApartments()->where('apartments.id', $validated['apartment_id'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'الشقة مضافة بالفعل للمفضلة'
            ], 400);
        }

        $user->favoriteApartments()->attach($validated['apartment_id']);

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

        if (! $user->favoriteApartments()->where('apartments.id', $validated['apartment_id'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'الشقة غير موجودة في المفضلة'
            ], 400);
        }

        $user->favoriteApartments()->detach($validated['apartment_id']);

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إزالة الشقة من المفضلة بنجاح'
        ]);
    }





    public static function isFavorite(int $id)
    {
        $user = FacadesAuth::user();

        $isFavorite = $user->favoriteApartments()->where('apartment_id', $id)->exists();

        return $isFavorite;
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

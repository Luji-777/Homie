<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Http\Request;
use App\Models\Apartment;
use App\Rules\NoOverlappingBooking;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    // 
    public function getBookedDates(int $apartmentId)
    {
        // التاريخ الحالي (بدون وقت)
        $startDate = Carbon::today();
        // بعد شهرين بالضبط (نهاية اليوم)
        $endDate = Carbon::today()->addMonths(2)->endOfDay();

        $bookings = Booking::where('apartment_id', $apartmentId)
            ->whereIn('status', ['pending', 'confirmed', 'owner_approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in', [$startDate, $endDate])
                    ->orWhereBetween('check_out', [$startDate, $endDate])
                    ->orWhereRaw('? BETWEEN check_in AND check_out', [$startDate])
                    ->orWhereRaw('? BETWEEN check_in AND check_out', [$endDate]);
            })
            ->select('check_in', 'check_out')
            ->get();

        $bookedDates = [];

        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in);
            $checkOut = Carbon::parse($booking->check_out);

            $current = $checkIn->copy();

            while ($current->lte($checkOut)) {
                if ($current->between($startDate, $endDate)) {
                    $bookedDates[] = $current->toDateString();
                }
                $current->addDay();
            }
        }

        // إزالة التكرار وترتيب الأيام
        $bookedDates = array_unique($bookedDates);
        sort($bookedDates);

        return response()->json([
            'apartment_id' => $apartmentId,
            'from'         => $startDate->toDateString(),
            'to'           => $endDate->toDateString(),
            'booked_dates' => array_values($bookedDates),
        ]);
    }


    public function store(Request $request, int $id)
    {
        $validated = $request->validate([
            'check_in'  => 'required|date|after_or_equal:today',
            'check_out' => [
                'required',
                'date',
                'after:check_in',
                // new NoOverlappingBooking(
                //     $id,
                //     $request->check_in
                // ),
            ],
        ]);



        try {
            $tempCheckIn = Carbon::parse($validated['check_in']);
            $tempCheckOut = Carbon::parse($validated['check_out']);

            $displayCheckIn = $tempCheckIn->format('j-n-Y');
            $displayCheckOut = $tempCheckOut->format('j-n-Y');

            $dbCheckIn = $tempCheckIn->format('Y-m-d');
            $dbCheckOut = $tempCheckOut->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                // 'message' => 'صيغة التاريخ غير صالحة'
                'message' => __('api.invalid_date_format')
            ], 422);
        }

        // فحص التداخل الصحيح
        $overlapData = ['check_in' => $dbCheckIn, 'check_out' => $dbCheckOut];
        $overlapValidator = Validator::make(
            ['check_out' => $dbCheckOut],
            [
                'check_out' => [
                    new NoOverlappingBooking(
                        $id,
                        $request->check_in
                    )
                ],
            ]
        );


        if ($overlapValidator->fails()) {
            return response()->json([
                // 'message' => 'التواريخ الجديدة متداخلة مع حجز آخر مؤكد'
                'message' => __('api.overlapping_dates')
            ], 422);
        }




        $apartment = Apartment::findOrFail($id);

        // حساب عدد الأيام
        $checkIn = Carbon::createFromFormat('d-m-Y', $request->check_in)->format('Y-m-d'); // شكل التاريخ
        $checkOut = Carbon::createFromFormat('d-m-Y', $request->check_out)->format('Y-m-d'); // شكل التاريخ

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $totalDays = $checkIn->diffInDays($checkOut) + 1;

        // حساب السعر (يومي أو شهري)
        if ($apartment->rent_type === 'month') {
            $fullMonths = floor($totalDays / 30);
            $remainingDays = $totalDays % 30;
            $totalPrice = ($fullMonths * $apartment->price) + ($remainingDays * ($apartment->price / 30));
        } else {
            $totalPrice = $totalDays * $apartment->price;
        }
        // if ($totalDays >= 30) {
        //     $fullMonths = floor($totalDays / 30);
        //     $remainingDays = $totalDays % 30;
        //     $totalPrice = ($fullMonths * $apartment->price_per_month) + ($remainingDays * $apartment->price_per_day);
        // } else {
        //     $totalPrice = $totalDays * $apartment->price_per_day;
        // }


        $tenant = FacadesAuth::user();
        // التحقق من الرصيد قبل إنشاء الحجز
        if ($tenant->balance < $totalPrice) {
            return response()->json([
                // 'message' => 'رصيدك غير كافٍ لإتمام هذا الحجز',
                'message' => __('api.insufficient_balance'),
                'required' => $totalPrice,
                'current_balance' => $tenant->balance,
            ], 400);
        }

        // خصم المبلغ من رصيد المستأجر فورًا
        $tenant->balance -= $totalPrice;
        $tenant->save();


        // إنشاء الحجز بحالة pending و owner_approval = false
        $booking = Booking::create([
            'apartment_id'   => $apartment->id,
            'owner_id'       => $apartment->owner_id,
            'tenant_id'      => FacadesAuth::user()->id,
            'check_in'       => $validated['check_in'],
            'check_out'      => $validated['check_out'],
            'total_price'    => $totalPrice,
            'status'         => 'pending',
            'request_status' => 'pending_owner',
            // 'owner_approval' => false,
            'cancellation_reason' => null,
        ]);

        return response()->json([
            // 'message' => 'تم تقديم طلب الحجز بنجاح، بانتظار موافقة صاحب الشقة',
            'message' => __('api.booking_request_sent'),
            'booking' => $booking,
            'total_price' => $totalPrice,
            'new_balance' => $tenant->balance,
        ], 201);
    }




    public function handleOwnerResponse(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $tenant = $booking->tenant;
        $owner = $booking->owner;

        // التأكد إن اللي بيعمل الطلب هو صاحب الشقة فعلاً
        if ($booking->owner_id !== FacadesAuth::user()->id) {
            return response()->json([
                // 'message' => 'غير مصرح لك بإدارة هذا الحجز'
                'message' => __('api.not_authorized_to_manage')
            ], 403);
        }

        // التأكد إن الحجز لا يزال معلق (pending)
        if ($booking->status !== 'pending') {
            return response()->json([
                // 'message' => 'لا يمكن تغيير حالة حجز غير معلق'
                'message' => __('api.cannot_change_non_pending')
            ], 400);
        }

        // التحقق من الإجراء المطلوب
        $request->validate([
            'action' => 'required|in:approve,reject'
        ]);



        if ($request->action === 'approve') {
            // الموافقة → تحويل المبلغ للمالك
            $owner->balance += $booking->total_price;
            $owner->save();
            $booking->update([
                'status' => 'owner_approved',
                'request_status' => 'owner_accepted',

                // 'owner_approval' => true,
            ]);

            return response()->json([
                // 'message' => 'تمت الموافقة على الحجز وتحويل المبلغ لحسابك بنجاح',
                'message' => __('api.approved_and_transferred'),
                'booking' => $booking->fresh()
            ], 200);
        }

        if ($request->action === 'reject') {
            $tenant->balance += $booking->total_price;
            $tenant->save();
            $booking->update([
                'status' => 'owner_rejected',
                'request_status' => 'owner_rejected',

                // 'owner_approval' => false,
            ]);

            return response()->json([
                // 'message' => 'تم رفض الحجز وإرجاع المبلغ للمستأجر'و
                'message' => __('api.rejected_and_refunded'),
                'booking' => $booking->fresh()
            ], 200);
        }
    }





    public function myBookings(Request $request)
    {
        $user = FacadesAuth::user();

        $type = $request->input('type'); // completed, cancelled, current

        $query = Booking::where('tenant_id', $user->id)
            ->with('apartment');

        // fillter
        if ($type === 'completed') {
            $query->where('status', 'completed');
        } elseif ($type === 'cancelled') {
            $query->where('status', 'cancelled');
        } elseif ($type === 'current') {
            $query->whereIn('status', ['pending', 'owner_approved', 'owner_rejected']);
        }
        // if !type => return all booking

        $bookings = $query->latest()->paginate(10);

        $formattedBookings = $bookings->map(function ($booking) {
            $apartment = $booking->apartment;


            // $statusText = match ($booking->status) {
            //     'pending'          => 'Pending Owner Approval',
            //     'owner_approved'   => 'Approved',
            //     'owner_rejected'   => 'Rejected',
            //     'completed'        => 'Completed',
            //     'cancelled'        => 'Cancelled',
            //     default            => $booking->status,
            // };

            return [
                'id'          => $apartment->id,
                // 'type'        => ucfirst($apartment->type ?? 'غير محدد'),
                'type'  => __('api.type_' . $apartment->type),
                'title'       => $apartment->title ?? 'غير محدد',
                'start_date'     => $booking->check_in,
                'end_date'       => $booking->check_out,

                'cover_image' => $apartment->apartment_image?->first()
                    ? asset('storage/' . $apartment->apartment_image->first()->image_path)
                    : null,

                'address' => [
                    'city_name'       => __('cities.' . ($apartment->area->city->name ?? '')),

                    'area_name' => __('areas.' . ($apartment->area->name ?? '')),
                    'detailed_address' => $apartment->address ?? null,
                ],

                'owner' => [
                    'id'           => $apartment->owner?->id,
                    'full_name'    => trim(
                        ($apartment->owner?->profile?->first_name ?? '') . ' ' .
                            ($apartment->owner?->profile?->last_name ?? '')
                    ) ?: 'غير محدد',
                    'phone_number' => $apartment->owner?->phone_number ?? null,
                    'profile_image' => $apartment->owner?->profile?->profile_photo ?? null,
                ],

                // معلومات الحجز الإضافية

                'booking_status' => __('api.' . $booking->status),

                // // اختياري - إذا بدك تضيف معلومات إضافية عن الحجز نفسها
                // 'booking_id'     => $booking->id,
                // 'isOwner'        => $apartment->owner_id === FacadesAuth::user()->id,
            ];
        });

        return response()->json([
            'bookings'      => $formattedBookings,
            'current_page'  => $bookings->currentPage(),
            'last_page'     => $bookings->lastPage(),
            'total'         => $bookings->total(),
            'per_page'      => $bookings->perPage(),
        ]);
    }


    // public function myBookings(Request $request)
    // {
    //     $user = FacadesAuth::user(); // أفضل بدون Facades\ في الغالب

    //     $type = $request->input('type'); // completed, cancelled, current

    //     $query = Booking::where('tenant_id', $user->id)
    //         ->with([
    //             'apartment' => function ($q) {
    //                 $q->with('address'); // افتراضاً أن عندك علاقة address
    //             }
    //         ]);

    //     // تصفية حسب النوع
    //     if ($type === 'completed') {
    //         $query->where('status', 'completed');
    //     } elseif ($type === 'cancelled') {
    //         $query->where('status', 'cancelled');
    //     } elseif ($type === 'current') {
    //         $query->whereIn('status', ['pending', 'owner_approved', 'owner_rejected']);
    //     }
    //     // بدون type → يرجع كل الحجوزات

    //     $bookings = $query->latest()->paginate(10);

    //     $formattedBookings = $bookings->map(function ($booking) {

    //         $statusText = match ($booking->status) {
    //             'pending'        => 'Pending Owner Approval',
    //             'owner_approved' => 'Approved',
    //             'owner_rejected' => 'Rejected',
    //             'completed'      => 'Completed',
    //             'cancelled'      => 'Cancelled',
    //             default          => $booking->status,
    //         };

    //         return [
    //             'id'          => $booking->id,
    //             'type'        => $booking->apartment->type ?? 'Not specified',
    //             'title'       => $booking->apartment->title ?? 'Not specified',
    //             'start_date'  => $booking->check_in,
    //             'end_date'    => $booking->check_out,
    //             'cover_image' => $booking->apartment->main_image ?? null,
    //             'address'     => [
    //                 'id'              => $booking->apartment->address?->id ?? null,
    //                 'city_name'       => $booking->apartment->address?->city_name ?? 'غير محدد',
    //                 'area_name'       => $booking->apartment->address?->area_name ?? null,
    //                 'detailed_address' => $booking->apartment->address?->detailed_address ?? null,
    //             ],
    //             'owner'       => [
    //                 'id'           => $booking->apartment->owner?->id,
    //                 'full_name'    => $booking->apartment->owner?->full_name,
    //                 'phone_number' => $booking->apartment->owner?->phone_number,
    //                 'profile_image' => $booking->apartment->owner?->profile_image ?? 'profiles/default-profile.png',
    //             ],
    //             'booking_status' => $statusText,
    //         ];
    //     });

    //     return response()->json([
    //         'bookings'     => $formattedBookings,
    //         'current_page' => $bookings->currentPage(),
    //         'last_page'    => $bookings->lastPage(),
    //         'total'        => $bookings->total(),
    //         'per_page'     => $bookings->perPage(),
    //     ]);
    // }





    public function requestCancellation(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->tenant_id !== FacadesAuth::user()->id) {
            return response()->json([
                // 'message' => 'غير مصرح لك بهذا الإجراء'
                'message' => __('api.not_authorized_to_cancel')
            ], 403);
        }

        if (!in_array($booking->status, ['owner_approved'])) {
            return response()->json([
                // 'message' => 'لا يمكن إلغاء حجز غير مؤكد'
                'message' => __('api.cannot_cancel_unconfirmed')
            ], 400);
        }

        $request->validate([
            'reason' => 'required|string|min:10|max:1000'
        ]);

        $booking->update([
            'cancellation_reason' => $request->reason,
            'request_status' => 'tenant_cancel_request',

        ]);

        // هنا ممكن ترسل notification للمالك

        return response()->json([
            // 'message' => 'تم إرسال طلب الإلغاء بنجاح، بانتظار موافقة صاحب الشقة',
            'message' => __('api.cancellation_request_sent'),
            'booking' => $booking->fresh()
        ], 200);
    }


    public function handleCancellationResponse(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // التأكد إن المستخدم هو المالك
        if ($booking->owner_id !== FacadesAuth::user()->id) {
            return response()->json([
                // 'message' => 'غير مصرح لك'
                'message' => __('api.not_authorized_to_handle_cancellation')
            ], 403);
        }

        if (empty($booking->cancellation_reason)) {
            return response()->json([
                // 'message' => 'لا يوجد طلب إلغاء'
                'message' => __('api.no_cancellation_request')
            ], 400);
        }

        $request->validate([
            'action' => 'required|in:accept,reject'
        ]);

        if ($request->action === 'reject') {
            $booking->update([
                'cancellation_reason' => null,
                'request_status' => 'owner_cancel_rejected',

            ]);

            return response()->json([
                // 'message' => 'تم رفض طلب الإلغاء',
                'message' => __('api.cancellation_rejected'),
                'booking' => $booking->fresh()
            ], 200);
        }
        //  قبول الإلغاء → إرجاع نصف المبلغ للمستأجر وحذفه من حساب المالك
        $tenant = $booking->tenant;
        $tenant->balance += $booking->total_price / 2;
        $tenant->save();
        $owner = $booking->owner;
        $owner->balance -= $booking->total_price / 2;
        $owner->save();
        $booking->update([
            'status' => 'cancelled',
            'request_status' => 'owner_cancel_accepted',

            // 'owner_approval' => false, // اختياري
        ]);

        return response()->json([
            // 'message' => 'تم إلغاء الحجز بنجاح',
            'message' => __('api.cancellation_accepted'),
            'booking' => $booking->fresh()
        ], 200);
    }





    public function requestModification(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        if (!FacadesAuth::check() || $booking->tenant_id !== FacadesAuth::id()) {
            return response()->json([
                // 'message' => 'غير مصرح لك بهذا الإجراء'
                'message' => __('api.not_authorized_to_modify')
            ], 403);
        }

        if (!in_array($booking->status, ['owner_approved'])) {
            return response()->json([
                // 'message' => 'لا يمكن تعديل حجز غير مؤكد'
                'message' => __('api.cannot_modify_unconfirmed')
            ], 400);
        }

        $validated = $request->validate([
            'new_check_in'  => 'required|date|after_or_equal:today',
            'new_check_out' => 'required|date|after:new_check_in',
            'reason'        => 'required|string|min:10|max:1000',
        ]);

        try {
            $tempCheckIn = Carbon::parse($validated['new_check_in']);
            $tempCheckOut = Carbon::parse($validated['new_check_out']);

            $displayCheckIn = $tempCheckIn->format('j-n-Y');
            $displayCheckOut = $tempCheckOut->format('j-n-Y');

            $dbCheckIn = $tempCheckIn->format('Y-m-d');
            $dbCheckOut = $tempCheckOut->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                // 'message' => 'صيغة التاريخ غير صالحة'
                'message' => __('api.invalid_date_format')
            ], 422);
        }

        // فحص التداخل الصحيح
        $overlapData = ['check_in' => $dbCheckIn, 'check_out' => $dbCheckOut];
        $overlapValidator = Validator::make(
            ['check_out' => $dbCheckOut],
            [
                'check_out' => [
                    new NoOverlappingBooking(
                        $booking->apartment_id,
                        $dbCheckIn,
                        $booking->id
                    )
                ],
            ]
        );


        if ($overlapValidator->fails()) {
            return response()->json([
                // 'message' => 'التواريخ الجديدة متداخلة مع حجز آخر مؤكد'
                'message' => __('api.overlapping_dates')
            ], 422);
        }

        $modificationText = "طلب تعديل الحجز:\n" .
            "تاريخ الدخول الجديد: {$displayCheckIn}\n" .
            "تاريخ الخروج الجديد: {$displayCheckOut}\n" .
            "السبب: {$validated['reason']}";

        $booking->update([
            'cancellation_reason' => $modificationText,
            'request_status' => 'tenant_modify_request',

        ]);

        return response()->json([
            // 'message' => 'تم إرسال طلب تعديل الحجز بنجاح، بانتظار موافقة صاحب الشقة',
            'message' => __('api.modification_request_sent'),
            'booking' => $booking->fresh()
        ], 200);
    }





    public function handleModificationResponse(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // التأكد إن المستخدم هو المالك
        if ($booking->owner_id !== FacadesAuth::id()) {
            return response()->json([
                // 'message' => 'غير مصرح لك'
                'message' => __('api.not_authorized_to_manage')
            ], 403);
        }

        // التأكد إن في طلب تعديل
        if (is_null($booking->cancellation_reason) || !str_contains($booking->cancellation_reason, 'طلب تعديل')) {
            return response()->json([
                // 'message' => 'لا يوجد طلب تعديل معلق'
                'message' => __('api.no_modification_request')
            ], 400);
        }

        $request->validate([
            'action' => 'required|in:accept,reject'
        ]);

        if ($request->action === 'reject') {
            $booking->update([
                'cancellation_reason' => null,
                'request_status' => 'owner_modify_rejected',

            ]);

            return response()->json([
                // 'message' => 'تم رفض طلب التعديل',
                'message' => __('api.modification_rejected'),
                'booking' => $booking->fresh()
            ], 200);
        }

        // استخراج التواريخ من النص (اللي محفوظ بالشكل j-n-Y مثل 1-2-2026)
        preg_match('/تاريخ الدخول الجديد:\s*([^\n\r]+)/', $booking->cancellation_reason, $inMatches);
        preg_match('/تاريخ الخروج الجديد:\s*([^\n\r]+)/', $booking->cancellation_reason, $outMatches);

        if (empty($inMatches[1]) || empty($outMatches[1])) {
            return response()->json([
                // 'message' => 'تعذر قراءة التواريخ من الطلب'
                'message' => __('api.failed_to_read_dates')
            ], 400);
        }

        $rawCheckIn = trim($inMatches[1]);
        $rawCheckOut = trim($outMatches[1]);

        try {
            $newCheckIn = Carbon::createFromFormat('j-n-Y', $rawCheckIn);
            $newCheckOut = Carbon::createFromFormat('j-n-Y', $rawCheckOut);

            if (!$newCheckIn || !$newCheckOut) {
                throw new \Exception('Invalid format');
            }

            $newCheckIn = $newCheckIn->format('Y-m-d');
            $newCheckOut = $newCheckOut->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                // 'message' => 'خطأ في قراءة التواريخ الجديدة'
                'message' => __('api.invalid_date_format')
            ], 400);
        }

        // جلب الشقة
        $apartment = Apartment::findOrFail($booking->apartment_id);

        // حساب السعر الجديد
        $totalDays = Carbon::parse($newCheckIn)->diffInDays(Carbon::parse($newCheckOut));
        $totalDays += 1;
        // حساب السعر الجديد
        $newPrice = $totalDays >= 30
            ? (floor($totalDays / 30) * $apartment->price) + (($totalDays % 30) * ($apartment->price / 30))
            : $totalDays * $apartment->price;
        // $newPrice = $totalDays >= 30
        //     ? (floor($totalDays / 30) * $apartment->price_per_month) + (($totalDays % 30) * $apartment->price_per_day)
        //     : $totalDays * $apartment->price_per_day;

        $tenant = $booking->tenant;
        $owner = $booking->owner;

        // تحقق من رصيد المستأجر إذا زاد السعر
        if ($newPrice > $booking->total_price) {
            $difference = $newPrice - $booking->total_price;
            if ($tenant->balance < $difference) {
                return response()->json([
                    // 'message' => 'رصيد المستأجر غير كافٍ لتغطية زيادة السعر المطلوبة',
                    'message' => __('api.insufficient_tenant_balance'),

                    'required_additional' => $difference,
                    'current_balance' => $tenant->balance,
                ], 400);
            }
        }
        // تحقق من رصيد المالك إذا نقص السعر
        if ($newPrice < $booking->total_price) {
            $difference = $booking->total_price - $newPrice;
            if ($owner->balance < $difference) {
                return response()->json([
                    // 'message' => 'رصيد المالك غير كافٍ لتغطية تخفيض السعر المطلوب',
                    'message' => __('api.insufficient_owner_balance'),
                    'required_deduction' => $difference,
                    'current_balance' => $owner->balance,
                ], 400);
            }
        }

        // تعديل رصيد المستأجر و المالك بناءً على الفرق
        if ($newPrice > $booking->total_price) {
            $difference = $newPrice - $booking->total_price;
            $owner->balance += $difference;
            $owner->save();
            $tenant->balance -= $difference;
            $tenant->save();
        } elseif ($newPrice < $booking->total_price) {
            $difference = $booking->total_price - $newPrice;
            $owner->balance -= $difference;
            $owner->save();
            $tenant->balance += $difference;
            $tenant->save();
        }

        // تطبيق التعديل
        $booking->update([
            'check_in' => $newCheckIn,
            'check_out' => $newCheckOut,
            'total_price' => $newPrice,
            'cancellation_reason' => null,
            'request_status' => 'owner_modify_accepted',

        ]);

        return response()->json([
            // 'message' => 'تم قبول طلب التعديل وتطبيقه بنجاح',
            'message' => __('api.modification_accepted'),
            'booking' => $booking->fresh()
        ], 200);
    }
}

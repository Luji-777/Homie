<?php

return [
  'test' => 'هذه رسالة اختبار باللغة العربية',
  'account_created_success' => 'تم إنشاء الحساب بنجاح، يرجى التحقق من رقم الهاتف.',
  'invalid_otp'             => 'الكود غير صحيح',
  'pending_admin_approval'  => 'الحساب في انتظار موافقة الإدارة.',
  'otp_resent_success'      => 'تم إعادة إرسال رمز التحقق بنجاح.',
  'invalid_login'           => 'بيانات تسجيل الدخول غير صحيحة',
  'login_success'           => 'تم تسجيل الدخول بنجاح',
  'logout_success'          => 'تم تسجيل الخروج بنجاح',




  'apartment_created'    => 'تم إنشاء الشقة بنجاح، بانتظار موافقة الإدارة.',
  'apartment_updated'    => 'تم تحديث الشقة بنجاح.',
  'apartment_deleted'    => 'تم حذف الشقة بنجاح.',
  'apartments_retrieved' => 'تم جلب الشقق بنجاح.',
  'unauthorized'         => 'غير مصرح',

  'type_room'   => 'غرفة',
  'type_studio' => 'استوديو',
  'type_house'  => 'منزل',
  'type_villa'  => 'فيلا',

  'rent_day'   => 'يومي',
  'rent_month' => 'شهري',





  // الحجز
  'pending_owner_approval' => 'بانتظار موافقة صاحب الشقة',
  'owner_approved' => 'تمت الموافقة',
  'owner_rejected' => 'تم رفض الحجز',
  'completed' => 'مكتمل',
  'cancelled' => 'ملغي',
  'tenant_cancel_request' => 'طلب إلغاء من المستأجر',
  'owner_cancel_accepted' => 'تم قبول إلغاء الحجز',
  'owner_cancel_rejected' => 'رفض طلب الإلغاء',
  'tenant_modify_request' => 'طلب تعديل من المستأجر',
  'owner_modify_accepted' => 'تم قبول التعديل',
  'owner_modify_rejected' => 'رفض طلب التعديل',

  // رسائل عامة
  'unauthorized_action' => 'غير مصرح لك بهذا الإجراء',
  'insufficient_balance' => 'رصيدك غير كافٍ لإتمام هذا الحجز',
  'pending_booking' => 'لا يمكن تغيير حالة حجز غير معلق',
  'cancellation_not_allowed' => 'لا يمكن إلغاء حجز غير مؤكد',
  'modification_not_allowed' => 'لا يمكن تعديل حجز غير مؤكد',
  'date_overlap' => 'التواريخ الجديدة متداخلة مع حجز آخر مؤكد',
  'invalid_date_format' => 'صيغة التاريخ غير صالحة',
  'modification_text_error' => 'تعذر قراءة التواريخ من الطلب',
  'insufficient_additional_balance' => 'رصيد المستأجر غير كافٍ لتغطية زيادة السعر المطلوبة',
  'insufficient_deduction_balance' => 'رصيد المالك غير كافٍ لتغطية تخفيض السعر المطلوب',
  'booking_request_sent' => 'تم تقديم طلب الحجز بنجاح، بانتظار موافقة صاحب الشقة',
  'cancellation_request_sent' => 'تم إرسال طلب الإلغاء بنجاح، بانتظار موافقة صاحب الشقة',
  'modification_request_sent' => 'تم إرسال طلب تعديل الحجز بنجاح، بانتظار موافقة صاحب الشقة',
  'cancellation_success' => 'تم إلغاء الحجز بنجاح',
  'modification_success' => 'تم قبول طلب التعديل وتطبيقه بنجاح',

  'failed_to_read_dates' => 'تعذر قراءة التواريخ من الطلب',
  'insufficient_tenant_balance' => 'رصيد المستأجر غير كافٍ لتغطية زيادة السعر المطلوبة',
  'insufficient_owner_balance' => 'رصيد المالك غير كافٍ لتغطية تخفيض السعر المطلوب',


  'pending'        => 'بانتظار الموافقة',
  'owner_approved' => 'تمت الموافقة من المالك',
  'owner_rejected' => 'تم الرفض من المالك',
  'cancelled'      => 'ملغى',
  'completed'      => 'مكتمل',

  'overlapping_dates'=> 'التواريخ الجديدة متداخلة مع حجز آخر مؤكد',
  'not_authorized_to_manage' => 'أنت غير مخول لإدارة هذا الحجز',
  'cannot_change_non_pending' => 'لا يمكن تغيير حالة حجز غير معلق',
  'approved_and_transferred' => 'تمت الموافقة وتحويل المبلغ بنجاح',
  'rejected_and_refunded' => 'تم الرفض واسترداد المبلغ بنجاح',

  'not_authorized_to_cancel' => 'أنت غير مخول لإلغاء هذا الحجز',
  'not_authorized_to_modify' => 'أنت غير مخول لتعديل هذا الحجز',
  'cannot_cancel_unconfirmed' => 'لا يمكن إلغاء حجز غير مؤكد',
  'cannot_modify_unconfirmed' => 'لا يمكن تعديل حجز غير مؤكد',
  'not_authorized_to_handle_cancellation' => 'أنت غير مخول للتعامل مع طلب إلغاء هذا الحجز',
  'not_authorized_to_handle_modification' => 'أنت غير مخول للتعامل مع  طلب تعديل هذا الحجز',
  'no_cancellation_request' => 'لا يوجد طلب إلغاء لمعالجته',
  'no_modification_request' => 'لا يوجد طلب تعديل لمعالجته',
  'cancellation_rejected' => 'تم رفض طلب الإلغاء',
  'cancellation_accepted' => 'تم قبول طلب الإلغاء',
  'modification_rejected' => 'تم رفض طلب التعديل',
  'modification_accepted' => 'تم قبول طلب التعديل',




  'invalid_booking_apartment' => 'الحجز لا يتطابق مع الشقة المطلوبة',
  'review_not_allowed' => 'لا يمكن تقييم الحجز إلا بعد انتهائه',
  'review_already_exists' => 'لقد قيّمت هذه الشقة مسبقًا',
  'review_created_success' => 'تم تسجيل التقييم بنجاح',
  'cannot_delete_review_unauthorized' => 'لا يمكنك حذف تقييم لا يخصك',
  'review_deleted_success' => 'تم حذف التقييم بنجاح',


  'apartment_already_favorite' => 'الشقة مضافة بالفعل للمفضلة',
  'apartment_added_to_favorites_successfully' => 'تمت إضافة الشقة إلى المفضلة بنجاح',
  'apartment_non_favorite' => 'الشقة غير موجودة في المفضلة',
  'apartment_removed_from_favorites_successfully' => 'تمت إزالة الشقة من المفضلة بنجاح',


  'profile_updated_successfully' => 'تم تحديث الملف الشخصي بنجاح',
];

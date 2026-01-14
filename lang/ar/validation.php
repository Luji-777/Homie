<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'يجب قبول :attribute.',
    'accepted_if'          => 'يجب قبول :attribute عندما يكون :other هو :value.',
    'active_url'           => ':attribute ليس رابطًا صحيحًا.',
    'after'                => 'يجب أن يكون :attribute تاريخًا بعد :date.',
    'after_or_equal'       => 'يجب أن يكون :attribute تاريخًا بعد أو يساوي :date.',
    'alpha'                => 'يجب أن يحتوي :attribute على حروف فقط.',
    'alpha_dash'           => 'يجب أن يحتوي :attribute على حروف وأرقام وشرطات وشرطات سفلية فقط.',
    'alpha_num'            => 'يجب أن يحتوي :attribute على حروف وأرقام فقط.',
    'array'                => 'يجب أن يكون :attribute مصفوفة.',
    'ascii'                => 'يجب أن يحتوي حقل :attribute على أحرف وأرقام ورموز ASCII فقط.',
    'before'               => 'يجب أن يكون :attribute تاريخًا قبل :date.',
    'before_or_equal'      => 'يجب أن يكون :attribute تاريخًا قبل أو يساوي :date.',
    'between'              => [
        'array'   => 'يجب أن يحتوي :attribute على عدد عناصر بين :min و :max.',
        'file'    => 'يجب أن يكون حجم الملف :attribute بين :min و :max كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'string'  => 'يجب أن يكون طول النص :attribute بين :min و :max حرف.',
    ],
    'boolean'              => 'يجب أن يكون حقل :attribute إما true أو false.',
    'can'                  => 'يحتوي حقل :attribute على قيمة غير مصرح بها.',
    'confirmed'            => 'التأكيد غير متطابق مع :attribute.',
    'current_password'     => 'كلمة المرور الحالية غير صحيحة.',
    'date'                 => ':attribute ليس تاريخًا صحيحًا.',
    'date_equals'          => 'يجب أن يكون :attribute تاريخًا يساوي :date.',
    'date_format'          => 'صيغة التاريخ غير مطابقة للصيغة :format.',
    'decimal'              => 'يجب أن يحتوي حقل :attribute على :decimal منازل عشرية.',
    'declined'             => 'يجب رفض :attribute.',
    'declined_if'          => 'يجب رفض :attribute عندما يكون :other هو :value.',
    'different'            => 'يجب أن يكون :attribute مختلفًا عن :other.',
    'digits'               => 'يجب أن يتكون :attribute من :digits أرقام.',
    'digits_between'       => 'يجب أن يكون عدد أرقام :attribute بين :min و :max.',
    'dimensions'           => 'أبعاد الصورة في :attribute غير صحيحة.',
    'distinct'             => 'يحتوي حقل :attribute على قيمة مكررة.',
    'doesnt_end_with'      => 'لا يجوز أن ينتهي حقل :attribute بأحد القيم التالية: :values.',
    'doesnt_start_with'    => 'لا يجوز أن يبدأ حقل :attribute بأحد القيم التالية: :values.',
    'email'                => 'يجب أن يكون :attribute عنوان بريد إلكتروني صحيح.',
    'ends_with'            => 'يجب أن ينتهي :attribute بأحد القيم التالية: :values.',
    'enum'                 => 'القيمة المختارة لـ :attribute غير موجودة في القائمة.',
    'exists'               => 'القيمة المختارة لـ :attribute غير موجودة.',
    'file'                 => 'يجب أن يكون :attribute ملفًا.',
    'filled'               => 'حقل :attribute مطلوب.',
    'gt' => [
        'array'   => 'يجب أن يحتوي :attribute على أكثر من :value عنصر.',
        'file'    => 'يجب أن يكون حجم الملف :attribute أكبر من :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من :value.',
        'string'  => 'يجب أن يكون طول النص :attribute أكبر من :value حرف.',
    ],
    'gte' => [
        'array'   => 'يجب أن يحتوي :attribute على :value عنصر على الأقل.',
        'file'    => 'يجب أن يكون حجم الملف :attribute :value كيلوبايت على الأقل.',
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من أو تساوي :value.',
        'string'  => 'يجب أن يكون طول النص :attribute :value حرف على الأقل.',
    ],
    'image'                => 'يجب أن يكون :attribute صورة.',
    'in'                   => 'القيمة المختارة لـ :attribute غير صالحة.',
    'in_array'             => 'حقل :attribute غير موجود في :other.',
    'integer'              => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'ip'                   => 'يجب أن يكون :attribute عنوان IP صحيح.',
    'ipv4'                 => 'يجب أن يكون :attribute عنوان IPv4 صحيح.',
    'ipv6'                 => 'يجب أن يكون :attribute عنوان IPv6 صحيح.',
    'json'                 => 'يجب أن يكون :attribute نص JSON صحيح.',
    'lowercase'            => 'يجب أن يكون حقل :attribute بالحروف الصغيرة.',
    'lt' => [
        'array'   => 'يجب أن يحتوي :attribute على أقل من :value عنصر.',
        'file'    => 'يجب أن يكون حجم الملف :attribute أقل من :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute أقل من :value.',
        'string'  => 'يجب أن يكون طول النص :attribute أقل من :value حرف.',
    ],
    'lte' => [
        'array'   => 'لا يجوز أن يحتوي :attribute على أكثر من :value عنصر.',
        'file'    => 'يجب أن يكون حجم الملف :attribute :value كيلوبايت على الأكثر.',
        'numeric' => 'يجب أن تكون قيمة :attribute أقل من أو تساوي :value.',
        'string'  => 'يجب أن يكون طول النص :attribute :value حرف على الأكثر.',
    ],
    'mac_address'          => 'يجب أن يكون :attribute عنوان MAC صحيح.',
    'max' => [
        'array'   => 'لا يجوز أن يحتوي :attribute على أكثر من :max عنصر.',
        'file'    => 'لا يجوز أن يكون حجم الملف :attribute أكبر من :max كيلوبايت.',
        'numeric' => 'لا يجوز أن تكون قيمة :attribute أكبر من :max.',
        'string'  => 'لا يجوز أن يكون طول النص :attribute أكبر من :max حرف.',
    ],
    'max_digits'           => 'لا يجوز أن يحتوي حقل :attribute على أكثر من :max رقم.',
    'mimes'                => 'يجب أن يكون :attribute ملف من نوع: :values.',
    'mimetypes'            => 'يجب أن يكون :attribute ملف من نوع: :values.',
    'min' => [
        'array'   => 'يجب أن يحتوي :attribute على :min عنصر على الأقل.',
        'file'    => 'يجب أن يكون حجم الملف :attribute :min كيلوبايت على الأقل.',
        'numeric' => 'يجب أن تكون قيمة :attribute :min على الأقل.',
        'string'  => 'يجب أن يكون طول النص :attribute :min حرف على الأقل.',
    ],
    'min_digits'           => 'يجب أن يحتوي حقل :attribute على :min رقم على الأقل.',
    'missing'              => 'يجب أن يكون حقل :attribute مفقودًا.',
    'missing_if'           => 'يجب أن يكون حقل :attribute مفقودًا عندما يكون :other هو :value.',
    'missing_unless'       => 'يجب أن يكون حقل :attribute مفقودًا ما لم يكن :other هو :value.',
    'missing_with'         => 'يجب أن يكون حقل :attribute مفقودًا عندما يكون :values موجودًا.',
    'missing_with_all'     => 'يجب أن يكون حقل :attribute مفقودًا عندما تكون :values موجودة.',
    'multiple_of'          => 'يجب أن تكون قيمة :attribute من مضاعفات :value.',
    'not_in'               => 'القيمة المختارة لـ :attribute غير صالحة.',
    'not_regex'            => 'صيغة :attribute غير صالحة.',
    'numeric'              => 'يجب أن يكون :attribute رقمًا.',
    'password' => [
        'letters'       => 'يجب أن يحتوي حقل :attribute على حرف واحد على الأقل.',
        'mixed'         => 'يجب أن يحتوي حقل :attribute على حرف كبير وحرف صغير على الأقل.',
        'numbers'       => 'يجب أن يحتوي حقل :attribute على رقم واحد على الأقل.',
        'symbols'       => 'يجب أن يحتوي حقل :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'القيمة المُدخلة في :attribute ظهرت في تسريب بيانات. يرجى اختيار قيمة مختلفة.',
    ],
    'present'              => 'يجب أن يكون حقل :attribute موجودًا.',
    'prohibited'           => 'حقل :attribute ممنوع.',
    'prohibited_if'        => 'حقل :attribute ممنوع عندما يكون :other هو :value.',
    'prohibited_unless'    => 'حقل :attribute ممنوع ما لم يكن :other في :values.',
    'prohibits'            => 'حقل :attribute يمنع وجود :other.',
    'regex'                => 'صيغة :attribute غير صحيحة.',
    'required'             => 'حقل :attribute مطلوب.',
    'required_array_keys'  => 'يجب أن يحتوي حقل :attribute على مدخلات لـ: :values.',
    'required_if'          => 'حقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_if_accepted' => 'حقل :attribute مطلوب عند قبول :other.',
    'required_unless'      => 'حقل :attribute مطلوب ما لم يكن :other في :values.',
    'required_with'        => 'حقل :attribute مطلوب عندما يكون :values موجودًا.',
    'required_with_all'    => 'حقل :attribute مطلوب عندما تكون :values موجودة.',
    'required_without'     => 'حقل :attribute مطلوب عندما لا يكون :values موجودًا.',
    'required_without_all' => 'حقل :attribute مطلوب عندما لا تكون أي من :values موجودة.',
    'same'                 => 'يجب أن يتطابق :attribute مع :other.',
    'size' => [
        'array'   => 'يجب أن يحتوي :attribute على :size عنصر بالضبط.',
        'file'    => 'يجب أن يكون حجم الملف :attribute :size كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute :size.',
        'string'  => 'يجب أن يكون طول النص :attribute :size حرف.',
    ],
    'starts_with'          => 'يجب أن يبدأ :attribute بأحد القيم التالية: :values.',
    'string'               => 'يجب أن يكون :attribute نصًا.',
    'timezone'             => 'يجب أن يكون :attribute منطقة زمنية صحيحة.',
    'unique'               => ':attribute مستخدم مسبقًا.',
    'uploaded'             => 'فشل رفع :attribute.',
    'uppercase'            => 'يجب أن يكون حقل :attribute بالحروف الكبيرة.',
    'url'                  => 'يجب أن يكون :attribute رابطًا صحيحًا.',
    'ulid'                 => 'يجب أن يكون حقل :attribute ULID صالح.',
    'uuid'                 => 'يجب أن يكون :attribute UUID صالح.',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'area_id' => [
            'exists' => 'المنطقة المختارة غير موجودة أو لا تتبع المحافظة المختارة.',
        ],
        // أضف هنا أي رسائل مخصصة أخرى تحتاجها لاحقًا
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'first_name'     => 'الاسم الأول',
        'last_name'      => 'الاسم الأخير',
        'phone_number'   => 'رقم الهاتف',
        'password'       => 'كلمة المرور',
        'birth_date'     => 'تاريخ الميلاد',
        'city_id'        => 'المحافظة',
        'area_id'        => 'المنطقة',
        'profile_photo'  => 'صورة الملف الشخصي',
        'personal_photo' => 'الصورة الشخصية',
        'id_photo'       => 'صورة الهوية',
        'otp'            => 'رمز التحقق',
        // أضف باقي الحقول التي تستخدمها في المشروع

        
        'title'       => 'عنوان الشقة',
        'discription' => 'وصف الشقة',
        'type'        => 'نوع الشقة',
        'rent_type'   => 'نوع الإيجار',
        'price'       => 'السعر',
        'space'       => 'المساحة',
        'bedrooms'    => 'عدد غرف النوم',
        'bathrooms'   => 'عدد الحمامات',
        'floor'       => 'الطابق',
        'wifi'        => 'واي فاي',
        'solar'       => 'سولار',
        'images'      => 'الصور',
        'cover_index' => 'صورة الغلاف',
        'area_id'     => 'المنطقة',
        'city_id'     => 'المحافظة',
    ],
];
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language', config('app.locale', 'en'));

        // لو الـ header جاي مثلاً "ar-SA,ar;q=0.9,en;q=0.8" بناخد أول لغة فقط
        $locale = explode(',', $locale)[0];           // ar-SA
        $locale = explode('-', $locale)[0];           // ar   (بنحولها لـ ar فقط)

        // اللغات المدعومة عندك (غيرها حسب حاجتك)
        $supported = ['en', 'ar']; // أضف فرنسي، تركي... إلخ لاحقاً

        if (!in_array($locale, $supported)) {
            $locale = config('app.fallback_locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
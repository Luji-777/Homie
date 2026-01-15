<?php

use Illuminate\Support\Facades\Log;

if (!function_exists('sendWhatsAppMessage')) {
    function sendWhatsAppMessage(string $to, string $message): bool
    {
        $instance = env('ULTRAMSG_INSTANCE_ID');
        $token    = env('ULTRAMSG_TOKEN');
        $apiUrl   = env('ULTRAMSG_API_URL', 'https://api.ultramsg.com'); // default لو مش موجود في .env

        if (empty($instance) || empty($token)) {
            Log::error('UltraMsg: Missing INSTANCE_ID or TOKEN in .env');
            return false;
        }

        // الرقم لازم بدون + ، مثال: 963501234567
        $to = ltrim($to, '+');

        $params = [
            'token' => $token,
            'to'    => $to,
            'body'  => $message,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => "{$apiUrl}/{$instance}/messages/chat",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYHOST => 0, // للتجربة فقط، في الإنتاج غيري لـ 2
            CURLOPT_SSL_VERIFYPEER => 0, // نفس الشيء
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_HTTPHEADER     => [
                "content-type: application/x-www-form-urlencoded"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            Log::error("UltraMsg cURL Error: {$err}");
            return false;
        }

        Log::info("UltraMsg Response: {$response}");

        // تحقق إذا نجح (غالباً الرد JSON يحتوي status أو success)
        $result = json_decode($response, true);
        if (isset($result['sent']) && $result['sent'] === true) {
            return true;
        }

        Log::warning("UltraMsg Send Failed: {$response}");
        return false;
    }
}

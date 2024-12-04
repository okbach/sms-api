<?php
function sendSMS($phone, $message) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../helpers/encoding.php';
    require_once __DIR__ . '/../../helpers/sms_helpers.php';

    // تحديد نوع التشفير
    $isGSM7 = isGSM7($message);
    $encode_type = $isGSM7 ? 'GSM7_default' : 'UNICODE';

    // حساب عدد الأجزاء
    $sms_parts = countSMSParts($message, $isGSM7);

    // تحويل النص إلى التشفير المناسب
    $encoded_message = $isGSM7 ? textToUnicode($message) : textToUnicode16($message);

    // إعداد البيانات
    $data = [
        'isTest' => 'false',
        'goformId' => 'SEND_SMS',
        'notCallback' => 'true',
        'Number' => $phone,
        'sms_time' => getSMSTime(),
        'MessageBody' => $encoded_message,
        'ID' => '10',
        'encode_type' => $encode_type,
    ];

    // إرسال الطلب عبر cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SMS_GATEWAY_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    curl_close($ch);

    return [
        'status' => $response ? 'success' : 'error',
        'encoding' => $encode_type,
        'sms_parts' => $sms_parts,
        'response' => $response
    ];
}

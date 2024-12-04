<?php
header('Content-Type: application/json');

require_once __DIR__ . '/services/sms/send.php';

// استقبال البيانات من الطلب
$requestData = json_decode(file_get_contents('php://input'), true);

if (!isset($requestData['phone']) || !isset($requestData['message'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Phone number and message are required.'
    ]);
    exit;
}

$phone = $requestData['phone'];
$message = $requestData['message'];

// إرسال الرسالة
$result = sendSMS($phone, $message);

// إرجاع النتيجة
echo json_encode($result);

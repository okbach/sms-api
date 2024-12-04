<?php
header('Content-Type: application/json');

require_once __DIR__ . '/services/sms/send.php'; // تأكد من وجود هذه الدالة في هذا المسار

// قائمة الرسائل المتاحة
$verificationMessages = [
    "Your one-time password is:",
    "Please enter this code to continue:",
    "Your confirmation code is:",
    "Use this PIN to verify your identity:",
    "Enter the following code for verification:",
    "Your temporary access code is:",
    "This is your authentication token:",
    "Your security code is:",
    "Please use this code to log in:",
    "Verification code for your account:"
];

// استقبال البيانات من الطلب
// استقبال البيانات بناءً على نوع الطلب (POST أو GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);
} else {
    $requestData = $_GET;
}

// التحقق من وجود رقم الهاتف
if (!isset($requestData['phoneNumber']) || empty($requestData['phoneNumber'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Phone number is required.'
    ]);
    exit;
}

$phone = $requestData['phoneNumber'];

// إزالة البادئات الدولية إذا كانت موجودة
//$phone = preg_replace('/^(\+|00)213/', '0', $phone);

// التحقق من أن الرقم يحتوي على أرقام فقط بعد إزالة البادئات
if (!preg_match('/^[0-9]{9,10}$/', $phone)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Phone number must contain only numbers and be between 9 and 10 digits long.',
        'data'=> $phone
    ]);
    exit;
}

// التحقق من قيمة $messageIndex
$messageIndex = isset($requestData['messagePrefix']) && is_numeric($requestData['messagePrefix']) 
    ? (int)$requestData['messagePrefix'] 
    : 0; // افتراضيًا إلى 0 إذا كانت فارغة

// التحقق من أن $messageIndex ضمن حدود المصفوفة، وإلا استخدام النص الأول
if ($messageIndex < 0 || $messageIndex >= count($verificationMessages)) {
    $messageIndex = 0; // تعيين النص الأول كافتراضي
}

// التحقق من وجود طول رمز PIN
$pinLength = isset($requestData['pinLength']) ? (int)$requestData['pinLength'] : 6; // الطول الافتراضي 6

if ($pinLength < 4 || $pinLength > 12) {
    echo json_encode([
        'status' => 'error',
        'message' => 'PIN length must be between 4 and 12.'
    ]);
    exit;
}

// التحقق من وجود خاصية alphaBeta في الطلب
$alphaBeta = isset($requestData['alphaBeta']) && $requestData['alphaBeta'] === true; 

// التحقق من وجود كود بين جاهز وطوله لا يقل عن 4 أرقام
if (!empty($requestData['pinCode'])) {
    // إذا كان الكود أقل من 4 أرقام، يتم طباعة خطأ
    if (strlen($requestData['pinCode']) < 4) {
        echo json_encode([
            'status' => 'error',
            'message' => 'PIN code must be at least 4 digits long.'
        ]);
        exit;
    }

    // استخدام الكود الجاهز إذا كان طوله 4 أرقام أو أكثر (يتكون من أرقام)
    if (preg_match('/^\d{4,12}$/', $requestData['pinCode'])) {
        $pin = $requestData['pinCode'];
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid PIN code format. It must contain only digits.'
        ]);
        exit;
    }
} else {
    // إذا كان الكود فارغًا، يتم توليد كود جديد
    if ($alphaBeta) {
        // توليد كود يحتوي على أرقام وحروف أبجدية
        $pin = '';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < $pinLength; $i++) {
            $pin .= $characters[random_int(0, strlen($characters) - 1)];
        }
    } else {
        // توليد كود رقمي فقط
        $pin = mt_rand(pow(10, $pinLength - 1), pow(10, $pinLength) - 1);
    }
}

// اختيار نص الرسالة بناءً على الرقم المقدم
$messagePrefix = $verificationMessages[$messageIndex];
$message = "$messagePrefix $pin";

// إرسال الرسالة
$result = sendSMS($phone, $message);

// إرجاع النتيجة مع تفاصيل الرسالة
$response = [
    'status' => 'success',
    'message' => 'SMS sent successfully',
    'phoneNumber' => $phone,
    'pin' => $pin,  // رمز PIN المُرسل
    'message_sent' => $message,  // الرسالة المُرسلة
    //'sms_response' => $result  // الاستجابة من الـ API لإرسال SMS
];

// إرجاع النتيجة
echo json_encode($response);
?>

<?php

header('Content-Type: application/json');

// إعداد الرد
$response = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'message' => 'Server is running smoothly.'
];

// إرجاع الرد مع كود الحالة 200
http_response_code(200);
echo json_encode($response);

<?php
/**
 * phan_hoi_json.php - Tra ve JSON response chuan
 */
function traVeJson(bool $success, $data = null, string $message = '', int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function layDuLieuJson(): array
{
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}

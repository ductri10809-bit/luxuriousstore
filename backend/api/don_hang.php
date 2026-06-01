<?php
/**
 * API: don_hang.php
 */
require_once __DIR__ . '/../cau_hinh/session.php';
require_once __DIR__ . '/../helpers/phan_hoi_json.php';
require_once __DIR__ . '/../middleware/xac_thuc.php';
require_once __DIR__ . '/../controller/don_hang_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    traVeJson(true);
}

$controller = new DonHangController();
$userId = layUserId();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        yeuCauDangNhap();
        if (!empty($_GET['id'])) {
            $result = $controller->chiTietDon((int) $_GET['id']);
        } else {
            $result = $controller->layDonCuaUser($userId);
        }
        break;
    case 'POST':
        yeuCauDangNhap();
        $result = $controller->taoDon($userId, layDuLieuJson());
        break;
    default:
        traVeJson(false, null, 'Method not allowed', 405);
}

traVeJson($result['success'], $result['data'] ?? null, $result['message'] ?? '', $result['success'] ? 200 : 400);

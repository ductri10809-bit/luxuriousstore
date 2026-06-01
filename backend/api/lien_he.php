<?php
/**
 * API: lien_he.php
 */
require_once __DIR__ . '/../helpers/phan_hoi_json.php';
require_once __DIR__ . '/../controller/lien_he_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    traVeJson(true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    traVeJson(false, null, 'Method not allowed', 405);
}

$result = (new LienHeController())->gui(layDuLieuJson());
traVeJson($result['success'], $result['data'] ?? null, $result['message'] ?? '', $result['success'] ? 201 : 400);

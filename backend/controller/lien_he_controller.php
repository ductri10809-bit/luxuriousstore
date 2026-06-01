<?php
/**
 * lien_he_controller.php
 */
require_once __DIR__ . '/../model/phan_hoi.php';
require_once __DIR__ . '/../helpers/validate.php';
require_once __DIR__ . '/../helpers/bao_mat.php';

class LienHeController
{
    private PhanHoi $model;

    public function __construct()
    {
        $this->model = new PhanHoi();
    }

    public function gui(array $data): array
    {
        $loi = validateRequired($data, ['ho_ten', 'email', 'noi_dung']);
        if ($loi) return ['success' => false, 'message' => $loi];

        if (!validateEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email khong hop le'];
        }

        $id = $this->model->tao([
            'ho_ten'   => sanitize($data['ho_ten']),
            'email'    => sanitize($data['email']),
            'noi_dung' => sanitize($data['noi_dung']),
        ]);

        return ['success' => true, 'message' => 'Gui lien he thanh cong', 'data' => ['id' => $id]];
    }
}

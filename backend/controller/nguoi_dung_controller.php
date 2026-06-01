<?php
/**
 * nguoi_dung_controller.php
 */
require_once __DIR__ . '/../model/nguoi_dung.php';
require_once __DIR__ . '/../helpers/bao_mat.php';
require_once __DIR__ . '/../helpers/validate.php';
require_once __DIR__ . '/../cau_hinh/session.php';

class NguoiDungController
{
    private NguoiDung $model;

    public function __construct()
    {
        $this->model = new NguoiDung();
    }

    public function dangKy(array $data): array
    {
        $loi = validateRequired($data, ['ho_ten', 'email', 'password']);
        if ($loi) return ['success' => false, 'message' => $loi];

        if (!validateEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email khong hop le'];
        }

        if ($this->model->timTheoEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email da ton tai'];
        }

        $id = $this->model->tao([
            'ho_ten'   => sanitize($data['ho_ten']),
            'email'    => sanitize($data['email']),
            'password' => hashMatKhau($data['password']),
            'sdt'      => sanitize($data['sdt'] ?? ''),
            'role'     => 'customer',
        ]);

        return ['success' => true, 'message' => 'Dang ky thanh cong', 'data' => ['id' => $id]];
    }

    public function dangNhap(array $data): array
    {
        $loi = validateRequired($data, ['email', 'password']);
        if ($loi) return ['success' => false, 'message' => $loi];

        $user = $this->model->timTheoEmail($data['email']);
        if (!$user || !kiemTraMatKhau($data['password'], $user['password'])) {
            return ['success' => false, 'message' => 'Email hoac mat khau khong dung'];
        }

        datUserId((int) $user['id']);
        return [
            'success' => true,
            'message' => 'Dang nhap thanh cong',
            'data' => [
                'id' => $user['id'],
                'ho_ten' => $user['ho_ten'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ];
    }

    public function layHoSo(int $userId): array
    {
        $user = $this->model->timTheoId($userId);
        if (!$user) return ['success' => false, 'message' => 'Khong tim thay nguoi dung'];
        return ['success' => true, 'data' => $user];
    }

    public function capNhatHoSo(int $userId, array $data): array
    {
        $loi = validateRequired($data, ['ho_ten', 'email']);
        if ($loi) return ['success' => false, 'message' => $loi];

        $ok = $this->model->capNhat($userId, [
            'ho_ten' => sanitize($data['ho_ten']),
            'email'  => sanitize($data['email']),
            'sdt'    => sanitize($data['sdt'] ?? ''),
        ]);

        return $ok
            ? ['success' => true, 'message' => 'Cap nhat thanh cong']
            : ['success' => false, 'message' => 'Cap nhat that bai'];
    }
}

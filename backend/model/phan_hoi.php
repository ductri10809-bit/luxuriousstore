<?php
/**
 * phan_hoi.php - Model bang feedback
 */
require_once __DIR__ . '/../cau_hinh/ket_noi_csdl.php';

class PhanHoi
{
    private PDO $db;

    public function __construct()
    {
        $this->db = ketNoiCSDL();
    }

    public function tao(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO feedback (ho_ten, email, noi_dung) VALUES (?, ?, ?)'
        );
        $stmt->execute([
            $data['ho_ten'],
            $data['email'],
            $data['noi_dung'],
        ]);
        return (int) $this->db->lastInsertId();
    }
}

<?php
/**
 * san_pham.php - Model bang product
 */
require_once __DIR__ . '/../cau_hinh/ket_noi_csdl.php';
require_once __DIR__ . '/../helpers/dinh_dang_san_pham.php';
require_once __DIR__ . '/bien_the.php';

class SanPham
{
    private PDO $db;
    private BienThe $bienThe;

    public function __construct()
    {
        $this->db = ketNoiCSDL();
        $this->bienThe = new BienThe();
    }

    public function layTatCa(array $filters = []): array
    {
        $sql = 'SELECT p.*, c.category_name
                FROM product p
                LEFT JOIN category c ON c.category_id = p.category_id
                WHERE p.product_id IN (
                    SELECT DISTINCT base_product_id FROM product_variant
                )';
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['tim'])) {
            $sql .= ' AND p.product_name LIKE ?';
            $params[] = '%' . $filters['tim'] . '%';
        }

        if (!empty($filters['noi_bat'])) {
            $sql .= ' AND p.is_bestseller = 1';
        }

        if (!empty($filters['mau_sac'])) {
            $ids = $this->bienThe->laySanPhamIdTheoMau($filters['mau_sac']);
            if (empty($ids)) {
                return [];
            }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql .= " AND p.product_id IN ($placeholders)";
            $params = array_merge($params, $ids);
        }

        $sql .= ' ORDER BY p.is_bestseller DESC, p.product_id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map(function ($row) {
            $sp = dinhDangSanPham($row);
            $sp['bien_the'] = $this->bienThe->layTheoSanPham($sp['id']);
            if (!empty($sp['bien_the'][0]['hinh_anh'])) {
                $sp['hinh_anh'] = $sp['bien_the'][0]['hinh_anh'];
            }
            return $sp;
        }, $rows);
    }

    public function timTheoId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.category_name
             FROM product p
             LEFT JOIN category c ON c.category_id = p.category_id
             WHERE p.product_id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $sp = dinhDangSanPham($row);
        $sp['bien_the'] = $this->bienThe->layTheoSanPham($id);
        return $sp;
    }

    public function tao(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO product (category_id, product_name, price, stock_quantity, image, description, is_bestseller, is_sale)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['category_id'] ?? null,
            $data['product_name'] ?? '',
            $data['price'] ?? 0,
            $data['stock_quantity'] ?? 0,
            $data['image'] ?? '',
            $data['description'] ?? '',
            $data['is_bestseller'] ? 1 : 0,
            $data['is_sale'] ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function capNhat(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE product SET category_id = ?, product_name = ?, price = ?, stock_quantity = ?, image = ?, description = ?, is_bestseller = ?, is_sale = ?
             WHERE product_id = ?'
        );
        return $stmt->execute([
            $data['category_id'] ?? null,
            $data['product_name'] ?? '',
            $data['price'] ?? 0,
            $data['stock_quantity'] ?? 0,
            $data['image'] ?? '',
            $data['description'] ?? '',
            $data['is_bestseller'] ? 1 : 0,
            $data['is_sale'] ? 1 : 0,
            $id,
        ]);
    }

    public function xoa(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM product WHERE product_id = ?');
        return $stmt->execute([$id]);
    }
}

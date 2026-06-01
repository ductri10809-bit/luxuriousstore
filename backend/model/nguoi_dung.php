<?php
/**
 * nguoi_dung.php - Model bang users
 */
require_once __DIR__ . '/../cau_hinh/ket_noi_csdl.php';

class NguoiDung
{
    private PDO $db;

    public function __construct()
    {
        $this->db = ketNoiCSDL();
    }

    public function timTheoEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? $this->dinhDang($row) : null;
    }

    public function timTheoId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->dinhDang($row) : null;
    }

    public function tao(array $data): int
    {
        // Ensure the 'role' column exists to support older databases/imports
        $this->ensureRoleColumnExists();

        $stmt = $this->db->prepare(
            'INSERT INTO users (fullname, email, password, phone, role) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['ho_ten'],
            $data['email'],
            $data['password'],
            $data['sdt'] ?? null,
            $data['role'] ?? 'customer',
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Create the `role` column on the users table if it does not exist yet.
     * This is defensive: some databases imported from older dumps may not have the column.
     */
    private function ensureRoleColumnExists(): void
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
            );
            $stmt->execute(['users', 'role']);
            $count = (int) $stmt->fetchColumn();
            if ($count === 0) {
                // Add the column with a sensible default
                $this->db->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'customer'");
            }
        } catch (PDOException $e) {
            // If anything goes wrong here, don't block user creation; rethrow so the outer code can handle it
            throw $e;
        }
    }

    public function capNhat(int $id, array $data): bool
    {
        $fields = ['fullname = ?', 'email = ?', 'phone = ?'];
        $params = [$data['ho_ten'], $data['email'], $data['sdt'] ?? null];

        if (!empty($data['role'])) {
            $fields[] = 'role = ?';
            $params[] = $data['role'];
        }

        $params[] = $id;
        $stmt = $this->db->prepare(
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE user_id = ?'
        );
        return $stmt->execute($params);
    }

    public function xoa(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE user_id = ?');
        return $stmt->execute([$id]);
    }

    public function layTatCa(): array
    {
        $stmt = $this->db->query('SELECT * FROM users ORDER BY user_id DESC');
        return array_map([$this, 'dinhDang'], $stmt->fetchAll());
    }

    private function dinhDang(array $row): array
    {
        return [
            'id'      => (int) $row['user_id'],
            'ho_ten'  => $row['fullname'] ?? '',
            'email'   => $row['email'] ?? '',
            'sdt'     => $row['phone'] ?? '',
            'role'    => $row['role'] ?? 'customer',
            'password'=> $row['password'] ?? '',
        ];
    }
}

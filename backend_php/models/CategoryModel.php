<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/response.php';

class CategoryModel {
    private static function db() {
        return \Database::connect();
    }

    public static function getList(int $page = 1, int $limit = 10, ?string $search = null): array {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT id, name, description, created_at, updated_at 
                FROM categories 
                WHERE 1=1";
        $params = [];
        $countSql = "SELECT COUNT(*) as total FROM categories WHERE 1=1";

        if ($search) {
            $sql .= " AND name LIKE :search";
            $countSql .= " AND name LIKE :search";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";

        $stmt = self::db()->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $items = $stmt->fetchAll();

        $countStmt = self::db()->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }

    public static function getById(int $id): ?array {
        $stmt = self::db()->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null
        ]);
        return self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $setParts = [];
        $params = [':id' => $id];
        if (isset($data['name'])) {
            $setParts[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $setParts[] = "description = :description";
            $params[':description'] = $data['description'];
        }
        if (empty($setParts)) return false;

        $sql = "UPDATE categories SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}


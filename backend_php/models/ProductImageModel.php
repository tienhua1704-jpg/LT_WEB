<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/response.php';

class ProductImageModel {
    private static function db() {
        return \Database::connect();
    }

    public static function getByProductId(int $product_id): array {
        $stmt = self::db()->prepare("SELECT * FROM product_images WHERE product_id = :product_id ORDER BY sort_order ASC, id ASC");
        $stmt->execute([':product_id' => $product_id]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array {
        $stmt = self::db()->prepare("SELECT * FROM product_images WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $product_id, string $image_url, int $sort_order = 0): int {
        $stmt = self::db()->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (:product_id, :image_url, :sort_order)");
        $stmt->execute([
            ':product_id' => $product_id,
            ':image_url' => $image_url,
            ':sort_order' => $sort_order
        ]);
        return self::db()->lastInsertId();
    }

    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM product_images WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public static function updateSortOrder(int $id, int $sort_order): bool {
        $stmt = self::db()->prepare("UPDATE product_images SET sort_order = :sort_order WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':sort_order' => $sort_order
        ]);
    }
}


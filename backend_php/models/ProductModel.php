<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/response.php';

class ProductModel {
    private static function db() {
        return \Database::connect();
    }

    public static function getPublicList(
        int $page = 1, 
        int $limit = 12, 
        ?string $search = null, 
        ?int $category_id = null, 
        ?float $min_price = null, 
        ?float $max_price = null,
        string $status = 'active'
    ): array {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT p.*, c.name as category_name,
                       CASE 
                           WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price 
                           ELSE p.price 
                       END as display_price
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.status = :status AND p.stock_quantity > 0";
        $params = [':status' => $status];
        $countSql = "SELECT COUNT(*) as total FROM products p WHERE p.status = :status AND p.stock_quantity > 0";

        if ($search) {
            $sql .= " AND p.name LIKE :search";
            $countSql .= " AND p.name LIKE :search";
            $params[':search'] = "%$search%";
        }
        if ($category_id) {
            $sql .= " AND p.category_id = :category_id";
            $countSql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        if ($min_price !== null) {
            $sql .= " AND p.price >= :min_price";
            $countSql .= " AND p.price >= :min_price";
            $params[':min_price'] = $min_price;
        }
        if ($max_price !== null) {
            $sql .= " AND p.price <= :max_price";
            $countSql .= " AND p.price <= :max_price";
            $params[':max_price'] = $max_price;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

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

    public static function getPublicDetail(int $id): ?array {
        $sql = "SELECT p.*, c.name as category_name,
                       CASE 
                           WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price 
                           ELSE p.price 
                       END as display_price
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id AND p.status = 'active' AND p.stock_quantity > 0";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();
        if (!$product) return null;

        // Lấy images
        $imgStmt = self::db()->prepare("SELECT * FROM product_images WHERE product_id = :id ORDER BY sort_order ASC");
        $imgStmt->execute([':id' => $id]);
        $product['images'] = $imgStmt->fetchAll();

        return $product;
    }

    public static function getAdminList(int $page = 1, int $limit = 10, ?string $search = null, ?int $category_id = null, ?string $status = null): array {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        $params = [];
        $countSql = "SELECT COUNT(*) as total FROM products p WHERE 1=1";

        if ($search) {
            $sql .= " AND p.name LIKE :search";
            $countSql .= " AND p.name LIKE :search";
            $params[':search'] = "%$search%";
        }
        if ($category_id) {
            $sql .= " AND p.category_id = :category_id";
            $countSql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        if ($status) {
            $sql .= " AND p.status = :status";
            $countSql .= " AND p.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

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
        $stmt = self::db()->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $sql = "INSERT INTO products (category_id, name, description, price, sale_price, stock_quantity, image, status) 
                VALUES (:category_id, :name, :description, :price, :sale_price, :stock_quantity, :image, :status)";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([
            ':category_id' => $data['category_id'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':sale_price' => $data['sale_price'] ?? null,
            ':stock_quantity' => $data['stock_quantity'] ?? 0,
            ':image' => $data['image'] ?? null,
            ':status' => $data['status'] ?? 'active'
        ]);
        return self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $setParts = [":id" => $id];
        $fields = [];
        if (isset($data['category_id'])) { $fields[] = "category_id = :category_id"; $setParts[':category_id'] = $data['category_id']; }
        if (isset($data['name'])) { $fields[] = "name = :name"; $setParts[':name'] = $data['name']; }
        if (isset($data['description'])) { $fields[] = "description = :description"; $setParts[':description'] = $data['description']; }
        if (isset($data['price'])) { $fields[] = "price = :price"; $setParts[':price'] = $data['price']; }
        if (array_key_exists('sale_price', $data)) { $fields[] = "sale_price = :sale_price"; $setParts[':sale_price'] = $data['sale_price']; }
        if (array_key_exists('stock_quantity', $data)) { $fields[] = "stock_quantity = :stock_quantity"; $setParts[':stock_quantity'] = $data['stock_quantity']; }
        if (isset($data['image'])) { $fields[] = "image = :image"; $setParts[':image'] = $data['image']; }
        if (isset($data['status'])) { $fields[] = "status = :status"; $setParts[':status'] = $data['status']; }
        if (empty($fields)) return false;

        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($setParts);
    }

    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public static function addImages(int $product_id, array $image_urls): bool {
        self::db()->beginTransaction();
        try {
            foreach ($image_urls as $index => $url) {
                $stmt = self::db()->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (:product_id, :image_url, :sort_order)");
                $stmt->execute([
                    ':product_id' => $product_id,
                    ':image_url' => $url,
                    ':sort_order' => $index
                ]);
            }
            self::db()->commit();
            return true;
        } catch (Exception $e) {
            self::db()->rollBack();
            return false;
        }
    }

    public static function deleteImage(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM product_images WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public static function getImagesById(int $image_id): array {
        $stmt = self::db()->prepare("SELECT * FROM product_images WHERE id = :id");
        $stmt->execute([':id' => $image_id]);
        return $stmt->fetchAll();
    }

    public static function getImages(int $product_id): array {
        $stmt = self::db()->prepare("SELECT * FROM product_images WHERE product_id = :product_id ORDER BY sort_order ASC");
        $stmt->execute([':product_id' => $product_id]);
        return $stmt->fetchAll();
    }
}


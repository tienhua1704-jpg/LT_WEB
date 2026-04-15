<?php
require_once __DIR__ . '/../utils/request.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class CategoryController
{
    public static function getPublicList(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        $page = max(1, $page);
        $limit = min(50, max(5, $limit)); // Giới hạn 5-50

        $result = CategoryModel::getList($page, $limit);
        successResponse('Lấy danh sách danh mục thành công', $result);
    }

    public static function getAdminList(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        $search = $_GET['search'] ?? null;
        $page = max(1, $page);
        $limit = min(50, max(5, $limit));

        $result = CategoryModel::getList($page, $limit, $search);
        successResponse('Lấy danh sách danh mục admin thành công', $result);
    }

    public static function create(): void
    {
        $data = getJsonBody();
        
        // Validation
        $errors = [];
        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Tên danh mục bắt buộc';
        }
        if (strlen($data['name'] ?? '') > 100) {
            $errors['name'] = 'Tên danh mục quá dài';
        }
        
        if (!empty($errors)) {
            errorResponse('Dữ liệu không hợp lệ', $errors, 422);
        }

        try {
            $id = CategoryModel::create($data);
            $category = CategoryModel::getById($id);
            successResponse('Tạo danh mục thành công', $category, 201);
        } catch (Exception $e) {
            errorResponse('Lỗi tạo danh mục', ['database' => $e->getMessage()], 500);
        }
    }

    public static function update($id): void
    {
        $data = getJsonBody();
        $category = CategoryModel::getById((int)$id);
        if (!$category) {
            errorResponse('Danh mục không tồn tại', [], 404);
        }
        
        // Validation
        $errors = [];
        if (isset($data['name']) && (empty(trim($data['name'])) || strlen($data['name']) > 100)) {
            $errors['name'] = 'Tên danh mục không hợp lệ';
        }
        
        if (!empty($errors)) {
            errorResponse('Dữ liệu không hợp lệ', $errors, 422);
        }

        try {
            CategoryModel::update((int)$id, $data);
            $updated = CategoryModel::getById((int)$id);
            successResponse('Cập nhật danh mục thành công', $updated);
        } catch (Exception $e) {
            errorResponse('Lỗi cập nhật danh mục', ['database' => $e->getMessage()], 500);
        }
    }

    public static function delete($id): void
    {
        $category = CategoryModel::getById((int)$id);
        if (!$category) {
            errorResponse('Danh mục không tồn tại', [], 404);
        }

        try {
            CategoryModel::delete((int)$id);
            successResponse('Xóa danh mục thành công', ['id' => (int)$id]);
        } catch (Exception $e) {
            errorResponse('Lỗi xóa danh mục (có thể có sản phẩm liên kết)', ['database' => $e->getMessage()], 500);
        }
    }
}


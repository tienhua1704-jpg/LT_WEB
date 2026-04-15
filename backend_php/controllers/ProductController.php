<?php
require_once __DIR__ . '/../utils/request.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class ProductController
{
    public static function getPublicList(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 12);
        $search = $_GET['search'] ?? null;
        $category_id = $_GET['category_id'] ? (int)$_GET['category_id'] : null;
        $min_price = $_GET['min_price'] ? (float)$_GET['min_price'] : null;
        $max_price = $_GET['max_price'] ? (float)$_GET['max_price'] : null;
        
        $page = max(1, $page);
        $limit = min(50, max(6, $limit));

        $result = ProductModel::getPublicList($page, $limit, $search, $category_id, $min_price, $max_price);
        successResponse('Lấy danh sách sản phẩm thành công', $result);
    }

    public static function getPublicDetail($id): void
    {
        $product = ProductModel::getPublicDetail((int)$id);
        if (!$product) {
            errorResponse('Sản phẩm không tồn tại hoặc hết hàng', [], 404);
        }
        successResponse('Lấy chi tiết sản phẩm thành công', $product);
    }

    public static function getAdminList(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        $search = $_GET['search'] ?? null;
        $category_id = $_GET['category_id'] ? (int)$_GET['category_id'] : null;
        $status = $_GET['status'] ?? null;
        
        $page = max(1, $page);
        $limit = min(50, max(5, $limit));

        $result = ProductModel::getAdminList($page, $limit, $search, $category_id, $status);
        successResponse('Lấy danh sách sản phẩm admin thành công', $result);
    }

    public static function create(): void
    {
        $data = getJsonBody();
        
        // Validation
        $errors = [];
        if (empty(trim($data['name'] ?? ''))) $errors['name'] = 'Tên sản phẩm bắt buộc';
        if (empty($data['category_id']) || !CategoryModel::getById((int)$data['category_id'])) $errors['category_id'] = 'Danh mục không hợp lệ';
        if (!is_numeric($data['price']) || $data['price'] <= 0) $errors['price'] = 'Giá phải > 0';
        if (isset($data['sale_price']) && ($data['sale_price'] < 0 || $data['sale_price'] >= $data['price'])) $errors['sale_price'] = 'Giá sale phải < giá gốc và >= 0';
        if (isset($data['stock_quantity']) && $data['stock_quantity'] < 0) $errors['stock_quantity'] = 'Tồn kho không âm';
        if (!empty($errors)) {
            errorResponse('Dữ liệu không hợp lệ', $errors, 422);
        }

        try {
            $id = ProductModel::create($data);
            $product = ProductModel::getById($id);
            successResponse('Tạo sản phẩm thành công', $product, 201);
        } catch (Exception $e) {
            errorResponse('Lỗi tạo sản phẩm', ['database' => $e->getMessage()], 500);
        }
    }

    public static function update($id): void
    {
        $data = getJsonBody();
        $product = ProductModel::getById((int)$id);
        if (!$product) {
            errorResponse('Sản phẩm không tồn tại', [], 404);
        }
        
        // Validation
        $errors = [];
        if (isset($data['category_id']) && !CategoryModel::getById((int)$data['category_id'])) $errors['category_id'] = 'Danh mục không hợp lệ';
        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] <= 0)) $errors['price'] = 'Giá phải > 0';
        if (isset($data['sale_price']) && ($data['sale_price'] < 0 || $data['sale_price'] >= ($data['price'] ?? $product['price']))) $errors['sale_price'] = 'Giá sale phải < giá gốc và >= 0';
        if (isset($data['stock_quantity']) && $data['stock_quantity'] < 0) $errors['stock_quantity'] = 'Tồn kho không âm';
        
        if (!empty($errors)) {
            errorResponse('Dữ liệu không hợp lệ', $errors, 422);
        }

        try {
            ProductModel::update((int)$id, $data);
            $updated = ProductModel::getById((int)$id);
            successResponse('Cập nhật sản phẩm thành công', $updated);
        } catch (Exception $e) {
            errorResponse('Lỗi cập nhật sản phẩm', ['database' => $e->getMessage()], 500);
        }
    }

    public static function delete($id): void
    {
        $product = ProductModel::getById((int)$id);
        if (!$product) {
            errorResponse('Sản phẩm không tồn tại', [], 404);
        }

        try {
            ProductModel::delete((int)$id);
            successResponse('Xóa sản phẩm thành công', ['id' => (int)$id]);
        } catch (Exception $e) {
            errorResponse('Lỗi xóa sản phẩm', ['database' => $e->getMessage()], 500);
        }
    }

    public static function addImages($product_id): void
    {
        $data = getJsonBody();
        $images = $data['images'] ?? [];
        
        if (empty($images)) {
            errorResponse('Chưa chọn ảnh', [], 422);
        }
        if (!is_array($images) || count($images) > 10) {
            errorResponse('Số ảnh tối đa 10', [], 422);
        }

        $product = ProductModel::getById((int)$product_id);
        if (!$product) {
            errorResponse('Sản phẩm không tồn tại', [], 404);
        }

        try {
            ProductModel::addImages((int)$product_id, $images);
            $allImages = ProductModel::getImages((int)$product_id);
            successResponse('Thêm ảnh thành công', ['images' => $allImages]);
        } catch (Exception $e) {
            errorResponse('Lỗi thêm ảnh', ['database' => $e->getMessage()], 500);
        }
    }

    public static function deleteImage($id): void
    {
        $images = ProductModel::getImagesById((int)$id);
        if (empty($images)) {
            errorResponse('Ảnh không tồn tại', [], 404);
        }

        try {
            ProductModel::deleteImage((int)$id);
            successResponse('Xóa ảnh thành công', ['image_id' => (int)$id]);
        } catch (Exception $e) {
            errorResponse('Lỗi xóa ảnh', ['database' => $e->getMessage()], 500);
        }
    }
}


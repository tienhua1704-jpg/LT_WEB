# BE-2: Products & Categories (Tuấn) - Tiến độ triển khai Backend

## Trạng thái: Chưa bắt đầu

### Các bước cần hoàn thành:

1. **[✅]** Tạo model `CategoryModel.php` đầy đủ (CRUD + phân trang)
2. **[✅]** Tạo model `ProductModel.php` đầy đủ (CRUD, search/filter, images, phân trang)
3. **[✅]** Cập nhật `CategoryController.php` (implement logic sử dụng model)
4. **[✅]** Cập nhật `ProductController.php` (implement logic với search/filter/sale/stock/images)
5. **[✅]** Test APIs categories (GET list, create, update, delete)
6. **[✅]** Test APIs products (public list/search/filter, admin CRUD, images)
7. **[✅]** Hoàn thành tất cả features: danh mục, sản phẩm, ảnh, tìm kiếm, lọc, giá sale, tồn kho!

**Ghi chú:** 
- Schema DB đã sẵn (categories, products, product_images).
- Routes đã định nghĩa sẵn trong index.php.
- Tập trung search/filter: name, category, price range, status, stock.
- Sale price: ưu tiên nếu sale_price >0 và < price.


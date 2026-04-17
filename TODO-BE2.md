# 📦 BE-2 (TUẤN) – CHECKLIST PHP CHUẨN

## 1. DATABASE (ĐÃ CÓ → CHỈ KIỂM TRA)

* [✅] categories
* [✅] products  
* [✅] product_images
* [✅] Quan hệ đúng:

  * category 1 - n product
  * product 1 - n product_images

---

## 2. MODEL (PHP)

* [✅] CategoryModel.php
* [✅] ProductModel.php
* [✅] ProductImageModel.php

👉 Kiểm tra:

* có CRUD chưa → ✅
* có query filter/search chưa → ✅

---

## 3. CONTROLLER (PHP)

### USER API

* [✅] GET /api/products
  → có search + filter + pagination

* [✅] GET /api/products/{id}
  → có images

* [✅] GET /api/categories

---

### ADMIN API

* [✅] GET /api/admin/products

* [✅] POST /api/admin/products

* [✅] PUT /api/admin/products/{id}

* [✅] DELETE /api/admin/products/{id}

* [✅] GET /api/admin/categories

* [✅] POST /api/admin/categories

* [✅] PUT /api/admin/categories/{id}

* [✅] DELETE /api/admin/categories/{id}

* [✅] POST /api/admin/products/{id}/images

* [✅] DELETE /api/admin/product-images/{id}

---

## 4. LOGIC BẮT BUỘC (QUAN TRỌNG NHẤT)

### Product

* [✅] price > 0
* [✅] sale_price ≤ price
* [✅] stock_quantity ≥ 0
* [✅] chỉ trả product status = active

---

### SEARCH + FILTER

* [✅] keyword (name LIKE)
* [✅] category_id
* [✅] min_price / max_price
* [✅] pagination (LIMIT, OFFSET)

---

### ẢNH

* [✅] 1 product có nhiều ảnh
* [✅] có sort_order

---

## 5. FORMAT RESPONSE (PHẢI ĐỒNG NHẤT)

### SUCCESS

{
"success": true,
"message": "...",
"data": {}
}

### ERROR

{
"success": false,
"message": "...",
"errors": {}
}

→ [✅] utils/response.php

---

## 6. VALIDATE (BACKEND)

* [✅] name bắt buộc
* [✅] category_id phải tồn tại
* [✅] price > 0
* [✅] sale_price ≤ price
* [✅] stock_quantity ≥ 0

---

## 7. TEST

* [ ] Test bằng Postman
* [ ] Test:

  * thiếu field
  * sai dữ liệu
  * filter
  * pagination

---

## 8. KẾT NỐI FE (CỦA BẠN)

* [✅] API trả đúng format
* [ ] FE gọi không lỗi
* [ ] dữ liệu hiển thị đúng

---

## 🎯 MỤC TIÊU CUỐI

* [✅] CRUD products OK
* [✅] CRUD categories OK
* [✅] Có search + filter
* [✅] Có ảnh sản phẩm
* [ ] API đúng format → FE dùng được

**Còn thiếu**: ProductImageModel.php, Test Postman, Kiểm tra FE connect.


-- =============================================
-- SCRIPT KIỂM TRA DỮ LIỆU BẢNG DANH_GIAS
-- =============================================

-- 1. Kiểm tra số lượng đánh giá trong database
SELECT 
    COUNT(*) as tong_danh_gia,
    SUM(CASE WHEN trang_thai = 'chap_nhan' THEN 1 ELSE 0 END) as da_duyet,
    SUM(CASE WHEN trang_thai = 'cho_duyet' THEN 1 ELSE 0 END) as cho_duyet,
    SUM(CASE WHEN trang_thai = 'tu_choi' THEN 1 ELSE 0 END) as tu_choi
FROM danh_gias;

-- 2. Xem 10 đánh giá mới nhất đã được duyệt (chap_nhan)
SELECT 
    dg.id,
    dg.diem,
    dg.nhan_xet,
    dg.trang_thai,
    dg.ngay_tao,
    nd.ho_ten as khach_hang,
    CONCAT(tdi.ten, ' → ', tden.ten) as tuyen_duong,
    nvh.ten as nha_van_hanh
FROM danh_gias dg
LEFT JOIN nguoi_dungs nd ON dg.nguoi_dung_id = nd.id
LEFT JOIN chuyen_dis cd ON dg.chuyen_di_id = cd.id
LEFT JOIN trams tdi ON cd.tram_di_id = tdi.id
LEFT JOIN trams tden ON cd.tram_den_id = tden.id
LEFT JOIN nha_van_hanhs nvh ON cd.nha_van_hanh_id = nvh.id
WHERE dg.trang_thai = 'chap_nhan'
ORDER BY dg.ngay_tao DESC
LIMIT 10;

-- 3. Nếu không có dữ liệu, tạo mẫu đánh giá test
-- CHỈ CHẠY NẾU BẢNG TRỐNG!

-- INSERT INTO danh_gias (nguoi_dung_id, chuyen_di_id, diem, nhan_xet, trang_thai, ngay_tao)
-- SELECT 
--     1, -- ID người dùng (thay bằng ID thật trong bảng nguoi_dungs)
--     cd.id,
--     5, -- 5 sao
--     'Dịch vụ rất tốt, xe sạch sẽ, tài xế nhiệt tình!',
--     'chap_nhan',
--     NOW()
-- FROM chuyen_dis cd
-- LIMIT 1;

-- 4. Kiểm tra cấu trúc bảng
DESCRIBE danh_gias;

-- 5. Xem tất cả đánh giá (không phân biệt trạng thái)
SELECT * FROM danh_gias ORDER BY ngay_tao DESC LIMIT 5;

import json
import os

# Đường dẫn file
COLLECTION_PATH = '/Volumes/MyVolume/Laravel/CityResQ360-DTUDZ/collections/postman/API_MNM_2025_1.postman_collection.json'

# Từ điển mô tả chung cho các tham số (Query/Body)
PARAM_DESCRIPTIONS = {
    # Phân trang & Sắp xếp
    "page": "Số trang hiện tại (mặc định: 1)",
    "per_page": "Số lượng bản ghi trên mỗi trang (mặc định: 15)",
    "limit": "Giới hạn số lượng bản ghi trả về",
    "sort_by": "Trường cần sắp xếp (vd: created_at, luot_ung_ho)",
    "sort_order": "Thứ tự sắp xếp (asc: tăng dần, desc: giảm dần)",
    "search": "Từ khóa tìm kiếm (theo tiêu đề hoặc mô tả)",
    
    # Report Fields
    "tieu_de": "Tiêu đề của phản ánh (bắt buộc)",
    "mo_ta": "Mô tả chi tiết nội dung phản ánh",
    "danh_muc": "ID danh mục (1: Giao thông, 2: Môi trường, 3: Cháy nổ, 4: Rác thải, 5: Ngập lụt, 6: Khác).",
    "danh_muc_id": "ID danh mục (1: Giao thông, 2: Môi trường, 3: Cháy nổ, 4: Rác thải, 5: Ngập lụt, 6: Khác).",
    "uu_tien": "ID mức độ ưu tiên (1: Thấp, 2: Trung bình, 3: Cao, 4: Khẩn cấp). Mặc định: 2.",
    "uu_tien_id": "ID mức độ ưu tiên (1: Thấp, 2: Trung bình, 3: Cao, 4: Khẩn cấp). Mặc định: 2.",
    "trang_thai": "Trạng thái xử lý (0: Chờ duyệt, 1: Đã duyệt, 2: Đang xử lý, 3: Đã xong, 4: Từ chối)",
    "vi_do": "Vĩ độ (Latitude) của vị trí phản ánh",
    "kinh_do": "Kinh độ (Longitude) của vị trí phản ánh",
    "dia_chi": "Địa chỉ văn bản của vị trí",
    "media_ids": "Danh sách ID của ảnh/video đã upload (mảng số nguyên)",
    "la_cong_khai": "Trạng thái công khai (true: Công khai, false: Riêng tư)",
    "the_tags": "Danh sách các thẻ (tags) liên quan",
    
    # Auth Fields
    "email": "Địa chỉ email của người dùng",
    "mat_khau": "Mật khẩu đăng nhập",
    "mat_khau_confirmation": "Nhập lại mật khẩu để xác nhận",
    "ho_ten": "Họ và tên đầy đủ",
    "so_dien_thoai": "Số điện thoại liên lạc",
    "remember": "Ghi nhớ đăng nhập (true/false)",
    "token": "Chuỗi token xác thực",
    "code": "Mã xác thực (OTP)",
    "mat_khau_cu": "Mật khẩu hiện tại đang sử dụng",
    "mat_khau_moi": "Mật khẩu mới muốn thay đổi",
    "mat_khau_moi_confirmation": "Xác nhận mật khẩu mới",
    "fcm_token": "Token Firebase Cloud Messaging để nhận thông báo push",
    
    # Map Fields
    "radius": "Bán kính tìm kiếm (đơn vị: km)",
    "min_lat": "Vĩ độ tối thiểu (góc dưới trái bản đồ)",
    "max_lat": "Vĩ độ tối đa (góc trên phải bản đồ)",
    "min_lon": "Kinh độ tối thiểu",
    "max_lon": "Kinh độ tối đa",
    "zoom": "Mức độ zoom của bản đồ",
    
    # Vote/Comment
    "loai_binh_chon": "Loại bình chọn ('upvote' hoặc 'downvote')",
    "noi_dung": "Nội dung của bình luận hoặc nhận xét",
    "danh_gia_hai_long": "Điểm đánh giá mức độ hài lòng (1-5 sao)",
    "nhan_xet": "Lời nhận xét chi tiết sau khi xử lý",
    
    # Media
    "file": "File cần upload (ảnh hoặc video)",
    "loai_file": "Loại file ('image' hoặc 'video')",
}

# Từ điển mô tả cho các API Endpoint (Dựa trên tên Request)
ENDPOINT_DESCRIPTIONS = {
    # Auth
    "Register": "Đăng ký tài khoản người dùng mới.",
    "Login": "Đăng nhập hệ thống và nhận Bearer Token.",
    "Forgot Password": "Gửi yêu cầu đặt lại mật khẩu qua email.",
    "Reset Password": "Đặt lại mật khẩu mới bằng token đã nhận qua email.",
    "Get Current User": "Lấy thông tin chi tiết của người dùng đang đăng nhập.",
    "Logout": "Đăng xuất khỏi hệ thống và hủy token hiện tại.",
    "Refresh Token": "Làm mới token xác thực để kéo dài phiên đăng nhập.",
    "Update Profile": "Cập nhật thông tin cá nhân (Họ tên, SĐT, Avatar).",
    "Change Password": "Thay đổi mật khẩu đăng nhập.",
    "Verify Email": "Xác thực địa chỉ email bằng mã OTP.",
    "Verify Phone": "Xác thực số điện thoại bằng mã OTP.",
    "Update FCM Token": "Cập nhật token FCM để nhận thông báo đẩy (Push Notification).",
    
    # Reports
    "List Reports": "Lấy danh sách các phản ánh với bộ lọc (trạng thái, danh mục, tìm kiếm...).",
    "Create Report": "Tạo một phản ánh mới. Yêu cầu upload media trước để lấy ID.",
    "Get My Reports": "Lấy danh sách các phản ánh do chính người dùng tạo.",
    "Get Nearby Reports": "Tìm các phản ánh xung quanh một vị trí GPS trong bán kính nhất định.",
    "Get Trending Reports": "Lấy các phản ánh đang được quan tâm nhiều nhất (nhiều lượt xem/ủng hộ).",
    "Get Report Detail": "Xem thông tin chi tiết của một phản ánh cụ thể.",
    "Update Report": "Cập nhật nội dung phản ánh (chỉ người tạo mới có quyền).",
    "Delete Report": "Xóa phản ánh (chỉ người tạo mới có quyền và khi chưa được xử lý).",
    "Vote Report": "Bình chọn (Ủng hộ/Không ủng hộ) cho một phản ánh.",
    "Increment View": "Tăng lượt xem cho phản ánh (dùng khi người dùng mở xem chi tiết).",
    "Rate Report": "Đánh giá mức độ hài lòng về kết quả xử lý phản ánh (chỉ người tạo).",
    
    # Comments
    "Get Report Comments": "Lấy danh sách bình luận của một phản ánh.",
    "Create Comment": "Thêm bình luận mới vào phản ánh.",
    "Update Comment": "Chỉnh sửa nội dung bình luận (chỉ người viết).",
    "Delete Comment": "Xóa bình luận (chỉ người viết).",
    "Like Comment": "Thích một bình luận.",
    "Unlike Comment": "Bỏ thích một bình luận.",
    
    # Media
    "Upload Media": "Upload file ảnh hoặc video lên server. Trả về ID để dùng cho tạo phản ánh.",
    "Get My Media": "Lấy danh sách các file media đã upload của người dùng.",
    "Get Media Detail": "Xem thông tin chi tiết của một file media.",
    "Delete Media": "Xóa file media (chỉ người upload).",
    
    # Map
    "Get Reports on Map": "Lấy dữ liệu GeoJSON của các phản ánh để hiển thị trên bản đồ.",
    "Get Heatmap Data": "Lấy dữ liệu mật độ phản ánh để vẽ biểu đồ nhiệt (Heatmap).",
    "Get Map Clusters": "Lấy dữ liệu gom nhóm (Cluster) các phản ánh khi zoom out.",
    "Get GTFS Routes": "Lấy thông tin lộ trình phương tiện công cộng (nếu có).",
    
    # Agencies
    "List Agencies": "Lấy danh sách các cơ quan chức năng/đơn vị xử lý.",
    "Get Agency Detail": "Xem thông tin chi tiết của một cơ quan.",
    "Get Agency Reports": "Lấy danh sách phản ánh thuộc thẩm quyền của cơ quan.",
    "Get Agency Stats": "Xem thống kê hiệu quả xử lý của cơ quan.",
    
    # User Stats
    "Get User Stats": "Xem thống kê hoạt động của cá nhân (số phản ánh, điểm tin cậy...).",
    "Get Leaderboard": "Xem bảng xếp hạng người dùng tích cực.",
    "Get User Activities": "Xem lịch sử hoạt động của người dùng.",
    
    # Wallet
    "Get Wallet Info": "Xem thông tin ví và số dư CityPoints.",
    "Get Transactions": "Xem lịch sử giao dịch/biến động số dư.",
    "Deposit Points": "Nạp điểm vào ví (Mô phỏng).",
    "Withdraw Points": "Rút điểm/Đổi quà (Mô phỏng).",
    
    # Notifications
    "List Notifications": "Lấy danh sách thông báo của người dùng.",
    "Mark as Read": "Đánh dấu một thông báo là đã đọc.",
    "Mark All as Read": "Đánh dấu tất cả thông báo là đã đọc.",
    "Delete Notification": "Xóa một thông báo.",
    
    # Categories & Priorities
    "List Categories": "Lấy danh sách danh mục phản ánh (Giao thông, Môi trường...).",
    "List Priorities": "Lấy danh sách mức độ ưu tiên.",
    
    # Weather
    "Get Current Weather": "Lấy thông tin thời tiết hiện tại tại vị trí.",
    "Get Weather Forecast": "Lấy dự báo thời tiết cho các ngày tới."
}

def update_item(item):
    # Update Request Description
    if 'request' in item:
        req_name = item.get('name', '')
        
        # Tìm description phù hợp
        desc = ENDPOINT_DESCRIPTIONS.get(req_name)
        if desc:
            item['request']['description'] = desc
            
        # Update Query Params Descriptions
        if 'url' in item['request'] and isinstance(item['request']['url'], dict) and 'query' in item['request']['url']:
            for param in item['request']['url']['query']:
                key = param.get('key')
                if key in PARAM_DESCRIPTIONS:
                    param['description'] = PARAM_DESCRIPTIONS[key]

        # Update Body Params Descriptions (form-data)
        if 'body' in item['request'] and item['request']['body'].get('mode') == 'formdata':
            for param in item['request']['body'].get('formdata', []):
                key = param.get('key')
                if key in PARAM_DESCRIPTIONS:
                    param['description'] = PARAM_DESCRIPTIONS[key]
                    
        # Update Body Params Descriptions (raw/json) - Append to main description
        if 'body' in item['request'] and item['request']['body'].get('mode') == 'raw':
            try:
                raw_body = item['request']['body']['raw']
                # Simple heuristic to find keys in JSON body
                # Note: This is not a full JSON parser for the body string, just a helper
                # to append field info to the main description if not already there.
                
                # Create a parameters table for the description
                param_table = "\n\n**Chi tiết tham số (Body):**\n| Tham số | Mô tả |\n|---|---|\n"
                has_params = False
                
                for key, val in PARAM_DESCRIPTIONS.items():
                    if f'"{key}"' in raw_body:
                        param_table += f"| `{key}` | {val} |\n"
                        has_params = True
                
                if has_params:
                    current_desc = item['request'].get('description', '')
                    if "**Chi tiết tham số (Body):**" not in current_desc:
                        item['request']['description'] = current_desc + param_table
                        
            except Exception:
                pass

    # Recursive for folders
    if 'item' in item:
        for sub_item in item['item']:
            update_item(sub_item)

def main():
    print(f"Reading collection from: {COLLECTION_PATH}")
    try:
        with open(COLLECTION_PATH, 'r', encoding='utf-8') as f:
            data = json.load(f)
            
        # Update collection info description
        data['info']['description'] = (
            "**CityResQ360 API Collection - Phiên bản 1.0**\n\n"
            "Tài liệu chi tiết API cho ứng dụng CityResQ360.\n"
            "Bao gồm đầy đủ mô tả Tiếng Việt cho các Endpoints và Tham số.\n\n"
            "**Tài liệu tham khảo:** Xem file `docs/API_DOCUMENTATION.md` trong source code.\n\n"
            "**Authentication:** Sử dụng Bearer Token (Laravel Sanctum).\n"
        )
            
        # Process items
        for item in data.get('item', []):
            update_item(item)
            
        # Save back
        with open(COLLECTION_PATH, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=4, ensure_ascii=False)
            
        print("✅ Đã cập nhật Postman Collection thành công!")
        
    except Exception as e:
        print(f"❌ Lỗi: {str(e)}")

if __name__ == "__main__":
    main()

# Hướng dẫn đóng góp chi tiết

# Hướng dẫn đóng góp

Cảm ơn bạn đã quan tâm đến việc đóng góp cho RELIEFLINK! 

## Các cách đóng góp

1. Báo cáo lỗi (Bug reports)
2. Đề xuất tính năng mới (Feature requests)
3. Sửa lỗi và cải thiện code
4. Cải thiện tài liệu
5. Dịch tài liệu sang ngôn ngữ khác

## Quy trình làm việc với code

### Chuẩn bị môi trường
1. Fork repository
2. Clone repository đã fork về máy local
3. Cài đặt các công cụ cần thiết:
   - Appsmith CLI
   - MongoDB Compass (để test database)
   - VS Code với các extensions cho JSON/Markdown

### Phát triển
1. Tạo branch mới cho tính năng/fix:
   ```bash
   git checkout -b feature/name
   # hoặc
   git checkout -b fix/issue-number
   ```

2. Viết code và test:
   - Tuân thủ coding style
   - Test kỹ trước khi commit
   - Viết test cases nếu cần thiết

3. Commit changes:
   ```bash
   git add .
   git commit -m "feat/fix: mô tả ngắn gọn"
   ```

4. Push và tạo Pull Request:
   ```bash
   git push origin feature/name
   ```

### Review process
1. Maintainers sẽ review PR của bạn
2. Có thể cần chỉnh sửa theo yêu cầu
3. Sau khi được approve, PR sẽ được merge

## Test

### Unit Tests
```bash
npm run test
```

### Integration Tests
```bash
npm run test:integration
```

### E2E Tests
```bash
npm run test:e2e
```

## Style Guide

### Commit Messages
- feat: Thêm tính năng mới
- fix: Sửa lỗi
- docs: Thay đổi documentation
- style: Format, thiếu dấu chấm phẩy,...
- refactor: Refactor code
- test: Thêm test cases
- chore: Cập nhật build tasks, package manager,...

### Code Style
- Sử dụng 2 spaces cho indentation
- Dòng không quá 80 ký tự
- Đặt tên biến/hàm rõ ràng, có ý nghĩa
- Comment code khi cần thiết 
# 📝 Commit Message Convention

Project này sử dụng **Conventional Commits** để tự động generate CHANGELOG.

## Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

## Types

| Type | Icon | Mô tả | Ví dụ |
|------|------|-------|-------|
| **feat** | ✨ | Tính năng mới | `feat: thêm đăng nhập Google` |
| **fix** | 🐛 | Sửa lỗi | `fix: lỗi không load ảnh` |
| **perf** | ⚡ | Cải thiện performance | `perf: optimize database queries` |
| **refactor** | ♻️ | Refactor code | `refactor: restructure repository` |
| **docs** | 📚 | Documentation | `docs: update README` |
| **style** | 💄 | Code style (format, spacing) | `style: format code with prettier` |
| **test** | ✅ | Thêm/sửa tests | `test: add auth tests` |
| **build** | 🔨 | Build system | `build: update dependencies` |
| **ci** | 👷 | CI/CD config | `ci: add GitHub Actions` |
| **chore** | 🔧 | Maintenance tasks | `chore: update config` |
| **revert** | ⏪ | Revert commit | `revert: revert "feat: add feature"` |

## Scope (Optional)

Phạm vi của thay đổi, ví dụ:
- `api`, `mobile`, `admin`, `auth`, `wallet`, `notification`, etc.

```bash
feat(auth): thêm 2FA authentication
fix(mobile): lỗi crash khi upload ảnh
docs(api): cập nhật API documentation
```

## Subject

- Viết ngắn gọn, dưới 50 ký tự
- Không viết hoa chữ cầu đầu
- Không có dấu chấm cuối câu
- Dùng imperative mood (thêm, sửa, update)

### ✅ Good Examples

```bash
feat: thêm chức năng đăng nhập bằng Google
fix: sửa lỗi không hiển thị ảnh đại diện
perf: tối ưu query database cho reports
docs: cập nhật hướng dẫn cài đặt Docker
refactor: tổ chức lại cấu trúc thư mục
```

### ❌ Bad Examples

```bash
Thêm tính năng login  # Không có type
feat: Thêm login      # Viết hoa chữ cầu đầu
fix: bug.             # Có dấu chấm cuối
feat: thêm login, sửa bug, update docs  # Quá nhiều thứ trong 1 commit
```

## Body (Optional)

Chi tiết hơn về thay đổi, giải thích **WHY** thay vì **WHAT**.

```bash
feat: thêm tích hợp Oxylabs proxy

Tích hợp Oxylabs Web Unblocker để bypass rate limiting.
Sử dụng session ID để duy trì IP cho mỗi voting session.

Closes #123
```

## Footer (Optional)

- **Breaking changes**: `BREAKING CHANGE: <description>`
- **Issue references**: `Closes #123`, `Fixes #456`

```bash
feat: restructure repository

BREAKING CHANGE: All services moved to modules/ directory.
Developers must rebuild containers after pulling.

Closes #789
```

## Breaking Changes

Dùng `BREAKING CHANGE:` trong footer hoặc thêm `!` sau type:

```bash
feat!: thay đổi API endpoint structure

BREAKING CHANGE: API endpoints đã được đổi từ /api/v1 sang /v2
```

## Automatic Versioning

Commits sẽ tự động bump version:

- `feat:` → **Minor version** (1.0.0 → 1.1.0)
- `fix:` → **Patch version** (1.0.0 → 1.0.1)
- `BREAKING CHANGE:` → **Major version** (1.0.0 → 2.0.0)

## Tools

### Commit với helper

```bash
# Install commitizen
npm install -g commitizen cz-conventional-changelog

# Setup
echo '{ "path": "cz-conventional-changelog" }' > ~/.czrc

# Commit
git cz
```

### Validate commits

```bash
# Install commitlint
npm install -g @commitlint/cli @commitlint/config-conventional

# Validate last commit
commitlint --from HEAD~1 --to HEAD --verbose
```

## References

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)
- [Keep a Changelog](https://keepachangelog.com/)

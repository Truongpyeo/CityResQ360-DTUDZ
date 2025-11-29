# Git Workflow & Release Scripts

Hướng dẫn sử dụng scripts tự động hóa quy trình Git và Release cho dự án CityResQ360-DTUDZ.

---

## 📁 Scripts Có Sẵn

### 1. **Development Scripts** (Hàng ngày)
- [`scripts/git/git-push.bat`](../scripts/git/git-push.bat) - Windows CMD
- [`scripts/git/git-push.ps1`](../scripts/git/git-push.ps1) - Windows PowerShell
- [`scripts/git/git-push.sh`](../scripts/git/git-push.sh) - Mac/Linux

### 2. **Release Scripts** (1-2 tuần/lần)
- [`scripts/git/create-release.ps1`](../scripts/git/create-release.ps1) - Windows PowerShell
- [`scripts/git/create-release.sh`](../scripts/git/create-release.sh) - Mac/Linux

---

## 🌳 GitFlow Workflow

```
feature/xyz  →  git-push  →  develop  →  create-release  →  master (tagged)
    ↓                          ↓                              ↓
  Daily                    Testing                       Production
```

---

## 🚀 Development Workflow (Hàng Ngày)

### **Bước 1: Tạo Feature Branch**
```bash
git checkout -b feature/new-dashboard
```

### **Bước 2: Code & Commit**
```bash
# Code, code, code...
```

### **Bước 3: Push & Merge to Develop**

**Windows:**
```powershell
.\scripts\git\git-push.ps1
```

**Mac/Linux:**
```bash
./scripts/git/git-push.sh
```

**Script sẽ:**
1. ✅ Hỏi commit type (feat, fix, docs, etc.)
2. ✅ Nhập commit message
3. ✅ Tự động commit theo Conventional Commits
4. ✅ Push lên origin
5. ✅ **Tự động merge vào develop**

---

## 📦 Release Workflow (1-2 Tuần/Lần)

Khi develop đã ổn định và sẵn sàng release:

### **Chạy Release Script**

**Windows:**
```powershell
.\scripts\git\create-release.ps1
```

**Mac/Linux:**
```bash
./scripts/git/create-release.sh
```

### **Script Tự Động:**

1. ✅ Checkout develop & pull latest
2. ✅ Checkout master & pull latest
3. ✅ **Merge develop → master**
4. ✅ Push master
5. ✅ Detect tag hiện tại
6. ✅ **Đề xuất version mới**
7. ✅ **Auto-generate release notes từ commits**
8. ✅ **Update CHANGELOG.md với timestamp**
9. ✅ Create & push git tag
10. ✅ Return về branch gốc

---

## 🎯 Version Bump Options

Script sẽ hỏi bạn chọn:

### **1. PATCH (v1.0.2 → v1.0.3)**
**Khi:** Chỉ bug fixes

**Commits:**
```
fix: resolve upload error
fix: correct validation
```

### **2. MINOR (v1.0.2 → v1.1.0)** ⭐ Phổ biến
**Khi:** Có features mới

**Commits:**
```
feat: add dashboard
feat: implement export
fix: minor bugs
```

### **3. MAJOR (v1.0.2 → v2.0.0)**
**Khi:** Breaking changes

**Commits:**
```
feat!: change API to OAuth2
BREAKING CHANGE: Remove old endpoints
```

---

## 📝 Auto-Generated CHANGELOG

Script tự động tạo CHANGELOG theo format:

```markdown
## 30/11/2024 - 01h39

### Sprint 5 Release

**✨ New Features:**
- Add analytics dashboard
- Implement real-time stats

**🐛 Bug Fixes:**
- Resolve API validation errors
- Fix media upload issues

**📚 Documentation:**
- Update README
- Add API docs

**Technical Details:**
- Tag: v1.1.0
- Commits: 47
- Released from: master branch
- Release URL: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/v1.1.0

---
```

---

## 🎬 Demo Sử Dụng

### **Example: Daily Development**

```powershell
PS> .\scripts\git\git-push.ps1

Select commit type:
1) feat      - New feature
2) fix       - Bug fix
...

Enter choice [1-10]: 1

Scope (optional): dashboard

Commit header:
> Add analytics widget

Commit body (optional):
> Implemented real-time analytics
> Added chart visualizations
>

Continue? [Y/n]: Y

[OK] Committed: feat(dashboard): Add analytics widget
[*] Pushing to feature/dashboard...
[*] Merging into develop...
[OK] All done!
```

### **Example: Release**

```powershell
PS> .\scripts\git\create-release.ps1

[*] Merging develop into master...
[OK] Merge successful!

Current version: v1.0.2

Select version bump:
1) PATCH   (v1.0.2 → v1.0.3)
2) MINOR   (v1.0.2 → v1.1.0)
3) MAJOR   (v1.0.2 → v2.0.0)

Select [1-5]: 2

[*] New version: v1.1.0

[*] Auto-generating release notes from commits...
[*] Found 47 commits

Enter release title (or press Enter for auto-title):
> Sprint 5 - Dashboard & Analytics

====================================
 CHANGELOG Preview
====================================

## 30/11/2024 - 01h39

### Sprint 5 - Dashboard & Analytics

**✨ New Features:**
- Add analytics dashboard
- Implement export to PDF
...

Create release? [Y/n]: Y

[OK] Release v1.1.0 Created!
View at: https://github.com/.../releases/tag/v1.1.0
```

---

## ⚙️ Conventional Commits

Scripts sử dụng [Conventional Commits](https://www.conventionalcommits.org/):

### **Format:**
```
<type>(<scope>): <subject>

<body>

<footer>
```

### **Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style
- `refactor`: Code refactoring
- `perf`: Performance
- `test`: Tests
- `build`: Build system
- `ci`: CI/CD
- `chore`: Maintenance

### **Examples:**
```
feat(auth): add OAuth2 authentication
fix(api): resolve validation error
docs: update README
feat(dashboard)!: redesign UI

BREAKING CHANGE: Old dashboard removed
```

---

## 🔧 Troubleshooting

### **Merge Conflict khi Release**
```
[X] Merge failed! Please resolve conflicts manually.
```

**Giải quyết:**
```bash
# Script dừng, bạn đang ở master
git status

# Fix conflicts
git add .
git commit

# Chạy lại script
.\scripts\git\create-release.ps1
```

### **Xóa Tag Nhầm**
```bash
# Local
git tag -d v1.1.0

# Remote
git push origin :refs/tags/v1.1.0
```

---

## 📅 Lịch Khuyến Nghị

### **Sprint-based (2 tuần):**
```
Week 1-2: Development
  - Daily: git-push → develop
  
Week 2 end: Release
  - create-release → v1.1.0 on master

Week 3-4: Development
  - Daily: git-push → develop
  
Week 4 end: Release
  - create-release → v1.2.0 on master
```

### **Hotfix (Urgent):**
```
Critical bug found:
  1. Fix on develop
  2. Test OK
  3. create-release (PATCH: v1.1.0 → v1.1.1)
```

---

## 📊 Benefits

### **git-push Scripts:**
- ✅ Conventional Commits tự động
- ✅ Auto-merge to develop
- ✅ Consistent commit format
- ✅ Giảm lỗi manual

### **create-release Scripts:**
- ✅ Auto-merge develop → master
- ✅ Auto-detect version
- ✅ **Auto-generate release notes**
- ✅ **Auto-update CHANGELOG.md**
- ✅ Semantic versioning
- ✅ GitHub Release tự động

---

## 🎯 Best Practices

### **DO:**
- ✅ Test kỹ trên develop trước release
- ✅ Viết commit messages rõ ràng
- ✅ Release định kỳ (1-2 tuần)
- ✅ Sử dụng MINOR cho features mới
- ✅ Sử dụng PATCH cho bug fixes

### **DON'T:**
- ❌ Release khi develop có bugs
- ❌ Skip testing
- ❌ Merge develop → master thủ công
- ❌ Tạo tags không có quy tắc

---

## 📚 Tài Liệu Liên Quan

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)
- [GitFlow Workflow](https://www.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow)
- [Keep a Changelog](https://keepachangelog.com/)

---

## 🆘 Support

Nếu gặp vấn đề, tạo issue tại:
[GitHub Issues](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/issues)

---

**Generated:** 2025-11-30  
**Version:** 2.0  
**Maintainer:** Development Team

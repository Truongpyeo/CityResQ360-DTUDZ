# Conventional Commits Guide

## 📋 **Commit Message Format**

```
<type>(<scope>): <subject>

[optional body]

[optional footer]
```

### **Examples:**
```bash
feat(auth): implement login functionality
fix(deploy): generate APP_KEY correctly
docs(readme): update installation guide
chore: remove unused files and add gitignore
```

---

## 🏷️ **Types**

| Type | Description | Example |
|------|-------------|---------|
| **feat** | New feature | `feat(auth): implement login` |
| **fix** | Bug fix | `fix(deploy): generate APP_KEY correctly` |
| **docs** | Documentation changes | `docs(readme): update installation guide` |
| **style** | Code formatting (no logic change) | `style(api): format code` |
| **refactor** | Code refactoring | `refactor(auth): simplify logic` |
| **test** | Add or update tests | `test(api): add unit tests` |
| **chore** | Maintenance tasks | `chore(deps): update dependencies` |
| **perf** | Performance improvement | `perf(db): optimize queries` |
| **ci** | CI/CD changes | `ci: update GitHub Actions` |
| **build** | Build system changes | `build: update Dockerfile` |
| **revert** | Revert previous commit | `revert: rollback feature X` |

---

## 🛠️ **Setup Tools**

### **Option 1: VSCode Extension (Recommended)**

1. Install extension: `vivaxy.vscode-conventional-commits`
2. Use: `Ctrl+Shift+P` → "Conventional Commits"
3. Fill in form → Commit!

### **Option 2: Commitizen CLI**

```bash
# Install dependencies
npm install

# Use interactive commit
npm run commit

# Or use git-cz directly
npx git-cz
```

### **Option 3: Commitlint + Husky (Auto-validation)**

```bash
# Install dependencies
npm install

# Setup husky
npm run prepare

# Now commits will be validated automatically
git commit -m "invalid commit"  # ❌ Will fail
git commit -m "feat: valid commit"  # ✅ Will pass
```

---

## 📝 **Best Practices**

1. **Use lowercase** for type and scope
2. **No period** at the end of subject
3. **Keep subject under 100 characters**
4. **Use imperative mood**: "add feature" not "added feature"
5. **Separate subject from body** with blank line

### **Good Examples:**
```bash
✅ feat(api): add user authentication endpoint
✅ fix(docker): resolve redis connection timeout
✅ docs: update deployment instructions
✅ chore(deps): upgrade Laravel to 11.x
```

### **Bad Examples:**
```bash
❌ Added new feature  # Missing type
❌ FEAT: new feature  # Type should be lowercase
❌ feat: Added new feature.  # Period at end, past tense
❌ update  # Too vague, missing type
```

---

## 🔗 **References**

- [Conventional Commits Specification](https://www.conventionalcommits.org/)
- [Angular Commit Guidelines](https://github.com/angular/angular/blob/main/CONTRIBUTING.md#commit)


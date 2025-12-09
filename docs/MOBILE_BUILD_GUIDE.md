# 📱 Hướng Dẫn Build Production - CityResQ360 Mobile App

> Hướng dẫn chi tiết cách build và release ứng dụng CityResQ360 cho iOS và Android

---

## 📋 Mục Lục

- [Yêu Cầu](#-yêu-cầu)
- [Chuẩn Bị Trước Khi Build](#-chuẩn-bị-trước-khi-build)
- [Build Android APK/AAB](#-build-android-apkaab)
- [Build iOS IPA](#-build-ios-ipa)
- [Code Signing](#-code-signing)
- [Release & Distribution](#-release--distribution)
- [Troubleshooting](#-troubleshooting)

---

## 🔧 Yêu Cầu

### Cho Android Build

| Yêu cầu            | Phiên bản | Ghi chú                 |
| ------------------ | --------- | ----------------------- |
| **JDK**            | 17+       | OpenJDK hoặc Oracle JDK |
| **Android Studio** | Latest    | Bao gồm Android SDK     |
| **Android SDK**    | API 33+   | Target SDK 34           |
| **Gradle**         | 8.0+      | Đi kèm với project      |
| **Node.js**        | 20.0+     | Để build JS bundle      |

### Cho iOS Build

| Yêu cầu                     | Phiên bản | Ghi chú                |
| --------------------------- | --------- | ---------------------- |
| **macOS**                   | 12.0+     | Bắt buộc cho iOS build |
| **Xcode**                   | 15.0+     | Từ App Store           |
| **CocoaPods**               | 1.12+     | Dependency manager     |
| **Apple Developer Account** | -         | Để code signing        |
| **Node.js**                 | 20.0+     | Để build JS bundle     |

---

## 🎯 Chuẩn Bị Trước Khi Build

### 1. Cấu Hình Environment Variables

Tạo file `env.ts` từ template:

```bash
cd modules/AppMobile/CityResQ360App/src/config
cp env.example.ts env.ts
```

Cập nhật `env.ts` với thông tin production:

```typescript
export default {
  // API Configuration
  API_URL: "https://api.cityresq360.io.vn",

  // Reverb WebSocket Configuration
  REVERB_APP_ID: "your_production_app_id",
  REVERB_APP_KEY: "your_production_app_key",
  REVERB_APP_SECRET: "your_production_app_secret",
  REVERB_HOST: "api.cityresq360.io.vn",
  REVERB_PORT: "443",
  REVERB_SCHEME: "https",

  // MapTiler Configuration
  MAPTILER_API_KEY: "your_production_maptiler_key",

  // Environment
  ENV: "production",
};
```

### 2. Cập Nhật Version

#### Android - `android/app/build.gradle`

```gradle
android {
    defaultConfig {
        applicationId "com.cityresq360"
        minSdkVersion 24
        targetSdkVersion 34
        versionCode 1          // Tăng mỗi lần release
        versionName "1.0.0"    // Semantic versioning
    }
}
```

#### iOS - `ios/CityResQ360App/Info.plist`

```xml
<key>CFBundleShortVersionString</key>
<string>1.0.0</string>
<key>CFBundleVersion</key>
<string>1</string>
```

### 3. Cài Đặt Dependencies

```bash
cd modules/AppMobile

# Install npm packages
npm install

# iOS only - Install CocoaPods
cd ios
pod install
cd ..
```

---

## 🤖 Build Android APK/AAB

### Bước 1: Generate Signing Key

**Tạo keystore file** (chỉ làm 1 lần, lưu trữ cẩn thận):

```bash
cd modules/AppMobile/android/app

keytool -genkeypair -v -storetype PKCS12 \
  -keystore cityresq360-release.keystore \
  -alias cityresq360-key \
  -keyalg RSA \
  -keysize 2048 \
  -validity 10000
```

**Thông tin cần nhập:**

- Keystore password: `[Tạo password mạnh]`
- Key password: `[Tạo password mạnh]`
- First and Last Name: `CityResQ360`
- Organizational Unit: `DTU-DZ`
- Organization: `Duy Tan University`
- City: `Da Nang`
- State: `Da Nang`
- Country Code: `VN`

> ⚠️ **QUAN TRỌNG**: Lưu trữ file `.keystore` và passwords an toàn. Nếu mất, không thể update app trên Google Play!

### Bước 2: Cấu Hình Gradle

Tạo file `android/gradle.properties` (hoặc cập nhật):

```properties
# Signing Config
CITYRESQ_UPLOAD_STORE_FILE=cityresq360-release.keystore
CITYRESQ_UPLOAD_KEY_ALIAS=cityresq360-key
CITYRESQ_UPLOAD_STORE_PASSWORD=your_keystore_password
CITYRESQ_UPLOAD_KEY_PASSWORD=your_key_password
```

> 🔒 **Bảo mật**: Không commit file này lên Git! Thêm vào `.gitignore`

Cập nhật `android/app/build.gradle`:

```gradle
android {
    ...
    signingConfigs {
        release {
            if (project.hasProperty('CITYRESQ_UPLOAD_STORE_FILE')) {
                storeFile file(CITYRESQ_UPLOAD_STORE_FILE)
                storePassword CITYRESQ_UPLOAD_STORE_PASSWORD
                keyAlias CITYRESQ_UPLOAD_KEY_ALIAS
                keyPassword CITYRESQ_UPLOAD_KEY_PASSWORD
            }
        }
    }
    buildTypes {
        release {
            signingConfig signingConfigs.release
            minifyEnabled true
            proguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'
        }
    }
}
```

### Bước 3: Build APK (Debug/Testing)

```bash
cd modules/AppMobile

# Build APK
cd android
./gradlew assembleRelease
cd ..

# APK output location:
# android/app/build/outputs/apk/release/app-release.apk
```

**Kiểm tra APK:**

```bash
# Xem thông tin APK
aapt dump badging android/app/build/outputs/apk/release/app-release.apk

# Cài đặt trên device
adb install android/app/build/outputs/apk/release/app-release.apk
```

### Bước 4: Build AAB (Google Play)

```bash
cd modules/AppMobile

# Build Android App Bundle
cd android
./gradlew bundleRelease
cd ..

# AAB output location:
# android/app/build/outputs/bundle/release/app-release.aab
```

**AAB vs APK:**

- **AAB**: Upload lên Google Play Console (khuyến nghị)
- **APK**: Phân phối trực tiếp (sideload)

### Bước 5: Tối Ưu Hóa Build

#### Enable Proguard (Minify Code)

File `android/app/proguard-rules.pro`:

```proguard
# Keep React Native
-keep class com.facebook.react.** { *; }
-keep class com.facebook.hermes.** { *; }

# Keep app classes
-keep class com.cityresq360.** { *; }

# Keep native methods
-keepclasseswithmembernames class * {
    native <methods>;
}
```

#### Enable Hermes Engine

File `android/app/build.gradle`:

```gradle
project.ext.react = [
    enableHermes: true  // Tăng performance
]
```

---

## 🍎 Build iOS IPA

### Bước 1: Cấu Hình Xcode Project

```bash
cd modules/AppMobile

# Open workspace
open ios/CityResQ360App.xcworkspace
```

**Trong Xcode:**

1. **Chọn project** → `CityResQ360App`
2. **General tab:**
   - Bundle Identifier: `com.cityresq360.app`
   - Version: `1.0.0`
   - Build: `1`
3. **Signing & Capabilities:**
   - Team: Chọn Apple Developer Team
   - Signing Certificate: Chọn certificate
   - Provisioning Profile: Chọn profile

### Bước 2: Certificates & Provisioning Profiles

#### Tạo App ID (Apple Developer Portal)

1. Truy cập: https://developer.apple.com/account
2. **Certificates, IDs & Profiles** → **Identifiers**
3. **+ New App ID**
   - Description: `CityResQ360`
   - Bundle ID: `com.cityresq360.app`
   - Capabilities: Push Notifications, Maps, Location

#### Tạo Distribution Certificate

```bash
# Generate CSR (Certificate Signing Request)
# Keychain Access → Certificate Assistant → Request a Certificate from a Certificate Authority
```

Upload CSR lên Apple Developer Portal → Download certificate → Double click để install

#### Tạo Provisioning Profile

1. **Profiles** → **+ New Profile**
2. **Distribution** → **App Store**
3. Chọn App ID: `com.cityresq360.app`
4. Chọn Certificate vừa tạo
5. Download và double click để install

### Bước 3: Build Archive

#### Cách 1: Xcode GUI

1. **Product** → **Scheme** → **Edit Scheme**
2. **Run** → **Build Configuration** → **Release**
3. **Product** → **Archive**
4. Đợi build hoàn tất (5-10 phút)
5. **Organizer** window sẽ mở → Chọn archive → **Distribute App**

#### Cách 2: Command Line

```bash
cd modules/AppMobile/ios

# Build archive
xcodebuild -workspace CityResQ360App.xcworkspace \
  -scheme CityResQ360App \
  -configuration Release \
  -archivePath build/CityResQ360App.xcarchive \
  archive

# Export IPA
xcodebuild -exportArchive \
  -archivePath build/CityResQ360App.xcarchive \
  -exportPath build \
  -exportOptionsPlist ExportOptions.plist
```

**Tạo file `ExportOptions.plist`:**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>method</key>
    <string>app-store</string>
    <key>teamID</key>
    <string>YOUR_TEAM_ID</string>
    <key>uploadBitcode</key>
    <false/>
    <key>uploadSymbols</key>
    <true/>
    <key>compileBitcode</key>
    <false/>
</dict>
</plist>
```

### Bước 4: Tối Ưu Hóa Build

#### Enable Hermes Engine

File `ios/Podfile`:

```ruby
use_react_native!(
  :path => config[:reactNativePath],
  :hermes_enabled => true  # Enable Hermes
)
```

Sau đó:

```bash
cd ios
pod install
cd ..
```

#### Optimize Images

```bash
# Install ImageOptim
brew install imageoptim-cli

# Optimize all images
imageoptim --directory ios/CityResQ360App/Images.xcassets
```

---

## 🔐 Code Signing

### Android Code Signing

**Verify Signature:**

```bash
jarsigner -verify -verbose -certs android/app/build/outputs/apk/release/app-release.apk
```

**View Certificate:**

```bash
keytool -list -v -keystore android/app/cityresq360-release.keystore
```

### iOS Code Signing

**Verify Signature:**

```bash
codesign -dv --verbose=4 ios/build/CityResQ360App.ipa
```

**Check Provisioning Profile:**

```bash
security cms -D -i ~/Library/MobileDevice/Provisioning\ Profiles/[profile-uuid].mobileprovision
```

---

## 📦 Release & Distribution

### Android - Google Play Console

1. **Tạo App**: https://play.google.com/console
2. **Upload AAB**: Production → Create new release
3. **Fill Store Listing:**
   - App name: `CityResQ360`
   - Short description: `Hệ thống quản lý sự cố đô thị thông minh`
   - Full description: [Chi tiết về app]
   - Screenshots: 2-8 screenshots
   - Feature graphic: 1024x500px
4. **Content Rating**: Complete questionnaire
5. **Pricing**: Free
6. **Submit for Review**

### iOS - App Store Connect

1. **Tạo App**: https://appstoreconnect.apple.com
2. **Upload IPA**:
   - Xcode → Organizer → Distribute App → App Store Connect
   - Hoặc dùng Transporter app
3. **Fill App Information:**
   - Name: `CityResQ360`
   - Subtitle: `Quản lý sự cố đô thị`
   - Description: [Chi tiết về app]
   - Keywords: `smart city, incident, emergency`
   - Screenshots: 6.5", 5.5" (iPhone), 12.9" (iPad)
4. **Pricing**: Free
5. **Submit for Review**

### Direct Distribution (APK)

**Tạo QR Code:**

```bash
# Upload APK lên server
scp android/app/build/outputs/apk/release/app-release.apk user@server:/var/www/downloads/

# Generate QR code
qrencode -o qr-android.png "https://cityresq360.io.vn/downloads/app-release.apk"
```

---

## 🐛 Troubleshooting

### Android Build Issues

#### Error: "Execution failed for task ':app:bundleReleaseJsAndAssets'"

```bash
# Clear cache
cd android
./gradlew clean
cd ..

# Rebuild
cd android
./gradlew bundleRelease
```

#### Error: "Could not find or load main class org.gradle.wrapper.GradleWrapperMain"

```bash
cd android
gradle wrapper
./gradlew clean
```

#### Error: "Duplicate resources"

```bash
# Clean build
cd android
./gradlew clean
rm -rf build
rm -rf app/build
cd ..
```

### iOS Build Issues

#### Error: "No signing certificate found"

1. Xcode → Preferences → Accounts
2. Download Manual Profiles
3. Xcode → Project → Signing & Capabilities → Automatically manage signing

#### Error: "CocoaPods could not find compatible versions"

```bash
cd ios
rm -rf Pods
rm Podfile.lock
pod install --repo-update
cd ..
```

#### Error: "Build input file cannot be found"

```bash
cd ios
xcodebuild clean -workspace CityResQ360App.xcworkspace -scheme CityResQ360App
pod install
cd ..
```

### Performance Issues

#### Large APK/IPA Size

**Android:**

```gradle
// Enable APK splitting
android {
    splits {
        abi {
            enable true
            reset()
            include "armeabi-v7a", "arm64-v8a", "x86", "x86_64"
            universalApk false
        }
    }
}
```

**iOS:**

- Enable Bitcode (if supported)
- Use App Thinning (automatic on App Store)
- Optimize images

#### Slow Build Time

```bash
# Enable Gradle daemon
echo "org.gradle.daemon=true" >> android/gradle.properties

# Increase heap size
echo "org.gradle.jvmargs=-Xmx4096m -XX:MaxPermSize=512m" >> android/gradle.properties
```

---

## 📊 Build Checklist

### Pre-Build

- [ ] Update version numbers (versionCode, versionName, CFBundleVersion)
- [ ] Update environment variables (API URLs, keys)
- [ ] Test app thoroughly on devices
- [ ] Run linter and fix warnings
- [ ] Update CHANGELOG.md
- [ ] Create Git tag for release

### Build

- [ ] Clean build directories
- [ ] Build release APK/AAB (Android)
- [ ] Build release IPA (iOS)
- [ ] Verify signatures
- [ ] Test signed builds on real devices

### Post-Build

- [ ] Upload to Play Console / App Store Connect
- [ ] Fill store listings
- [ ] Upload screenshots
- [ ] Submit for review
- [ ] Monitor crash reports
- [ ] Respond to user reviews

---

## 🔗 Tài Liệu Tham Khảo

### Android

- [Publishing to Google Play](https://reactnative.dev/docs/signed-apk-android)
- [Android App Bundle](https://developer.android.com/guide/app-bundle)
- [ProGuard](https://developer.android.com/studio/build/shrink-code)

### iOS

- [Publishing to App Store](https://reactnative.dev/docs/publishing-to-app-store)
- [App Store Review Guidelines](https://developer.apple.com/app-store/review/guidelines/)
- [TestFlight](https://developer.apple.com/testflight/)

### React Native

- [React Native Build](https://reactnative.dev/docs/running-on-device)
- [Hermes Engine](https://reactnative.dev/docs/hermes)
- [Performance Optimization](https://reactnative.dev/docs/performance)

---

## 📞 Hỗ Trợ

Gặp vấn đề khi build? Liên hệ:

- **GitHub Issues**: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/issues
- **Email Team**:
  - Lê Thanh Trường: thanhtruong23111999@gmail.com
  - Nguyễn Văn Nhân: vannhan130504@gmail.com
  - Nguyễn Ngọc Duy Thái: kkdn011@gmail.com

---

**Chúc bạn build thành công! 🎉**

© 2025 CityResQ360 – DTU-DZ Team

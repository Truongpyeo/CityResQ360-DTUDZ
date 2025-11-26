# [](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/v1.0.1...v) (2025-11-26)


### Features

* Update Root Repo ([252715c](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/252715cd078188cdeb49eb551d9815cd9e0812ab))



# [1.0.0](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/a9585e5d3e275aa0ee68923e0cdf25ae943a8d6b...v1.0.0) (2025-11-25)


### Bug Fixes

* **coreapi:** Thêm PHP Redis extension vào Dockerfile ([e9a3109](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e9a31096dea6f70c052140ad4b37b3efbe2b04aa))
* **coreapi:** Thêm trustProxies và intl extension ([4549d60](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/4549d6083fc2ff7de876a64369add3e3acc1bb0d))
* **deploy:** Dùng php artisan key:generate trong container ([804bff9](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/804bff910d04ff37546dc163f7cbdd10701b6634))
* **deploy:** generate APP_KEY before starting containers to prevent missing key error ([7c3d218](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/7c3d218c2ded1132ebcf659a543842a737304bb4))
* **deploy:** Generate APP_KEY theo chuẩn Laravel base64 format ([667b15c](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/667b15cf6b0ee8b03e55587f404d995cee6b7779))
* **deploy:** reuse passwords from existing .env to prevent MySQL access denied ([0ae0ca3](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/0ae0ca3b6c910f54cb963a6dc94ba30f9725a527))
* **deploy:** Sửa APP_KEY generation giữ base64 hợp lệ ([d4dbac0](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/d4dbac0871d0a6c2d977b3616e816910ed7aa849))
* **deploy:** Sửa lỗi JWT_SECRET xuống dòng và ký tự đặc biệt ([c7903eb](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/c7903eb2ffa48ce5a145e3f9a9801137f85bd7a5))
* **deploy:** Sửa lỗi rsync và docker exec -it trong script tự động ([77a9f68](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/77a9f6896389cb36659d6a4c0e9ee981b8173d4e))
* **deploy:** Thay rsync bằng cp để tương thích Ubuntu ([74586ab](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/74586ab5dd4295ff5fa1d1ffbcaab550f9008213))
* **docker:** Bỏ mount .env file, dùng environment variables ([44f2766](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/44f2766d8c27d0cd134a3fc7356fa908c0f4a578))
* **docker:** Mount .env file và thêm APP_KEY cho CoreAPI container ([79dcf3f](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/79dcf3f54a1febcb21d2c90a207067a3ef648754))
* **docker:** Xóa duplicate volumes key gây lỗi YAML parse ([668bf83](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/668bf8307ee3d8e197198d32820909909c9b692a))
* **mysql:** Bỏ init.sql mount vì syntax không tương thích MySQL 8.0 ([fb31d5e](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/fb31d5ece3ac016bd68fcb5fd033ec4953101dd3))
* readme ([a9585e5](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/a9585e5d3e275aa0ee68923e0cdf25ae943a8d6b))




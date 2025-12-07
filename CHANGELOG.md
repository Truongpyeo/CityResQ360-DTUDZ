# [](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/v2.0.0...v) (2025-12-07)


### Bug Fixes

* **api:** admin FE ([28292b3](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/28292b36a3680a52b75fbb8d478f7824f23a34cb))
* **Deloy:** Sửa Build FE Admin ([45a0f4a](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/45a0f4a14831e3eb1423f5f43fd25b822df4adbb))
* **docker:** AIML Service external port: 8003 → 8013 ([f716b2e](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/f716b2eb50268d6c4b6c1f14a3d63ff48f9beaef))
* **docker:** sửa deployment và blade template errors ([5778b2c](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/5778b2c291fcc2273e93477a2011c32f47e2c314))
* **docker:** sửa deployment, blade templates và MQTT port conflict ([e48e46e](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e48e46ef98321224a23f830568a36f521782a3e9))
* **docker:** Sửa ver mongoDB ([3429373](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/34293737b0b2d3b6ad68f2b6fe74e06319cac425))
* **templates:** thêm PHP closing tag còn thiếu trong blade templates ([11fedea](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/11fedea20d1a74e26a03fd9ff2cb27fce84f95c9))


### Features

* **api:** Add frontend build step to deployment script ([7a61b13](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/7a61b136465a2085a086e78b5a6f184b2bc73c98))
* **service:** Cập nhật IncidentService với direct incident creation ([5ba7965](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/5ba79653115d226b6a2b516a6b71cc2d3f96e613))
* **service:** Thêm IncidentService vào web documentation với JWT authentication ([162b379](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/162b37927b380c03e77ee0ad9dd6aa7f0dff7f43))



# [2.0.0](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/v1.1.0...v2.0.0) (2025-12-04)


* fix(apip)!: liên kết media với report qua phan_anh_id ([db668b2](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/db668b204e04309ba55a5b24488ae1693a0edd9d))
* fix(docker)!: sửa lỗi deployment và thêm script rebuild an toàn ([b2a0c64](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/b2a0c64f01d5666be4e852529b85a6567b93e785))


### Bug Fixes

* add db credentials for media service ([a060847](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/a060847ed9ddbfd6ef0502623ad093657463ab54))
* **api:** chuyển đổi report api sang dùng id và cập nhật tài liệu ([7b9a9c6](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/7b9a9c6cb29dc59bdd7e320b87969bf65a0c2096))
* **api:** replace l5-swagger class constant with hardcoded value for docker build ([9d90b34](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/9d90b340df57d1b3c6c512e1daf33b50b478c95c))
* **api:** Sửa lỗi giao diện API Keys, xoá dependency lỗi & đồng bộ hạ tầng ([e52c848](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e52c8489c14e916a0eb452b35714d4926fc55393))
* **api:** Sửa lỗi Report API (SQL injection và priority mapping) ([2427602](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/242760233beb43769586029308f6ce7ab04b29e4))
* **api:** thêm 5 phương thức còn thiếu trong ReportController ([d65f96a](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/d65f96a0647b72820c45ea40d34912859eb55485))
* **docker:** Expose MinIO ports and update Nginx proxy config ([7197899](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/719789958bab03536d1e0189ac8af9f7da2bbeb7))
* **docker:** resolve port 8004 conflicts in production ([3b822ed](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/3b822ed767eaf5d2b090339005f5e551a3dcf8db))
* **docker:** Sửa docker composer ([39e49df](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/39e49df98deca5331b9999390f5b5f2e386d22e5))
* **media-service:** Bỏ IP whitelisting, chỉ giữ dual auth (Sanctum + JWT) ([bb0e24c](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/bb0e24c20163dd820b77fcfb1bca37ffb4fe1a22))
* **media-service:** Clean dual auth without IP whitelisting ([3d515ce](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/3d515cedb15403d456243156cc58b72782d4e023))
* **media-service:** Hash only plaintext part of Sanctum token ([d55420b](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/d55420b3538a393c7fb926f5d7a59cfdd6a8e84f))
* **media-service:** Remove non-existent revoked_at and expires_at columns ([144ed00](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/144ed00c2ee7c59b3ce9a1540869c2b6ec2ea34f))
* **media-service:** Return public URLs instead of internal MinIO URLs ([b6b0ac1](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/b6b0ac1b0db58c3d062a26aa5919d8bbd29f3728))
* **media:** Fix lỗi upload media và cài đặt sharp ([e0eca00](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e0eca005b96af259222dbec985d43423b395514a))
* **nginx:** Fix nginx default ([b2d544a](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/b2d544a61e47887212cb9a7da2424830f958622c))


### Features

* **deploy:** Thêm quản lý env tự động & chuẩn hóa biến MAIL_* ([a494741](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/a494741edfe1cde59db1e3393c081794268eed27))
* **deploy:** Thêm quản lý env tự động & chuẩn hóa biến MAIL_* ([c0ccdd2](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/c0ccdd23e1aca9586342b0942a7c70783574948d))
* **iot:** tích hợp hệ thống IoT và kiến trúc event-driven với RabbitMQ ([8ee53c2](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/8ee53c29b9d88370af2dde23a11835aecbb1750b))
* **media-service:** Implement dual authentication (Sanctum + JWT) với IP whitelisting ([ee9423e](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/ee9423e0279667e62b220eb976202cf20910379e))
* **service:** Thêm kiến trúc Hybrid cho MediaService với CORS và Nginx ([f5a62a3](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/f5a62a312b99615f3cb52b4850b3b885017dc2c2))
* update mobile ([5006bfe](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/5006bfec465dd19faad2685b8cb80d81f3147359))


### BREAKING CHANGES

* 
* Ports thay đổi cho media-service và iot-service



# [1.1.0](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/v1.0.5...v1.1.0) (2025-11-30)



## [1.0.5](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/v1.0.4...v1.0.5) (2025-11-30)


### Bug Fixes

* **api:** Fix Api Update Media, Mediaservice ([e51178e](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e51178e8a384bf9653593d4d1a59da371e46d893))
* **auto:** fix create-release.sh lần 2 ([44b66c4](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/44b66c4718eb72cfa0215ae5896e92590159804e))
* **auto:** Fix file create-release.sh ([5f585ef](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/5f585efbbe5cde70f10785084f0e2465e9e45884))
* **auto:** Sửa auto release cho .sh ([57984cf](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/57984cf632953cf341ad65a8a517c0a940aa807a))


### Features

* **test:** thêm script test tự động API production ([08444a9](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/08444a99cf92924042517da7646b6444b60154d2))



## [1.0.4](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/v1.0.3...v1.0.4) (2025-11-29)



## [1.0.3](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/v1.0.2...v1.0.3) (2025-11-29)


* fix(api)!: đạt 100% API hoạt động - sửa tất cả lỗi 500 ([faa3e71](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/faa3e71e0ec477c53391f0f8a3d710c4027512d7))
* feat(service)!: hoàn thiện code cho IoTService, NotificationService và WalletService ([9877d86](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/9877d86ea388d49b20d49d9d66dbe909deda09be))
* fix(api)!: fix bộ lọc /api/v1/reports ([a6fcf3b](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/a6fcf3be06eb1df96a6aaa1141a4e206de66f7c6))
* feat(ngsi-ld)!: trien khai NGSI-LD API va tich hop OpenWeatherMap ([b496ea6](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/b496ea61645c6631e9fdafe0d3098175f9451475))
* perf(postman)!: Cập nhật Postman Collection đầy đủ các API ([eae50e6](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/eae50e6a41b38b51f6e7c82641b5c57dc5a53a9a))
* build(docker)!: Chỉnh Sửa DockerFile trong CoreAPI ([7794e54](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/7794e545657940f0b16d3edc40ec86c9a85ab609))
* build(deloy)!: Cập nhật Deloy.sh ([8fca540](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/8fca5405562e302259521635b4f27fa5f712b96f))
* fix(docker)!: fix ([e5617d6](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e5617d65b76d54b9ce91a9fd460bf7f862d15967))
* build(docker)!: Thay đổi Docker ([de9b2b0](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/de9b2b06057af348a2574105e56a96cfcb88fc69))
* feat(api)!: Hoàn thiện API client cho React Native Mobile ([2b92a48](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/2b92a483731eb56b6d0703d7c8b3837f1ffef25c))


### Bug Fixes

* **api:** khắc phục phản hồi null và tối ưu hóa bộ kiểm thử (tỷ lệ đạt 97%) ([e39a7f7](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e39a7f7760d577a6eb93f0f6a73aaedd2e9ca934))
* **deploy:** syntax error in NC variable assignment ([eb075da](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/eb075dad631e28e08a93170c9b6565b057056061))
* **docker:** resolve invalid mqtt volume mount syntax ([8d62ebf](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/8d62ebf6bd2cd621fdbc79106e7f83fc05031a5b))
* **docker:** Sửa đường dẫn trong docker-compose.production.yml ([e08ff4c](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e08ff4cff947d22f2d1abd1692e51f7d8ae9a1df))
* **docker:** Sửa update-deloy.sh ([5228c22](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/5228c226264d87fdd38758b716e4508f9cb9405b))
* **Scope:** Sửa lỗi 500 NGSI-LD API và cập nhật cấu hình Docker ([b2a60d2](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/b2a60d208860094d714fdd21c10cc8c4eaf6c205))
* **sh:** Sửa smart-deploy.sh ([17970d6](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/17970d614a119392a6872d0cb2dacba140a2ced1))


### Features

* **api:** thêm API danh mục, ưu tiên và bổ sung Postman collection đầy đủ ([bbde086](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/bbde0867376afa47b556454716e23e8364f2fbe5))
* **services:** hoàn thiện code 4 services còn lại (Search, Analytics, Incident, FloodEye) ([765bb3f](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/765bb3f25de66f05822ed954d69dadeb5164236a))
* Update Root Repo ([252715c](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/252715cd078188cdeb49eb551d9815cd9e0812ab))


### Performance Improvements

* Tạo smart deloy ([8c76f2d](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/8c76f2d9aa94bce34b38bcacc59c99110f6b22fe))


### BREAKING CHANGES

* Model NguoiDung giờ kế thừa Authenticatable

Đóng: Lỗi CoreAPI endpoints
Kiểm tra bởi: Manual curl testing + Python scripts
* - IoTService: Thêm TimescaleDB, MQTT, Sensor API.
- NotificationService: Thêm MongoDB, Firebase, Email API.
- WalletService: Thêm PostgreSQL, GORM, Wallet API.
* Sửa lỗi bộ lọc bị trả về null
* - Triển khai các API endpoints chuẩn ETSI NGSI-LD tại /ngsi-ld/v1/ cho Alert và WeatherObserved.
- Tích hợp OpenWeatherMap API để lấy và lưu trữ dữ liệu thời tiết.
- Thêm hỗ trợ geo-query (near, within) và lọc theo thuộc tính.
- Tạo bảng 'weather_observations' và migration tương ứng.
- Thêm các script quản lý Docker (scripts/local/run.sh, scripts/rebuild-docker.sh).
- Cập nhật Postman collection với các endpoints NGSI-LD mới.
* Đã thêm 5 groups mới:
* Thực hiện fix và chỉnh sửa dockerfile trong CoreAPI và deloy.sh
* Thay đổi deloy.sh để đưa lên VPS
* Fix Deloy.sh
* Thay đổi cấu hình Nginx, Viết lại DockerFile, Viết lại Deloy.sh để auto Deloy
* Hoàn thiện API client cho React Native Mobile



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




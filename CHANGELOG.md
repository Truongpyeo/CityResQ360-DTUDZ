# CHANGELOG

## 30/11/2025 - 02h31

### Chỉnh Sửa Script

**Bug Fixes:**
- modules): ổn định tính năng upload media và build service

**Documentation:**
- update CHANGELOG.md [skip ci]

**Other Changes:**
- ﻿fix(api): Switch media upload fallback to S3

**Technical Details:**
- Tag: v1.0.4
- Commits: 3
- Released from: master branch
- Release URL: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/v1.0.4

---

# [](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/v1.0.3...v) (2025-11-29)



## [1.0.3](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/v1.0.2...v1.0.3) (2025-11-29)


* fix(api)!: Ä‘áº¡t 100% API hoáº¡t Ä‘á»™ng - sá»­a táº¥t cáº£ lá»—i 500 ([faa3e71](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/faa3e71e0ec477c53391f0f8a3d710c4027512d7))
* feat(service)!: hoÃ n thiá»‡n code cho IoTService, NotificationService vÃ  WalletService ([9877d86](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/9877d86ea388d49b20d49d9d66dbe909deda09be))
* fix(api)!: fix bá»™ lá»c /api/v1/reports ([a6fcf3b](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/a6fcf3be06eb1df96a6aaa1141a4e206de66f7c6))
* feat(ngsi-ld)!: trien khai NGSI-LD API va tich hop OpenWeatherMap ([b496ea6](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/b496ea61645c6631e9fdafe0d3098175f9451475))
* perf(postman)!: Cáº­p nháº­t Postman Collection Ä‘áº§y Ä‘á»§ cÃ¡c API ([eae50e6](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/eae50e6a41b38b51f6e7c82641b5c57dc5a53a9a))
* build(docker)!: Chá»‰nh Sá»­a DockerFile trong CoreAPI ([7794e54](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/7794e545657940f0b16d3edc40ec86c9a85ab609))
* build(deloy)!: Cáº­p nháº­t Deloy.sh ([8fca540](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/8fca5405562e302259521635b4f27fa5f712b96f))
* fix(docker)!: fix ([e5617d6](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e5617d65b76d54b9ce91a9fd460bf7f862d15967))
* build(docker)!: Thay Ä‘á»•i Docker ([de9b2b0](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/de9b2b06057af348a2574105e56a96cfcb88fc69))
* feat(api)!: HoÃ n thiá»‡n API client cho React Native Mobile ([2b92a48](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/2b92a483731eb56b6d0703d7c8b3837f1ffef25c))


### Bug Fixes

* **api:** kháº¯c phá»¥c pháº£n há»“i null vÃ  tá»‘i Æ°u hÃ³a bá»™ kiá»ƒm thá»­ (tá»· lá»‡ Ä‘áº¡t 97%) ([e39a7f7](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e39a7f7760d577a6eb93f0f6a73aaedd2e9ca934))
* **deploy:** syntax error in NC variable assignment ([eb075da](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/eb075dad631e28e08a93170c9b6565b057056061))
* **docker:** resolve invalid mqtt volume mount syntax ([8d62ebf](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/8d62ebf6bd2cd621fdbc79106e7f83fc05031a5b))
* **docker:** Sá»­a Ä‘Æ°á»ng dáº«n trong docker-compose.production.yml ([e08ff4c](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e08ff4cff947d22f2d1abd1692e51f7d8ae9a1df))
* **docker:** Sá»­a update-deloy.sh ([5228c22](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/5228c226264d87fdd38758b716e4508f9cb9405b))
* **Scope:** Sá»­a lá»—i 500 NGSI-LD API vÃ  cáº­p nháº­t cáº¥u hÃ¬nh Docker ([b2a60d2](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/b2a60d208860094d714fdd21c10cc8c4eaf6c205))
* **sh:** Sá»­a smart-deploy.sh ([17970d6](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/17970d614a119392a6872d0cb2dacba140a2ced1))


### Features

* **api:** thÃªm API danh má»¥c, Æ°u tiÃªn vÃ  bá»• sung Postman collection Ä‘áº§y Ä‘á»§ ([bbde086](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/bbde0867376afa47b556454716e23e8364f2fbe5))
* **services:** hoÃ n thiá»‡n code 4 services cÃ²n láº¡i (Search, Analytics, Incident, FloodEye) ([765bb3f](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/765bb3f25de66f05822ed954d69dadeb5164236a))
* Update Root Repo ([252715c](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/252715cd078188cdeb49eb551d9815cd9e0812ab))


### Performance Improvements

* Táº¡o smart deloy ([8c76f2d](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/8c76f2d9aa94bce34b38bcacc59c99110f6b22fe))


### BREAKING CHANGES

* Model NguoiDung giá» káº¿ thá»«a Authenticatable

ÄÃ³ng: Lá»—i CoreAPI endpoints
Kiá»ƒm tra bá»Ÿi: Manual curl testing + Python scripts
* - IoTService: ThÃªm TimescaleDB, MQTT, Sensor API.
- NotificationService: ThÃªm MongoDB, Firebase, Email API.
- WalletService: ThÃªm PostgreSQL, GORM, Wallet API.
* Sá»­a lá»—i bá»™ lá»c bá»‹ tráº£ vá» null
* - Triá»ƒn khai cÃ¡c API endpoints chuáº©n ETSI NGSI-LD táº¡i /ngsi-ld/v1/ cho Alert vÃ  WeatherObserved.
- TÃ­ch há»£p OpenWeatherMap API Ä‘á»ƒ láº¥y vÃ  lÆ°u trá»¯ dá»¯ liá»‡u thá»i tiáº¿t.
- ThÃªm há»— trá»£ geo-query (near, within) vÃ  lá»c theo thuá»™c tÃ­nh.
- Táº¡o báº£ng 'weather_observations' vÃ  migration tÆ°Æ¡ng á»©ng.
- ThÃªm cÃ¡c script quáº£n lÃ½ Docker (scripts/local/run.sh, scripts/rebuild-docker.sh).
- Cáº­p nháº­t Postman collection vá»›i cÃ¡c endpoints NGSI-LD má»›i.
* ÄÃ£ thÃªm 5 groups má»›i:
* Thá»±c hiá»‡n fix vÃ  chá»‰nh sá»­a dockerfile trong CoreAPI vÃ  deloy.sh
* Thay Ä‘á»•i deloy.sh Ä‘á»ƒ Ä‘Æ°a lÃªn VPS
* Fix Deloy.sh
* Thay Ä‘á»•i cáº¥u hÃ¬nh Nginx, Viáº¿t láº¡i DockerFile, Viáº¿t láº¡i Deloy.sh Ä‘á»ƒ auto Deloy
* HoÃ n thiá»‡n API client cho React Native Mobile



# [1.0.0](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/a9585e5d3e275aa0ee68923e0cdf25ae943a8d6b...v1.0.0) (2025-11-25)


### Bug Fixes

* **coreapi:** ThÃªm PHP Redis extension vÃ o Dockerfile ([e9a3109](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e9a31096dea6f70c052140ad4b37b3efbe2b04aa))
* **coreapi:** ThÃªm trustProxies vÃ  intl extension ([4549d60](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/4549d6083fc2ff7de876a64369add3e3acc1bb0d))
* **deploy:** DÃ¹ng php artisan key:generate trong container ([804bff9](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/804bff910d04ff37546dc163f7cbdd10701b6634))
* **deploy:** generate APP_KEY before starting containers to prevent missing key error ([7c3d218](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/7c3d218c2ded1132ebcf659a543842a737304bb4))
* **deploy:** Generate APP_KEY theo chuáº©n Laravel base64 format ([667b15c](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/667b15cf6b0ee8b03e55587f404d995cee6b7779))
* **deploy:** reuse passwords from existing .env to prevent MySQL access denied ([0ae0ca3](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/0ae0ca3b6c910f54cb963a6dc94ba30f9725a527))
* **deploy:** Sá»­a APP_KEY generation giá»¯ base64 há»£p lá»‡ ([d4dbac0](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/d4dbac0871d0a6c2d977b3616e816910ed7aa849))
* **deploy:** Sá»­a lá»—i JWT_SECRET xuá»‘ng dÃ²ng vÃ  kÃ½ tá»± Ä‘áº·c biá»‡t ([c7903eb](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/c7903eb2ffa48ce5a145e3f9a9801137f85bd7a5))
* **deploy:** Sá»­a lá»—i rsync vÃ  docker exec -it trong script tá»± Ä‘á»™ng ([77a9f68](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/77a9f6896389cb36659d6a4c0e9ee981b8173d4e))
* **deploy:** Thay rsync báº±ng cp Ä‘á»ƒ tÆ°Æ¡ng thÃ­ch Ubuntu ([74586ab](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/74586ab5dd4295ff5fa1d1ffbcaab550f9008213))
* **docker:** Bá» mount .env file, dÃ¹ng environment variables ([44f2766](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/44f2766d8c27d0cd134a3fc7356fa908c0f4a578))
* **docker:** Mount .env file vÃ  thÃªm APP_KEY cho CoreAPI container ([79dcf3f](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/79dcf3f54a1febcb21d2c90a207067a3ef648754))
* **docker:** XÃ³a duplicate volumes key gÃ¢y lá»—i YAML parse ([668bf83](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/668bf8307ee3d8e197198d32820909909c9b692a))
* **mysql:** Bá» init.sql mount vÃ¬ syntax khÃ´ng tÆ°Æ¡ng thÃ­ch MySQL 8.0 ([fb31d5e](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/fb31d5ece3ac016bd68fcb5fd033ec4953101dd3))
* readme ([a9585e5](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/a9585e5d3e275aa0ee68923e0cdf25ae943a8d6b))




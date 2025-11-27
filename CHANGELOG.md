# Changelog

All notable changes to CityResQ360-DTUDZ will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- GPL v3 license headers to all source files ([#PR])
- Documentation for building without Docker ([docs/BUILD_WITHOUT_DOCKER.md](docs/BUILD_WITHOUT_DOCKER.md))
- Script for automatic license header insertion ([scripts/add-license-headers.sh](scripts/add-license-headers.sh))

### Changed
- Improved CHANGELOG format to follow Keep a Changelog standard

---

## [1.0.0] - 2025-11-26

### Added
- **Core Features**
  - AI-powered incident detection and classification
  - Real-time incident mapping with PostGIS
  - Mobile app (React Native) for citizen reporting
  - Web dashboard (VueJS) for government agencies
  - Microservices architecture with 11 services
  - CityPoint reward system for community engagement

- **Infrastructure**
  - Docker Compose deployment setup
  - PostgreSQL with PostGIS extension
  - Redis caching layer
  - MongoDB for IoT data
  - Apache Kafka message broker
  - MQTT support (Mosquitto/EMQX)

- **Services**
  - CoreAPI (Laravel 12) - Main API backend
  - AIMLService (FastAPI) - AI/ML processing
  - IoTService (Node.js) - IoT data collection
  - MediaService (Node.js) - Image/video storage
  - NotificationService (Node.js) - Push notifications
  - WalletService (Go) - CityPoint management
  - IncidentService (Node.js) - Incident processing
  - SearchService (Python) - Full-text search
  - AnalyticsService (Python) - Data analytics
  - FloodEyeService (Python) - Flood monitoring
  - ContextBroker - NGSI-LD support (planned)

- **Documentation**
  - Comprehensive README with architecture diagrams
  - Project context and development workflow guides
  - Docker deployment documentation
  - Contributing guidelines
  - Code of conduct

### Changed
- Repository restructured into modular architecture
  - `modules/` - All microservices
  - `infrastructure/` - Deployment configs
  - `docs/` - Documentation
  - `scripts/` - Utility scripts
  - `collections/` - API test collections

### Fixed
- **CoreAPI**
  - PHP Redis extension added to Dockerfile ([e9a3109])
  - TrustProxies and intl extension configured ([4549d60])
  
- **Deployment**
  - APP_KEY generation with proper base64 format ([667b15c])
  - JWT_SECRET handling for special characters ([c7903eb])
  - MySQL password reuse to prevent access denied errors ([0ae0ca3])
  - Docker exec -it compatibility in deployment scripts ([77a9f68])
  - rsync replaced with cp for Ubuntu compatibility ([74586ab])

- **Docker**
  - Environment variables properly configured ([44f2766])
  - Duplicate volumes key removed from YAML ([668bf83])
  - MySQL 8.0 syntax compatibility ([fb31d5e])

### Security
- Secure environment variable handling
- Database credentials isolation
- API authentication with Laravel Sanctum

---

## Historical Releases

### Pre-1.0.0 - Development Phase

Initial development and prototyping of CityResQ360 system.

---

## Links

- [Repository](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ)
- [Issues](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/issues)
- [Pull Requests](https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/pulls)

---

[Unreleased]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/releases/tag/v1.0.0

[e9a3109]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/e9a3109
[4549d60]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/4549d60
[667b15c]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/667b15c
[c7903eb]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/c7903eb
[0ae0ca3]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/0ae0ca3
[77a9f68]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/77a9f68
[74586ab]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/74586ab
[44f2766]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/44f2766
[668bf83]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/668bf83
[fb31d5e]: https://github.com/MNM-DTU-DZ/CityResQ360-DTUDZ/commit/fb31d5e

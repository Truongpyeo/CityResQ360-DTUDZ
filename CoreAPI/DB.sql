CREATE TABLE `quan_tri_viens` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ten_quan_tri` varchar(120),
  `email` varchar(190) UNIQUE,
  `mat_khau` varchar(255),
  `vai_tro` tinyint DEFAULT 1 COMMENT '0:superadmin, 1:data_admin, 2:agency_admin',
  `anh_dai_dien` varchar(255),
  `trang_thai` tinyint DEFAULT 1 COMMENT '1:active, 0:locked',
  `lan_dang_nhap_cuoi` timestamp,
  `created_at` timestamp,
  `updated_at` timestamp,
  `deleted_at` timestamp
);

CREATE TABLE `nguoi_dungs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ho_ten` varchar(120),
  `email` varchar(190) UNIQUE,
  `mat_khau` varchar(255),
  `so_dien_thoai` varchar(20),
  `vai_tro` tinyint DEFAULT 0 COMMENT '0:citizen, 1:officer',
  `anh_dai_dien` varchar(255),
  `trang_thai` tinyint DEFAULT 1 COMMENT '1:active, 0:banned',
  `diem_thanh_pho` int DEFAULT 0 COMMENT 'CityPoint token thưởng',
  `xac_thuc_cong_dan` boolean DEFAULT false COMMENT 'KYC verified',
  `diem_uy_tin` int DEFAULT 0,
  `tong_so_phan_anh` int DEFAULT 0,
  `so_phan_anh_chinh_xac` int DEFAULT 0,
  `ty_le_chinh_xac` float DEFAULT 0 COMMENT '%',
  `cap_huy_hieu` tinyint DEFAULT 0 COMMENT '0:bronze, 1:silver, 2:gold, 3:platinum',
  `push_token` varchar(255) COMMENT 'FCM token',
  `tuy_chon_thong_bao` json,
  `vi_tri_cuoi` geometry(point,4326),
  `created_at` timestamp,
  `updated_at` timestamp,
  `deleted_at` timestamp
);

CREATE TABLE `co_quan_xu_lys` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ten_co_quan` varchar(150),
  `email_lien_he` varchar(150),
  `so_dien_thoai` varchar(30),
  `dia_chi` varchar(255),
  `cap_do` tinyint DEFAULT 0 COMMENT '0:ward, 1:district, 2:city',
  `mo_ta` text,
  `trang_thai` tinyint DEFAULT 1 COMMENT '1:active, 0:inactive',
  `created_at` timestamp,
  `updated_at` timestamp,
  `deleted_at` timestamp
);

CREATE TABLE `phan_anhs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `nguoi_dung_id` bigint,
  `tieu_de` varchar(255),
  `mo_ta` text,
  `danh_muc` tinyint COMMENT '0:traffic, 1:environment, 2:fire, 3:waste, 4:flood, 5:other',
  `trang_thai` tinyint DEFAULT 0 COMMENT '0:pending, 1:verified, 2:in_progress, 3:resolved, 4:rejected',
  `uu_tien` tinyint DEFAULT 1 COMMENT '0:low, 1:medium, 2:high, 3:urgent',
  `vi_do` decimal(10,7),
  `kinh_do` decimal(10,7),
  `dia_chi` varchar(255),
  `duong_dan_anh` varchar(255),
  `nhan_ai` varchar(100) COMMENT 'kết quả AI phân loại',
  `do_tin_cay` float COMMENT 'AI confidence 0-1',
  `co_quan_phu_trach_id` bigint,
  `la_cong_khai` boolean DEFAULT true,
  `luot_ung_ho` int DEFAULT 0,
  `luot_khong_ung_ho` int DEFAULT 0,
  `luot_xem` int DEFAULT 0,
  `han_phan_hoi` timestamp,
  `thoi_gian_phan_hoi_thuc_te` int COMMENT 'minutes',
  `thoi_gian_giai_quyet` int COMMENT 'hours',
  `danh_gia_hai_long` tinyint COMMENT '1-5 stars',
  `la_trung_lap` boolean DEFAULT false,
  `trung_lap_voi_id` bigint,
  `the_tags` json COMMENT 'array tags',
  `du_lieu_mo_rong` json COMMENT 'metadata từ AI, sensors',
  `created_at` timestamp,
  `updated_at` timestamp,
  `deleted_at` timestamp
);

CREATE TABLE `su_cos` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `phan_anh_id` bigint,
  `loai_su_co` varchar(100),
  `muc_do_nghiem_trong` tinyint COMMENT '0:low, 1:medium, 2:high, 3:critical',
  `trang_thai` tinyint DEFAULT 0 COMMENT '0:new, 1:monitoring, 2:alerted, 3:closed',
  `co_quan_phu_trach_id` bigint,
  `mo_ta` text,
  `thoi_gian_xu_ly_du_kien` timestamp,
  `thoi_gian_xu_ly_thuc_te` timestamp,
  `ghi_chu_xu_ly` text,
  `created_at` timestamp,
  `updated_at` timestamp,
  `deleted_at` timestamp
);

CREATE TABLE `canh_baos` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `su_co_id` bigint,
  `ma_quy_tac` varchar(50),
  `loai_canh_bao` tinyint COMMENT '0:sensor, 1:vision, 2:nlp, 3:manual',
  `thong_diep` text,
  `thoi_gian_kich_hoat` timestamp,
  `thoi_gian_giai_quyet` timestamp,
  `trang_thai` tinyint DEFAULT 0 COMMENT '0:active, 1:resolved',
  `muc_do_uu_tien` tinyint COMMENT '0:info, 1:warning, 2:critical',
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `thong_baos` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `nguoi_dung_id` bigint,
  `canh_bao_id` bigint,
  `tieu_de` varchar(150),
  `noi_dung` text,
  `kenh_gui` tinyint COMMENT '0:app, 1:email, 2:sms, 3:websocket',
  `thoi_gian_gui` timestamp,
  `thoi_gian_doc` timestamp,
  `la_da_doc` boolean DEFAULT false,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `cam_biens` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ma_cam_bien` varchar(100) UNIQUE,
  `ten_cam_bien` varchar(150),
  `loai_cam_bien` varchar(100),
  `vi_tri` geometry(point,4326),
  `gia_tri_cuoi` float,
  `don_vi` varchar(50),
  `nha_san_xuat` varchar(100),
  `mo_hinh` varchar(100),
  `so_seri` varchar(150),
  `ngay_lap_dat` date,
  `ngay_bao_tri_cuoi` date,
  `muc_pin` float COMMENT '%',
  `cuong_do_tin_hieu` int COMMENT 'dBm',
  `trang_thai_truc_tuyen` boolean DEFAULT true,
  `trang_thai_hieu_chuan` tinyint COMMENT '0:calibrated, 1:needs_calibration, 2:faulty',
  `du_lieu_mo_rong` json COMMENT 'NGSI-LD metadata',
  `updated_at` timestamp,
  `created_at` timestamp,
  `deleted_at` timestamp
);

CREATE TABLE `quan_sats` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `cam_bien_id` bigint,
  `thuoc_tinh_quan_sat` varchar(100),
  `gia_tri` float,
  `don_vi` varchar(50),
  `thoi_gian_quan_sat` timestamp,
  `chat_luong_du_lieu` tinyint COMMENT '0:good, 1:fair, 2:poor',
  `ghi_chu` text,
  `created_at` timestamp
);

CREATE TABLE `tep_phuong_tiends` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `phan_anh_id` bigint,
  `duong_dan_tep` varchar(255),
  `loai_tap_tin` varchar(100),
  `kich_thuoc` bigint COMMENT 'bytes',
  `thoi_gian_tai_len` timestamp,
  `la_anh_chinh` boolean DEFAULT false,
  `thu_tu_hien_thi` int DEFAULT 0,
  `created_at` timestamp
);

CREATE TABLE `giao_dich_vi_dien_tus` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `nguoi_dung_id` bigint,
  `so_tien` decimal(10,2),
  `loai_giao_dich` tinyint COMMENT '0:reward, 1:spend, 2:admin_adjust',
  `mo_ta` varchar(255),
  `ma_giao_dich_hash` varchar(100),
  `phan_anh_lien_quan_id` bigint,
  `trang_thai` tinyint DEFAULT 1 COMMENT '0:pending, 1:completed, 2:failed',
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `nhat_ky_he_thongs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `nguoi_dung_id` bigint,
  `hanh_dong` varchar(100),
  `loai_doi_tuong` varchar(100),
  `id_doi_tuong` bigint,
  `du_lieu_meta` json,
  `dia_chi_ip` varchar(45),
  `user_agent` text,
  `created_at` timestamp
);

CREATE TABLE `ngsi_entities` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `entity_id` varchar(255) UNIQUE COMMENT 'urn:ngsi-ld:Event:001',
  `loai_entity` varchar(100) COMMENT 'Event, Location, Agency, Sensor',
  `context_url` varchar(255) COMMENT '@context URL',
  `thuoc_tinh` json COMMENT 'NGSI-LD attributes',
  `quan_he` json COMMENT 'NGSI-LD relationships',
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `quan_he_entities` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `entity_nguon_id` bigint,
  `entity_dich_id` bigint,
  `loai_quan_he` varchar(100) COMMENT 'hasLocation, managedBy, observedBy',
  `du_lieu_mo_rong` json,
  `created_at` timestamp
);

CREATE TABLE `rdf_triples` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `chu_the` varchar(255) COMMENT 'Subject URI',
  `vi_tu` varchar(255) COMMENT 'Predicate URI - sosa:observes',
  `doi_tuong` varchar(255) COMMENT 'Object value hoặc URI',
  `loai_doi_tuong` tinyint COMMENT '0:uri, 1:literal, 2:blank_node',
  `kieu_du_lieu` varchar(100),
  `ngon_ngu` varchar(10) COMMENT 'vi, en',
  `graph_uri` varchar(255),
  `created_at` timestamp
);

CREATE TABLE `khu_vuc_ngap_luts` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ten_khu_vuc` varchar(150),
  `ma_khu_vuc` varchar(50) UNIQUE,
  `vung_dia_ly` geometry(polygon,4326),
  `muc_do_rui_ro` tinyint COMMENT '0:low, 1:medium, 2:high, 3:critical',
  `dan_so_anh_huong` int,
  `mo_ta` text,
  `ngay_cap_nhat_rui_ro` date,
  `trang_thai` tinyint DEFAULT 0 COMMENT '0:normal, 1:warning, 2:danger',
  `created_at` timestamp,
  `updated_at` timestamp,
  `deleted_at` timestamp
);

CREATE TABLE `canh_bao_ngap_luts` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `khu_vuc_id` bigint,
  `muc_nuoc` float COMMENT 'cm',
  `nguong_vuot_qua` boolean DEFAULT false,
  `thong_diep_canh_bao` text,
  `thoi_gian_kich_hoat` timestamp,
  `thoi_gian_giai_quyet` timestamp,
  `trang_thai` tinyint DEFAULT 0 COMMENT '0:active, 1:monitoring, 2:resolved',
  `muc_do_nghiem_trong` tinyint COMMENT '0:info, 1:warning, 2:danger, 3:critical',
  `du_lieu_cam_bien` json,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `cam_bien_muc_nuocs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `cam_bien_id` bigint,
  `khu_vuc_id` bigint,
  `muc_nuoc_hien_tai` float COMMENT 'cm',
  `nguong_canh_bao` float COMMENT 'cm',
  `nguong_nguy_hiem` float COMMENT 'cm',
  `thoi_gian_do_cuoi` timestamp,
  `trang_thai_hoat_dong` tinyint COMMENT '0:normal, 1:warning, 2:error',
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `nguon_du_lieu_mos` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ten_nguon` varchar(150) COMMENT 'OpenStreetMap, OpenWeather, GTFS',
  `loai_nguon` tinyint COMMENT '0:osm, 1:weather, 2:transport, 3:other',
  `api_endpoint` varchar(255),
  `api_key` varchar(255),
  `thoi_gian_dong_bo_cuoi` timestamp,
  `trang_thai_dong_bo` tinyint COMMENT '0:success, 1:failed, 2:pending',
  `so_ban_ghi_dong_bo` int,
  `nhat_ky_loi` text,
  `tan_suat_dong_bo` int COMMENT 'minutes',
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `bo_nho_dem_du_lieus` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `nguon_id` bigint,
  `khoa_cache` varchar(255) UNIQUE,
  `du_lieu` json,
  `thoi_gian_het_han` timestamp,
  `so_lan_truy_cap` int DEFAULT 0,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `du_lieu_huan_luyen_ais` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `phan_anh_id` bigint,
  `loai_mo_hinh` tinyint COMMENT '0:nlp, 1:vision, 2:hybrid',
  `van_ban_dau_vao` text,
  `duong_dan_anh_dau_vao` varchar(255),
  `nhan_du_doan` varchar(100),
  `nhan_thuc_te` varchar(100) COMMENT 'human verified',
  `do_tin_cay` float,
  `da_xac_minh` boolean DEFAULT false,
  `nguoi_xac_minh_id` bigint,
  `thoi_gian_xac_minh` timestamp,
  `ghi_chu` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `hieu_suat_mo_hinhs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ten_mo_hinh` varchar(100),
  `phien_ban` varchar(50),
  `do_chinh_xac` float COMMENT 'accuracy',
  `do_chinh_xac_du_doan` float COMMENT 'precision',
  `ty_le_hoi_tuong` float COMMENT 'recall',
  `diem_f1` float COMMENT 'f1_score',
  `ma_tran_nham_lan` json COMMENT 'confusion matrix',
  `so_mau_kiem_tra` int,
  `thoi_gian_danh_gia` timestamp,
  `ghi_chu` text,
  `created_at` timestamp
);

CREATE TABLE `binh_luan_phan_anhs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `phan_anh_id` bigint,
  `nguoi_dung_id` bigint,
  `noi_dung` text,
  `la_chinh_thuc` boolean DEFAULT false COMMENT 'từ cơ quan',
  `binh_luan_cha_id` bigint,
  `created_at` timestamp,
  `updated_at` timestamp,
  `deleted_at` timestamp
);

CREATE TABLE `binh_chon_phan_anhs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `phan_anh_id` bigint,
  `nguoi_dung_id` bigint,
  `loai_binh_chon` tinyint COMMENT '1:upvote, 0:downvote',
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `chi_so_dashboards` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `khoa_chi_so` varchar(100) UNIQUE,
  `gia_tri_chi_so` decimal(15,2),
  `don_vi` varchar(50),
  `danh_muc` tinyint COMMENT '0:reports, 1:incidents, 2:response_time, 3:city_points, 4:agencies',
  `thoi_gian_tinh_toan` timestamp,
  `chu_ky` tinyint COMMENT '0:hourly, 1:daily, 2:weekly, 3:monthly',
  `du_lieu_mo_rong` json,
  `created_at` timestamp
);

CREATE TABLE `hieu_suat_co_quans` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `co_quan_id` bigint,
  `tong_so_duoc_giao` int,
  `tong_so_da_giai_quyet` int,
  `thoi_gian_phan_hoi_trung_binh` int COMMENT 'minutes',
  `thoi_gian_giai_quyet_trung_binh` int COMMENT 'hours',
  `diem_hai_long` float COMMENT '1-5',
  `ty_le_giai_quyet_dung_han` float COMMENT '%',
  `ngay_bat_dau` date,
  `ngay_ket_thuc` date,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `lich_su_trang_thai_phan_anhs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `phan_anh_id` bigint,
  `trang_thai_cu` tinyint,
  `trang_thai_moi` tinyint,
  `nguoi_thay_doi_id` bigint,
  `ghi_chu` text,
  `created_at` timestamp
);

CREATE TABLE `quy_tac_canh_baos` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ten_quy_tac` varchar(150),
  `ma_quy_tac` varchar(50) UNIQUE,
  `mo_ta` text,
  `dieu_kien` json COMMENT 'rule conditions',
  `hanh_dong` json COMMENT 'actions to take',
  `loai_quy_tac` tinyint COMMENT '0:sensor, 1:time_based, 2:threshold, 3:pattern',
  `muc_do_uu_tien` tinyint,
  `trang_thai` tinyint DEFAULT 1 COMMENT '1:active, 0:inactive',
  `so_lan_kich_hoat` int DEFAULT 0,
  `lan_kich_hoat_cuoi` timestamp,
  `created_at` timestamp,
  `updated_at` timestamp,
  `deleted_at` timestamp
);

CREATE TABLE `du_lieu_thoi_tiets` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `vi_do` decimal(10,7),
  `kinh_do` decimal(10,7),
  `nhiet_do` float COMMENT 'Celsius',
  `do_am` int COMMENT '%',
  `luong_mua` float COMMENT 'mm',
  `toc_do_gio` float COMMENT 'km/h',
  `huong_gio` varchar(20),
  `mo_ta_thoi_tiet` varchar(100),
  `chi_so_uv` int,
  `tam_nhin` float COMMENT 'km',
  `ap_suat` float COMMENT 'hPa',
  `thoi_gian_du_bao` timestamp,
  `nguon_du_lieu` varchar(100) COMMENT 'OpenWeather',
  `created_at` timestamp
);

CREATE TABLE `tuyen_giao_thongs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ma_tuyen` varchar(50) UNIQUE,
  `ten_tuyen` varchar(150),
  `loai_phuong_tien` tinyint COMMENT '0:bus, 1:metro, 2:train, 3:ferry',
  `tuyen_duong` geometry(linestring,4326),
  `mau_sac_tuyen` varchar(20),
  `trang_thai_hoat_dong` tinyint DEFAULT 1 COMMENT '1:active, 0:inactive',
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `tram_dungs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `ma_tram` varchar(50) UNIQUE,
  `ten_tram` varchar(150),
  `vi_tri` geometry(point,4326),
  `dia_chi` varchar(255),
  `loai_tram` tinyint COMMENT '0:bus_stop, 1:metro_station, 2:train_station',
  `tien_nghi` json COMMENT 'facilities available',
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `chi_tiet_tuyen_trams` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `tuyen_id` bigint,
  `tram_id` bigint,
  `thu_tu_dung` int,
  `khoang_cach_tu_tram_truoc` float COMMENT 'km',
  `thoi_gian_du_kien` int COMMENT 'minutes',
  `created_at` timestamp
);

CREATE TABLE `cau_hinh_he_thongs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `khoa_cau_hinh` varchar(100) UNIQUE,
  `gia_tri` text,
  `loai_du_lieu` tinyint COMMENT '0:string, 1:integer, 2:float, 3:boolean, 4:json',
  `mo_ta` text,
  `nhom` varchar(50),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `phien_ban_apis` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `phien_ban` varchar(20) UNIQUE,
  `mo_ta` text,
  `ngay_phat_hanh` date,
  `ngay_het_han` date,
  `trang_thai` tinyint COMMENT '0:deprecated, 1:active, 2:beta',
  `ghi_chu_thay_doi` text,
  `created_at` timestamp
);

CREATE INDEX `quan_tri_viens_index_0` ON `quan_tri_viens` (`email`);

CREATE INDEX `quan_tri_viens_index_1` ON `quan_tri_viens` (`vai_tro`, `trang_thai`);

CREATE INDEX `nguoi_dungs_index_2` ON `nguoi_dungs` (`email`);

CREATE INDEX `nguoi_dungs_index_3` ON `nguoi_dungs` (`so_dien_thoai`);

CREATE INDEX `nguoi_dungs_index_4` ON `nguoi_dungs` (`vai_tro`, `trang_thai`);

CREATE INDEX `nguoi_dungs_index_5` ON `nguoi_dungs` (`diem_uy_tin`);

CREATE INDEX `co_quan_xu_lys_index_6` ON `co_quan_xu_lys` (`cap_do`, `trang_thai`);

CREATE INDEX `phan_anhs_index_7` ON `phan_anhs` (`nguoi_dung_id`, `created_at`);

CREATE INDEX `phan_anhs_index_8` ON `phan_anhs` (`trang_thai`, `created_at`);

CREATE INDEX `phan_anhs_index_9` ON `phan_anhs` (`danh_muc`, `trang_thai`);

CREATE INDEX `phan_anhs_index_10` ON `phan_anhs` (`co_quan_phu_trach_id`, `trang_thai`);

CREATE INDEX `phan_anhs_index_11` ON `phan_anhs` (`uu_tien`, `trang_thai`);

CREATE INDEX `su_cos_index_12` ON `su_cos` (`phan_anh_id`);

CREATE INDEX `su_cos_index_13` ON `su_cos` (`muc_do_nghiem_trong`, `trang_thai`);

CREATE INDEX `su_cos_index_14` ON `su_cos` (`co_quan_phu_trach_id`, `trang_thai`);

CREATE INDEX `su_cos_index_15` ON `su_cos` (`created_at`);

CREATE INDEX `canh_baos_index_16` ON `canh_baos` (`su_co_id`, `thoi_gian_kich_hoat`);

CREATE INDEX `canh_baos_index_17` ON `canh_baos` (`trang_thai`, `thoi_gian_kich_hoat`);

CREATE INDEX `canh_baos_index_18` ON `canh_baos` (`loai_canh_bao`);

CREATE INDEX `thong_baos_index_19` ON `thong_baos` (`nguoi_dung_id`, `thoi_gian_gui`);

CREATE INDEX `thong_baos_index_20` ON `thong_baos` (`nguoi_dung_id`, `la_da_doc`);

CREATE INDEX `cam_biens_index_21` ON `cam_biens` (`ma_cam_bien`);

CREATE INDEX `cam_biens_index_22` ON `cam_biens` (`loai_cam_bien`, `trang_thai_truc_tuyen`);

CREATE INDEX `cam_biens_index_23` ON `cam_biens` (`trang_thai_hieu_chuan`);

CREATE INDEX `quan_sats_index_24` ON `quan_sats` (`cam_bien_id`, `thoi_gian_quan_sat`);

CREATE INDEX `quan_sats_index_25` ON `quan_sats` (`thuoc_tinh_quan_sat`, `thoi_gian_quan_sat`);

CREATE INDEX `tep_phuong_tiends_index_26` ON `tep_phuong_tiends` (`phan_anh_id`, `thu_tu_hien_thi`);

CREATE INDEX `giao_dich_vi_dien_tus_index_27` ON `giao_dich_vi_dien_tus` (`nguoi_dung_id`, `created_at`);

CREATE INDEX `giao_dich_vi_dien_tus_index_28` ON `giao_dich_vi_dien_tus` (`loai_giao_dich`, `created_at`);

CREATE INDEX `nhat_ky_he_thongs_index_29` ON `nhat_ky_he_thongs` (`nguoi_dung_id`, `created_at`);

CREATE INDEX `nhat_ky_he_thongs_index_30` ON `nhat_ky_he_thongs` (`loai_doi_tuong`, `id_doi_tuong`);

CREATE INDEX `nhat_ky_he_thongs_index_31` ON `nhat_ky_he_thongs` (`hanh_dong`, `created_at`);

CREATE INDEX `ngsi_entities_index_32` ON `ngsi_entities` (`entity_id`);

CREATE INDEX `ngsi_entities_index_33` ON `ngsi_entities` (`loai_entity`, `created_at`);

CREATE INDEX `quan_he_entities_index_34` ON `quan_he_entities` (`entity_nguon_id`, `loai_quan_he`);

CREATE INDEX `quan_he_entities_index_35` ON `quan_he_entities` (`entity_dich_id`);

CREATE INDEX `rdf_triples_index_36` ON `rdf_triples` (`chu_the`, `vi_tu`);

CREATE INDEX `rdf_triples_index_37` ON `rdf_triples` (`vi_tu`, `doi_tuong`);

CREATE INDEX `rdf_triples_index_38` ON `rdf_triples` (`graph_uri`);

CREATE INDEX `khu_vuc_ngap_luts_index_39` ON `khu_vuc_ngap_luts` (`ma_khu_vuc`);

CREATE INDEX `khu_vuc_ngap_luts_index_40` ON `khu_vuc_ngap_luts` (`muc_do_rui_ro`, `trang_thai`);

CREATE INDEX `canh_bao_ngap_luts_index_41` ON `canh_bao_ngap_luts` (`khu_vuc_id`, `thoi_gian_kich_hoat`);

CREATE INDEX `canh_bao_ngap_luts_index_42` ON `canh_bao_ngap_luts` (`trang_thai`, `muc_do_nghiem_trong`);

CREATE INDEX `cam_bien_muc_nuocs_index_43` ON `cam_bien_muc_nuocs` (`cam_bien_id`);

CREATE INDEX `cam_bien_muc_nuocs_index_44` ON `cam_bien_muc_nuocs` (`khu_vuc_id`, `thoi_gian_do_cuoi`);

CREATE INDEX `nguon_du_lieu_mos_index_45` ON `nguon_du_lieu_mos` (`loai_nguon`, `trang_thai_dong_bo`);

CREATE INDEX `nguon_du_lieu_mos_index_46` ON `nguon_du_lieu_mos` (`thoi_gian_dong_bo_cuoi`);

CREATE INDEX `bo_nho_dem_du_lieus_index_47` ON `bo_nho_dem_du_lieus` (`khoa_cache`, `thoi_gian_het_han`);

CREATE INDEX `bo_nho_dem_du_lieus_index_48` ON `bo_nho_dem_du_lieus` (`nguon_id`, `thoi_gian_het_han`);

CREATE INDEX `du_lieu_huan_luyen_ais_index_49` ON `du_lieu_huan_luyen_ais` (`loai_mo_hinh`, `da_xac_minh`);

CREATE INDEX `du_lieu_huan_luyen_ais_index_50` ON `du_lieu_huan_luyen_ais` (`nhan_du_doan`, `nhan_thuc_te`);

CREATE INDEX `du_lieu_huan_luyen_ais_index_51` ON `du_lieu_huan_luyen_ais` (`phan_anh_id`);

CREATE INDEX `hieu_suat_mo_hinhs_index_52` ON `hieu_suat_mo_hinhs` (`ten_mo_hinh`, `phien_ban`);

CREATE INDEX `hieu_suat_mo_hinhs_index_53` ON `hieu_suat_mo_hinhs` (`thoi_gian_danh_gia`);

CREATE INDEX `binh_luan_phan_anhs_index_54` ON `binh_luan_phan_anhs` (`phan_anh_id`, `created_at`);

CREATE INDEX `binh_luan_phan_anhs_index_55` ON `binh_luan_phan_anhs` (`nguoi_dung_id`);

CREATE INDEX `binh_luan_phan_anhs_index_56` ON `binh_luan_phan_anhs` (`binh_luan_cha_id`);

CREATE UNIQUE INDEX `binh_chon_phan_anhs_index_57` ON `binh_chon_phan_anhs` (`phan_anh_id`, `nguoi_dung_id`);

CREATE INDEX `binh_chon_phan_anhs_index_58` ON `binh_chon_phan_anhs` (`phan_anh_id`, `loai_binh_chon`);

CREATE INDEX `chi_so_dashboards_index_59` ON `chi_so_dashboards` (`khoa_chi_so`, `thoi_gian_tinh_toan`);

CREATE INDEX `chi_so_dashboards_index_60` ON `chi_so_dashboards` (`danh_muc`, `chu_ky`);

CREATE INDEX `hieu_suat_co_quans_index_61` ON `hieu_suat_co_quans` (`co_quan_id`, `ngay_bat_dau`);

CREATE INDEX `hieu_suat_co_quans_index_62` ON `hieu_suat_co_quans` (`diem_hai_long`);

CREATE INDEX `lich_su_trang_thai_phan_anhs_index_63` ON `lich_su_trang_thai_phan_anhs` (`phan_anh_id`, `created_at`);

CREATE INDEX `quy_tac_canh_baos_index_64` ON `quy_tac_canh_baos` (`ma_quy_tac`);

CREATE INDEX `quy_tac_canh_baos_index_65` ON `quy_tac_canh_baos` (`loai_quy_tac`, `trang_thai`);

CREATE INDEX `du_lieu_thoi_tiets_index_66` ON `du_lieu_thoi_tiets` (`vi_do`, `kinh_do`, `thoi_gian_du_bao`);

CREATE INDEX `du_lieu_thoi_tiets_index_67` ON `du_lieu_thoi_tiets` (`thoi_gian_du_bao`);

CREATE INDEX `tuyen_giao_thongs_index_68` ON `tuyen_giao_thongs` (`ma_tuyen`);

CREATE INDEX `tuyen_giao_thongs_index_69` ON `tuyen_giao_thongs` (`loai_phuong_tien`, `trang_thai_hoat_dong`);

CREATE INDEX `tram_dungs_index_70` ON `tram_dungs` (`ma_tram`);

CREATE INDEX `tram_dungs_index_71` ON `tram_dungs` (`loai_tram`);

CREATE INDEX `chi_tiet_tuyen_trams_index_72` ON `chi_tiet_tuyen_trams` (`tuyen_id`, `thu_tu_dung`);

CREATE INDEX `chi_tiet_tuyen_trams_index_73` ON `chi_tiet_tuyen_trams` (`tram_id`);

CREATE INDEX `cau_hinh_he_thongs_index_74` ON `cau_hinh_he_thongs` (`khoa_cau_hinh`);

CREATE INDEX `cau_hinh_he_thongs_index_75` ON `cau_hinh_he_thongs` (`nhom`);

CREATE INDEX `phien_ban_apis_index_76` ON `phien_ban_apis` (`phien_ban`);

CREATE INDEX `phien_ban_apis_index_77` ON `phien_ban_apis` (`trang_thai`);

ALTER TABLE `phan_anhs` ADD FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dungs` (`id`);

ALTER TABLE `phan_anhs` ADD FOREIGN KEY (`co_quan_phu_trach_id`) REFERENCES `co_quan_xu_lys` (`id`);

ALTER TABLE `phan_anhs` ADD FOREIGN KEY (`trung_lap_voi_id`) REFERENCES `phan_anhs` (`id`);

ALTER TABLE `su_cos` ADD FOREIGN KEY (`phan_anh_id`) REFERENCES `phan_anhs` (`id`);

ALTER TABLE `su_cos` ADD FOREIGN KEY (`co_quan_phu_trach_id`) REFERENCES `co_quan_xu_lys` (`id`);

ALTER TABLE `canh_baos` ADD FOREIGN KEY (`su_co_id`) REFERENCES `su_cos` (`id`);

ALTER TABLE `thong_baos` ADD FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dungs` (`id`);

ALTER TABLE `thong_baos` ADD FOREIGN KEY (`canh_bao_id`) REFERENCES `canh_baos` (`id`);

ALTER TABLE `quan_sats` ADD FOREIGN KEY (`cam_bien_id`) REFERENCES `cam_biens` (`id`);

ALTER TABLE `tep_phuong_tiends` ADD FOREIGN KEY (`phan_anh_id`) REFERENCES `phan_anhs` (`id`);

ALTER TABLE `giao_dich_vi_dien_tus` ADD FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dungs` (`id`);

ALTER TABLE `giao_dich_vi_dien_tus` ADD FOREIGN KEY (`phan_anh_lien_quan_id`) REFERENCES `phan_anhs` (`id`);

ALTER TABLE `nhat_ky_he_thongs` ADD FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dungs` (`id`);

ALTER TABLE `quan_he_entities` ADD FOREIGN KEY (`entity_nguon_id`) REFERENCES `ngsi_entities` (`id`);

ALTER TABLE `quan_he_entities` ADD FOREIGN KEY (`entity_dich_id`) REFERENCES `ngsi_entities` (`id`);

ALTER TABLE `canh_bao_ngap_luts` ADD FOREIGN KEY (`khu_vuc_id`) REFERENCES `khu_vuc_ngap_luts` (`id`);

ALTER TABLE `cam_bien_muc_nuocs` ADD FOREIGN KEY (`cam_bien_id`) REFERENCES `cam_biens` (`id`);

ALTER TABLE `cam_bien_muc_nuocs` ADD FOREIGN KEY (`khu_vuc_id`) REFERENCES `khu_vuc_ngap_luts` (`id`);

ALTER TABLE `bo_nho_dem_du_lieus` ADD FOREIGN KEY (`nguon_id`) REFERENCES `nguon_du_lieu_mos` (`id`);

ALTER TABLE `du_lieu_huan_luyen_ais` ADD FOREIGN KEY (`phan_anh_id`) REFERENCES `phan_anhs` (`id`);

ALTER TABLE `du_lieu_huan_luyen_ais` ADD FOREIGN KEY (`nguoi_xac_minh_id`) REFERENCES `quan_tri_viens` (`id`);

ALTER TABLE `binh_luan_phan_anhs` ADD FOREIGN KEY (`phan_anh_id`) REFERENCES `phan_anhs` (`id`);

ALTER TABLE `binh_luan_phan_anhs` ADD FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dungs` (`id`);

ALTER TABLE `binh_luan_phan_anhs` ADD FOREIGN KEY (`binh_luan_cha_id`) REFERENCES `binh_luan_phan_anhs` (`id`);

ALTER TABLE `binh_chon_phan_anhs` ADD FOREIGN KEY (`phan_anh_id`) REFERENCES `phan_anhs` (`id`);

ALTER TABLE `binh_chon_phan_anhs` ADD FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dungs` (`id`);

ALTER TABLE `hieu_suat_co_quans` ADD FOREIGN KEY (`co_quan_id`) REFERENCES `co_quan_xu_lys` (`id`);

ALTER TABLE `lich_su_trang_thai_phan_anhs` ADD FOREIGN KEY (`phan_anh_id`) REFERENCES `phan_anhs` (`id`);

ALTER TABLE `lich_su_trang_thai_phan_anhs` ADD FOREIGN KEY (`nguoi_thay_doi_id`) REFERENCES `nguoi_dungs` (`id`);

ALTER TABLE `chi_tiet_tuyen_trams` ADD FOREIGN KEY (`tuyen_id`) REFERENCES `tuyen_giao_thongs` (`id`);

ALTER TABLE `chi_tiet_tuyen_trams` ADD FOREIGN KEY (`tram_id`) REFERENCES `tram_dungs` (`id`);

package sync

import (
	"database/sql"
	"fmt"
	"log"
	"time"

	_ "github.com/go-sql-driver/mysql"
	"searchservice/internal/config"
	"searchservice/internal/meilisearch"
	"searchservice/internal/models"
)

type Syncer struct {
	db     *sql.DB
	meili  *meilisearch.Client
	config *config.Config
}

// NewSyncer creates a new database syncer
func NewSyncer(cfg *config.Config, meili *meilisearch.Client) (*Syncer, error) {
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true",
		cfg.MySQLUser,
		cfg.MySQLPassword,
		cfg.MySQLHost,
		cfg.MySQLPort,
		cfg.MySQLDatabase,
	)

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, fmt.Errorf("failed to connect to MySQL: %w", err)
	}

	// Test connection
	if err := db.Ping(); err != nil {
		return nil, fmt.Errorf("failed to ping MySQL: %w", err)
	}

	return &Syncer{
		db:     db,
		meili:  meili,
		config: cfg,
	}, nil
}

// SyncAll syncs all reports from MySQL to Meilisearch
func (s *Syncer) SyncAll() (int, error) {
	log.Println("Starting full sync...")

	query := `
		SELECT 
			p.id,
			p.tieu_de,
			p.mo_ta,
			p.danh_muc_id,
			dm.ten_danh_muc AS danh_muc_ten,
			p.trang_thai,
			p.uu_tien_id,
			COALESCE(ut.ten_muc, '') AS uu_tien_ten,
			p.vi_do,
			p.kinh_do,
			p.dia_chi,
			p.luot_ung_ho,
			UNIX_TIMESTAMP(p.created_at) AS created_at,
			UNIX_TIMESTAMP(p.updated_at) AS updated_at
		FROM phan_anhs p
		LEFT JOIN danh_muc_phan_anhs dm ON p.danh_muc_id = dm.id
		LEFT JOIN muc_uu_tiens ut ON p.uu_tien_id = ut.id
		WHERE p.deleted_at IS NULL
		ORDER BY p.id
	`

	rows, err := s.db.Query(query)
	if err != nil {
		return 0, fmt.Errorf("query failed: %w", err)
	}
	defer rows.Close()

	reports := make([]models.Report, 0)
	batchSize := 100

	for rows.Next() {
		report, err := s.scanReport(rows)
		if err != nil {
			log.Printf("Warning: failed to scan report: %v", err)
			continue
		}

		reports = append(reports, report)

		// Index in batches
		if len(reports) >= batchSize {
			if err := s.meili.IndexDocuments(reports); err != nil {
				return 0, fmt.Errorf("failed to index batch: %w", err)
			}
			reports = make([]models.Report, 0)
		}
	}

	// Index remaining
	if len(reports) > 0 {
		if err := s.meili.IndexDocuments(reports); err != nil {
			return 0, fmt.Errorf("failed to index final batch: %w", err)
		}
	}

	if err := rows.Err(); err != nil {
		return 0, err
	}

	log.Println("✓ Full sync completed")
	return len(reports), nil
}

// SyncRecent syncs reports created/updated after a certain timestamp
func (s *Syncer) SyncRecent(since time.Time) (int, error) {
	log.Printf("Syncing reports since %s...", since.Format(time.RFC3339))

	query := `
		SELECT 
			p.id,
			p.tieu_de,
			p.mo_ta,
			p.danh_muc_id,
			dm.ten_danh_muc AS danh_muc_ten,
			p.trang_thai,
			p.uu_tien_id,
			COALESCE(ut.ten_muc, '') AS uu_tien_ten,
			p.vi_do,
			p.kinh_do,
			p.dia_chi,
			p.luot_ung_ho,
			UNIX_TIMESTAMP(p.created_at) AS created_at,
			UNIX_TIMESTAMP(p.updated_at) AS updated_at
		FROM phan_anhs p
		LEFT JOIN danh_muc_phan_anhs dm ON p.danh_muc_id = dm.id
		LEFT JOIN muc_uu_tiens ut ON p.uu_tien_id = ut.id
		WHERE p.deleted_at IS NULL 
		  AND p.updated_at >= ?
		ORDER BY p.updated_at
	`

	rows, err := s.db.Query(query, since)
	if err != nil {
		return 0, fmt.Errorf("query failed: %w", err)
	}
	defer rows.Close()

	reports := make([]models.Report, 0)

	for rows.Next() {
		report, err := s.scanReport(rows)
		if err != nil {
			log.Printf("Warning: failed to scan report: %v", err)
			continue
		}
		reports = append(reports, report)
	}

	if len(reports) > 0 {
		if err := s.meili.IndexDocuments(reports); err != nil {
			return 0, fmt.Errorf("failed to index documents: %w", err)
		}
	}

	log.Printf("✓ Synced %d recent reports", len(reports))
	return len(reports), nil
}

// scanReport scans a row into a Report struct
func (s *Syncer) scanReport(rows *sql.Rows) (models.Report, error) {
	var report models.Report
	var viDo, kinhDo sql.NullFloat64
	var danhMucTen, uuTienTen sql.NullString

	err := rows.Scan(
		&report.ID,
		&report.TieuDe,
		&report.MoTa,
		&report.DanhMucID,
		&danhMucTen,
		&report.TrangThai,
		&report.UuTienID,
		&uuTienTen,
		&viDo,
		&kinhDo,
		&report.DiaChi,
		&report.LuotUngHo,
		&report.CreatedAtTS,
		&report.UpdatedAtTS,
	)

	if err != nil {
		return report, err
	}

	// Set optional fields
	if danhMucTen.Valid {
		report.DanhMucTen = danhMucTen.String
	}
	if uuTienTen.Valid {
		report.UuTienTen = uuTienTen.String
	}

	// Set geo point if coordinates are valid
	if viDo.Valid && kinhDo.Valid {
		report.Geo = &models.GeoPoint{
			Lat: viDo.Float64,
			Lng: kinhDo.Float64,
		}
	}

	return report, nil
}

// Close closes the database connection
func (s *Syncer) Close() {
	if s.db != nil {
		s.db.Close()
	}
}

/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

package mysql

import (
	"database/sql"
	"fmt"
	
	_ "github.com/go-sql-driver/mysql"
	"contextbrokeradapter/internal/config"
	"contextbrokeradapter/internal/models"
)

type Client struct {
	db *sql.DB
}

func NewClient(cfg *config.Config) (*Client, error) {
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
	
	if err := db.Ping(); err != nil {
		return nil, fmt.Errorf("failed to ping MySQL: %w", err)
	}
	
	return &Client{db: db}, nil
}

func (c *Client) GetAllReports() ([]models.Report, error) {
	query := `
		SELECT 
			p.id,
			p.tieu_de,
			p.mo_ta,
			p.danh_muc_id,
			COALESCE(dm.ten_danh_muc, '') AS ten_danh_muc,
			p.trang_thai,
			p.uu_tien_id,
			COALESCE(ut.ten_muc, '') AS ten_muc,
			p.vi_do,
			p.kinh_do,
			p.dia_chi,
			p.luot_ung_ho,
			p.created_at,
			p.updated_at
		FROM phan_anhs p
		LEFT JOIN danh_muc_phan_anhs dm ON p.danh_muc_id = dm.id
		LEFT JOIN muc_uu_tiens ut ON p.uu_tien_id = ut.id
		WHERE p.deleted_at IS NULL
		ORDER BY p.id
	`
	
	rows, err := c.db.Query(query)
	if err != nil {
		return nil, fmt.Errorf("query failed: %w", err)
	}
	defer rows.Close()
	
	var reports []models.Report
	for rows.Next() {
		var r models.Report
		err := rows.Scan(
			&r.ID,
			&r.TieuDe,
			&r.MoTa,
			&r.DanhMucID,
			&r.DanhMucTen,
			&r.TrangThai,
			&r.UuTienID,
			&r.UuTienTen,
			&r.ViDo,
			&r.KinhDo,
			&r.DiaChi,
			&r.LuotUngHo,
			&r.CreatedAt,
			&r.UpdatedAt,
		)
		if err != nil {
			return nil, fmt.Errorf("scan failed: %w", err)
		}
		reports = append(reports, r)
	}
	
	return reports, nil
}

func (c *Client) Close() {
	if c.db != nil {
		c.db.Close()
	}
}

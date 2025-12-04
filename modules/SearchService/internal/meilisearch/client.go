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

package meilisearch

import (
	"fmt"
	"log"

	"github.com/meilisearch/meilisearch-go"
	"searchservice/internal/models"
)

const IndexName = "reports"

type Client struct {
	client *meilisearch.Client
	index  *meilisearch.Index
}

// NewClient creates a new Meilisearch client
func NewClient(url, apiKey string) (*Client, error) {
	client := meilisearch.NewClient(meilisearch.ClientConfig{
		Host:   url,
		APIKey: apiKey,
	})

	// Get or create index
	index := client.Index(IndexName)

	// Configure index settings
	if err := configureIndex(index); err != nil {
		return nil, fmt.Errorf("failed to configure index: %w", err)
	}

	return &Client{
		client: client,
		index:  index,
	}, nil
}

// configureIndex sets up searchable, filterable attributes
func configureIndex(index *meilisearch.Index) error {
	settings := &meilisearch.Settings{
		SearchableAttributes: []string{
			"tieu_de",
			"mo_ta",
			"dia_chi",
			"danh_muc_ten",
		},
		FilterableAttributes: []string{
			"danh_muc_id",
			"trang_thai",
			"uu_tien_id",
			"created_at",
			"updated_at",
		},
		SortableAttributes: []string{
			"created_at",
			"updated_at",
			"luot_ung_ho",
		},
		RankingRules: []string{
			"words",
			"typo",
			"proximity",
			"attribute",
			"sort",
			"exactness",
		},
	}

	_, err := index.UpdateSettings(settings)
	if err != nil {
		return err
	}

	log.Println("✓ Index settings configured")
	return nil
}

// IndexDocuments adds or updates documents in Meilisearch
func (c *Client) IndexDocuments(reports []models.Report) error {
	if len(reports) == 0 {
		return nil
	}

	task, err := c.index.AddDocuments(reports, "id")
	if err != nil {
		return fmt.Errorf("failed to index documents: %w", err)
	}

	log.Printf("Indexed %d documents (task: %d)", len(reports), task.TaskUID)
	return nil
}

// Search performs a search query
func (c *Client) Search(req *models.SearchRequest) (*models.SearchResponse, error) {
	searchReq := &meilisearch.SearchRequest{
		Limit:  int64(req.Limit),
		Offset: int64(req.Offset),
	}

	// Build filter string
	var filters []string
	if req.CategoryID != nil {
		filters = append(filters, fmt.Sprintf("danh_muc_id = %d", *req.CategoryID))
	}
	if req.Status != nil {
		filters = append(filters, fmt.Sprintf("trang_thai = %d", *req.Status))
	}
	if req.PriorityID != nil {
		filters = append(filters, fmt.Sprintf("uu_tien_id = %d", *req.PriorityID))
	}

	if len(filters) > 0 {
		filterStr := ""
		for i, f := range filters {
			if i > 0 {
				filterStr += " AND "
			}
			filterStr += f
		}
		searchReq.Filter = filterStr
	}

	// Geo search
	if req.Lat != nil && req.Lng != nil {
		radius := 5000 // default 5km
		if req.Radius != nil {
			radius = *req.Radius
		}
		searchReq.Filter = fmt.Sprintf("_geoRadius(%f, %f, %d)", *req.Lat, *req.Lng, radius)
	}

	// Facets
	if req.Facets != "" {
		searchReq.Facets = []string{req.Facets}
	}

	// Execute search
	result, err := c.index.Search(req.Query, searchReq)
	if err != nil {
		return nil, fmt.Errorf("search failed: %w", err)
	}

	// Parse hits
	hits := make([]models.Report, 0)
	for _, hit := range result.Hits {
		// Type assertion to map
		if hitMap, ok := hit.(map[string]interface{}); ok {
			report := parseHit(hitMap)
			hits = append(hits, report)
		}
	}

	return &models.SearchResponse{
		Hits:                hits,
		Query:               req.Query,
		ProcessingTimeMs:    int(result.ProcessingTimeMs),
		EstimatedTotalHits:  int(result.EstimatedTotalHits),
		Offset:              int(result.Offset),
		Limit:               int(result.Limit),
		FacetDistribution:   convertFacetDistribution(result.FacetDistribution),
	}, nil
}

// parseHit converts a hit map to Report struct
func parseHit(m map[string]interface{}) models.Report {
	report := models.Report{}

	if id, ok := m["id"].(float64); ok {
		report.ID = int64(id)
	}
	if title, ok := m["tieu_de"].(string); ok {
		report.TieuDe = title
	}
	if desc, ok := m["mo_ta"].(string); ok {
		report.MoTa = desc
	}
	if catID, ok := m["danh_muc_id"].(float64); ok {
		report.DanhMucID = int(catID)
	}
	if catName, ok := m["danh_muc_ten"].(string); ok {
		report.DanhMucTen = catName
	}
	if status, ok := m["trang_thai"].(float64); ok {
		report.TrangThai = int(status)
	}
	if priID, ok := m["uu_tien_id"].(float64); ok {
		report.UuTienID = int(priID)
	}
	if addr, ok := m["dia_chi"].(string); ok {
		report.DiaChi = addr
	}
	if votes, ok := m["luot_ung_ho"].(float64); ok {
		report.LuotUngHo = int(votes)
	}
	if created, ok := m["created_at"].(float64); ok {
		report.CreatedAtTS = int64(created)
	}

	return report
}

// convertFacetDistribution safely converts facet distribution interface to map
func convertFacetDistribution(facets interface{}) map[string]interface{} {
	if facets == nil {
		return nil
	}
	if m, ok := facets.(map[string]interface{}); ok {
		return m
	}
	return nil
}

// ClearIndex deletes all documents from the index
func (c *Client) ClearIndex() error {
	_, err := c.index.DeleteAllDocuments()
	return err
}

// IsHealthy checks if Meilisearch is healthy
func (c *Client) IsHealthy() bool {
	health, err := c.client.Health()
	if err != nil {
		return false
	}
	return health.Status == "available"
}

// GetStats returns index statistics
func (c *Client) GetStats() (map[string]interface{}, error) {
	stats, err := c.index.GetStats()
	if err != nil {
		return nil, err
	}

	return map[string]interface{}{
		"numberOfDocuments": stats.NumberOfDocuments,
		"isIndexing":        stats.IsIndexing,
		"fieldDistribution": stats.FieldDistribution,
	}, nil
}

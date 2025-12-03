package transform

import (
	"fmt"
	
	"contextbrokeradapter/internal/models"
)

// ToNGSILD converts a MySQL Report to NGSI-LD entity
func ToNGSILD(report models.Report) models.NGSILDEntity {
	entity := models.NGSILDEntity{
		"id":   fmt.Sprintf("urn:ngsi-ld:Report:%d", report.ID),
		"type": "Report",
		"@context": "https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld",
	}
	
	// Title
	entity["title"] = map[string]interface{}{
		"type":  "Property",
		"value": report.TieuDe,
	}
	
	// Description
	if report.MoTa != "" {
		entity["description"] = map[string]interface{}{
			"type":  "Property",
			"value": report.MoTa,
		}
	}
	
	// Category
	entity["category"] = map[string]interface{}{
		"type":  "Property",
		"value": MapCategory(report.DanhMucID),
	}
	
	if report.DanhMucTen != "" {
		entity["subCategory"] = map[string]interface{}{
			"type":  "Property",
			"value": report.DanhMucTen,
		}
	}
	
	// Status
	entity["status"] = map[string]interface{}{
		"type":  "Property",
		"value": MapStatus(report.TrangThai),
	}
	
	// Severity/Priority
	entity["severity"] = map[string]interface{}{
		"type":  "Property",
		"value": MapSeverity(report.UuTienID),
	}
	
	// Location (GeoProperty)
	if report.ViDo != nil && report.KinhDo != nil {
		entity["location"] = map[string]interface{}{
			"type": "GeoProperty",
			"value": map[string]interface{}{
				"type":        "Point",
				"coordinates": []float64{*report.KinhDo, *report.ViDo},
			},
		}
	}
	
	// Address
	if report.DiaChi != "" {
		entity["address"] = map[string]interface{}{
			"type": "Property",
			"value": map[string]interface{}{
				"addressLocality": "Ho Chi Minh City",
				"addressCountry":  "VN",
				"streetAddress":   report.DiaChi,
			},
		}
	}
	
	// Vote count
	entity["voteCount"] = map[string]interface{}{
		"type":  "Property",
		"value": report.LuotUngHo,
	}
	
	// Alert source
	entity["alertSource"] = map[string]interface{}{
		"type":  "Property",
		"value": "citizen-report",
	}
	
	// Timestamps - TODO: Fix format issue with Orion-LD
	// if report.CreatedAt != "" {
	// 	entity["dateIssued"] = map[string]interface{}{
	// 		"type":  "Property",
	// 		"value": report.CreatedAt,
	// 	}
	// }
	
	// if report.UpdatedAt != "" {
	// 	entity["dateModified"] = map[string]interface{}{
	// 		"type":  "Property",
	// 		"value": report.UpdatedAt,
	// 	}
	// }
	
	return entity
}

// MapCategory maps MySQL category ID to NGSI-LD category
func MapCategory(id int) string {
	categories := map[int]string{
		1: "traffic",
		2: "environment",
		3: "infrastructure",
		4: "publicService",
		5: "safety",
		6: "health",
	}
	if cat, ok := categories[id]; ok {
		return cat
	}
	return "other"
}

// MapStatus maps MySQL status to NGSI-LD status
func MapStatus(status int) string {
	statuses := map[int]string{
		0: "pending",
		1: "verified",
		2: "in_progress",
		3: "resolved",
		4: "rejected",
	}
	if s, ok := statuses[status]; ok {
		return s
	}
	return "pending"
}

// MapSeverity maps MySQL priority to NGSI-LD severity
func MapSeverity(priority int) string {
	severities := map[int]string{
		1: "low",
		2: "medium",
		3: "high",
		4: "critical",
	}
	if sev, ok := severities[priority]; ok {
		return sev
	}
	return "medium"
}

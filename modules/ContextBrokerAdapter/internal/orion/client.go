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

package orion

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	
	"contextbrokeradapter/internal/config"
	"contextbrokeradapter/internal/models"
)

type Client struct {
	baseURL string
	client  *http.Client
}

func NewClient(cfg *config.Config) *Client {
	return &Client{
		baseURL: cfg.OrionURL,
		client:  &http.Client{Timeout: 10 * 1000000000}, // 10s
	}
}

// CreateEntity creates or updates an entity in Orion-LD
func (c *Client) CreateEntity(entity models.NGSILDEntity) error {
	url := fmt.Sprintf("%s/ngsi-ld/v1/entities", c.baseURL)
	
	jsonData, err := json.Marshal(entity)
	if err != nil {
		return fmt.Errorf("marshal failed: %w", err)
	}
	
	req, err := http.NewRequest("POST", url, bytes.NewBuffer(jsonData))
	if err != nil {
		return fmt.Errorf("create request failed: %w", err)
	}
	
	req.Header.Set("Content-Type", "application/ld+json")
	
	resp, err := c.client.Do(req)
	if err != nil {
		return fmt.Errorf("request failed: %w", err)
	}
	defer resp.Body.Close()
	
	// 201 = created, 409 = already exists (update instead)
	if resp.StatusCode == 409 {
		// Entity exists, update instead
		return c.UpdateEntity(entity)
	}
	
	if resp.StatusCode != 201 {
		body, _ := io.ReadAll(resp.Body)
		return fmt.Errorf("unexpected status %d: %s", resp.StatusCode, string(body))
	}
	
	return nil
}

// UpdateEntity updates an existing entity
func (c *Client) UpdateEntity(entity models.NGSILDEntity) error {
	entityID := entity["id"].(string)
	url := fmt.Sprintf("%s/ngsi-ld/v1/entities/%s/attrs", c.baseURL, entityID)
	
	// Remove id, type, @context from attrs update
	attrs := make(models.NGSILDEntity)
	for k, v := range entity {
		if k != "id" && k != "type" && k != "@context" {
			attrs[k] = v
		}
	}
	
	jsonData, err := json.Marshal(attrs)
	if err != nil {
		return fmt.Errorf("marshal failed: %w", err)
	}
	
	req, err := http.NewRequest("PATCH", url, bytes.NewBuffer(jsonData))
	if err != nil {
		return fmt.Errorf("create request failed: %w", err)
	}
	
	req.Header.Set("Content-Type", "application/ld+json")
	
	resp, err := c.client.Do(req)
	if err != nil {
		return fmt.Errorf("request failed: %w", err)
	}
	defer resp.Body.Close()
	
	if resp.StatusCode != 204 {
		body, _ := io.ReadAll(resp.Body)
		return fmt.Errorf("unexpected status %d: %s", resp.StatusCode, string(body))
	}
	
	return nil
}

// DeleteEntity deletes an entity
func (c *Client) DeleteEntity(entityID string) error {
	url := fmt.Sprintf("%s/ngsi-ld/v1/entities/%s", c.baseURL, entityID)
	
	req, err := http.NewRequest("DELETE", url, nil)
	if err != nil {
		return fmt.Errorf("create request failed: %w", err)
	}
	
	resp, err := c.client.Do(req)
	if err != nil {
		return fmt.Errorf("request failed: %w", err)
	}
	defer resp.Body.Close()
	
	if resp.StatusCode != 204 {
		body, _ := io.ReadAll(resp.Body)
		return fmt.Errorf("unexpected status %d: %s", resp.StatusCode, string(body))
	}
	
	return nil
}

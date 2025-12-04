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

package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"

	amqp "github.com/rabbitmq/amqp091-go"
)

type ReportEvent struct {
	Event     string                 `json:"event"`
	ReportID  int                    `json:"report_id"`
	UserID    *int                   `json:"user_id"`
	Data      map[string]interface{} `json:"data"`
	Timestamp string                 `json:"timestamp"`
}

func main() {
	log.Println("🔄 Orion Sync Consumer starting...")

	rabbitMQURL := os.Getenv("RABBITMQ_URL")
	if rabbitMQURL == "" {
		rabbitMQURL = "amqp://cityresq:cityresq_password@rabbitmq:5672/"
	}

	// Connect to RabbitMQ with retry
	var conn *amqp.Connection
	var err error
	for i := 0; i < 30; i++ {
		conn, err = amqp.Dial(rabbitMQURL)
		if err == nil {
			break
		}
		log.Printf("⚠️ Failed to connect to RabbitMQ: %v. Retrying in 2s...", err)
		time.Sleep(2 * time.Second)
	}
	if err != nil {
		log.Fatalf("❌ Could not connect to RabbitMQ: %v", err)
	}
	defer conn.Close()
	log.Println("✅ Connected to RabbitMQ")

	ch, err := conn.Channel()
	if err != nil {
		log.Fatalf("❌ Failed to open a channel: %v", err)
	}
	defer ch.Close()

	// Declare queue (idempotent)
	q, err := ch.QueueDeclare(
		"orion.sync.queue",   // name
		true,                 // durable
		false,                // delete when unused
		false,                // exclusive
		false,                // no-wait
		nil,                  // arguments
	)
	if err != nil {
		log.Fatalf("❌ Failed to declare queue: %v", err)
	}

	// Bind queue to exchange
	err = ch.QueueBind(
		q.Name,              // queue name
		"report.created",    // routing key
		"cityresq.reports",  // exchange
		false,               // no-wait
		nil,                 // args
	)
	if err != nil {
		log.Fatalf("❌ Failed to bind queue: %v", err)
	}
	log.Printf("✅ Queue bound to exchange 'cityresq.reports' with routing key 'report.created'")

	// Consume messages
	msgs, err := ch.Consume(
		q.Name, // queue
		"",     // consumer
		true,   // auto-ack
		false,  // exclusive
		false,  // no-local
		false,  // no-wait
		nil,    // args
	)
	if err != nil {
		log.Fatalf("❌ Failed to register a consumer: %v", err)
	}

	// Handle messages
	go func() {
		for d := range msgs {
			var event ReportEvent
			if err := json.Unmarshal(d.Body, &event); err != nil {
				log.Printf("⚠️ Error parsing JSON: %v", err)
				continue
			}

			handleEvent(event)
		}
	}()

	log.Println("🎧 Waiting for messages. To exit press CTRL+C")
	
	// Keep alive
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	<-quit
	log.Println("👋 Shutting down Orion Sync Consumer")
}

func handleEvent(event ReportEvent) {
	log.Printf("🌍 [ORION SYNC] Processing event '%s' for Report #%d", event.Event, event.ReportID)
	
	if event.Event == "report.created" || event.Event == "report.updated" {
		log.Printf("   → Transforming Report #%d to NGSI-LD Entity...", event.ReportID)
		
		// Transform to NGSI-LD format
		entity := transformToNGSILD(event)
		
		log.Printf("   → Pushing to Context Broker (http://orion-ld:1026/ngsi-ld/v1/entities)...")
		
		// POST to Orion-LD
		err := postToOrionLD(entity, event.ReportID)
		if err != nil {
			log.Printf("   ❌ Failed to sync: %v", err)
			return
		}
		
		log.Printf("   ✅ Synced successfully")
	}
}

func transformToNGSILD(event ReportEvent) map[string]interface{} {
	entity := map[string]interface{}{
		"id":   "urn:ngsi-ld:Report:" + fmt.Sprintf("%d", event.ReportID),
		"type": "Report",
		"@context": []string{
			"https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld",
		},
	}
	
	// Add report data as properties
	if title, ok := event.Data["title"].(string); ok {
		entity["title"] = map[string]interface{}{
			"type":  "Property",
			"value": title,
		}
	}
	
	if description, ok := event.Data["description"].(string); ok {
		entity["description"] = map[string]interface{}{
			"type":  "Property",
			"value": description,
		}
	}
	
	if status, ok := event.Data["status"]; ok {
		entity["status"] = map[string]interface{}{
			"type":  "Property",
			"value": status,
		}
	}
	
	// Add location if available
	if location, ok := event.Data["location"].(map[string]interface{}); ok {
		if latStr, latOk := location["lat"].(string); latOk {
			if lngStr, lngOk := location["lng"].(string); lngOk {
				// Parse strings to float64
				var lat, lng float64
				if _, err := fmt.Sscanf(latStr, "%f", &lat); err == nil {
					if _, err := fmt.Sscanf(lngStr, "%f", &lng); err == nil {
						entity["location"] = map[string]interface{}{
							"type": "GeoProperty",
							"value": map[string]interface{}{
								"type":        "Point",
								"coordinates": []interface{}{lng, lat}, // [longitude, latitude]
							},
						}
					}
				}
			}
		}
		
		if address, ok := location["address"].(string); ok {
			entity["address"] = map[string]interface{}{
				"type":  "Property",
				"value": address,
			}
		}
	}
	
	return entity
}

func postToOrionLD(entity map[string]interface{}, reportID int) error {
	orionURL := os.Getenv("ORION_LD_URL")
	if orionURL == "" {
		orionURL = "http://orion-ld:1026"
	}
	
	endpoint := orionURL + "/ngsi-ld/v1/entities"
	
	jsonData, err := json.Marshal(entity)
	if err != nil {
		return fmt.Errorf("failed to marshal entity: %v", err)
	}
	
	log.Printf("   → Payload: %s", string(jsonData))
	
	req, err := http.NewRequest("POST", endpoint, bytes.NewBuffer(jsonData))
	if err != nil {
		return fmt.Errorf("failed to create request: %v", err)
	}
	
	req.Header.Set("Content-Type", "application/ld+json")
	
	client := &http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		return fmt.Errorf("failed to send request: %v", err)
	}
	defer resp.Body.Close()
	
	// Read response body for debugging
	var responseBody interface{}
	_ = json.NewDecoder(resp.Body).Decode(&responseBody)
	
	// 201 Created or 409 Conflict (already exists) are both acceptable
	if resp.StatusCode != http.StatusCreated && resp.StatusCode != http.StatusConflict {
		log.Printf("   → Response Status: %d", resp.StatusCode)
		log.Printf("   → Response Body: %v", responseBody)
		return fmt.Errorf("unexpected status code: %d", resp.StatusCode)
	}
	
	return nil
}

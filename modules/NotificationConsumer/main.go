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
	"encoding/json"
	"log"
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
	log.Println("🚀 Notification Consumer starting...")

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
		"notification.queue", // name
		true,                 // durable
		false,                // delete when unused
		false,                // exclusive
		false,                // no-wait
		nil,                  // arguments
	)
	if err != nil {
		log.Fatalf("❌ Failed to declare queue: %v", err)
	}

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
			log.Printf("📥 Received a message: %s", d.Body)
			
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
	log.Println("👋 Shutting down Notification Consumer")
}

func handleEvent(event ReportEvent) {
	log.Printf("🔔 Processing event: %s for Report ID: %d", event.Event, event.ReportID)
	
	// Simulation of sending notification
	// In real implementation, this would call FCM or SMS service
	
	if event.Event == "report.created" {
		title, _ := event.Data["title"].(string)
		log.Printf("📱 [PUSH NOTIFICATION] New Report: %s", title)
		log.Printf("📧 [EMAIL] Sending email to admins about Report #%d", event.ReportID)
	} else if event.Event == "report.updated" {
		log.Printf("📱 [PUSH NOTIFICATION] Report #%d Updated", event.ReportID)
	}
}

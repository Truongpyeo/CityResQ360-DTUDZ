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
	log.Println("📊 Analytics Consumer starting...")

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
		"analytics.queue",    // name
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
			// log.Printf("📥 Received a message: %s", d.Body)
			
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
	log.Println("👋 Shutting down Analytics Consumer")
}

func handleEvent(event ReportEvent) {
	log.Printf("📈 [ANALYTICS] Recording event '%s' for Report #%d", event.Event, event.ReportID)
	
	// Simulation of analytics recording (e.g. to ClickHouse or TimeScaleDB)
	// In real implementation, this would insert into DB
}

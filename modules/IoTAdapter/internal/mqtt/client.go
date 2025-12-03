package mqtt

import (
	"encoding/json"
	"fmt"
	"log"
	
	mqtt "github.com/eclipse/paho.mqtt.golang"
	"iotadapter/internal/config"
)

type Client struct {
	client    mqtt.Client
	cfg       *config.Config
	handlers  map[string]MessageHandler
}

type MessageHandler func(topic string, payload []byte) error

func NewClient(cfg *config.Config) (*Client, error) {
	opts := mqtt.NewClientOptions()
	brokerURL := fmt.Sprintf("tcp://%s:%s", cfg.MQTTBroker, cfg.MQTTPort)
	opts.AddBroker(brokerURL)
	opts.SetClientID(cfg.MQTTClientID)
	opts.SetDefaultPublishHandler(messagePubHandler)
	opts.SetConnectionLostHandler(connLostHandler)
	opts.SetOnConnectHandler(connectHandler)
	
	client := mqtt.NewClient(opts)
	
	c := &Client{
		client:   client,
		cfg:      cfg,
		handlers: make(map[string]MessageHandler),
	}
	
	return c, nil
}

func (c *Client) Connect() error {
	if token := c.client.Connect(); token.Wait() && token.Error() != nil {
		return fmt.Errorf("failed to connect to MQTT broker: %w", token.Error())
	}
	log.Printf("✅ Connected to MQTT broker: %s:%s", c.cfg.MQTTBroker, c.cfg.MQTTPort)
	return nil
}

func (c *Client) Subscribe(topic string, handler MessageHandler) error {
	c.handlers[topic] = handler
	
	token := c.client.Subscribe(topic, 1, func(client mqtt.Client, msg mqtt.Message) {
		actualTopic := msg.Topic()
		
		// Find matching handler by pattern matching
		var matched bool
		for pattern, h := range c.handlers {
			if matchTopic(pattern, actualTopic) {
				if err := h(actualTopic, msg.Payload()); err != nil {
					log.Printf("❌ Error handling message from %s: %v", actualTopic, err)
				} else {
					log.Printf("✅ Message processed: %s (matched pattern: %s)", actualTopic, pattern)
				}
				matched = true
				break // Use first matching handler
			}
		}
		
		if !matched {
			log.Printf("⚠️  No handler found for topic: %s", actualTopic)
		}
	})
	
	token.Wait()
	if token.Error() != nil {
		return fmt.Errorf("failed to subscribe to %s: %w", topic, token.Error())
	}
	
	log.Printf("📡 Subscribed to: %s", topic)
	return nil
}

func (c *Client) Disconnect() {
	c.client.Disconnect(250)
	log.Println("✅ Disconnected from MQTT broker")
}

// Helper function to parse JSON payload
func ParsePayload(payload []byte, v interface{}) error {
	return json.Unmarshal(payload, v)
}

// matchTopic checks if an actual topic matches a topic pattern with wildcards
// Supports single-level wildcard (+) and multi-level wildcard (#)
// Examples:
//   - matchTopic("sensors/+/data", "sensors/temp/data") → true
//   - matchTopic("sensors/+/data", "sensors/temp/other") → false
//   - matchTopic("sensors/#", "sensors/temp/data/sub") → true
func matchTopic(pattern, topic string) bool {
	patternParts := splitTopic(pattern)
	topicParts := splitTopic(topic)
	
	// Multi-level wildcard at the end matches everything after
	if len(patternParts) > 0 && patternParts[len(patternParts)-1] == "#" {
		// Check all parts before the #
		for i := 0; i < len(patternParts)-1; i++ {
			if i >= len(topicParts) {
				return false
			}
			if patternParts[i] != "+" && patternParts[i] != topicParts[i] {
				return false
			}
		}
		return true
	}
	
	// Without multi-level wildcard, lengths must match
	if len(patternParts) != len(topicParts) {
		return false
	}
	
	for i := range patternParts {
		if patternParts[i] == "+" {
			continue // Single-level wildcard matches any single level
		}
		if patternParts[i] != topicParts[i] {
			return false
		}
	}
	return true
}

// splitTopic splits a topic string by '/' delimiter
func splitTopic(topic string) []string {
	if topic == "" {
		return []string{}
	}
	parts := []string{}
	for _, part := range splitString(topic, '/') {
		if part != "" {
			parts = append(parts, part)
		}
	}
	return parts
}

// splitString splits a string by a delimiter (simple implementation)
func splitString(s string, delimiter rune) []string {
	var parts []string
	var current string
	for _, c := range s {
		if c == delimiter {
			parts = append(parts, current)
			current = ""
		} else {
			current += string(c)
		}
	}
	parts = append(parts, current)
	return parts
}

// Message handlers
var messagePubHandler mqtt.MessageHandler = func(client mqtt.Client, msg mqtt.Message) {
	// Default handler - logged for debugging
}

var connectHandler mqtt.OnConnectHandler = func(client mqtt.Client) {
	log.Println("🔌 MQTT connection established")
}

var connLostHandler mqtt.ConnectionLostHandler = func(client mqtt.Client, err error) {
	log.Printf("⚠️  MQTT connection lost: %v", err)
	log.Println("🔄 Attempting to reconnect...")
}

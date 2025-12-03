<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;
use Log;

class RabbitMQService
{
    protected $connection;
    protected $channel;
    protected $config;

    public function __construct()
    {
        $this->config = [
            'host' => env('RABBITMQ_HOST', 'rabbitmq'),
            'port' => env('RABBITMQ_PORT', 5672),
            'user' => env('RABBITMQ_USER', 'cityresq'),
            'password' => env('RABBITMQ_PASSWORD', 'cityresq_password'),
            'vhost' => env('RABBITMQ_VHOST', '/'),
        ];
    }

    public function connect()
    {
        if ($this->connection && $this->connection->isConnected()) {
            return;
        }

        try {
            $this->connection = new AMQPStreamConnection(
                $this->config['host'],
                $this->config['port'],
                $this->config['user'],
                $this->config['password'],
                $this->config['vhost']
            );

            $this->channel = $this->connection->channel();
        } catch (Exception $e) {
            Log::error('RabbitMQ connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function publish(string $exchange, string $routingKey, array $data)
    {
        $this->connect();

        // Declare exchange (idempotent - safe to call even if exists)
        $this->channel->exchange_declare(
            $exchange,           // exchange name
            'topic',            // type
            false,              // passive
            true,               // durable
            false               // auto_delete
        );

        $message = new AMQPMessage(
            json_encode($data),
            ['delivery_mode' => 2] // 2 = persistent
        );

        $this->channel->basic_publish($message, $exchange, $routingKey);

        Log::info("Published to RabbitMQ", [
            'exchange' => $exchange,
            'routing_key' => $routingKey,
            'data' => $data,
        ]);
    }

    public function publishReportEvent(string $eventType, $report, $user = null)
    {
        $data = [
            'event' => $eventType,
            'report_id' => $report->id,
            'user_id' => $user?->id,
            'data' => [
                'title' => $report->tieu_de,
                'description' => $report->mo_ta,
                'category_id' => $report->danh_muc_id,
                'status' => $report->trang_thai,
                'severity' => $report->muc_do,
                'location' => [
                    'lat' => $report->vi_do,
                    'lng' => $report->kinh_do,
                    'address' => $report->dia_chi,
                ],
                'created_at' => $report->created_at?->toIso8601String(),
                'updated_at' => $report->updated_at?->toIso8601String(),
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $this->publish('cityresq.reports', $eventType, $data);
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

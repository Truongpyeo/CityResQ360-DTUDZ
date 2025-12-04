<?php
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

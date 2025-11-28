<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weather_observations', function (Blueprint $table) {
            $table->id();
            
            // Location
            $table->decimal('location_lat', 10, 7)->index();
            $table->decimal('location_lng', 10, 7)->index();
            $table->string('location_name', 100)->default('Ho Chi Minh City');
            
            // Temperature (Celsius)
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('feels_like', 5, 2)->nullable();
            $table->decimal('temp_min', 5, 2)->nullable();
            $table->decimal('temp_max', 5, 2)->nullable();
            
            // Atmospheric
            $table->decimal('pressure', 6, 2)->nullable(); // hPa
            $table->decimal('humidity', 5, 2)->nullable(); // percentage
            $table->decimal('visibility', 6, 2)->nullable(); // km
            
            // Wind
            $table->decimal('wind_speed', 6, 2)->nullable(); // km/h
            $table->integer('wind_direction')->nullable(); // degrees
            $table->decimal('wind_gust', 6, 2)->nullable(); // km/h
            
            // Precipitation & Clouds
            $table->decimal('precipitation', 8, 2)->default(0); // mm
            $table->integer('cloudiness')->nullable(); // percentage
            
            // Weather Type
            $table->string('weather_type', 50)->nullable();
            $table->string('weather_description')->nullable();
            $table->string('weather_icon', 10)->nullable();
            
            // Timestamps
            $table->timestamp('observed_at')->index();
            $table->timestamp('sunrise')->nullable();
            $table->timestamp('sunset')->nullable();
            
            // Metadata
            $table->string('source', 100)->default('OpenWeatherMap');
            $table->json('raw_data')->nullable();
            
            $table->timestamps();
            
            // Composite index for location-based queries
            $table->index(['location_lat', 'location_lng', 'observed_at'], 'location_time_idx');
        });
        
        // Create view for latest weather
        DB::statement('
            CREATE OR REPLACE VIEW latest_weather AS
            SELECT DISTINCT ON (location_lat, location_lng)
                *
            FROM weather_observations
            ORDER BY location_lat, location_lng, observed_at DESC
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS latest_weather');
        Schema::dropIfExists('weather_observations');
    }
};

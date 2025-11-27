/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

const axios = require('axios');

/**
 * OpenWeatherMap Integration Service
 * 
 * Fetchweather data from OpenWeatherMap API and transform to NGSI-LD format
 * 
 * @see https://openweathermap.org/api
 * @see https://smartdatamodels.org/dataModel.Weather/WeatherObserved
 */
class WeatherService {
    constructor() {
        this.apiKey = process.env.OPENWEATHER_API_KEY;
        this.baseUrl = 'https://api.openweathermap.org/data/2.5';
        this.defaultLocation = {
            lat: 10.7769, // TP.HCM
            lon: 106.7009
        };
    }

    /**
     * Lấy thời tiết hiện tại
     * 
     * @param {number} lat - Latitude
     * @param {number} lon - Longitude
     * @returns {Promise<Object>} Weather data
     */
    async getCurrentWeather(lat = null, lon = null) {
        try {
            const latitude = lat || this.defaultLocation.lat;
            const longitude = lon || this.defaultLocation.lon;

            const response = await axios.get(`${this.baseUrl}/weather`, {
                params: {
                    lat: latitude,
                    lon: longitude,
                    appid: this.apiKey,
                    units: 'metric', // Celsius
                    lang: 'vi'
                },
                timeout: 10000
            });

            return this.transformToInternal(response.data, latitude, longitude);

        } catch (error) {
            console.error('Error fetching current weather:', error.message);
            throw new Error(`Failed to fetch weather: ${error.message}`);
        }
    }

    /**
     * Lấy dự báo 5 ngày
     * 
     * @param {number} lat - Latitude
     * @param {number} lon - Longitude
     * @returns {Promise<Array>} Forecast data array
     */
    async getForecast(lat = null, lon = null) {
        try {
            const latitude = lat || this.defaultLocation.lat;
            const longitude = lon || this.defaultLocation.lon;

            const response = await axios.get(`${this.baseUrl}/forecast`, {
                params: {
                    lat: latitude,
                    lon: longitude,
                    appid: this.apiKey,
                    units: 'metric',
                    lang: 'vi'
                },
                timeout: 10000
            });

            // Transform all forecast entries
            return response.data.list.map(item =>
                this.transformToInternal(item, latitude, longitude, item.dt_txt)
            );

        } catch (error) {
            console.error('Error fetching forecast:', error.message);
            throw new Error(`Failed to fetch forecast: ${error.message}`);
        }
    }

    /**
     * Transform OpenWeatherMap format to internal format
     * 
     * @param {Object} data - OpenWeatherMap API response
     * @param {number} lat - Latitude
     * @param {number} lon - Longitude
     * @param {string} dateTime - ISO datetime (optional)
     * @returns {Object} Transformed weather data
     */
    transformToInternal(data, lat, lon, dateTime = null) {
        const observedAt = dateTime || new Date(data.dt * 1000).toISOString();

        return {
            location: {
                lat: lat,
                lon: lon
            },
            temperature: data.main.temp,
            feelsLike: data.main.feels_like,
            tempMin: data.main.temp_min,
            tempMax: data.main.temp_max,
            pressure: data.main.pressure,
            humidity: data.main.humidity,
            visibility: data.visibility / 1000, // meters to km
            windSpeed: data.wind.speed * 3.6, // m/s to km/h
            windDirection: data.wind.deg,
            windGust: data.wind.gust ? data.wind.gust * 3.6 : null,
            cloudiness: data.clouds.all,
            precipitation: this.calculatePrecipitation(data),
            weatherType: this.mapWeatherType(data.weather[0].main),
            weatherDescription: data.weather[0].description,
            weatherIcon: data.weather[0].icon,
            observedAt: observedAt,
            sunrise: data.sys.sunrise ? new Date(data.sys.sunrise * 1000).toISOString() : null,
            sunset: data.sys.sunset ? new Date(data.sys.sunset * 1000).toISOString() : null,
            source: 'OpenWeatherMap'
        };
    }

    /**
     * Calculate precipitation from rain/snow data
     * 
     * @param {Object} data - OpenWeatherMap data
     * @returns {number} Precipitation in mm
     */
    calculatePrecipitation(data) {
        let precipitation = 0;

        if (data.rain) {
            // Rain volume for 1h or 3h
            precipitation += data.rain['1h'] || data.rain['3h'] || 0;
        }

        if (data.snow) {
            // Snow volume for 1h or 3h
            precipitation += data.snow['1h'] || data.snow['3h'] || 0;
        }

        return precipitation;
    }

    /**
     * Map OpenWeatherMap weather type to CityResQ360 categories
     * 
     * @param {string} weatherMain - OpenWeatherMap main weather type
     * @returns {string} CityResQ360 weather type
     */
    mapWeatherType(weatherMain) {
        const typeMap = {
            'Clear': 'clear',
            'Clouds': 'cloudy',
            'Rain': 'rainy',
            'Drizzle': 'rainy',
            'Thunderstorm': 'stormy',
            'Snow': 'snowy',
            'Mist': 'foggy',
            'Fog': 'foggy',
            'Haze': 'hazy',
            'Dust': 'dusty',
            'Sand': 'dusty',
            'Smoke': 'smoky'
        };

        return typeMap[weatherMain] || 'unknown';
    }

    /**
     * Kiểm tra điều kiện thời tiết nguy hiểm
     * 
     * @param {Object} weather - Weather data
     * @returns {Object} Risk assessment
     */
    assessWeatherRisks(weather) {
        const risks = {
            flood: false,
            fire: false,
            storm: false,
            severity: 'low'
        };

        // Nguy cơ ngập lụt
        if (weather.precipitation > 50) { // > 50mm/h
            risks.flood = true;
            risks.severity = 'high';
        } else if (weather.precipitation > 20) {
            risks.flood = true;
            risks.severity = 'medium';
        }

        // Nguy cơ cháy (gió mạnh + độ ẩm thấp + nắng)
        if (weather.windSpeed > 30 && weather.humidity < 30 && weather.weatherType === 'clear') {
            risks.fire = true;
            risks.severity = risks.severity === 'high' ? 'high' : 'medium';
        }

        // Nguy cơ bão
        if (weather.weatherType === 'stormy' || weather.windSpeed > 50) {
            risks.storm = true;
            risks.severity = 'high';
        }

        return risks;
    }

    /**
     * Validate API key
     * 
     * @returns {Promise<boolean>} True if API key is valid
     */
    async validateApiKey() {
        try {
            await this.getCurrentWeather();
            return true;
        } catch (error) {
            if (error.response && error.response.status === 401) {
                console.error('Invalid OpenWeatherMap API key');
                return false;
            }
            throw error;
        }
    }
}

module.exports = WeatherService;

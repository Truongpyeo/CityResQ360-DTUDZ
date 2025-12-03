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



namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="CityResQ360 NGSI-LD API",
 *     version="1.0.0",
 *     description="API dữ liệu mở liên kết (Linked Open Data) theo chuẩn ETSI NGSI-LD cho hệ thống ứng phó khẩn cấp thành phố thông minh. Hỗ trợ 5 loại entity: Alert (Phản ánh), Organization (Tổ chức), Person (Người dùng), Category (Danh mục), WeatherObserved (Thời tiết).",
 *     @OA\Contact(
 *         email="contact@cityresq360.io.vn",
 *         name="DTU-DZ Team"
 *     ),
 *     @OA\License(
 *         name="GPL-3.0",
 *         url="https://www.gnu.org/licenses/gpl-3.0.html"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 * 
 * @OA\Server(
 *     url="https://api.cityresq360.io.vn",
 *     description="Production Server"
 * )
 * 
 * @OA\Tag(
 *     name="NGSI-LD OpenData",
 *     description="Dữ liệu mở liên kết theo chuẩn ETSI GS CIM 009. Các endpoints này tuân thủ chuẩn NGSI-LD và trả về dữ liệu ở định dạng JSON-LD với semantic context."
 * )
 */
abstract class Controller
{
    //
}

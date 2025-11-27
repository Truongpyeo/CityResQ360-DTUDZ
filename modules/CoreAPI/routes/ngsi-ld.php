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

use App\Http\Controllers\Api\V1\NgsiLdController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| NGSI-LD API Routes
|--------------------------------------------------------------------------
|
| NGSI-LD (Next Generation Service Interfaces - Linked Data)
| Specification: ETSI GS CIM 009 V1.6.1
| 
| For OLP 2025 competition - Smart City Linked Open Data requirement
|
| Base URL: /ngsi-ld/v1
| Content-Type: application/ld+json
|
*/

Route::prefix('ngsi-ld/v1')->group(function () {
    
    // Entities management
    Route::get('/entities', [NgsiLdController::class, 'getEntities']);
    Route::get('/entities/{id}', [NgsiLdController::class, 'getEntity']);
    Route::post('/entities', [NgsiLdController::class, 'createEntity']);
    Route::patch('/entities/{id}/attrs', [NgsiLdController::class, 'updateEntityAttrs']);
    Route::delete('/entities/{id}', [NgsiLdController::class, 'deleteEntity']);
    
    // Note: Authentication optional for GET (open data)
    // POST/PATCH/DELETE should be authenticated in production
});

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

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * NGSI-LD Resource
 * 
 * Transforms Laravel Eloquent models to NGSI-LD entity format
 * following ETSI GS CIM 009 V1.6.1 specification
 * 
 * @see https://www.etsi.org/deliver/etsi_gs/CIM/001_099/009/01.06.01_60/gs_CIM009v010601p.pdf
 * @see https://smartdatamodels.org/
 */
class NgsiLdResource extends JsonResource
{
    /**
     * Transform PhanAnh model to NGSI-LD Alert entity
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Generate URN format ID
        $entityId = "urn:ngsi-ld:Alert:" . $this->id;
        
        // Get base URL for @context
        $baseUrl = config('app.url');
        
        return [
            'id' => $entityId,
            'type' => 'Alert',
            '@context' => $baseUrl . '/@context.jsonld',
            
            // Category (traffic, environment, fire, waste, flood, other)
            'category' => $this->propertyValue($this->getCategoryName()),
            
            // Alert source
            'alertSource' => $this->propertyValue('citizen-report'),
            
            // Severity (based on priority)
            'severity' => $this->propertyValue($this->getSeverityLevel()),
            
            // Description
            'description' => $this->propertyValue($this->mo_ta ?? $this->tieu_de),
            
            // Location as GeoProperty
            'location' => $this->geoProperty($this->kinh_do, $this->vi_do),
            
            // Address
            'address' => $this->propertyValue($this->dia_chi),
            
            // Date issued
            'dateIssued' => $this->dateTimeProperty($this->created_at),
            
            // Status
            'status' => $this->propertyValue($this->getStatusName()),
            
            // Priority
            'priority' => $this->propertyValue($this->getPriorityName()),
            
            // Reported by (Relationship)
            'reportedBy' => $this->when($this->nguoi_dung_id, function() {
                return $this->relationship('User', $this->nguoi_dung_id);
            }),
            
            // Assigned to agency (Relationship)
            'assignedTo' => $this->when($this->co_quan_xu_ly_id, function() {
                return $this->relationship('Agency', $this->co_quan_xu_ly_id);
            }),
            
            // Vote count
            'voteCount' => $this->propertyValue($this->so_luot_ung_ho ?? 0, 'Integer'),
            
            // View count
            'viewCount' => $this->propertyValue($this->so_luot_xem ?? 0, 'Integer'),
            
            // Media URLs
            // Media URLs (TODO: Fix schema for hinh_anh_phan_anhs before enabling)
            /*
            'mediaUrls' => $this->when($this->hinhAnhs()->exists(), function() {
                return $this->propertyValue(
                    $this->hinhAnhs->pluck('duong_dan')->toArray(),
                    'Array'
                );
            }),
            */
            
            // Tags
            'tags' => $this->when(!empty($this->the_tags), function() {
                return $this->propertyValue($this->the_tags, 'Array');
            }),
        ];
    }
    
    /**
     * Create NGSI-LD Property
     * 
     * @param mixed $value
     * @param string $type
     * @return array
     */
    protected function propertyValue($value, $type = 'String')
    {
        $property = [
            'type' => 'Property',
            'value' => $value,
        ];
        
        // Add metadata if needed
        if ($type !== 'String') {
            $property['unitCode'] = $type;
        }
        
        return $property;
    }
    
    /**
     * Create NGSI-LD GeoProperty
     * 
     * @param float $longitude
     * @param float $latitude
     * @return array
     */
    protected function geoProperty($longitude, $latitude)
    {
        return [
            'type' => 'GeoProperty',
            'value' => [
                'type' => 'Point',
                'coordinates' => [(float)$longitude, (float)$latitude]
            ]
        ];
    }
    
    /**
     * Create NGSI-LD Relationship
     * 
     * @param string $type
     * @param int $id
     * @return array
     */
    protected function relationship($type, $id)
    {
        return [
            'type' => 'Relationship',
            'object' => "urn:ngsi-ld:{$type}:{$id}"
        ];
    }
    
    /**
     * Create NGSI-LD DateTime Property
     * 
     * @param \Carbon\Carbon $datetime
     * @return array
     */
    protected function dateTimeProperty($datetime)
    {
        return [
            'type' => 'Property',
            'value' => [
                '@type' => 'DateTime',
                '@value' => $datetime->toIso8601String()
            ]
        ];
    }
    
    /**
     * Get category name from enum
     * 
     * @return string
     */
    protected function getCategoryName()
    {
        $categories = [
            0 => 'traffic',
            1 => 'environment',
            2 => 'fire',
            3 => 'waste',
            4 => 'flood',
            5 => 'other'
        ];
        
        return $categories[$this->danh_muc] ?? 'other';
    }
    
    /**
     * Get severity level based on priority
     * 
     * @return string
     */
    protected function getSeverityLevel()
    {
        if (!isset($this->muc_uu_tien_id)) {
            return 'medium';
        }
        
        $severityMap = [
            1 => 'low',
            2 => 'medium',
            3 => 'high',
            4 => 'critical'
        ];
        
        return $severityMap[$this->muc_uu_tien_id] ?? 'medium';
    }
    
    /**
     * Get status name from enum
     * 
     * @return string
     */
    protected function getStatusName()
    {
        $statuses = [
            0 => 'pending',
            1 => 'verified',
            2 => 'in_progress',
            3 => 'resolved',
            4 => 'rejected'
        ];
        
        return $statuses[$this->trang_thai] ?? 'pending';
    }
    
    /**
     * Get priority name
     * 
     * @return string
     */
    protected function getPriorityName()
    {
        if (!isset($this->muc_uu_tien_id)) {
            return 'normal';
        }
        
        $priorities = [
            1 => 'low',
            2 => 'normal', 
            3 => 'high',
            4 => 'urgent'
        ];
        
        return $priorities[$this->muc_uu_tien_id] ?? 'normal';
    }
}

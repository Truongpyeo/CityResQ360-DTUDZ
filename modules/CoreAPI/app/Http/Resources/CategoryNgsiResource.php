<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 * Licensed under GPL-3.0
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * NGSI-LD Resource for Category (DanhMucPhanAnh)
 * 
 * Custom model for CityResQ360
 */
class CategoryNgsiResource extends JsonResource
{
    /**
     * Transform Category to NGSI-LD format
     */
    public function toArray($request)
    {
        return [
            'id' => "urn:ngsi-ld:Category:{$this->id}",
            'type' => 'Category',
            '@context' => [
                'https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld',
                'https://schema.org/',
                url('/@context.jsonld')
            ],

            // Basic info
            'name' => $this->property($this->ten_danh_muc),
            'description' => $this->property($this->mo_ta),

            // Visual properties
            'icon' => $this->property($this->icon),
            'color' => $this->property($this->mau_sac),

            // Status
            'status' => $this->property($this->trang_thai ? 'active' : 'inactive'),

            // Timestamps
            'dateCreated' => $this->dateTimeProperty($this->created_at),
            'dateModified' => $this->dateTimeProperty($this->updated_at),
        ];
    }

    /**
     * Create NGSI-LD Property
     */
    private function property($value, $type = null)
    {
        $property = [
            'type' => 'Property',
            'value' => $value
        ];

        if ($type) {
            $property['valueType'] = $type;
        }

        return $property;
    }

    /**
     * Create NGSI-LD DateTime Property
     */
    private function dateTimeProperty($value)
    {
        if (!$value) return null;

        return [
            'type' => 'Property',
            'value' => [
                '@type' => 'DateTime',
                '@value' => $value->toIso8601String()
            ]
        ];
    }
}

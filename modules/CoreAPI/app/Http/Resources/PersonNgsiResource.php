<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 * Licensed under GPL-3.0
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * NGSI-LD Resource for Person (NguoiDung)
 * 
 * Schema.org: Person
 * Spec: https://schema.org/Person
 */
class PersonNgsiResource extends JsonResource
{
    /**
     * Transform Person to NGSI-LD format
     */
    public function toArray($request)
    {
        return [
            'id' => "urn:ngsi-ld:Person:{$this->id}",
            'type' => 'Person',
            '@context' => [
                'https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld',
                'https://schema.org/',
                url('/@context.jsonld')
            ],

            // Basic info
            'name' => $this->property($this->ho_ten),
            'email' => $this->property($this->email),
            'telephone' => $this->property($this->so_dien_thoai),

            // Profile image
            'image' => $this->property($this->anh_dai_dien),

            // Address
            'address' => $this->property([
                'streetAddress' => $this->dia_chi,
                'addressCountry' => 'VN'
            ]),

            // Reputation/Points
            'reputation' => $this->property($this->diem_thanh_pho ?? 0, 'Integer'),

            // Status
            'status' => $this->property($this->trang_thai ? 'active' : 'inactive'),

            // Verification
            'emailVerified' => $this->property($this->email_verified_at !== null, 'Boolean'),
            'phoneVerified' => $this->property($this->so_dien_thoai_verified_at !== null, 'Boolean'),

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

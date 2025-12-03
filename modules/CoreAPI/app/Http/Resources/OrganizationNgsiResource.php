<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 * Licensed under GPL-3.0
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * NGSI-LD Resource for Organization (CoQuanXuLy)
 * 
 * FiWARE Smart Data Model: Organization
 * Spec: https://github.com/smart-data-models/dataModel.Organization
 */
class OrganizationNgsiResource extends JsonResource
{
    /**
     * Transform Organization to NGSI-LD format
     */
    public function toArray($request)
    {
        return [
            'id' => "urn:ngsi-ld:Organization:{$this->id}",
            'type' => 'Organization',
            '@context' => [
                'https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld',
                'https://schema.org/',
                url('/@context.jsonld')
            ],

            // Name
            'name' => $this->property($this->ten_co_quan),

            // Contact information
            'email' => $this->property($this->email_lien_he),
            'telephone' => $this->property($this->so_dien_thoai),

            // Address
            'address' => $this->property([
                'streetAddress' => $this->dia_chi,
                'addressCountry' => 'VN'
            ]),

            // Organization level (cap_do: 0=thành phố, 1=quận, 2=phường)
            'level' => $this->property($this->cap_do, 'Integer'),

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

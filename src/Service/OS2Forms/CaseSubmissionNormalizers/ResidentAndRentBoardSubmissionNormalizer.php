<?php

namespace App\Service\OS2Forms\CaseSubmissionNormalizers;

class ResidentAndRentBoardSubmissionNormalizer extends AbstractNormalizer
{
    protected function getConfig(): array
    {
        return [
            'bringer_phone' => [
                'os2forms_key' => 'indbringer_telefonnummer',
                'type' => 'int',
            ],
            'lease_address_street' => [
                'os2forms_key' => 'lejemaals_adresse_vej',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a lease address street.',
            ],
            'lease_address_number' => [
                'os2forms_key' => 'lejemaals_adresse_nummer',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a lease address street.',
            ],
            'lease_address_floor' => [
                'os2forms_key' => 'lejemaals_adresse_etage',
                'type' => 'string',
            ],
            'lease_address_side' => [
                'os2forms_key' => 'lejemaals_adresse_side',
                'type' => 'string',
            ],
            'lease_address_postal_code' => [
                'os2forms_key' => 'lejemaals_adresse_postnummer',
                'required' => true,
                'type' => 'int',
                'error_message' => 'Submission data does not contain a lease address postal code.',
            ],
            'lease_address_city' => [
                'os2forms_key' => 'lejemaals_adresse_by',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a lease address city.',
            ],
            'lease_address_extra_information' => [
                'os2forms_key' => 'lejemaals_adresse_ekstra_adresse_information',
                'type' => 'string',
            ],
            'lease_has_vacated' => [
                'os2forms_key' => 'lejemaal_fraflyttet',
                'required' => true,
                'type' => 'boolean',
                'error_message' => 'Submission data does not contain a lease has vacated property.',
            ],
            'lease_started' => [
                'os2forms_key' => 'lejemaal_lejeforhold_paabegyndt',
                'type' => 'datetime',
            ],
            'lease_size' => [
                'os2forms_key' => 'lejemaal_areal',
                'type' => 'int',
            ],
            'lease_agreed_rent' => [
                'os2forms_key' => 'lejemaal_aftalt_husleje',
                'type' => 'int',
            ],
            'lease_interior_maintenance' => [
                'os2forms_key' => 'lejemaal_indvendig_vedligeholdelse',
                'type' => 'string',
            ],
            'lease_regulated_rent' => [
                'os2forms_key' => 'lejemaal_lejen_reguleret',
                'type' => 'boolean',
            ],
            'lease_regulated_at' => [
                'os2forms_key' => 'lejemaal_reguleringsdato',
                'type' => 'datetime',
            ],
            'lease_rent_at_collection_time' => [
                'os2forms_key' => 'lejemaal_husleje_paa_indbringelsestidspunkt',
                'type' => 'int',
            ],
            'lease_security_deposit' => [
                'os2forms_key' => 'lejemaal_depositum',
                'type' => 'int',
            ],
            'lease_prepaid_rent' => [
                'os2forms_key' => 'lejemaal_forudbetalt_leje',
                'type' => 'int',
            ],
        ];
    }
}

<?php

namespace App\Service\OS2Forms\SubmissionNormalizers;

class FenceBoardSubmissionNormalizer extends AbstractSubmissionNormalizer
{
    protected function getConfig(): array
    {
        return [
            'bringer_cadastral_number' => [
                'os2forms_key' => 'indbringer_matrikelnummer',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a bringer cadastral number.',
            ],
            'accused_name' => [
                'os2forms_key' => 'modpart_navn',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a accused name.',
            ],
            'accused_address_street' => [
                'os2forms_key' => 'modpart_adresse_vej',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a accused address street.',
            ],
            'accused_address_number' => [
                'os2forms_key' => 'modpart_adresse_nummer',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a accused address number.',
            ],
            'accused_address_floor' => [
                'os2forms_key' => 'modpart_adresse_etage',
                'type' => 'string',
            ],
            'accused_address_side' => [
                'os2forms_key' => 'modpart_adresse_side',
                'type' => 'string',
            ],
            'accused_address_postal_code' => [
                'os2forms_key' => 'modpart_adresse_postnummer',
                'required' => true,
                'type' => 'int',
                'error_message' => 'Submission data does not contain a accused address postal code.',
            ],
            'accused_address_city' => [
                'os2forms_key' => 'modpart_adresse_by',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a accused address city.',
            ],
            'accused_address_extra_information' => [
                'os2forms_key' => 'modpart_adresse_ekstra_adresse_information',
                'type' => 'string',
            ],
            'accused_cadastral_number' => [
                'os2forms_key' => 'modpart_matrikelnummer',
                'type' => 'string',
            ],
            'conditions' => [
                'os2forms_key' => 'forhold',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain bringer conditions.',
            ],
            'bringer_claim' => [
                'os2forms_key' => 'paastand',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain bringer claim.',
            ],
        ];
    }
}

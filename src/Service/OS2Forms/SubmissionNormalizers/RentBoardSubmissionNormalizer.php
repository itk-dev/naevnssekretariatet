<?php

namespace App\Service\OS2Forms\SubmissionNormalizers;

class RentBoardSubmissionNormalizer extends AbstractSubmissionNormalizer
{
    protected function getConfig(): array
    {
        return [
            'lease_type' => [
                'os2forms_key' => 'lejemaal_type',
                'type' => 'string',
                'allowed_values' => [
                    'Stor',
                    'Lille',
                    null,
                ],
            ],
        ];
    }
}

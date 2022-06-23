<?php

namespace App\Service\OS2Forms\CaseSubmissionNormalizers;

use App\Exception\WebformSubmissionException;

class ResidentAndRentBoardSubmissionNormalizer implements SubmissionNormalizerInterface
{
    /**
     * Handles the shared properties between ResidentComplaintBoardCase and RentBoardCase.
     */
    public function normalizeSubmissionData(string $sender, array $submissionData): array
    {
        $normalizedArray = [];

        // Bringer phone
        $normalizedArray['bringer_phone'] = isset($submissionData['indbringer_telefonnummer']) && !empty($submissionData['indbringer_telefonnummer'])
            ? (int) $submissionData['indbringer_telefonnummer']
            : null
        ;

        // Lease address

        $normalizedArray['lease_address_street'] = isset($submissionData['lejemaals_adresse_vej']) && !empty($submissionData['lejemaals_adresse_vej'])
            ? $submissionData['lejemaals_adresse_vej']
            : throw new WebformSubmissionException('Submission data does not contain a lease address street.')
        ;

        $normalizedArray['lease_address_number'] = isset($submissionData['lejemaals_adresse_nummer']) && !empty($submissionData['lejemaals_adresse_nummer'])
            ? $submissionData['lejemaals_adresse_nummer']
            : throw new WebformSubmissionException('Submission data does not contain a lease address number.')
        ;

        $normalizedArray['lease_address_floor'] = isset($submissionData['lejemaals_adresse_etage']) && !empty($submissionData['lejemaals_adresse_etage'])
            ? $submissionData['lejemaals_adresse_etage']
            : null
        ;

        $normalizedArray['lease_address_side'] = isset($submissionData['lejemaals_adresse_side']) && !empty($submissionData['lejemaals_adresse_side'])
            ? $submissionData['lejemaals_adresse_side']
            : null
        ;

        $normalizedArray['lease_address_postal_code'] = isset($submissionData['lejemaals_adresse_postnummer']) && !empty($submissionData['lejemaals_adresse_postnummer'])
            ? $submissionData['lejemaals_adresse_postnummer']
            : throw new WebformSubmissionException('Submission data does not contain a lease address postal code.')
        ;

        $normalizedArray['lease_address_city'] = isset($submissionData['lejemaals_adresse_by']) && !empty($submissionData['lejemaals_adresse_by'])
            ? $submissionData['lejemaals_adresse_by']
            : throw new WebformSubmissionException('Submission data does not contain a lease address city.')
        ;

        $normalizedArray['lease_address_extra_information'] = isset($submissionData['lejemaals_adresse_ekstra_adresse_information']) && !empty($submissionData['lejemaals_adresse_ekstra_adresse_information'])
            ? $submissionData['lejemaals_adresse_ekstra_adresse_information']
            : null
        ;

        // Other lease properties
        // hasVacated

        $normalizedArray['lease_has_vacated'] = empty($submissionData['lejemaal_fraflyttet'])
            // else section (: match ...) must be placed on same line due to PHP CS Fixer single_line_throw rule.
            ? throw new WebformSubmissionException('Submission data does not contain a lease has vacated property.') : match ($submissionData['lejemaal_fraflyttet']) {
                'Ja' => true,
                'Nej' => false,
                default => throw new WebformSubmissionException('The lease has vacated value %s is not valid.', $submissionData['lejemaal_fraflyttet']),
            }
        ;

        // leaseStarted
        $normalizedArray['lease_started'] = isset($submissionData['lejemaal_lejeforhold_paabegyndt']) && !empty($submissionData['lejemaal_lejeforhold_paabegyndt'])
            ? new \DateTime($submissionData['lejemaal_lejeforhold_paabegyndt'])
            : null
        ;

        // leaseSize
        $normalizedArray['lease_size'] = isset($submissionData['lejemaal_areal']) && !empty($submissionData['lejemaal_areal'])
            ? (int) $submissionData['lejemaal_areal']
            : null
        ;

        // leaseAgreedRent
        $normalizedArray['lease_agreed_rent'] = isset($submissionData['lejemaal_aftalt_husleje']) && !empty($submissionData['lejemaal_aftalt_husleje'])
            ? (int) $submissionData['lejemaal_aftalt_husleje']
            : null
        ;

        // leaseInteriorMaintenance
        $normalizedArray['lease_interior_maintenance'] = isset($submissionData['lejemaal_indvendig_vedligeholdelse']) && !empty($submissionData['lejemaal_indvendig_vedligeholdelse'])
            ? $submissionData['lejemaal_indvendig_vedligeholdelse']
            : null
        ;

        // leaseRegulatedRent
        $normalizedArray['lease_regulated_rent'] = empty($submissionData['lejemaal_lejen_reguleret'])
            // else section (: match ...) must be placed on same line due to PHP CS Fixer single_line_throw rule.
            ? throw new WebformSubmissionException('Submission data does not contain a lease has vacated property.') : match ($submissionData['lejemaal_lejen_reguleret']) {
                'Ja' => true,
                'Nej' => false,
                default => throw new WebformSubmissionException('The lease regulated value %s is not valid.', $submissionData['lejemaal_lejen_reguleret']),
            }
        ;

        // leaseRegulatedAt
        $normalizedArray['lease_regulated_at'] = isset($submissionData['lejemaal_reguleringsdato']) && !empty($submissionData['lejemaal_reguleringsdato'])
            ? new \DateTime($submissionData['lejemaal_reguleringsdato'])
            : null
        ;

        // leaseRentAtCollectionTime
        $normalizedArray['lease_rent_at_collection_time'] = isset($submissionData['lejemaal_husleje_paa_indbringelsestidspunkt']) && !empty($submissionData['lejemaal_husleje_paa_indbringelsestidspunkt'])
            ? (int) $submissionData['lejemaal_husleje_paa_indbringelsestidspunkt']
            : null
        ;

        // leaseSecurityDeposit
        $normalizedArray['lease_security_deposit'] = isset($submissionData['lejemaal_depositum']) && !empty($submissionData['lejemaal_depositum'])
            ? (int) $submissionData['lejemaal_depositum']
            : null
        ;

        // leasePrepaidRent
        $normalizedArray['lease_prepaid_rent'] = isset($submissionData['lejemaal_forudbetalt_leje']) && !empty($submissionData['lejemaal_forudbetalt_leje'])
            ? (int) $submissionData['lejemaal_forudbetalt_leje']
            : null
        ;

        return $normalizedArray;
    }
}

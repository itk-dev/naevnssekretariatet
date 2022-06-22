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
        if (isset($submissionData['indbringer_telefonnummer']) && !empty($submissionData['indbringer_telefonnummer'])) {
            $normalizedArray['bringer_phone'] = (int) $submissionData['indbringer_telefonnummer'];
        } else {
            $normalizedArray['bringer_phone'] = null;
        }

        // Lease address

        if (isset($submissionData['lejemaals_adresse_vej']) && !empty($submissionData['lejemaals_adresse_vej'])) {
            $normalizedArray['lease_address_street'] = $submissionData['lejemaals_adresse_vej'];
        } else {
            $message = sprintf('Submission data does not contain a lease address street.');
            throw new WebformSubmissionException($message);
        }

        if (isset($submissionData['lejemaals_adresse_nummer']) && !empty($submissionData['lejemaals_adresse_nummer'])) {
            $normalizedArray['lease_address_number'] = $submissionData['lejemaals_adresse_nummer'];
        } else {
            $message = sprintf('Submission data does not contain a lease address number.');
            throw new WebformSubmissionException($message);
        }

        if (isset($submissionData['lejemaals_adresse_etage']) && !empty($submissionData['lejemaals_adresse_etage'])) {
            $normalizedArray['lease_address_floor'] = $submissionData['lejemaals_adresse_etage'];
        } else {
            $normalizedArray['lease_address_floor'] = null;
        }

        if (isset($submissionData['lejemaals_adresse_side']) && !empty($submissionData['lejemaals_adresse_side'])) {
            $normalizedArray['lease_address_side'] = $submissionData['lejemaals_adresse_side'];
        } else {
            $normalizedArray['lease_address_side'] = null;
        }

        if (isset($submissionData['lejemaals_adresse_postnummer']) && !empty($submissionData['lejemaals_adresse_postnummer'])) {
            $normalizedArray['lease_address_postal_code'] = (int) $submissionData['lejemaals_adresse_postnummer'];
        } else {
            $message = sprintf('Submission data does not contain a lease address postal code.');
            throw new WebformSubmissionException($message);
        }

        if (isset($submissionData['lejemaals_adresse_by']) && !empty($submissionData['lejemaals_adresse_by'])) {
            $normalizedArray['lease_address_city'] = $submissionData['lejemaals_adresse_by'];
        } else {
            $message = sprintf('Submission data does not contain a lease address city.');
            throw new WebformSubmissionException($message);
        }

        if (isset($submissionData['lejemaals_adresse_ekstra_adresse_information']) && !empty($submissionData['lejemaals_adresse_ekstra_adresse_information'])) {
            $normalizedArray['lease_address_extra_information'] = $submissionData['lejemaals_adresse_ekstra_adresse_information'];
        } else {
            $normalizedArray['lease_address_extra_information'] = null;
        }

        // Other lease properties
        // hasVacated
        if (isset($submissionData['lejemaal_fraflyttet']) && !empty($submissionData['lejemaal_fraflyttet'])) {
            if ('Ja' === $submissionData['lejemaal_fraflyttet']) {
                $normalizedArray['lease_has_vacated'] = true;
            } elseif ('Nej' === $submissionData['lejemaal_fraflyttet']) {
                $normalizedArray['lease_has_vacated'] = false;
            } else {
                $message = sprintf('The lease has vacated %s is not valid.', $submissionData['lejemaal_fraflyttet']);
                throw new WebformSubmissionException($message);
            }
        } else {
            $message = sprintf('Submission data does not contain a lease has vacated property.');
            throw new WebformSubmissionException($message);
        }

        // leaseStarted
        if (isset($submissionData['lejemaal_lejeforhold_paabegyndt']) && !empty($submissionData['lejemaal_lejeforhold_paabegyndt'])) {
            $normalizedArray['lease_started'] = new \DateTime($submissionData['lejemaal_lejeforhold_paabegyndt']);
        } else {
            $normalizedArray['lease_started'] = null;
        }

        // leaseSize
        if (isset($submissionData['lejemaal_areal']) && !empty($submissionData['lejemaal_areal'])) {
            $normalizedArray['lease_size'] = (int) $submissionData['lejemaal_areal'];
        } else {
            $normalizedArray['lease_size'] = null;
        }

        // leaseAgreedRent
        if (isset($submissionData['lejemaal_aftalt_husleje']) && !empty($submissionData['lejemaal_aftalt_husleje'])) {
            $normalizedArray['lease_agreed_rent'] = (int) $submissionData['lejemaal_aftalt_husleje'];
        } else {
            $normalizedArray['lease_agreed_rent'] = null;
        }

        // leaseInteriorMaintenance
        if (isset($submissionData['lejemaal_indvendig_vedligeholdelse']) && !empty($submissionData['lejemaal_indvendig_vedligeholdelse'])) {
            $normalizedArray['lease_interior_maintenance'] = $submissionData['lejemaal_indvendig_vedligeholdelse'];
        } else {
            $normalizedArray['lease_interior_maintenance'] = null;
        }

        // leaseRegulatedRent
        if (isset($submissionData['lejemaal_lejen_reguleret']) && !empty($submissionData['lejemaal_lejen_reguleret'])) {
            if ('Ja' === $submissionData['lejemaal_lejen_reguleret']) {
                $normalizedArray['lease_regulated_rent'] = true;
            } elseif ('Nej' === $submissionData['lejemaal_lejen_reguleret']) {
                $normalizedArray['lease_regulated_rent'] = false;
            } else {
                $message = sprintf('The lease has vacated %s is not valid.', $submissionData['lejemaal_lejen_reguleret']);
                throw new WebformSubmissionException($message);
            }
        } else {
            $normalizedArray['lease_regulated_rent'] = null;
        }

        // leaseRegulatedAt
        if (isset($submissionData['lejemaal_reguleringsdato']) && !empty($submissionData['lejemaal_reguleringsdato'])) {
            $normalizedArray['lease_regulated_at'] = new \DateTime($submissionData['lejemaal_reguleringsdato']);
        } else {
            $normalizedArray['lease_regulated_at'] = null;
        }

        // leaseRentAtCollectionTime
        if (isset($submissionData['lejemaal_husleje_paa_indbringelsestidspunkt']) && !empty($submissionData['lejemaal_husleje_paa_indbringelsestidspunkt'])) {
            $normalizedArray['lease_rent_at_collection_time'] = (int) $submissionData['lejemaal_husleje_paa_indbringelsestidspunkt'];
        } else {
            $normalizedArray['lease_rent_at_collection_time'] = null;
        }

        // leaseSecurityDeposit
        if (isset($submissionData['lejemaal_depositum']) && !empty($submissionData['lejemaal_depositum'])) {
            $normalizedArray['lease_security_deposit'] = (int) $submissionData['lejemaal_depositum'];
        } else {
            $normalizedArray['lease_security_deposit'] = null;
        }

        // leasePrepaidRent
        if (isset($submissionData['lejemaal_forudbetalt_leje']) && !empty($submissionData['lejemaal_forudbetalt_leje'])) {
            $normalizedArray['lease_prepaid_rent'] = (int) $submissionData['lejemaal_forudbetalt_leje'];
        } else {
            $normalizedArray['lease_prepaid_rent'] = null;
        }

        return $normalizedArray;
    }
}

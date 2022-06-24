<?php

namespace App\Service\OS2Forms\CaseSubmissionManager;

use App\Entity\ResidentComplaintBoardCase;
use App\Service\OS2Forms\CaseSubmissionNormalizers\CaseSubmissionNormalizer;
use App\Service\OS2Forms\CaseSubmissionNormalizers\ResidentAndRentBoardSubmissionNormalizer;
use App\Service\OS2Forms\CaseSubmissionNormalizers\SubmissionNormalizerInterface;

class ResidentComplaintBoardCaseTypeManager implements CaseSubmissionManagerInterface
{
    public function __construct(private CaseSubmissionNormalizer $caseSubmissionNormalizer, private ResidentAndRentBoardSubmissionNormalizer $residentAndRentBoardSubmissionNormalizer)
    {
    }

    public function createCaseFromSubmissionData(string $sender, array $submissionData): array
    {
        $normalizedData = [];
        foreach ($this->getNormalizers() as $normalizer) {
            assert($normalizer instanceof SubmissionNormalizerInterface);
            $normalizedData += $normalizer->normalizeSubmissionData($sender, $submissionData);
        }

        $case = $this->createCaseFromNormalizedSubmissionData($normalizedData);

        // Return case, board and document array
        return [$case, $normalizedData['board'], $normalizedData['documents']];
    }

    public function getNormalizers(): array
    {
        return [
            $this->caseSubmissionNormalizer,
            $this->residentAndRentBoardSubmissionNormalizer,
        ];
    }

    public function createCaseFromNormalizedSubmissionData(array $normalizedData): ResidentComplaintBoardCase
    {
        $case = new ResidentComplaintBoardCase();

        // Bringer
        $case
            ->setBringer($normalizedData['bringer'])
            ->setBringerPhone($normalizedData['bringer_phone'])
        ;

        // Bringer Id
        $bringerId = $case->getBringerIdentification();

        $bringerId
            ->setType($normalizedData['bringer_id_type'])
            ->setIdentifier($normalizedData['bringer_id_identifier'])
            ->setPNumber($normalizedData['bringer_id_p_number'])
        ;

        // Bringer address
        $bringerAddress = $case->getBringerAddress();

        $bringerAddress
            ->setStreet($normalizedData['bringer_address_street'])
            ->setNumber($normalizedData['bringer_address_number'])
            ->setFloor($normalizedData['bringer_address_floor'])
            ->setSide($normalizedData['bringer_address_side'])
            ->setPostalCode($normalizedData['bringer_address_postal_code'])
            ->setCity($normalizedData['bringer_address_city'])
            ->setExtraInformation($normalizedData['bringer_address_extra_information'])
        ;

        // Lease address
        $leaseAddress = $case->getLeaseAddress();

        $leaseAddress
            ->setStreet($normalizedData['lease_address_street'])
            ->setNumber($normalizedData['lease_address_number'])
            ->setFloor($normalizedData['lease_address_floor'])
            ->setSide($normalizedData['lease_address_side'])
            ->setPostalCode($normalizedData['lease_address_postal_code'])
            ->setCity($normalizedData['lease_address_city'])
            ->setExtraInformation($normalizedData['lease_address_extra_information'])
        ;

        // Lease others
        $case
            ->setHasVacated($normalizedData['lease_has_vacated'])
            ->setLeaseStarted($normalizedData['lease_started'])
            ->setLeaseSize($normalizedData['lease_size'])
            ->setLeaseAgreedRent($normalizedData['lease_agreed_rent'])
            ->setLeaseInteriorMaintenance($normalizedData['lease_interior_maintenance'])
            ->setLeaseRegulatedRent($normalizedData['lease_regulated_rent'])
            ->setLeaseRegulatedAt($normalizedData['lease_regulated_at'])
            ->setLeaseRentAtCollectionTime($normalizedData['lease_rent_at_collection_time'])
            ->setLeaseSecurityDeposit($normalizedData['lease_security_deposit'])
            ->setPrepaidRent($normalizedData['lease_prepaid_rent'])
        ;

        // Complaint category
        $case
            ->setComplaintCategory($normalizedData['complaint_category'])
            ->setExtraComplaintCategoryInformation($normalizedData['complaint_category_extra_information'])
        ;

        return $case;
    }
}

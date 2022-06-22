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
        $case->setBringer($normalizedData['bringer']);
        $case->setBringerPhone($normalizedData['bringer_phone']);

        // Bringer Id
        $bringerId = $case->getBringerIdentification();

        $bringerId->setType($normalizedData['bringer_id_type']);
        $bringerId->setIdentifier($normalizedData['bringer_id_identifier']);
        $bringerId->setPNumber($normalizedData['bringer_id_p_number']);

        // Bringer address
        $bringerAddress = $case->getBringerAddress();

        $bringerAddress->setStreet($normalizedData['bringer_address_street']);
        $bringerAddress->setNumber($normalizedData['bringer_address_number']);
        $bringerAddress->setFloor($normalizedData['bringer_address_floor']);
        $bringerAddress->setSide($normalizedData['bringer_address_side']);
        $bringerAddress->setPostalCode($normalizedData['bringer_address_postal_code']);
        $bringerAddress->setCity($normalizedData['bringer_address_city']);
        $bringerAddress->setExtraInformation($normalizedData['bringer_address_extra_information']);

        // Lease address
        $leaseAddress = $case->getLeaseAddress();

        $leaseAddress->setStreet($normalizedData['lease_address_street']);
        $leaseAddress->setNumber($normalizedData['lease_address_number']);
        $leaseAddress->setFloor($normalizedData['lease_address_floor']);
        $leaseAddress->setSide($normalizedData['lease_address_side']);
        $leaseAddress->setPostalCode($normalizedData['lease_address_postal_code']);
        $leaseAddress->setCity($normalizedData['lease_address_city']);
        $leaseAddress->setExtraInformation($normalizedData['lease_address_extra_information']);

        // Lease others
        $case->setHasVacated($normalizedData['lease_has_vacated']);
        $case->setLeaseStarted($normalizedData['lease_started']);
        $case->setLeaseSize($normalizedData['lease_size']);
        $case->setLeaseAgreedRent($normalizedData['lease_agreed_rent']);
        $case->setLeaseInteriorMaintenance($normalizedData['lease_interior_maintenance']);
        $case->setLeaseRegulatedRent($normalizedData['lease_regulated_rent']);
        $case->setLeaseRegulatedAt($normalizedData['lease_regulated_at']);
        $case->setLeaseRentAtCollectionTime($normalizedData['lease_rent_at_collection_time']);
        $case->setLeaseSecurityDeposit($normalizedData['lease_security_deposit']);
        $case->setPrepaidRent($normalizedData['lease_prepaid_rent']);

        // Complaint category and documents
        $case->setComplaintCategory($normalizedData['complaint_category']);

        return $case;
    }
}

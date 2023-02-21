<?php

namespace App\Service\OS2Forms\SubmissionManager;

use App\Entity\RentBoardCase;
use App\Exception\WebformSubmissionException;
use App\Repository\UserRepository;
use App\Service\OS2Forms\SubmissionNormalizers\CaseSubmissionNormalizer;
use App\Service\OS2Forms\SubmissionNormalizers\RentBoardSubmissionNormalizer;
use App\Service\OS2Forms\SubmissionNormalizers\ResidentAndRentBoardSubmissionNormalizer;
use App\Service\OS2Forms\SubmissionNormalizers\SubmissionNormalizerInterface;

class RentComplaintBoardCaseTypeManager implements CaseSubmissionManagerInterface
{
    public function __construct(private readonly CaseSubmissionNormalizer $caseSubmissionNormalizer, private readonly ResidentAndRentBoardSubmissionNormalizer $residentAndRentBoardSubmissionNormalizer, private readonly RentBoardSubmissionNormalizer $rentBoardSubmissionNormalizer, private readonly UserRepository $userRepository)
    {
    }

    public function getNormalizers(): array
    {
        return [
            $this->caseSubmissionNormalizer,
            $this->residentAndRentBoardSubmissionNormalizer,
            $this->rentBoardSubmissionNormalizer,
        ];
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

    public function createCaseFromNormalizedSubmissionData(array $normalizedData): RentBoardCase
    {
        $case = new RentBoardCase();

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
            ->setLeaseType($normalizedData['lease_type'])
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

        // ReceivedAt
        $case->setReceivedAt(new \DateTime('today'));

        // Set created by to OS2Forms
        $user = $this->userRepository->findOneBy(['name' => 'OS2Forms']);
        if (null === $user) {
            throw new WebformSubmissionException('Could not find OS2Forms system user.');
        }

        $case->setCreatedBy($user);

        return $case;
    }
}

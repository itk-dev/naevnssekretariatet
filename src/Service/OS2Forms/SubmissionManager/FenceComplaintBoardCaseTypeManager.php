<?php

namespace App\Service\OS2Forms\SubmissionManager;

use App\Entity\FenceReviewCase;
use App\Exception\WebformSubmissionException;
use App\Repository\UserRepository;
use App\Service\OS2Forms\SubmissionNormalizers\CaseSubmissionNormalizer;
use App\Service\OS2Forms\SubmissionNormalizers\FenceBoardSubmissionNormalizer;
use App\Service\OS2Forms\SubmissionNormalizers\SubmissionNormalizerInterface;

class FenceComplaintBoardCaseTypeManager implements CaseSubmissionManagerInterface
{
    public function __construct(private readonly CaseSubmissionNormalizer $caseSubmissionNormalizer, private readonly FenceBoardSubmissionNormalizer $fenceBoardSubmissionNormalizer, private readonly UserRepository $userRepository)
    {
    }

    public function getNormalizers(): array
    {
        return [
            $this->caseSubmissionNormalizer,
            $this->fenceBoardSubmissionNormalizer,
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

    public function createCaseFromNormalizedSubmissionData(array $normalizedData): FenceReviewCase
    {
        $case = new FenceReviewCase();

        // Bringer
        $case
            ->setBringer($normalizedData['bringer'])
            ->setBringerCadastralNumber($normalizedData['bringer_cadastral_number'])
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

        // Bringer cannot (should not) have knowledge of accused identifier,
        // i.e. we can only set accused name, address and cadastral number.
        $case->setAccused($normalizedData['accused_name']);

        $accusedAddress = $case->getAccusedAddress();

        $accusedAddress
            ->setStreet($normalizedData['accused_address_street'])
            ->setNumber($normalizedData['accused_address_number'])
            ->setFloor($normalizedData['accused_address_floor'])
            ->setSide($normalizedData['accused_address_side'])
            ->setPostalCode($normalizedData['accused_address_postal_code'])
            ->setCity($normalizedData['accused_address_city'])
            ->setExtraInformation($normalizedData['accused_address_extra_information'])
        ;

        $case->setAccusedCadastralNumber($normalizedData['accused_cadastral_number']);

        // Claim and conditions
        $case->setBringerClaim($normalizedData['bringer_claim']);
        $case->setConditions($normalizedData['conditions']);

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

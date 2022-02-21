<?php

namespace App\Service;

use App\Entity\Municipality;
use App\Entity\User;
use App\Repository\BoardRepository;
use App\Repository\CaseEntityRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardHelper
{
    public function __construct(private BoardRepository $boardRepository, private CaseEntityRepository $caseRepository, private TranslatorInterface $translator, private UrlGeneratorInterface $router, private UserRepository $userRepository)
    {
    }

    public function getDashboardGridInformation(Municipality $municipality, User $user): array
    {
        $gridInformation = [];

        $gridInformation[] = $this->getUserColumnCaseInformation($municipality, $user);

        $gridInformation = array_merge($gridInformation, $this->getBoardColumnsCaseInformation($municipality));

        return $gridInformation;
    }

    private function getUserColumnCaseInformation(Municipality $municipality, User $user): array
    {
        $row = [];

        // Construct the filter urls and do the counts
        // In hearing
        $hearingUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $user->getId(),
            'specialStateFilter' => CaseSpecialFilterStatuses::IN_HEARING,
        ]]);

        $row[] = [
            'label' => $this->translator->trans('Hearing in progress', [], 'dashboard'),
            'url' => $hearingUrl,
            'count' => $this->caseRepository->findCountOfCasesWithActiveHearingBy(['municipality' => $municipality, 'assignedTo' => $user]),
        ];

        // Has new party submission
        $newPartySubmissionUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $user->getId(),
            'specialStateFilter' => CaseSpecialFilterStatuses::NEW_HEARING_POST,
        ]]);

        $row[] = [
            'label' => $this->translator->trans('New post', [], 'dashboard'),
            'url' => $newPartySubmissionUrl,
            'count' => $this->caseRepository->findCountOfCasesWithNewHearingPostBy(['municipality' => $municipality, 'assignedTo' => $user]),
        ];

        // On agenda
        $agendaUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $user->getId(),
            'specialStateFilter' => CaseSpecialFilterStatuses::ON_AGENDA,
        ]]);

        $row[] = [
            'label' => $this->translator->trans('On agenda', [], 'dashboard'),
            'url' => $agendaUrl,
            'count' => $this->caseRepository->findCountOfCasesAndWithActiveAgendaBy(['municipality' => $municipality, 'assignedTo' => $user]),
        ];

        // Has exceeded one or more deadlines
        $exceededDeadlineUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $user->getId(),
            'deadlines' => CaseDeadlineStatuses::SOME_DEADLINE_EXCEEDED,
        ]]);

        $row[] = [
            'label' => $this->translator->trans('Deadline reached', [], 'dashboard'),
            'url' => $exceededDeadlineUrl,
            'count' => $this->caseRepository->findCountOfCasesWithSomeExceededDeadlineBy(['municipality' => $municipality, 'assignedTo' => $user]),
        ];

        // TODO: When all rows are fully implemented update beneath
//        $count = array_sum(array_column($row, 'count'));
        // Fix to avoid counting -1 which represent not implemented yet
        $counts = array_column($row, 'count');
        $sum = 0;
        foreach ($counts as $count) {
            if (-1 !== $count) {
                $sum += $count;
            }
        }

        return [
            'label' => $this->translator->trans('My cases', [], 'dashboard'),
            'count' => $sum,
            'rows' => $row,
        ];
    }

    /**
     * Gets columns for all boards in municipality.
     */
    private function getBoardColumnsCaseInformation(Municipality $municipality): array
    {
        $boards = $this->boardRepository->findBy(['municipality' => $municipality], ['name' => 'ASC']);

        $boardsInformation = [];

        foreach ($boards as $board) {
            $rows = [];

            // Construct the filter urls and do the counts
            // In hearing
            $boardHearingUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $board->getId(),
                'specialStateFilter' => CaseSpecialFilterStatuses::IN_HEARING,
            ]]);

            $rows[] = [
                'label' => $this->translator->trans('Hearing in progress', [], 'dashboard'),
                'url' => $boardHearingUrl,
                'count' => $this->caseRepository->findCountOfCasesWithActiveHearingBy(['board' => $board]),
            ];

            // Has new party submission
            $boardNewPartySubmissionUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $board->getId(),
                'specialStateFilter' => CaseSpecialFilterStatuses::NEW_HEARING_POST,
            ]]);

            $rows[] = [
                'label' => $this->translator->trans('New post', [], 'dashboard'),
                'url' => $boardNewPartySubmissionUrl,
                'count' => $this->caseRepository->findCountOfCasesWithNewHearingPostBy(['board' => $board]),
            ];

            // On agenda
            $boardAgendaUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $board->getId(),
                'specialStateFilter' => CaseSpecialFilterStatuses::ON_AGENDA,
            ]]);

            $rows[] = [
                'label' => $this->translator->trans('On agenda', [], 'dashboard'),
                'url' => $boardAgendaUrl,
                'count' => $this->caseRepository->findCountOfCasesAndWithActiveAgendaBy(['board' => $board]),
            ];

            // Has exceeded one or more deadlines
            $boardExceededDeadlineUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $board->getId(),
                'deadlines' => CaseDeadlineStatuses::SOME_DEADLINE_EXCEEDED,
            ]]);

            $rows[] = [
                'label' => $this->translator->trans('Deadline reached', [], 'dashboard'),
                'url' => $boardExceededDeadlineUrl,
                'count' => $this->caseRepository->findCountOfCasesWithSomeExceededDeadlineBy(['board' => $board]),
            ];

            // TODO: When all rows are fully implemented update beneath
//            $boardCount = array_sum(array_column($rows, 'count'));
            // Fix to avoid counting -1 which represent not implemented yet
            $counts = array_column($rows, 'count');
            $boardSum = 0;
            foreach ($counts as $count) {
                if (-1 !== $count) {
                    $boardSum += $count;
                }
            }

            $boardsInformation[] = [
                'label' => $board->getName(),
                'count' => $boardSum,
                'rows' => $rows,
            ];
        }

        return $boardsInformation;
    }
}

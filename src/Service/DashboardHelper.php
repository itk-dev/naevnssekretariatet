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

        $gridInformation = array_merge($gridInformation, $this->getBoardsColumnCaseInformation($municipality));

        return $gridInformation;
    }

    private function getUserColumnCaseInformation(Municipality $municipality, User $user): array
    {
        // Construct the filter urls and do the counts
        // In hearing
        $hearingUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $user->getId(),
            'specialStateFilter' => CaseSpecialFilterStatuses::IN_HEARING,
        ]]);

        // TODO: Update beneath when hearing stuff has been implemented
        $hearingCount = $this->caseRepository->count(['assignedTo' => $user, 'municipality' => $municipality]);

        $hearingData = [
            'label' => $this->translator->trans('Hearing in progress', [], 'dashboard'),
            'url' => $hearingUrl,
            'count' => $hearingCount,
            'button_style' => 'primary',
        ];

        // Has new party submission
        $newPartySubmissionUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $user->getId(),
            'specialStateFilter' => CaseSpecialFilterStatuses::NEW_HEARING_POST,
        ]]);

        // TODO: Update beneath when hearing stuff has been implemented
        $newPartySubmissionCount = $this->caseRepository->count(['assignedTo' => $user, 'municipality' => $municipality]);

        $newPartySubmissionData = [
            'label' => $this->translator->trans('New post', [], 'dashboard'),
            'url' => $newPartySubmissionUrl,
            'count' => $newPartySubmissionCount,
            'button_style' => 'secondary',
        ];

        // On agenda
        $agendaUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $user->getId(),
            'specialStateFilter' => CaseSpecialFilterStatuses::ON_AGENDA,
        ]]);

        $agendaCount = $this->caseRepository->findCountOfCasesWithUserAndMunicipalityAndWithActiveAgenda($municipality, $user);

        $agendaData = [
            'label' => $this->translator->trans('On agenda', [], 'dashboard'),
            'url' => $agendaUrl,
            'count' => $agendaCount,
            'button_style' => 'info',
        ];

        // Has exceeded one or more deadlines
        $exceededDeadlineUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $user->getId(),
            'deadlines' => CaseDeadlineStatuses::SOME_DEADLINE_EXCEEDED,
        ]]);

        $exceededDeadlineCount = $this->caseRepository->findCountOfCasesWithUserAndMunicipalityAndSomeExceededDeadline($municipality, $user);

        $exceededDeadlineData = [
            'label' => $this->translator->trans('Deadline reached', [], 'dashboard'),
            'url' => $exceededDeadlineUrl,
            'count' => $exceededDeadlineCount,
            'button_style' => 'dark',
        ];

        $columnCount = $hearingCount + $newPartySubmissionCount + $agendaCount + $exceededDeadlineCount;

        return [
            'label' => $this->translator->trans('My cases', [], 'dashboard'),
            'count' => $columnCount,
            'rows' => [
                $hearingData,
                $newPartySubmissionData,
                $agendaData,
                $exceededDeadlineData,
            ],
        ];
    }

    private function getBoardsColumnCaseInformation(Municipality $municipality): array
    {
        $boards = $this->boardRepository->findBy(['municipality' => $municipality], ['name' => 'ASC']);

        $boardsInformation = [];

        foreach ($boards as $board) {
            // Construct the filter urls and do the counts
            // In hearing
            $boardHearingUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $board->getId(),
                'specialStateFilter' => CaseSpecialFilterStatuses::IN_HEARING,
            ]]);

            // TODO: Update beneath when hearing stuff has been implemented
            $boardHearingCount = $this->caseRepository->count(['board' => $board]);

            $boardHearingData = [
                'label' => $this->translator->trans('Hearing in progress', [], 'dashboard'),
                'url' => $boardHearingUrl,
                'count' => $boardHearingCount,
                'button_style' => 'primary',
            ];

            // Has new party submission
            $boardNewPartySubmissionUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $board->getId(),
                'specialStateFilter' => CaseSpecialFilterStatuses::NEW_HEARING_POST,
            ]]);

            // TODO: Update beneath when hearing stuff has been implemented
            $boardNewPartySubmissionCount = $this->caseRepository->count(['board' => $board]);

            $boardNewPartySubmissionData = [
                'label' => $this->translator->trans('New post', [], 'dashboard'),
                'url' => $boardNewPartySubmissionUrl,
                'count' => $boardNewPartySubmissionCount,
                'button_style' => 'secondary',
            ];

            // On agenda
            $boardAgendaUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $board->getId(),
                'specialStateFilter' => CaseSpecialFilterStatuses::ON_AGENDA,
            ]]);

            $boardAgendaCount = $this->caseRepository->findCountOfCasesWithActiveAgendaByBoard($board);

            $boardAgendaData = [
                'label' => $this->translator->trans('On agenda', [], 'dashboard'),
                'url' => $boardAgendaUrl,
                'count' => $boardAgendaCount,
                'button_style' => 'info',
            ];

            // Has exceeded one or more deadlines
            $boardExceededDeadlineUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $board->getId(),
                'deadlines' => CaseDeadlineStatuses::SOME_DEADLINE_EXCEEDED,
            ]]);

            $boardExceededDeadlineCount = $this->caseRepository->findCountOfCasesWithSomeExceededDeadlineByBoard($board);

            $boardExceededDeadlineData = [
                'label' => $this->translator->trans('Deadline reached', [], 'dashboard'),
                'url' => $boardExceededDeadlineUrl,
                'count' => $boardExceededDeadlineCount,
                'button_style' => 'dark',
            ];

            $boardCount = $boardHearingCount + $boardNewPartySubmissionCount + $boardAgendaCount + $boardExceededDeadlineCount;

            array_push($boardsInformation, [
                'label' => $board->getName(),
                'count' => $boardCount,
                'rows' => [
                    $boardHearingData,
                    $boardNewPartySubmissionData,
                    $boardAgendaData,
                    $boardExceededDeadlineData,
                ],
            ]);
        }

        return $boardsInformation;
    }
}

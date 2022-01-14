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
    private BoardRepository $boardRepository;
    private CaseEntityRepository $caseRepository;
    private TranslatorInterface $translator;
    private UrlGeneratorInterface $router;
    private UserRepository $userRepository;

    public function __construct(BoardRepository $boardRepository, CaseEntityRepository $caseRepository, TranslatorInterface $translator, UrlGeneratorInterface $router, UserRepository $userRepository)
    {
        $this->boardRepository = $boardRepository;
        $this->caseRepository = $caseRepository;
        $this->translator = $translator;
        $this->router = $router;
        $this->userRepository = $userRepository;
    }

    public function getDashboardGridInformation(Municipality $municipality, User $user): array
    {
        $gridInformation = [];

        $gridInformation = $this->addUserColumnCaseInformation($user, $gridInformation);

        $gridInformation = $this->addBoardsColumnCaseInformation($municipality, $gridInformation);

        return $gridInformation;
    }

    private function addUserColumnCaseInformation(User $user, array $gridInformation): array
    {
        // Detect which filter option current user is
        $userFilterOption = $this->getCurrentUserFilterOption($user);

        // Construct the filter urls and do the counts
        // In hearing
        $hearingUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $userFilterOption,
            'specialStateFilter' => CaseSpecialFilterStatuses::IN_HEARING,
        ]]);

        // TODO: Update beneath when hearing stuff has been implemented
        $hearingCount = $this->caseRepository->count(['assignedTo' => $user]);

        $hearingData = [
            'url' => $hearingUrl,
            'count' => $hearingCount,
        ];

        // Has new party submission
        $newPartySubmissionUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $userFilterOption,
            'specialStateFilter' => CaseSpecialFilterStatuses::NEW_HEARING_POST,
        ]]);

        // TODO: Update beneath when hearing stuff has been implemented
        $newPartySubmissionCount = $this->caseRepository->count(['assignedTo' => $user]);

        $newPartySubmissionData = [
            'url' => $newPartySubmissionUrl,
            'count' => $newPartySubmissionCount,
        ];

        // On agenda
        $agendaUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $userFilterOption,
            'specialStateFilter' => CaseSpecialFilterStatuses::ON_AGENDA,
        ]]);

        $agendaCount = $this->caseRepository->findCountOfCasesWithUserAndWithActiveAgenda($user);

        $agendaData = [
            'url' => $agendaUrl,
            'count' => $agendaCount,
        ];

        // Has exceeded one or more deadlines
        $exceededDeadlineUrl = $this->router->generate('case_index', ['case_filter' => [
            'assignedTo' => $userFilterOption,
            'deadlines' => CaseDeadlineStatuses::SOME_DEADLINE_EXCEEDED,
        ]]);

        $exceededDeadlineCount = $this->caseRepository->findCountOfCasesWithUserAndSomeExceededDeadline($user);

        $exceededDeadlineData = [
            'url' => $exceededDeadlineUrl,
            'count' => $exceededDeadlineCount,
        ];

        array_push($gridInformation, [
            'grid_label' => $this->translator->trans('My cases', [], 'dashboard'),
            'hearing_data' => $hearingData,
            'new_party_submission_data' => $newPartySubmissionData,
            'agenda_data' => $agendaData,
            'exceeded_deadline_data' => $exceededDeadlineData,
        ]);

        return $gridInformation;
    }

    private function addBoardsColumnCaseInformation(Municipality $municipality, array $gridInformation): array
    {
        $boards = $this->boardRepository->findBy(['municipality' => $municipality], ['name' => 'ASC']);

        foreach ($boards as $board) {
            $boardFilterOption = array_search($board, $boards);

            // Construct the filter urls and do the counts
            // In hearing
            $boardHearingUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $boardFilterOption,
                'specialStateFilter' => CaseSpecialFilterStatuses::IN_HEARING,
            ]]);

            // TODO: Update beneath when hearing stuff has been implemented
            $boardHearingCount = $this->caseRepository->count(['board' => $board]);

            $boardHearingData = [
                'url' => $boardHearingUrl,
                'count' => $boardHearingCount,
            ];

            // Has new party submission
            $boardNewPartySubmissionUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $boardFilterOption,
                'specialStateFilter' => CaseSpecialFilterStatuses::NEW_HEARING_POST,
            ]]);

            // TODO: Update beneath when hearing stuff has been implemented
            $boardNewPartySubmissionCount = $this->caseRepository->count(['board' => $board]);

            $boardNewPartySubmissionData = [
                'url' => $boardNewPartySubmissionUrl,
                'count' => $boardNewPartySubmissionCount,
            ];

            // On agenda
            $boardAgendaUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $boardFilterOption,
                'specialStateFilter' => CaseSpecialFilterStatuses::ON_AGENDA,
            ]]);

            $boardAgendaCount = $this->caseRepository->findCountOfCasesWithActiveAgendaByBoard($board);

            $boardAgendaData = [
                'url' => $boardAgendaUrl,
                'count' => $boardAgendaCount,
            ];

            // Has exceeded one or more deadlines
            $boardExceededDeadlineUrl = $this->router->generate('case_index', ['case_filter' => [
                'board' => $boardFilterOption,
                'deadlines' => CaseDeadlineStatuses::SOME_DEADLINE_EXCEEDED,
            ]]);

            $boardExceededDeadlineCount = $this->caseRepository->findCountOfCasesWithSomeExceededDeadlineByBoard($board);

            $boardExceededDeadlineData = [
                'url' => $boardExceededDeadlineUrl,
                'count' => $boardExceededDeadlineCount,
            ];

            array_push($gridInformation, [
                'grid_label' => $board->getName(),
                'hearing_data' => $boardHearingData,
                'new_party_submission_data' => $boardNewPartySubmissionData,
                'agenda_data' => $boardAgendaData,
                'exceeded_deadline_data' => $boardExceededDeadlineData,
            ]);
        }

        return $gridInformation;
    }

    private function getCurrentUserFilterOption(User $user): int
    {
        $caseworkers = $this->userRepository->findByRole('ROLE_CASEWORKER', ['name' => 'ASC']);

        // Reindex caseworkers as UserRepository findByRole does not guarantee incrementing indexes starting from 0
        $reindexedCaseworkers = array_values($caseworkers);

        $userKey = array_search($user, $reindexedCaseworkers);

        return $userKey;
    }
}

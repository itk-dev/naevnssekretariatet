<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Service\WorkflowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CaseProgressController extends AbstractController
{
    private $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function renderCaseProgressBar(CaseEntity $case): Response
    {
        $workflow = $this->workflowService->getWorkflowForCase($case);
        $places = $workflow->getDefinition()->getPlaces();

        // We add 1 to the index, to make the progress bar show which current status
        // the case is in.
        $currentIndex = array_search($case->getCurrentPlace(), array_keys($places)) + 1;
        $progress = floor($currentIndex / sizeof($places) * 100);

        return $this->render('common/case_progress.html.twig', [
            'places' => $places,
            'progress' => $progress,
        ]);
    }
}
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

        $currentIndex = array_search($case->getCurrentPlace(), array_keys($places));

        // We split each interval (one per case status/place) into 2 smaller intervals and
        // fill the progress bar up to the first of these smaller intervals to clearly indicate current status/place
        $progress = floor((($currentIndex * 2 + 1) / (sizeof($places) * 2)) * 100);

        return $this->render('common/case_progress.html.twig', [
            'places' => $places,
            'progress' => $progress,
        ]);
    }
}

<?php

namespace App\Service;

use App\Entity\CaseEntity;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class WorkflowService
{
    public function getWorkflowForCase(CaseEntity $caseEntity): Workflow
    {
        $places = $this->getPlacesForCase($caseEntity);

        $definitionBuilder = new DefinitionBuilder();
        $definitionBuilder->addPlaces($places);
        $definitionBuilder->addTransitions($this->buildTransitionsForSucceedingPlaces($places));
        $definitionBuilder->addTransitions($this->buildTransitionsForPrecedingPlaces($places));

        return new Workflow(
            $definitionBuilder->build(),
            new MethodMarkingStore(true, 'currentPlace')
        );
    }

    private function getPlacesForCase(CaseEntity $caseEntity): array
    {
        $rawPlaces = explode(
            PHP_EOL,
            $caseEntity->getBoard()->getStatuses()
        );

        $trimmedPlaces = [];
        foreach ($rawPlaces as $rawPlace) {
            $trimmedPlaces[] = trim($rawPlace);
        }

        return $trimmedPlaces;
    }

    private function buildTransitionsForSucceedingPlaces(array $places): array
    {
        $transitions = [];
        foreach ($places as $key => $place) {
            if (!array_key_exists($key + 1, $places)) {
                break;
            }

            $nextPlace = $places[$key + 1];
            $transitions[] = new Transition($place.'_'.$nextPlace, $place, $nextPlace);
        }

        return $transitions;
    }

    private function buildTransitionsForPrecedingPlaces(array $places): array
    {
        $transitions = [];

        $keys = array_keys($places);
        foreach ($places as $key => $place) {
            $previousKeys = array_slice($keys, 0, $key);

            if (empty($previousKeys)) {
                continue;
            }

            foreach ($previousKeys as $previousKey) {
                $nextPlace = $places[$previousKey];
                $transitions[] = new Transition($place.'_'.$nextPlace, $place, $nextPlace);
            }
        }

        return $transitions;
    }

    public function getPlaceChoicesForCase(CaseEntity $case, Workflow $workflow): array
    {
        $transitions = $workflow->getEnabledTransitions($case);

        $choices = [];
        foreach ($transitions as $transition) {
            $choices[current($transition->getTos())] = $transition->getName();
        }

        return $choices;
    }
}

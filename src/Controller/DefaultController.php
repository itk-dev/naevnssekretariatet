<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MunicipalitySelectorType;
use App\Repository\CaseEntityRepository;
use App\Repository\MunicipalityRepository;
use App\Repository\ReminderRepository;
use App\Service\MunicipalityHelper;
use App\Service\ReminderHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(CaseEntityRepository $caseRepository, MunicipalityHelper $municipalityHelper, MunicipalityRepository $municipalityRepository, ReminderHelper $reminderHelper, ReminderRepository $reminderRepository, Security $security, Request $request): Response
    {
        // Get current User
        /** @var User $user */
        $user = $security->getUser();

        // Despite chosen municipality we show ALL reminders
        $upcomingReminders = $reminderHelper->getRemindersWithinWeekByUserGroupedByDay($user);
        $exceededReminders = $reminderRepository->findExceededRemindersByUser($user);

        $activeMunicipality = $municipalityHelper->getActiveMunicipality();
        $municipalities = $municipalityRepository->findAll();

        $unassignedCases = $caseRepository->findBy([
            'assignedTo' => null,
            'municipality' => $activeMunicipality,
        ]);

        $municipalityForm = $this->createForm(MunicipalitySelectorType::class, null, [
            'municipalities' => $municipalities,
            'active_municipality' => $activeMunicipality,
        ]);

        $municipalityForm->handleRequest($request);
        if ($municipalityForm->isSubmitted()) {
            $municipality = $municipalityForm->get('municipality')->getData();

            $municipalityHelper->setActiveMunicipalitySession($municipality);

            return $this->redirectToRoute('default');
        }

        return $this->render('dashboard/index.html.twig', [
            'upcoming_reminders' => $upcomingReminders,
            'exceeded_reminders' => $exceededReminders,
            'unassigned_cases' => $unassignedCases,
            'municipality_form' => $municipalityForm->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): Response
    {
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}

<?php

namespace App\Controller;

use App\Controller\Admin\UserCrudController;
use App\Entity\User;
use App\Form\MunicipalitySelectorType;
use App\Repository\CaseEntityRepository;
use App\Repository\MunicipalityRepository;
use App\Repository\ReminderRepository;
use App\Service\DashboardHelper;
use App\Service\MunicipalityHelper;
use App\Service\ReminderHelper;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(CaseEntityRepository $caseRepository, DashboardHelper $dashboardHelper, MunicipalityHelper $municipalityHelper, MunicipalityRepository $municipalityRepository, ReminderHelper $reminderHelper, ReminderRepository $reminderRepository, Security $security, Request $request): Response
    {
        // Board member are redirected to case index
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            return $this->redirectToRoute('agenda_index');
        }

        // Get current User
        /** @var User $user */
        $user = $security->getUser();

        // Find chosen municipality or choose one
        $activeMunicipality = $municipalityHelper->getActiveMunicipality();
        $municipalities = $municipalityRepository->findBy([], ['name' => 'ASC']);

        // Show reminders accordingly to chosen municipality
        $upcomingReminders = $reminderHelper->getRemindersWithinWeekByUserAndMunicipalityGroupedByDay($user, $activeMunicipality);
        $exceededReminders = $reminderRepository->findExceededRemindersByUserAndMunicipality($user, $activeMunicipality);

        $gridInformation = $dashboardHelper->getDashboardGridInformation($activeMunicipality, $user);

        $unassignedCases = $caseRepository->findBy([
            'assignedTo' => null,
            'municipality' => $activeMunicipality,
        ], ['caseNumber' => 'ASC']);

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
            'grid_information' => $gridInformation,
        ]);
    }

    /**
     * @Route("/user-settings", name="user_settings")
     */
    public function redirectToUserSettings(AdminUrlGenerator $urlGenerator): Response
    {
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

        $url = $urlGenerator
            ->setController(UserCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($this->getUser()->getId())
            ->generateUrl()
        ;

        return $this->redirect($url);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        // This will never be called so can be blank,
        // cf. https://symfony.com/doc/5.x/security.html#logging-out
    }
}

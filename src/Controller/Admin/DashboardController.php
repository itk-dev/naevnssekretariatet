<?php

namespace App\Controller\Admin;

use App\Entity\Board;
use App\Entity\BoardMember;
use App\Entity\ComplaintCategory;
use App\Entity\Municipality;
use App\Entity\Party;
use App\Entity\Settings;
use App\Entity\SubBoard;
use App\Entity\User;
use App\Repository\SettingsRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractDashboardController
{
    /**
     * @var SettingsRepository
     */
    private $settingsRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(SettingsRepository $settingsRepository, TranslatorInterface $translator)
    {
        $this->settingsRepository = $settingsRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTranslationDomain('admin')
            ->setTitle($this->translator->trans('dashboard', [], 'admin'))
            ;
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setPaginatorPageSize(10)
            ->setPaginatorRangeSize(2)
            ;
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX)
        ;
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->setName($user->getName());
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Complaint category', '', ComplaintCategory::class);
        yield MenuItem::linkToCrud('Municipality', '', Municipality::class);
        yield MenuItem::linkToCrud('Board', '', Board::class)
            ->setPermission('ROLE_ADMIN')
        ;
        yield MenuItem::linkToCrud('Subboards', '', SubBoard::class);
        yield MenuItem::linkToCrud('Boardmember', '', BoardMember::class);
        yield MenuItem::linkToCrud('Part Index', '', Party::class);
        yield MenuItem::linkToCrud('User Settings', '', User::class)
            ->setAction('edit')
            ->setEntityId($this->getUser()->getId())
        ;
        yield MenuItem::linkToCrud('Deadlines and notification', '', Settings::class)
            ->setAction('edit')
            ->setEntityId($this->settingsRepository->getSettings($this->getUser())->getId())
        ;
    }
}

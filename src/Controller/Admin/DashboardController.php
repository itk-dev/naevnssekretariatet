<?php

namespace App\Controller\Admin;

use App\Entity\Board;
use App\Entity\BoardMember;
use App\Entity\ComplaintCategory;
use App\Entity\Municipality;
use App\Entity\Party;
use App\Entity\User;
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
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
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
            ->setTitle($this->translator->trans('Settings', [], 'admin'))
            ->setTranslationDomain('admin')
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
            ->setName($user->getName())
            ;
    }

    public function configureMenuItems(): iterable
    {
        //yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud($this->translator->trans('Complaint categories', [], 'admin'), '', ComplaintCategory::class);
        yield MenuItem::linkToCrud($this->translator->trans('Municipality', [], 'admin'), '', Municipality::class);
        yield MenuItem::linkToCrud($this->translator->trans('Board', [], 'admin'), '', Board::class);
        yield MenuItem::linkToCrud($this->translator->trans('Boardmember', [], 'admin'), '', BoardMember::class);
        yield MenuItem::linkToCrud($this->translator->trans('Part Index', [], 'admin'), '', Party::class);
        yield MenuItem::linkToCrud($this->translator->trans('User Settings', [], 'admin'), '', User::class)
            ->setAction('edit')
            ->setEntityId($this->getUser()->getId())
        ;
    }
}

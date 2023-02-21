<?php

namespace App\Controller\Admin;

use App\Entity\Board;
use App\Entity\BoardMember;
use App\Entity\BoardRole;
use App\Entity\CaseEntity;
use App\Entity\ComplaintCategory;
use App\Entity\MailTemplate;
use App\Entity\MailTemplateMacro;
use App\Entity\Municipality;
use App\Entity\Party;
use App\Entity\UploadedDocumentType;
use App\Entity\User;
use App\Monolog\LogEntry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(AdminUrlGenerator::class);

        $redirectUrl = $routeBuilder
            ->setController(UserCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($this->getUser()->getId())
            ->generateUrl()
        ;

        return $this->redirect($redirectUrl);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTranslationDomain('admin')
            ->setTitle($this->translator->trans('Dashboard', [], 'admin'))
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
        // See AbstractDashboardController configureUserMenu method, here with unnecessary features such as logout removed
        return UserMenu::new()
            ->displayUserName()
            ->setName($user->getName())
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoRoute('Back to the website', '', 'default');
        yield MenuItem::linkToCrud('Municipality', '', Municipality::class);
        yield MenuItem::linkToCrud('Board', '', Board::class);
        yield MenuItem::linkToCrud('Board roles', '', BoardRole::class);
        yield MenuItem::linkToCrud('Boardmember', '', BoardMember::class);
        yield MenuItem::linkToCrud('Part Index', '', Party::class);
        yield MenuItem::linkToCrud('Complaint category', '', ComplaintCategory::class);
        yield MenuItem::linkToCrud('Document types', '', UploadedDocumentType::class);
        yield MenuItem::subMenu('Mail templates', null)
            ->setSubItems([
                MenuItem::linkToCrud('Mail templates', '', MailTemplate::class),
                MenuItem::linkToCrud('Macros', '', MailTemplateMacro::class),
            ])
        ;
        yield MenuItem::linkToCrud('Deleted cases', '', CaseEntity::class)
            ->setPermission('ROLE_ADMIN')
        ;
        yield MenuItem::linkToCrud('Log', '', LogEntry::class);
        yield MenuItem::linkToCrud('User Settings', '', User::class)
            ->setAction('edit')
            ->setEntityId($this->getUser()->getId())
        ;
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Board;
use App\Entity\BoardMember;
use App\Entity\Municipality;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
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
            ->setTitle('Settings');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
        ->setPaginatorPageSize(10)
        ->setPaginatorRangeSize(2);
    }

    public function configureMenuItems(): iterable
    {
        //yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Municipalities', '', Municipality::class);
        yield MenuItem::linkToCrud('Boards', '', Board::class);
        yield MenuItem::linkToCrud('Boardmembers', '', BoardMember::class);
    }
}

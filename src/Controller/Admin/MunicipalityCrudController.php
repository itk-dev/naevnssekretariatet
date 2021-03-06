<?php

namespace App\Controller\Admin;

use App\Entity\Municipality;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MunicipalityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Municipality::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Add municipality')
            ->setEntityLabelInSingular('Municipality')
            ->setEntityLabelInPlural('Municipalities')
            ->setSearchFields(['name'])
            ->setDefaultSort(['name' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');
    }
}

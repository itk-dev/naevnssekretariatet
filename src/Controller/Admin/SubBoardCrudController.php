<?php

namespace App\Controller\Admin;

use App\Entity\SubBoard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SubBoardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SubBoard::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Add Subboard')
            ->setEntityLabelInSingular('Subboard')
            ->setEntityLabelInPlural('Subboards')
            ->setSearchFields(['name', 'board.name'])
            ->setDefaultSort(['name' => 'ASC'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');
        yield AssociationField::new('mainBoard', 'Board');
    }
}

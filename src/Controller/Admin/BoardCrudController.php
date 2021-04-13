<?php

namespace App\Controller\Admin;

use App\Entity\Board;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use function Symfony\Component\Translation\t;

class BoardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Board::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Add board')
            ->setEntityLabelInSingular('Board')
            ->setEntityLabelInPlural('Boards')
            ->setSearchFields(['name', 'municipality.name'])
            ->setDefaultSort(['name' => 'ASC'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield AssociationField::new('municipality');
    }
}

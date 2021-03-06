<?php

namespace App\Controller\Admin;

use App\Entity\ComplaintCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ComplaintCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ComplaintCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Add complaint category')
            ->setEntityLabelInSingular('Complaint category')
            ->setEntityLabelInPlural('Complaint categories')
            ->setSearchFields(['name', 'fee'])
            ->setDefaultSort(['name' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');
        yield NumberField::new('fee', 'Fee')
            ->setRequired(true);
        yield AssociationField::new('board', 'Board')
            ->setRequired(true);
        yield AssociationField::new('municipality', 'Municipality')
            ->setRequired(true);
    }
}

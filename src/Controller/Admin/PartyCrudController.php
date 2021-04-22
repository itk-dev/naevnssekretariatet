<?php

namespace App\Controller\Admin;

use App\Entity\Party;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PartyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Party::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Add party')
            ->setEntityLabelInSingular('Party')
            ->setEntityLabelInPlural('Parties')
            ->setSearchFields(['firstName', 'lastName'])
            ->setDefaultSort(['firstName' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('firstName', 'First name');
        yield TextField::new('lastName', 'Last name');
        yield TextField::new('address', 'Address');
        yield TextField::new('phoneNumber', 'Phone number');
        yield BooleanField::new('isNameAndAddressProtected', 'Name and address protected');
        yield TextField::new('journalNumber', 'Journal number');
    }
}

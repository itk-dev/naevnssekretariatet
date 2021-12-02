<?php

namespace App\Controller\Admin;

use App\Entity\UploadedDocumentType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DocumentTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UploadedDocumentType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Add document type')
            ->setEntityLabelInSingular('Document type')
            ->setEntityLabelInPlural('Document types')
            ->setSearchFields(['name'])
            ->setDefaultSort(['name' => 'ASC'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name')
            ->setRequired(true)
        ;
    }
}

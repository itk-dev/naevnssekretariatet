<?php

namespace App\Controller\Admin;

use App\Entity\MailTemplate;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichFileType;

class MailTemplateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MailTemplate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Mail templates')
            ->setEntityLabelInSingular('mail template')
            ->overrideTemplate('crud/detail', 'admin/mail-template/detail.html.twig')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield ChoiceField::new('type')
            ->setChoices([
                'Inspection letter' => 'inspection_letter',
                'Decision' => 'decision',
            ])
        ;
        yield TextField::new('name');
        yield TextareaField::new('description');
        yield Field::new('templateFile')
            ->setLabel('Template')
            // ->setRequired(true)
            ->setFormType(VichFileType::class)
            ->setFormTypeOption('allow_delete', false)
            ->onlyOnForms()
        ;
        yield Field::new('templateFilename')
            ->setLabel('Template')
            ->setFormType(VichFileType::class)
            ->hideOnForm()
        ;
    }
}

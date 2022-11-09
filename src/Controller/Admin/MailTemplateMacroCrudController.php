<?php

namespace App\Controller\Admin;

use App\Entity\MailTemplateMacro;
use App\Service\MailTemplateHelper;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailTemplateMacroCrudController extends AbstractCrudController
{
    public function __construct(private MailTemplateHelper $mailTemplateHelper, private TranslatorInterface $translator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return MailTemplateMacro::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Mail template macros')
            ->setEntityLabelInSingular('Mail template macro')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextField::new('macro')
            ->setHelp($this->translator->trans('The value to insert to use the macro in a template, i.e. if the name is <code>name_and_address</code> then <code>${name_and_address}</code> can be used in the template.', [], 'admin'))
        ;
        yield TextareaField::new('content')
            ->setTemplatePath('admin/mail_template_macro/content.html.twig')
            ->setSortable(false)
            ->setHelp($this->translator->trans('The content of the expanded macro.', [], 'admin'))
        ;
        yield ChoiceField::new('templateTypes')
            ->setFormTypeOptions([
                'multiple' => true,
                'expanded' => true,
            ])
            ->setChoices($this->mailTemplateHelper->getMailTemplateTypeChoices())
            ->setHelp($this->translator->trans('Select template types that this macro can be used for. If no template types are selected, the macro can be used in all templates.', [], 'admin'))
        ;
    }
}

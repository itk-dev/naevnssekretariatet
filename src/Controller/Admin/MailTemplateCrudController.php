<?php

namespace App\Controller\Admin;

use App\Entity\MailTemplate;
use App\Service\MailTemplateHelper;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Translation\TranslatableMessage;
use Vich\UploaderBundle\Form\Type\VichFileType;

class MailTemplateCrudController extends AbstractCrudController
{
    public function __construct(private MailTemplateHelper $mailTemplateHelper)
    {
    }

    public static function getEntityFqcn(): string
    {
        return MailTemplate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Mail templates')
            ->setEntityLabelInSingular('Mail template')
            ->overrideTemplate('crud/detail', 'admin/mail-template/detail.html.twig')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine')->getManagerForClass(self::getEntityFqcn());
        /** @var ?MailTemplate $mailTemplate */
        $mailTemplate = $this->getContext()->getEntity()->getInstance();
        $isNew = (null === $mailTemplate) || !$entityManager->contains($mailTemplate);

        yield ChoiceField::new('type')
            ->setChoices($this->mailTemplateHelper->getMailTemplateTypeChoices())
        ;
        yield TextField::new('name');
        yield TextareaField::new('description');
        yield Field::new('templateFile')
            ->setLabel('Template')
            // Require file on new template.
            ->setRequired($isNew)
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions([
                'allow_delete' => false,
                'download_uri' => $isNew ? false
                    : $this->generateUrl('admin_mail_template_template_file', ['id' => $mailTemplate->getId()]),
                'download_label' => new PropertyPath('templateFilename'),
            ])
           ->onlyOnForms()
        ;
    }

    public function delete(AdminContext $context)
    {
        try {
            parent::delete($context);
        } catch (EntityRemoveException $e) {
            // Display flash message
            if (str_contains($e->getMessage(), 'ForeignKeyConstraintViolationException')) {
                $this->addFlash('danger', new TranslatableMessage('Could not delete, as one or more other entities is related to this entity.', [], 'admin'));
            } else {
                $this->addFlash('danger', new TranslatableMessage('Something went wrong when attempting to delete complaint category.', [], 'admin'));
            }
        }

        return $this->redirect($this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->unset(EA::ENTITY_ID)->generateUrl());
    }
}

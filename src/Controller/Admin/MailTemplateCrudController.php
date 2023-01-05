<?php

namespace App\Controller\Admin;

use App\Entity\MailTemplate;
use App\Service\MailTemplateHelper;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class MailTemplateCrudController extends AbstractCrudController
{
    public function __construct(private MailTemplateHelper $mailTemplateHelper, private TranslatorInterface $translator)
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
            ->setFormThemes(['admin/mail-template/form.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
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
        yield TextareaField::new('customFields', 'Custom fields')
            ->setHelp($this->translator->trans('List of custom fields (one per line). Format «name»|«label»|«type». Here «type» can be text or textarea. If «type» is omitted or does not match either of the allowed types the input field will be of type text. Example: full_name|Full name|textarea.',
                [], 'admin'))
            // Avoid showing 'Null' or 'Tom' if customFields is not set.
            ->formatValue(function ($value) {
                return $value ?? ' ';
            })
        ;
        yield TextareaField::new('description');
        yield Field::new('templateFile')
            ->setLabel($this->translator->trans('Template document', [], 'mail_template'))
            ->setHelp('Upload a Word document (docx) to use as a template.')
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
        yield BooleanField::new('isArchived')
            ->renderAsSwitch(false)
            ->setLabel($this->translator->trans('Archived', [], 'mail_template'))
            ->setHelp($this->translator->trans('Templates that are archived will not appear when choosing mail template.', [], 'mail_template'))
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

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_DETAIL === $responseParameters->get('pageName')) {
            $entity = $responseParameters->get('entity');
            assert($entity instanceof EntityDto);
            $mailTemplate = $entity->getInstance();
            assert($mailTemplate instanceof MailTemplate);
            $entities = $this->mailTemplateHelper->getPreviewEntities($mailTemplate);
            $responseParameters->set('preview_entities', $entities);
        }

        return $responseParameters;
    }
}

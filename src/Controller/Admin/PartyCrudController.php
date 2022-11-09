<?php

namespace App\Controller\Admin;

use App\Entity\Party;
use App\Form\Embeddable\AddressType;
use App\Form\Embeddable\IdentificationType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class PartyCrudController extends AbstractCrudController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
            ->setSearchFields(['name'])
            ->setDefaultSort(['name' => 'ASC'])
            // @see https://symfony.com/bundles/EasyAdminBundle/current/design.html#form-field-templates
            ->setFormThemes([
                'admin/field/party_identifier_lookup.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield Field::new('identification', 'Identification')
            ->setFormType(IdentificationType::class)
            ->onlyOnForms()
            ->setFormTypeOptions([
                'block_name' => 'lookup_identifier',
                'is_required' => true,
            ])
            ->addWebpackEncoreEntries('admin_party_identifier_lookup')
        ;
        yield BooleanField::new('isUnderAddressProtection', 'Is under address protection')
            ->hideOnIndex()
            ->setFormTypeOptions([
                'label' => $this->translator->trans('!Is under address protection!', [], 'case'),
            ])
        ;
        yield TextField::new('name', 'Name');
        yield TextField::new('phoneNumber', 'Phone number');
        yield Field::new('address', 'Address')
            ->setFormType(AddressType::class)
        ;
    }

    /**
     * Display only parties which are to be part of part index.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->andWhere('entity.isPartOfPartIndex = true');

        return $response;
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

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addWebpackEncoreEntry('address_protection')
        ;
    }
}

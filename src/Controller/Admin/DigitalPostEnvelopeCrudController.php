<?php

namespace App\Controller\Admin;

use App\Entity\DigitalPostEnvelope;
use App\Service\DigitalPostEnvelopeHelper;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class DigitalPostEnvelopeCrudController extends AbstractCrudController
{
    public function __construct(
        readonly private DigitalPostEnvelopeHelper $envelopeHelper
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return DigitalPostEnvelope::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnIndex()
        ;
        yield TextField::new('status');
        yield TextField::new('statusMessage')
            ->hideOnIndex()
        ;
        yield UrlField::new('digitalPost', 'Digital post')
            ->setTemplatePath('admin/field/digital_post_envelope/digital_post/show.html.twig')
        ;
        yield AssociationField::new('recipient', 'Recipient');
        yield DateField::new('createdAt')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
        yield DateField::new('updatedAt')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Digital post envelopes')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Digital post envelope')
            ->setDefaultSort(['updatedAt' => 'ASC'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::EDIT)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status')
                ->setChoices([
                    DigitalPostEnvelope::STATUS_CREATED => DigitalPostEnvelope::STATUS_CREATED,
                    DigitalPostEnvelope::STATUS_SENT => DigitalPostEnvelope::STATUS_SENT,
                    DigitalPostEnvelope::STATUS_DELIVERED => DigitalPostEnvelope::STATUS_DELIVERED,
                    DigitalPostEnvelope::STATUS_FAILED => DigitalPostEnvelope::STATUS_FAILED,
                    DigitalPostEnvelope::STATUS_FAILED_TOO_MANY_RETRIES => DigitalPostEnvelope::STATUS_FAILED_TOO_MANY_RETRIES,
                ])
            )
        ;
    }
}

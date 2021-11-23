<?php

namespace App\Controller\Admin;

use App\Entity\Party;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
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
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');
        yield TextField::new('cpr', 'CPR');
        yield TextField::new('address', 'Address');
        yield TextField::new('phoneNumber', 'Phone number');
        yield TextField::new('journalNumber', 'Journal number')
            ->formatValue(function ($value) {
                if (null === $value) {
                    return $this->translator->trans('Empty', [], 'admin');
                }

                return $value;
            })
        ;
        yield BooleanField::new('isPartOfPartIndex', 'Add to part index')
            ->hideOnIndex();
    }

    /**
     * Display only parties which are to be part of part index.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->where('entity.isPartOfPartIndex = true');

        return $response;
    }
}

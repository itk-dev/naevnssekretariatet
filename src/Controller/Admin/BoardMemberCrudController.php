<?php

namespace App\Controller\Admin;

use App\Entity\BoardMember;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatorInterface;

class BoardMemberCrudController extends AbstractCrudController
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
        return BoardMember::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', $this->translator->trans('Add boardmember', [], 'admin'))
            ->setEntityLabelInSingular($this->translator->trans('Boardmember', [], 'admin'))
            ->setEntityLabelInPlural($this->translator->trans('Boardmembers', [], 'admin'))
            ->setSearchFields(['name', 'az', 'board.name'])
            ->setDefaultSort(['name' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', $this->translator->trans('Name', [], 'admin'));
        yield TextField::new('az', $this->translator->trans('AZ-ident', [], 'admin'));
        yield AssociationField::new('board', $this->translator->trans('Board', [], 'admin'));
    }
}

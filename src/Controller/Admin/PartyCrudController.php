<?php

namespace App\Controller\Admin;

use App\Entity\Party;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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
            ->setPageTitle('new', $this->translator->trans('Add party', [], 'admin'))
            ->setEntityLabelInSingular($this->translator->trans('Party', [], 'admin'))
            ->setEntityLabelInPlural($this->translator->trans('Parties', [], 'admin'))
            ->setSearchFields(['firstName', 'lastName'])
            ->setDefaultSort(['firstName' => 'ASC'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('firstName', $this->translator->trans('First name', [], 'admin'));
        yield TextField::new('lastName', $this->translator->trans('Last name', [], 'admin'));
        yield TextField::new('address', $this->translator->trans('Address', [], 'admin'));
        yield TextField::new('phoneNumber', $this->translator->trans('Phone number', [], 'admin'));
        yield BooleanField::new('isNameAndAddressProtected', $this->translator->trans('Name and address protected', [], 'admin'));
        yield TextField::new('journalNumber', $this->translator->trans('Journal number', [], 'admin'));
    }
}

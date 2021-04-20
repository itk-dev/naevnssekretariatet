<?php

namespace App\Controller\Admin;

use App\Entity\ComplaintCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatorInterface;

class ComplaintCategoryCrudController extends AbstractCrudController
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
        return ComplaintCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', $this->translator->trans('Add complaint category', [], 'admin'))
            ->setEntityLabelInSingular($this->translator->trans('Complaint category', [], 'admin'))
            ->setEntityLabelInPlural($this->translator->trans('Complaint categories', [], 'admin'))
            ->setSearchFields(['name', 'fee'])
            ->setDefaultSort(['name' => 'ASC'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', $this->translator->trans('Name', [], 'admin'));
        yield NumberField::new('fee', $this->translator->trans('Fee', [], 'admin'));
    }
}

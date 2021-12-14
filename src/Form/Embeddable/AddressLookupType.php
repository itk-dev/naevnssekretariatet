<?php

namespace App\Form\Embeddable;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Address field with DAWA addres look up.
 *
 * @see https://autocomplete.aws.dk/guide2.html
 */
class AddressLookupType extends AddressType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver
            ->setRequired('lookup-placeholder')
            ->setDefault('lookup-help', null)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['attr']['data-dawa-address-lookup'] = json_encode(array_filter([
            'placeholder' => $options['lookup-placeholder'],
            'help' => $options['lookup-help'],
            'selector-pattern' => sprintf('[name="%s[%%name%%]"]', $view->vars['full_name']),
        ]), JSON_THROW_ON_ERROR);
    }
}

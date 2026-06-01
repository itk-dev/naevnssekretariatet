<?php

namespace App\Form\Embeddable;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Address field with adressevælger address lookup.
 *
 * @see https://github.com/Klimadatastyrelsen/adressevaelger
 */
class AddressLookupType extends AddressType
{
    public function __construct(
        TranslatorInterface $translator,
        private readonly string $adressevaelgerToken,
        private readonly string $adressevaelgerApiUrl,
    ) {
        parent::__construct($translator);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver
            ->setRequired('lookup-placeholder')
            ->setDefault('lookup-help', null)
            ->setDefault('lookup-kommune-kode', null)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['attr']['data-address-lookup'] = json_encode(array_filter([
            'placeholder' => $options['lookup-placeholder'],
            'help' => $options['lookup-help'],
            'selector-pattern' => sprintf('[name="%s[%%name%%]"]', $view->vars['full_name']),
            'token' => $this->adressevaelgerToken,
            'api-url' => $this->adressevaelgerApiUrl ?: null,
            'kommune-kode' => $options['lookup-kommune-kode'],
        ]), JSON_THROW_ON_ERROR);
    }
}

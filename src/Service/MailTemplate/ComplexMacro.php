<?php

namespace App\Service\MailTemplate;

use PhpOffice\PhpWord\Element\AbstractElement;

class ComplexMacro
{
    public function __construct(private AbstractElement $element, private string $description)
    {
    }

    public function getElement(): AbstractElement
    {
        return $this->element;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}

<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('camelCaseToUnderscore', [$this, 'camelCaseToUnderscore']),
            new TwigFunction('class', [$this, 'getClass']),
        ];
    }

    public function camelCaseToUnderscore(string $camelCaseString): string
    {
        $result = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $camelCaseString));

        return $result;
    }

    public function getClass($object): string
    {
        return (new \ReflectionClass($object))->getShortName();
    }
}

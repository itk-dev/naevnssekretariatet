<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('camelCaseToUnderscore', [$this, 'camelCaseToUnderscore']),
            new TwigFunction('class', [$this, 'getClass']),
            new TwigFunction('type', 'gettype'),
        ];
    }

    public function getFilters()
    {
        return [
          new TwigFilter('with_unit', [$this, 'withUnit']),
        ];
    }

    public function camelCaseToUnderscore(string $camelCaseString): string
    {
        $result = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $camelCaseString));

        return $result;
    }

    public function getClass($object): ?string
    {
        return is_object($object) ? (new \ReflectionClass($object))->getShortName() : null;
    }

    public function withUnit(string $formattedNumber, string $unit): string
    {
        // Separate formatted number and unit with a narrow non-breaking space.
        return $formattedNumber."\u{202F}".$unit;
    }
}

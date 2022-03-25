<?php

namespace App\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

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
            new TwigFilter('date_nullable', [$this, 'dateNullableFilter']),
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

    public function dateNullableFilter($timestamp, $format): string
    {
        return $timestamp ? twig_date_format_filter($this->twig, $timestamp, $format) : '';
    }
}

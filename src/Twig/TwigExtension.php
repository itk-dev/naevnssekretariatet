<?php

namespace App\Twig;

use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Entity\MailTemplate;
use App\Service\DocumentDeletableHelper;
use App\Service\MailTemplateHelper;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct(private Environment $twig, private DocumentDeletableHelper $deletableHelper, private MailTemplateHelper $mailTemplateHelper)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('camelCaseToUnderscore', [$this, 'camelCaseToUnderscore']),
            new TwigFunction('class', [$this, 'getClass']),
            new TwigFunction('type', 'gettype'),
            new TwigFunction('isDocumentDeletable', [$this, 'isDocumentDeletable']),
            new TwigFunction('getCustomFields', [$this, 'getCustomFields']),
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

    public function dateNullableFilter($timestamp, $format, $nullDisplayValue = ''): string
    {
        return $timestamp ? twig_date_format_filter($this->twig, $timestamp, $format) : $nullDisplayValue;
    }

    public function isDocumentDeletable(Document $document, CaseEntity $case): bool
    {
        return $this->deletableHelper->isDocumentDeletable($document, $case);
    }

    public function getCustomFields(MailTemplate $mailTemplate): array
    {
        return $this->mailTemplateHelper->getCustomFields($mailTemplate);
    }
}

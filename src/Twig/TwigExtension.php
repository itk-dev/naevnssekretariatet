<?php

namespace App\Twig;

use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Repository\DigitalPostRepository;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct(private Environment $twig, private DigitalPostRepository $digitalPostRepository)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('camelCaseToUnderscore', [$this, 'camelCaseToUnderscore']),
            new TwigFunction('class', [$this, 'getClass']),
            new TwigFunction('type', 'gettype'),
            new TwigFunction('isDocumentDeletable', [$this, 'isDocumentDeletable']),
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

    /**
     * Checks whether document on case is deletable.
     */
    public function isDocumentDeletable(Document $document, CaseEntity $case): bool
    {
        $digitalPosts = $this->digitalPostRepository->findByEntity($case);

        // Check whether document has been sent out via digital post
        foreach ($digitalPosts as $digitalPost) {
            if ($document->getId() === $digitalPost->getDocument()->getId()) {
                return false;
            }

            $attachments = $digitalPost->getAttachments();

            foreach ($attachments as $attachment) {
                if ($attachment->getDocument()->getId() === $document->getId()) {
                    return false;
                }
            }
        }

        // Check whether document is attached to agenda case item
        $agendaCaseItems = $case->getAgendaCaseItems();

        foreach ($agendaCaseItems as $agendaCaseItem) {
            $agendaCaseItemDocuments = $agendaCaseItem->getDocuments();
            foreach ($agendaCaseItemDocuments as $agendaCaseItemDocument) {
                if ($document->getId() === $agendaCaseItemDocument->getId()) {
                    return false;
                }
            }
        }

        return true;
    }
}

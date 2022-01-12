<?php

namespace App\Service;

use App\Entity\LogEntry;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogEntryHelper
{
    public function __construct(private PropertyAccessorInterface $propertyAccessor, private TranslatorInterface $translator)
    {
    }

    public function getDisplayData(LogEntry $logEntry): array
    {
        $data = $logEntry->getData();
        unset($data['updatedAt']);

        return $this->getTranslatedData($data);
    }

    private function getTranslatedData(array $data): array
    {
        $translatedData = [];
        foreach ($data as $propertyPath => $value) {
            $label = $this->translatePropertyPath($propertyPath);
            $translatedData[$label] = is_array($value) ? $this->getTranslatedData($value) : $value;
        }

        return $translatedData;
    }

    private function translatePropertyPath(string $propertyPath): string
    {
        $messages = $this->getMessages();
        // Keep only letters, digits and dots and make lowercase.
        $messageKey = strtolower(preg_replace('/[^[:alnum:].]+/', '', $propertyPath));

        return $messages[$messageKey] ?? $propertyPath;
    }

    private ?array $messages = null;

    /**
     * Get list of lowercase messages.
     *
     * The list contains all messages from the "case" and "address" domains.
     *
     * Furthermore combinations from "case" and "address", e.g.
     * "leaseaddress.street", are included to easily translate embedded
     * properties.
     */
    private function getMessages(): array
    {
        if (null === $this->messages) {
            $this->messages = [];
            if ($this->translator instanceof DataCollectorTranslator) {
                $catalogue = $this->translator->getCatalogue();
                // @todo Use class metadata to improve this.
                // @todo Cache the built translations for speedup.
                foreach ($catalogue->all('case') as $key => $value) {
                    // Keep only letters and digits and make lowercase.
                    $caseKey = strtolower(preg_replace('/[^[:alnum:]]+/', '', $key));
                    $this->messages[$caseKey] = $value;
                    foreach ($catalogue->all('address') as $addressKey => $addressValue) {
                        $addressKey = strtolower(preg_replace('/[^[:alnum:]]+/', '', $addressKey));
                        $this->messages[$addressKey] = $addressValue;
                        $this->messages[$caseKey.'.'.$addressKey] = sprintf('%s (%s)', $addressValue, $value);
                    }
                }
            }
        }

        return $this->messages;
    }
}

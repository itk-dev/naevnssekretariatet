<?php

namespace App\Service;

use App\Entity\LogEntry;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogEntryHelper
{
    private array $options;

    public function __construct(private PropertyAccessorInterface $propertyAccessor, private TranslatorInterface $translator, array $logEntryHelperOptions)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($logEntryHelperOptions);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('log_entry_display')
            ->setDefault('log_entry_display', function (OptionsResolver $logEntryResolver) {
                $logEntryResolver
                    ->setRequired('exclude_keys')
                    ->setAllowedValues('exclude_keys', fn ($value) => is_array($value) && array_is_list($value))
                    ->setInfo('exclude_keys', 'log_entry_display.exclude_keys must a list of values')

                    ->setRequired('translation_domains')
                    ->setAllowedTypes('translation_domains', 'array')
                ;
            })
        ;
    }

    public function getDisplayData(LogEntry $logEntry): array
    {
        $data = $logEntry->getData();
        foreach ($this->options['log_entry_display']['exclude_keys'] as $key) {
            if (preg_match('/([\/#~]).+\1$/', $key)) {
                // It looks like a regex.
                $data = array_filter($data, fn ($name) => 0 === preg_match($key, $name), ARRAY_FILTER_USE_KEY);
            } else {
                unset($data[$key]);
            }
        }

        return $this->getTranslatedData($data);
    }

    /**
     * Translate keys in data.
     */
    private function getTranslatedData(array $data): array
    {
        $translatedData = [];
        foreach ($data as $propertyPath => $value) {
            $label = $this->translatePropertyPath($propertyPath);

            // Fixes overwriting of logged properties
            if (isset($translatedData[$label])) {
                $label = $propertyPath;
            }

            if (isset($translatedData[$label])) {
                // Here label is the original $propertyPath, which should never really happen
                $label = $label.uniqid();
            }
            $translatedData[$label] = is_array($value) ? $this->getTranslatedData($value) : $value;
        }

        return $translatedData;
    }

    /**
     * Translate a property.
     */
    private function translatePropertyPath(string $propertyPath): string
    {
        $messages = $this->getMessages();

        // Check for message matching the lowercase property path.
        //
        // This assumes that the translation of a property path matches the path
        // when all non-numeric characters are removed from the translation,
        // i.e. we assume that the property "leaseSize", say, has a translation
        // like "Lease size".
        return $messages[strtolower($propertyPath)] ?? $propertyPath;
    }

    private ?array $messages = null;

    /**
     * Get list of lowercase messages.
     *
     * The list contains all messages from the "case" and "address" domains with
     * non-alpha-numeric characters removed and converted to lowercase, i.e.
     *
     *   "Complaint category" is converted to "complaintcategory"
     *   "Lease size" is converted to "leasesize"
     *
     * Furthermore combinations from "case" and "address", e.g.
     * "leaseaddress.street", are combined and included to easily translate
     * embedded properties.
     */
    private function getMessages(): array
    {
        if (null === $this->messages) {
            $this->messages = [];
            if ($this->translator instanceof DataCollectorTranslator) {
                $catalogue = $this->translator->getCatalogue();
                // @todo Use class metadata to improve this.
                // @todo Cache the built translations for speedup.
                foreach ($this->options['log_entry_display']['translation_domains'] as $domain => $subDomains) {
                    $this->collectMessages($domain, $subDomains, $catalogue);
                }
            }
        }

        return $this->messages;
    }

    private function collectMessages(string $domain, ?array $subDomains, MessageCatalogueInterface $catalogue, string $messagePrefix = '')
    {
        foreach ($catalogue->all($domain) as $key => $message) {
            // Keep only letters and digits and make lowercase.
            $messageKey = strtolower(preg_replace('/[^[:alnum:]]+/', '', $key));
            $this->messages[$messagePrefix.$messageKey] = $message;
            if (is_array($subDomains)) {
                foreach ($subDomains as $subDomain => $subSubDomains) {
                    $this->collectMessages($subDomain, $subSubDomains, $catalogue, $messageKey.'.');
                }
            }
        }
    }
}

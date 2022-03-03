<?php

namespace App\Service;

use App\Entity\DigitalPost as DigitalPostBase;
use App\Entity\DigitalPostAttachment;
use App\Entity\Document;
use App\Entity\Embeddable\Address;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\Serviceplatformen\DigitalPost\DigitalPost;
use ItkDev\Serviceplatformen\SF1600\EnumType\KanalvalgType;
use ItkDev\Serviceplatformen\SF1600\EnumType\PrioritetType;
use ItkDev\Serviceplatformen\SF1600\StructType\BilagSamlingType;
use ItkDev\Serviceplatformen\SF1600\StructType\BilagType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Uid\Uuid;

class DigitalPostHelper extends DigitalPost
{
    private array $serviceOptions;

    public function __construct(private DocumentUploader $documentUploader, private EntityManagerInterface $entityManager, array $options)
    {
        parent::__construct();
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);
    }

    /**
     * @param string         $content     PDF content
     * @param array|string[] $attachments list of PDF content (name => content)
     *
     * @throws \Exception
     */
    public function sendDigitalPost(string $cpr, string $name, Address $address, string $title, string $content, array $attachments = []): array
    {
        $bilag = null;
        if (!empty($attachments)) {
            $bilag = new BilagSamlingType();
            $counter = 0;
            foreach ($attachments as $name => $attachment) {
                $bilag->addToBilag((new BilagType())
                    ->setBilagNavn($name)
                    ->setBilagSorteringsIndeksIdentifikator(++$counter)
                    ->setFilformatNavn('PDF')
                    ->setVedhaeftningIndholdData($attachment)
                );
            }
        }

        $result = $this->setServiceOptions($this->serviceOptions['digital_post_options'])
            ->afsendBrevPerson(
                KanalvalgType::VALUE_A,
                PrioritetType::VALUE_D,
                $cpr,
                $name,
                null,
                $address->getStreet(),
                $address->getNumber(),
                $address->getFloor().($address->getSide() ? ' '.$address->getSide() : ''),
                null,
                null,
                $address->getPostalCode(),
                null,
                null,
                null,
                'PDF',
                $content,
                $title,
                null,
                $bilag
            )
        ;

        return $result;
    }

    public function createDigitalPost(Document $document, string $subject, string $entityType, Uuid $entityId, array $digitalPostAttachments, array $digitalPostRecipients): void
    {
        $this->documentUploader->specifyDirectory('/case_documents/');

        $digitalPosts = [];

        // Setup first DigitalPost
        $digitalPost = new DigitalPostBase();
        $digitalPosts[] = $digitalPost;

        // Digital post restrictions on size and number of attachments
        $sizeLimit = (int) $this->serviceOptions['restriction_options']['total_filesize_allowed'];
        $attachmentLimit = (int) $this->serviceOptions['restriction_options']['number_of_attachments_allowed'];

        // Handle non-attachment properties on DigitalPost
        foreach ($digitalPostRecipients as $recipient) {
            assert($recipient instanceof DigitalPostBase\Recipient);
            $digitalPost->addRecipient($recipient);
        }

        $digitalPost->setDocument($document);
        $documentPath = $this->documentUploader->getFilepath($document->getFilename());
        $digitalPost->setTotalFileSize(filesize($documentPath));
        $digitalPost->setEntityType($entityType);
        $digitalPost->setEntityId($entityId);

        // Subject might depend on attachments in case multiple digital posts are needed,
        // but we add it here and modify it later if necessary
        $digitalPost->setSubject($subject);

        if (count($digitalPostAttachments) > 0) {
            foreach ($digitalPostAttachments as $attachment) {
                assert($attachment instanceof DigitalPostAttachment);
                $attachmentSize = filesize($this->documentUploader->getFilepath($attachment->getDocument()->getFilename()));

                // Check if adding attachment would violate restrictions
                if ($digitalPost->getTotalFileSize() + $attachmentSize >= $sizeLimit || $digitalPost->getAttachments()->count() >= $attachmentLimit) {
                    // Make new DigitalPost for current and remaining attachments

                    $digitalPost = new DigitalPostBase();
                    $digitalPosts[] = $digitalPost;

                    // Handle non-attachment properties on DigitalPost
                    foreach ($digitalPostRecipients as $recipient) {
                        $digitalPost->addRecipient($recipient);
                    }

                    $digitalPost->setDocument($document);
                    $digitalPost->setTotalFileSize(filesize($documentPath));
                    $digitalPost->setEntityType($entityType);
                    $digitalPost->setEntityId($entityId);
                    $digitalPost->setSubject($subject);
                }

                // Add current attachment
                $digitalPost->addAttachment($attachment);
                $digitalPost->setTotalFileSize($digitalPost->getTotalFileSize() + $attachmentSize);
                $this->entityManager->persist($attachment);
            }
        }

        // Modify subject, pointers and persist digital posts
        $numberOfDigitalPosts = count($digitalPosts);

        foreach ($digitalPosts as $index => $digitalPost) {
            assert($digitalPost instanceof DigitalPostBase);
            if ($index > 0) {
                $digitalPost->setPrevious($digitalPosts[$index - 1]);
            }
            if ($numberOfDigitalPosts > 1) {
                $newSubject = sprintf('%s (%d/%d)', $digitalPost->getSubject(), $index + 1, $numberOfDigitalPosts);
                $digitalPost->setSubject($newSubject);
            }

            $this->entityManager->persist($digitalPost);
        }

        $this->entityManager->flush();
    }

    protected function acquireLock(): bool
    {
        return true;
    }

    protected function releaseLock()
    {
        return;
    }

    protected function waitLock(): bool
    {
        return true;
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('digital_post_options')
        ;

        $resolver
            ->setRequired('restriction_options')
            ->setDefault('restriction_options', function (OptionsResolver $optionsResolver) {
                $optionsResolver->setRequired('number_of_attachments_allowed');
                $optionsResolver->setRequired('total_filesize_allowed');
            })
        ;
    }
}

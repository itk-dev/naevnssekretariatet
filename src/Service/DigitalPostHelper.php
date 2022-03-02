<?php

namespace App\Service;

use App\Entity\DigitalPost as DigitalPostEntity;
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
        $digitalPosts = [];

        // Handle attachments and add extra digital post entities if necessary due to size or number of attachments
        // Size limit for attachments and main document combined is 80mb
        // The max number of attachments per digital post is 10
        $documentPath = $this->getFilePath($document);
        $currentSizeOfDigitalPost = $mainDocumentSize = filesize($documentPath);
        $currentNumberOfAttachments = 0;
        $sizeLimit = 80 * (10 ** 6);
        $attachmentLimit = 10;

        // Setup first DigitalPost
        $currentDigitalPost = new DigitalPostEntity();

        // Handle non-attachment properties on DigitalPost
        foreach ($digitalPostRecipients as $recipient) {
            assert($recipient instanceof DigitalPostEntity\Recipient);
            $currentDigitalPost->addRecipient($recipient);
        }

        $currentDigitalPost->setDocument($document);
        $currentDigitalPost->setEntityType($entityType);
        $currentDigitalPost->setEntityId($entityId);

        // Subject might depend on attachments in case multiple digital posts are needed,
        // but we add it here and modify it later if necessary
        $currentDigitalPost->setSubject($subject);

        $this->entityManager->persist($currentDigitalPost);

        if (count($digitalPostAttachments) > 0) {
            foreach ($digitalPostAttachments as $attachment) {
                assert($attachment instanceof DigitalPostAttachment);
                $attachmentSize = filesize($this->getFilePath($attachment->getDocument()));
                // Attachments are made from documents i.e. we are guaranteed they are less than 80mb

                // Check if adding attachment would violate restrictions
                if ($currentSizeOfDigitalPost + $attachmentSize >= $sizeLimit || $currentNumberOfAttachments >= $attachmentLimit) {
                    // Persist current digital post entity and make new for current attachment
                    $digitalPosts[] = $currentDigitalPost;

                    $currentDigitalPost = new DigitalPostEntity();

                    // Handle non-attachment properties on DigitalPost
                    foreach ($digitalPostRecipients as $recipient) {
                        $currentDigitalPost->addRecipient($recipient);
                    }

                    $currentDigitalPost->setDocument($document);
                    $currentDigitalPost->setEntityType($entityType);
                    $currentDigitalPost->setEntityId($entityId);
                    $currentDigitalPost->setSubject($subject);

                    $this->entityManager->persist($currentDigitalPost);
                    $this->entityManager->flush();

                    $currentSizeOfDigitalPost = $mainDocumentSize;
                    $currentNumberOfAttachments = 0;
                }

                // Add current attachment
                $attachment->setPosition($currentNumberOfAttachments + 1);
                $currentDigitalPost->addAttachment($attachment);
                $this->entityManager->persist($attachment);

                $currentSizeOfDigitalPost += $attachmentSize;
                ++$currentNumberOfAttachments;

                $this->entityManager->flush();
            }
            // Now handle the pointers between digital posts if there are more than one digital post entity
        } else {
            // Simply persist and flush
            $this->entityManager->persist($currentDigitalPost);
            $this->entityManager->flush();
        }

        // TODO: Modify title of digital post if more than one was created
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
        $resolver->setRequired([
            'digital_post_options',
        ]);
    }

    private function getFilePath(Document $document): string
    {
        $this->documentUploader->specifyDirectory('/case_documents/');

        return $this->documentUploader->getDirectory().'/'.$document->getFilename();
    }
}

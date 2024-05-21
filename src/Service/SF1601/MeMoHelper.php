<?php

namespace App\Service\SF1601;

use App\Entity\DigitalPost;
use App\Entity\DigitalPost\Recipient as DigitalPostRecipient;
use App\Entity\Document;
use App\Service\DocumentUploader;
use App\Service\IdentificationHelper;
use DataGovDk\Model\Core\Address;
use DigitalPost\MeMo\AdditionalDocument;
use DigitalPost\MeMo\AttentionData;
use DigitalPost\MeMo\AttentionPerson;
use DigitalPost\MeMo\File;
use DigitalPost\MeMo\MainDocument;
use DigitalPost\MeMo\Message;
use DigitalPost\MeMo\MessageBody;
use DigitalPost\MeMo\MessageHeader;
use DigitalPost\MeMo\Recipient;
use DigitalPost\MeMo\Sender;
use ItkDev\Serviceplatformen\Service\SF1601\Serializer;
use ItkDev\Serviceplatformen\Service\SF1601\SF1601;

class MeMoHelper
{
    public const IDENTIFIER_TYPE_CPR = 'CPR';
    public const IDENTIFIER_TYPE_CVR = 'CVR';
    public const SENDER_IDENTIFIER_TYPE = 'sender_identifier_type';
    public const SENDER_IDENTIFIER = 'sender_identifier';
    public const SENDER_LABEL = 'sender_label';

    public function __construct(private DocumentUploader $documentUploader)
    {
    }

    public function createMeMoMessage(DigitalPost $digitalPost, DigitalPostRecipient $digitalPostRecipient, array $options): Message
    {
        $messageUUID = Serializer::createUuid();
        $messageID = Serializer::createUuid();

        $message = new Message();

        $sender = (new Sender())
            ->setIdType($options[self::SENDER_IDENTIFIER_TYPE])
            ->setSenderID($options[self::SENDER_IDENTIFIER])
            ->setLabel($options[self::SENDER_LABEL])
        ;

        $label = $digitalPost->getSubject();
        $recipient = $this->createRecipient($digitalPostRecipient);

        $messageHeader = (new MessageHeader())
            ->setMessageType(SF1601::MESSAGE_TYPE_DIGITAL_POST)
            ->setMessageUUID($messageUUID)
            ->setMessageID($messageID)
            ->setLabel($label)
            ->setMandatory(false)
            ->setLegalNotification(false)
            ->setSender($sender)
            ->setRecipient($recipient)
        ;

        $message->setMessageHeader($messageHeader);

        $body = (new MessageBody())
            ->setCreatedDateTime(new \DateTime())
        ;

        $document = $digitalPost->getDocument();
        $mainDocument = (new MainDocument())
            ->setLabel($document->getDocumentName())
            ->setFile([$this->createFile($document)])
        ;
        $body->setMainDocument($mainDocument);

        foreach ($digitalPost->getAttachments() as $attachment) {
            $document = $attachment->getDocument();

            $additionalDocument = (new AdditionalDocument())
                ->setLabel($document->getDocumentName())
                ->setFile([$this->createFile($document)])
            ;
            $body->addToAdditionalDocument($additionalDocument);
        }

        $message->setMessageBody($body);

        return $message;
    }

    public function removeDocumentContent(Message $message): Message
    {
        $body = $message->getMessageBody();
        $body->getMainDocument()->setFile([]);
        $body->setAdditionalDocument([]);

        return $message;
    }

    private function createFile(Document $document): File
    {
        return (new File())
            ->setEncodingFormat($this->documentUploader->getMimeType($document))
            ->setLanguage('da')
            ->setFilename($document->getFilename())
            ->setContent($this->documentUploader->getFileContent($document))
        ;
    }

    /**
     * Enrich recipient with additional data from a lookup.
     */
    private function createRecipient(DigitalPostRecipient $digitalPostRecipient): Recipient
    {
        $idType = IdentificationHelper::IDENTIFIER_TYPE_CVR === $digitalPostRecipient->getIdentifierType()
            ? self::IDENTIFIER_TYPE_CVR
            : self::IDENTIFIER_TYPE_CPR;
        $recipient = (new Recipient())
            ->setIdType($idType)
            ->setRecipientID($digitalPostRecipient->getIdentifier())
            ->setLabel($digitalPostRecipient->getName())
        ;

        $address = $digitalPostRecipient->getAddress();

        $attentionData = (new AttentionData())
            ->setAttentionPerson((new AttentionPerson())
                ->setLabel($recipient->getLabel())
                ->setPersonID($recipient->getRecipientID())
            )
            ->setAddress((new Address())
                ->setCo('')
                ->setAddressLabel($address->getStreet())
                ->setHouseNumber($address->getNumber())
                ->setFloor($address->getFloor() ?: '')
                ->setDoor($address->getSide() ?: '')
                ->setZipCode($address->getPostalCode() ?: '')
                ->setCity($address->getCity() ?: '')
                ->setCountry('DK')
            )
        ;

        $recipient->setAttentionData($attentionData);

        return $recipient;
    }
}

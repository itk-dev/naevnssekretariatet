<?php

namespace App\Service\SF1601;

use App\Entity\DigitalPost;
use App\Entity\DigitalPost\Recipient as DigitalPostRecipient;
use App\Entity\DigitalPostAttachment;
use App\Entity\Document;
use App\Service\DocumentUploader;
use ItkDev\Serviceplatformen\Service\SF1601\Serializer;
use Oio\Dkal\AfsendelseModtager;
use Oio\Ebxml\CountryIdentificationCode;
use Oio\Fjernprint\Bilag;
use Oio\Fjernprint\DokumentParametre;
use Oio\Fjernprint\ForsendelseI;
use Oio\Fjernprint\ForsendelseModtager;
use Oio\Fjernprint\ModtagerAdresse;

class ForsendelseHelper
{
    public const FORSENDELSES_TYPE_IDENTIFIKATOR = 'forsendelses_type_identifikator';

    public const IDENTIFIER_TYPE_CPR = 'CPR';
    public const IDENTIFIER_TYPE_CVR = 'CVR';
    public const SENDER_IDENTIFIER_TYPE = 'sender_identifier_type';
    public const SENDER_IDENTIFIER = 'sender_identifier';

    public function __construct(private DocumentUploader $documentUploader)
    {
    }

    public function createForsendelse(DigitalPost $digitalPost, DigitalPostRecipient $digitalPostRecipient, array $options): ?ForsendelseI
    {
        // TODO What to do if content is not all PDF files?
        $document = $digitalPost->getDocument();
        if (!$this->isPDF($document)) {
            return null;
        }
        foreach ($digitalPost->getAttachments() as $attachment) {
            if (!$this->isPDF($attachment->getDocument())) {
                return null;
            }
        }

        $forsendelse = new ForsendelseI();

        $forsendelse
            ->setForsendelseModtager($this->createModtager($digitalPostRecipient))
            ->setForsendelseTypeIdentifikator($options[self::FORSENDELSES_TYPE_IDENTIFIKATOR])
            ->setAfsendelseIdentifikator(Serializer::createUuid())
            ->setTransaktionsParametreI()
            ->setDokumentParametre((new DokumentParametre())
                ->setTitelTekst($digitalPost->getSubject())
            )
        ;

        $forsendelse
            ->setFilformatNavn('PDF')
            ->setMeddelelseIndholdData($this->documentUploader->getFileContent($document))
        ;

        foreach ($digitalPost->getAttachments() as $attachment) {
            $document = $attachment->getDocument();

            $bilag = (new Bilag())
                ->setBilagNavn($this->getBilagNavn($attachment))
                ->setFilformatNavn('PDF')
                ->setVedhaeftningIndholdData($this->documentUploader->getFileContent($document))
            ;
            $forsendelse->addToBilagSamling($bilag);
        }

        return $forsendelse;
    }

    /**
     * "Navnet skal være mellem 1 og 1024 tegn, og må ikke slutte med . eller indeholde specialtegn.".
     */
    private function getBilagNavn(DigitalPostAttachment $attachment): string
    {
        $document = $attachment->getDocument();

        $navn = $document->getFilename() ?? sprintf('Bilag %d', $attachment->getPosition());
        $navn = preg_replace('/[^\w-]/', '', $navn);
        $navn = rtrim($navn, '.');
        $navn = substr($navn, 0, 1024);

        return $navn;
    }

    public function removeDocumentContent(ForsendelseI $forsendelse): ForsendelseI
    {
        $forsendelse->setMeddelelseIndholdData('');
        $forsendelse->setBilagSamling([]);

        return $forsendelse;
    }

    private function createModtager(DigitalPostRecipient $recipient): ForsendelseModtager
    {
        $modtager = new ForsendelseModtager();

        $modtager->setAfsendelseModtager((new AfsendelseModtager())
            ->setCPRnummerIdentifikator('0000000000'));

        $address = $recipient->getAddress();

        $modtagerAdresse = (new ModtagerAdresse())
            ->setPersonName($recipient->getName())
            ->setStreetName($address->getStreet())
            ->setStreetBuildingIdentifier($address->getNumber())
            ->setPostCodeIdentifier($address->getPostalCode() ?: '')
            ->setCountryIdentificationCode((new CountryIdentificationCode('DK'))
                ->setScheme('iso3166-alpha2')
            )
        ;

        if ($floor = trim($address->getFloor() ?? '')) {
            $modtagerAdresse->setFloorIdentifier($floor);
        }
        if ($side = trim($address->getSide() ?? '')) {
            $modtagerAdresse->setSuiteIdentifier($side);
        }

        $modtager->setModtagerAdresse($modtagerAdresse);

        return $modtager;
    }

    private function isPDF(Document $document): bool
    {
        return 'application/pdf' === $this->documentUploader->getMimeType($document);
    }
}

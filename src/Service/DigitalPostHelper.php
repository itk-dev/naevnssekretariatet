<?php

namespace App\Service;

use App\Entity\Embeddable\Address;
use ItkDev\Serviceplatformen\DigitalPost\DigitalPost;
use ItkDev\Serviceplatformen\SF1600\EnumType\KanalvalgType;
use ItkDev\Serviceplatformen\SF1600\EnumType\PrioritetType;
use ItkDev\Serviceplatformen\SF1600\StructType\BilagSamlingType;
use ItkDev\Serviceplatformen\SF1600\StructType\BilagType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DigitalPostHelper extends DigitalPost
{
    private array $serviceOptions;

    public function __construct(array $options)
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
}

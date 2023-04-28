<?php

namespace App\Entity\Embeddable;

use App\Exception\IdentifierTypeException;
use App\Logging\LoggableEntityInterface;
use App\Service\IdentificationHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Embeddable
 */
class Identification implements LoggableEntityInterface
{
    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $identifier;

    /**
     * @see https://www.billy.dk/billypedia/p-nummer/
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $pNumber;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedAt;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getPNumber(): ?string
    {
        return $this->pNumber;
    }

    public function setPNumber(?string $pNumber): self
    {
        $this->pNumber = $pNumber;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeInterface $validatedAt): self
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'type',
            'identifier',
            'validatedAt',
        ];
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        switch ($this->type) {
            case IdentificationHelper::IDENTIFIER_TYPE_CPR:
                // Ensure identifier looks like a CPR number
                if (!preg_match('/^\d{10}$/', $this->identifier, $matches)) {
                    // Note that the translation domain must be validators in order to work
                    // @see https://symfony.com/doc/current/validation/translations.html
                    $message = new TranslatableMessage('CPR number must contain 10 digits', [], 'validators');
                    $context->buildViolation($message)
                        ->atPath('identifier')
                        ->addViolation()
                    ;
                }
                break;
            case IdentificationHelper::IDENTIFIER_TYPE_CVR:
                // Ensure identifier looks like a CVR number
                if (!preg_match('/^\d{8}$/', $this->identifier, $matches)) {
                    // Note that the translation domain must be validators in order to work
                    // @see https://symfony.com/doc/current/validation/translations.html
                    $message = new TranslatableMessage('CVR number must contain 8 digits', [], 'validators');
                    $context->buildViolation($message)
                        ->atPath('identifier')
                        ->addViolation()
                    ;
                }
                break;
            default:
                $message = sprintf('Identification type should be one of the following: %s, %s.', IdentificationHelper::IDENTIFIER_TYPE_CPR, IdentificationHelper::IDENTIFIER_TYPE_CVR);
                throw new IdentifierTypeException($message);
        }
    }
}

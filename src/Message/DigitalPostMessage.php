<?php

namespace App\Message;

use App\Entity\DigitalPost;
use App\Entity\DigitalPost\Recipient;
use Symfony\Component\Uid\Uuid;

class DigitalPostMessage implements \JsonSerializable
{
    private Uuid $digitalPostId;
    private Uuid $recipientId;

    public function __construct(DigitalPost $digitalPost, Recipient $recipient)
    {
        $this->digitalPostId = $digitalPost->getId();
        $this->recipientId = $recipient->getId();
    }

    public function getDigitalPostId(): Uuid
    {
        return $this->digitalPostId;
    }

    public function getRecipientId(): Uuid
    {
        return $this->recipientId;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'digital_post_id' => $this->getDigitalPostId(),
            'recipient_id' => $this->getRecipientId(),
        ];
    }
}

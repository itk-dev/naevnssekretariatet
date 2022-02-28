<?php

namespace App\Entity;

use App\Repository\HearingPostRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=HearingPostRequestRepository::class)
 */
class HearingPostRequest extends HearingPost
{
}

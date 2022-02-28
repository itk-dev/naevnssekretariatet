<?php

namespace App\Entity;

use App\Repository\HearingPostResponseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=HearingPostResponseRepository::class)
 */
class HearingPostResponse extends HearingPost
{
}

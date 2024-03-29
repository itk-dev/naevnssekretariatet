<?php

namespace App\Service;

use App\Entity\Municipality;
use App\Entity\User;
use App\Repository\MunicipalityRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

class MunicipalityHelper
{
    /**
     * @var MunicipalityRepository
     */
    private $municipalityRepository;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(MunicipalityRepository $municipalityRepository, Security $security, SessionInterface $session)
    {
        $this->municipalityRepository = $municipalityRepository;
        $this->security = $security;
        $this->session = $session;
    }

    /**
     * Finds municipality considered 'most' active from list below.
     *
     * First is considered 'most' active.
     *
     * Session
     * Favorite municipality User setting
     * Any municipality
     * Null
     */
    public function getActiveMunicipality(): ?Municipality
    {
        /** @var User $user */
        $user = $this->security->getUser();

        // Check if session contains active municipality
        if ($this->session->has('active_municipality')) {
            $activeMunicipality = $this->municipalityRepository->findOneBy(['id' => $this->session->get('active_municipality')]);
        } elseif (null !== $user->getFavoriteMunicipality()) {
            $activeMunicipality = $user->getFavoriteMunicipality();
        } else {
            $activeMunicipality = $this->municipalityRepository->findOneBy([]);
        }

        return $activeMunicipality;
    }

    public function setActiveMunicipalitySession($municipality)
    {
        $this->session->set('active_municipality', $municipality->getId());
    }
}

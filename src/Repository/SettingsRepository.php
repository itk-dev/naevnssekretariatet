<?php

namespace App\Repository;

use App\Entity\Settings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Settings|null find($id, $lockMode = null, $lockVersion = null)
 * @method Settings|null findOneBy(array $criteria, array $orderBy = null)
 * @method Settings[]    findAll()
 * @method Settings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Settings::class);
    }

    public function getSettings(UserInterface $user): Settings
    {
        $favMunicipality = $user->getFavoriteMunicipality();

        if (null === $favMunicipality) {
            throw new \Exception('No favorite municipality set');
        }

        $settings = $this->findOneBy(['municipality' => $favMunicipality]);

        if (null === $settings) {
            $settings = new Settings();
            $settings->setDeadline(14);
            $settings->setMunicipality($favMunicipality);

            $em = $this->getEntityManager();
            $em->persist($settings);
            $em->flush();
        }

        return $settings;
    }
}

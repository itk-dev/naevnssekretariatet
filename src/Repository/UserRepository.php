<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly RoleHierarchyInterface $roleHierarchy)
    {
        parent::__construct($registry, User::class);
    }

    public function findByRole(string $role, array $orderBy): array
    {
        $users = $this->findBy([], $orderBy);

        return array_filter($users, function (User $user) use ($role) {
            $reachableRoles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

            return in_array($role, $reachableRoles, true);
        });
    }
}

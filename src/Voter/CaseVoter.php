<?php

namespace App\Voter;

use App\Entity\CaseEntity;
use App\Entity\User;
use App\Exception\VoterException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class CaseVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        // only vote on cases objects
        if (!$subject instanceof CaseEntity) {
            return false;
        }

        return true;
    }

    /**
     * @throws VoterException
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // As a consequence of the supports method we know subject is a case
        $case = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($case, $user);
            case self::EDIT:
                return $this->canEdit($case, $user);
        }

        $message = sprintf('Unhandled attribute %s in %s', $attribute, CaseVoter::class);
        throw new VoterException($message);
    }

    private function canView(mixed $case, User $user): bool
    {
        return true;
    }

    private function canEdit(mixed $case, User $user): bool
    {
        if ($this->security->isGranted('ROLE_BOARD_MEMBER')) {
            return false;
        }

        return true;
    }
}
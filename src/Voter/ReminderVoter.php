<?php

namespace App\Voter;

use App\Entity\Reminder;
use App\Entity\User;
use App\Exception\VoterException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class ReminderVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on cases objects
        if (!$subject instanceof Reminder) {
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

        // As a consequence of the supports method we know subject is a reminder
        $reminder = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($reminder, $user);
            case self::EDIT:
                return $this->canEdit($reminder, $user);
            case self::DELETE:
                return $this->canDelete($reminder, $user);
        }

        $message = sprintf('Unhandled attribute %s in %s', $attribute, AgendaVoter::class);
        throw new VoterException($message);
    }

    private function canView(mixed $reminder, User $user): bool
    {
        $isCaseworker = $this->security->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->security->isGranted('ROLE_ADMINISTRATION');

        if ($isCaseworker || $isAdministration) {
            return true;
        } else {
            return false;
        }
    }

    private function canEdit(mixed $reminder, User $user): bool
    {
        $isCaseworker = $this->security->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->security->isGranted('ROLE_ADMINISTRATION');

        if ($isCaseworker || $isAdministration) {
            return true;
        } else {
            return false;
        }
    }

    private function canDelete(mixed $reminder, User $user): bool
    {
        $isCaseworker = $this->security->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->security->isGranted('ROLE_ADMINISTRATION');

        if ($isCaseworker || $isAdministration) {
            return true;
        } else {
            return false;
        }
    }
}

<?php

namespace App\Voter;

use App\Entity\Agenda;
use App\Entity\User;
use App\Exception\VoterException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class AgendaVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';
    public const EMPLOYEE = 'employee';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::EMPLOYEE])) {
            return false;
        }

        // only vote on cases objects
        if (!$subject instanceof Agenda) {
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

        // As a consequence of the supports method we know subject is an agenda
        $agenda = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($agenda, $user);
            case self::EDIT:
                return $this->canEdit($agenda, $user);
            case self::DELETE:
                return $this->canDelete($agenda, $user);
            case self::EMPLOYEE:
                return $this->isEmployee($agenda, $user);
        }

        $message = sprintf('Unhandled attribute %s in %s', $attribute, AgendaVoter::class);
        throw new VoterException($message);
    }

    private function canView(mixed $agenda, User $user): bool
    {
        $isCaseworker = $this->security->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->security->isGranted('ROLE_ADMINISTRATION');
        $isBoardMember = $this->security->isGranted('ROLE_BOARD_MEMBER');

        return $isCaseworker || $isAdministration || $isBoardMember;
    }

    private function canEdit(mixed $agenda, User $user): bool
    {
        $isCaseworker = $this->security->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->security->isGranted('ROLE_ADMINISTRATION');

        return $isCaseworker || $isAdministration;
    }

    private function canDelete(mixed $agenda, User $user): bool
    {
        $isCaseworker = $this->security->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->security->isGranted('ROLE_ADMINISTRATION');

        return $isCaseworker || $isAdministration;
    }

    private function isEmployee(mixed $agenda, User $user): bool
    {
        $isCaseworker = $this->security->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->security->isGranted('ROLE_ADMINISTRATION');

        return $isCaseworker || $isAdministration;
    }
}

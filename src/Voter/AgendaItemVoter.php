<?php

namespace App\Voter;

use App\Entity\AgendaItem;
use App\Entity\User;
use App\Exception\VoterException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class AgendaItemVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on cases objects
        if (!$subject instanceof AgendaItem) {
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

        // As a consequence of the supports method we know subject is an agenda item
        $agendaItem = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($agendaItem, $user);
            case self::EDIT:
                return $this->canEdit($agendaItem, $user);
            case self::DELETE:
                return $this->canDelete($agendaItem, $user);
        }

        $message = sprintf('Unhandled attribute %s in %s', $attribute, AgendaItemVoter::class);
        throw new VoterException($message);
    }

    private function canView(mixed $agendaItem, User $user): bool
    {
        $isCaseworker = $this->security->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->security->isGranted('ROLE_ADMINISTRATION');
        $isBoardMember = $this->security->isGranted('ROLE_BOARD_MEMBER');

        return $isCaseworker || $isAdministration || $isBoardMember;
    }

    private function canEdit(mixed $agendaItem, User $user): bool
    {
        $isCaseworker = $this->security->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->security->isGranted('ROLE_ADMINISTRATION');

        return $isCaseworker || $isAdministration;
    }

    private function canDelete(mixed $agendaItem, User $user): bool
    {
        $isCaseworker = $this->security->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->security->isGranted('ROLE_ADMINISTRATION');

        return $isCaseworker || $isAdministration;
    }
}

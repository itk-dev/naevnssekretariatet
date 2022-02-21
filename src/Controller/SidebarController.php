<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\UuidV4;
use Symfony\Contracts\Translation\TranslatorInterface;

class SidebarController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function renderMenu(string $activeRoute): Response
    {
        $menuItems = [];

        $isCaseworker = $this->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->isGranted('ROLE_ADMINISTRATION');
        $isBoardMember = $this->isGranted('ROLE_BOARD_MEMBER');

        if ($isCaseworker || $isAdministration) {
            $menuItems[] = $this->generateMenuItem(
                $this->translator->trans('Dashboard', [], 'sidebar'),
                $this->translator->trans('Go to dashboard', [], 'sidebar'),
                'default',
                'tachometer-alt',
                $activeRoute
            );
        }

        if ($isCaseworker || $isAdministration || $isBoardMember) {
            $menuItems[] = $this->generateMenuItem(
                $this->translator->trans('Case list', [], 'sidebar'),
                $this->translator->trans('Go to list of cases', [], 'sidebar'),
                'case_index',
                'list',
                $activeRoute
            );
        }

        if ($isCaseworker || $isAdministration || $isBoardMember) {
            $menuItems[] = $this->generateMenuItem(
                $this->translator->trans('Agenda list', [], 'sidebar'),
                $this->translator->trans('Go to list of agendas', [], 'sidebar'),
                'agenda_index',
                'list-alt',
                $activeRoute
            );
        }

        if ($isCaseworker || $isAdministration) {
            $menuItems[] = $this->generateMenuItem(
                    $this->translator->trans('Settings', [], 'sidebar'),
                    $this->translator->trans('Go to settings', [], 'sidebar'),
                    'admin',
                    'cog',
                    $activeRoute
            );
        }

        return $this->render('sidebar/_menu.html.twig', [
            'menu_items' => $menuItems,
        ]);
    }

    private function generateMenuItem(string $name, string $tooltip, string $route, string $icon, $activeRoute): array
    {
        return [
            'name' => $this->translator->trans($name, [], 'sidebar'),
            'tooltip' => $this->translator->trans($tooltip, [], 'sidebar'),
            'link' => $this->generateUrl($route),
            'icon' => $icon,
            'active' => $activeRoute === $route,
        ];
    }

    public function renderCaseSubmenu(UuidV4 $caseId, string $activeRoute): Response
    {
        $submenuItems = [];

        $isCaseworker = $this->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->isGranted('ROLE_ADMINISTRATION');
        $isBoardMember = $this->isGranted('ROLE_BOARD_MEMBER');

        if ($isCaseworker || $isAdministration || $isBoardMember) {
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Summary', [], 'sidebar'), ['case_summary'], $caseId, $activeRoute);
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Basic Information', [], 'sidebar'), ['case_show', 'case_edit', 'party_add', 'party_add_from_index', 'party_edit'], $caseId, $activeRoute);
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Status Info', [], 'sidebar'), ['case_status'], $caseId, $activeRoute);
        }

        if ($isCaseworker || $isAdministration) {
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Hearing', [], 'sidebar'), ['case_hearing_index', 'case_hearing_start', 'case_hearing_post_create', 'case_hearing_post_show', 'case_hearing_post_edit'], $caseId, $activeRoute);
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Communication', [], 'sidebar'), ['case_communication'], $caseId, $activeRoute);

            // Currently, they do not wish to create case presentation and decision proposal via TVIST1
            // $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Case presentation', [], 'sidebar'), ['case_presentation'], $caseId, $activeRoute);
            // $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Decision proposal', [], 'sidebar'), ['case_decision_proposal'], $caseId, $activeRoute);

            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Decision', [], 'sidebar'), ['case_decision'], $caseId, $activeRoute);
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Documents', [], 'sidebar'), ['document_index', 'document_create', 'document_copy'], $caseId, $activeRoute);
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Notes', [], 'sidebar'), ['note_index', 'note_edit'], $caseId, $activeRoute);
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Log', [], 'sidebar'), ['case_log', 'case_log_entry_show'], $caseId, $activeRoute);
        }

        return $this->render('sidebar/_submenu.html.twig', [
            'submenu_items' => $submenuItems,
        ]);
    }

    public function renderAgendaSubmenu(UuidV4 $agendaId, string $activeRoute): Response
    {
        $submenuItems = [];

        $isCaseworker = $this->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->isGranted('ROLE_ADMINISTRATION');
        $isBoardMember = $this->isGranted('ROLE_BOARD_MEMBER');

        if ($isCaseworker || $isAdministration || $isBoardMember) {
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Agenda', [], 'sidebar'), ['agenda_show', 'agenda_add_board_member', 'agenda_item_create'], $agendaId, $activeRoute);
        }

        if ($isCaseworker || $isAdministration) {
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Protocol', [], 'sidebar'), ['agenda_protocol'], $agendaId, $activeRoute);
            $submenuItems[] = $this->generateSubmenuItem($this->translator->trans('Broadcast agenda', [], 'sidebar'), ['agenda_broadcast'], $agendaId, $activeRoute);
        }

        return $this->render('sidebar/_submenu.html.twig', [
            'submenu_items' => $submenuItems,
        ]);
    }

    public function renderAgendaCaseItemSubmenu(UuidV4 $agendaId, UuidV4 $agendaItemId, bool $isInspection, string $activeRoute): Response
    {
        $submenuItems = [];

        $isCaseworker = $this->isGranted('ROLE_CASEWORKER');
        $isAdministration = $this->isGranted('ROLE_ADMINISTRATION');
        $isBoardMember = $this->isGranted('ROLE_BOARD_MEMBER');

        if ($isCaseworker || $isAdministration || $isBoardMember) {
            $submenuItems[] = $this->generateAgendaItemSubmenuItem($this->translator->trans('Agenda item', [], 'sidebar'), ['agenda_item_edit'], $agendaId, $agendaItemId, $activeRoute);
            $submenuItems[] = $this->generateAgendaItemSubmenuItem($this->translator->trans('Documents', [], 'sidebar'), ['agenda_case_item_document', 'agenda_case_item_document_attach'], $agendaId, $agendaItemId, $activeRoute);
        }

        // Currently, they do not wish to create case presentation and decision proposal via TVIST1
        // $submenuItems[] = $this->generateAgendaItemSubmenuItem($this->translator->trans('Case presentation', [], 'sidebar'), ['agenda_case_item_presentation'], $agendaId, $agendaItemId, $activeRoute),
        // $submenuItems[] = $this->generateAgendaItemSubmenuItem($this->translator->trans('Decision proposal', [], 'sidebar'), ['agenda_case_item_decision_proposal'], $agendaId, $agendaItemId, $activeRoute),

        //TODO: For Phase one they will not be making inspection letters through TVIST1
//        if ($isInspection && ($isCaseworker || $isAdministration)) {
//            $submenuItems[] = $this->generateAgendaItemSubmenuItem($this->translator->trans('Inspection', [], 'sidebar'), ['agenda_case_item_inspection', 'agenda_case_item_inspection_letter'], $agendaId, $agendaItemId, $activeRoute);
//        }

        return $this->render('sidebar/_submenu.html.twig', [
            'submenu_items' => $submenuItems,
        ]);
    }

    public function renderAgendaManuelItemSubmenu(UuidV4 $agendaId, UuidV4 $agendaItemId, string $activeRoute): Response
    {
        $submenuItems = [
            $this->generateAgendaItemSubmenuItem($this->translator->trans('Agenda item', [], 'sidebar'), ['agenda_item_edit'], $agendaId, $agendaItemId, $activeRoute),
            $this->generateAgendaItemSubmenuItem($this->translator->trans('Documents', [], 'sidebar'), ['agenda_manuel_item_documents', 'agenda_manuel_item_upload_document'], $agendaId, $agendaItemId, $activeRoute),
        ];

        return $this->render('sidebar/_submenu.html.twig', [
            'submenu_items' => $submenuItems,
        ]);
    }

    /**
     * Notice that the generated link uses the first route in array of routes,
     * when it generates the url.
     */
    private function generateSubmenuItem(string $name, array $routes, string $caseId, $activeRoute): array
    {
        return [
            'name' => $this->translator->trans($name, [], 'sidebar'),
            'link' => $this->generateUrl($routes[0], ['id' => $caseId]),
            'active' => in_array($activeRoute, $routes),
        ];
    }

    private function generateAgendaItemSubmenuItem(string $name, array $routes, string $agendaId, string $agendaItemId, $activeRoute): array
    {
        return [
            'name' => $this->translator->trans($name, [], 'sidebar'),
            'link' => $this->generateUrl($routes[0], ['id' => $agendaId, 'agenda_item_id' => $agendaItemId]),
            'active' => in_array($activeRoute, $routes),
        ];
    }
}

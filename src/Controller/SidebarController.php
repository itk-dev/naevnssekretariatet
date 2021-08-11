<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
        $menuItems = [
            $this->generateMenuItem(
                'Dashboard',
                'Go to dashboard',
                'default',
                'tachometer-alt',
                $activeRoute
            ),
            $this->generateMenuItem(
                'Case list',
                'Go to list of cases',
                'case_index',
                'list',
                $activeRoute
            ),
            $this->generateMenuItem(
                'Settings',
                'Go to settings',
                'admin',
                'cog',
                $activeRoute
            ),
        ];

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

    public function renderSubmenu(string $caseId, string $activeRoute): Response
    {
        $submenuItems = [
            $this->generateSubmenuItem('Summary', 'case_summary', $activeRoute),
            $this->generateSubmenuItem('Basic information', 'case_information', $activeRoute),
            $this->generateSubmenuItem('Status info', 'case_status', $activeRoute),
            $this->generateSubmenuItem('Hearing', 'case_hearing', $activeRoute),
            $this->generateSubmenuItem('Communication', 'case_communication', $activeRoute),
            $this->generateSubmenuItem('Documents', 'case_documents', $activeRoute),
            $this->generateSubmenuItem('Decision', 'case_decision', $activeRoute),
            $this->generateSubmenuItem('Notes', 'case_notes', $activeRoute),
            $this->generateSubmenuItem('Log', 'case_log', $activeRoute),
        ];

        return $this->render('sidebar/_submenu.html.twig', [
            'submenu_items' => $submenuItems,
        ]);
    }

    private function generateSubmenuItem(string $name, string $route, $activeRoute): array
    {
        return [
            'name' => $this->translator->trans($name, [], 'sidebar'),
            'link' => $this->generateUrl($route),
            'active' => $activeRoute === $route,
        ];
    }
}

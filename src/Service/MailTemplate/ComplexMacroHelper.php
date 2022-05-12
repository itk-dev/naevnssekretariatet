<?php

namespace App\Service\MailTemplate;

use App\Entity\Agenda;
use App\Entity\CaseEntity;
use App\Repository\BoardMemberRepository;
use PhpOffice\PhpWord\Element\Link;
use PhpOffice\PhpWord\Element\Row;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Font;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ComplexMacroHelper
{
    private array $options;

    public function __construct(private RouterInterface $router, private TranslatorInterface $translator, private BoardMemberRepository $memberRepository, array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    /**
     * @param $entity
     *
     * @return array|ComplexMacro[]
     */
    public function buildMacros($entity): array
    {
        $values = [];

        $values += match (true) {
            $entity instanceof CaseEntity => $this->buildCaseMacros($entity),
            $entity instanceof Agenda => $this->buildAgendaMacros($entity),
            default => []
        };

        return $values;
    }

    private function buildCaseMacros(CaseEntity $case): array
    {
        // Note: Setting text on the link will break the link.
        $values['case.link'] = new ComplexMacro(
            $this->createLink(
                $this->router->generate('case_show', ['id' => $case->getId()], RouterInterface::ABSOLUTE_URL),
                $this->translator->trans('Open case in browser', [], 'case')
            ),
            'Link to the case'
        );

        return $values;
    }

    private function buildAgendaMacros(Agenda $agenda): array
    {
        // Note: Setting text on the link will break the link.
        $values['agenda.link'] = new ComplexMacro(
            $this->createLink(
                $this->router->generate('agenda_show', ['id' => $agenda->getId()], RouterInterface::ABSOLUTE_URL),
                $this->translator->trans('Open agenda in browser', [], 'agenda')
            ),
            'Link to the agenda'
        );

        // Agenda items
        $table = new Table([
            'unit' => TblWidth::TWIP,
            'cellMargin' => 0,
            'spacing' => 0,
        ]);

        foreach ($agenda->getAgendaItems() as $agendaItem) {
            $this->addTableRow($table, [
                sprintf('%s–%s',
                    $agendaItem->getStartTime()->format('H:i'),
                    $agendaItem->getEndTime()->format('H:i')
                ),
                $agendaItem->getTitle(),
                $agendaItem->getMeetingPoint(),
            ]);
        }

        $values['agenda.items'] = new ComplexMacro($table, 'Agenda items');

        // Board members
        $table = new Table([
            'unit' => TblWidth::TWIP,
            'cellMargin' => 0,
            'spacing' => 0,
        ]);

        $members = $this->memberRepository->getMembersAndRolesByAgenda($agenda);
        foreach ($members as $member) {
            $this->addTableRow($table, [
                sprintf('%s (%s)', $member['name'], $member['title']),
            ]);
        }

        $values['agenda.board_members'] = new ComplexMacro($table, 'Board members');

        return $values;
    }

    private function createLink(string $url, string $text = null): Link
    {
        $linkFontStyle = (new Font())
            ->setStyleByArray($this->options['formatting']['link']['font_style'])
        ;

        return new Link($url, $text, $linkFontStyle);
    }

    /**
     * Add values to a new table row.
     *
     * Each value must be
     *
     *   1. a scalar or
     *
     *   2. an array with a `text` key mapping to the cell value (the value must
     *      be a scalar) and, optionally, a `text-style` key with a TextRun
     *      style, e.g.
     *
     *        [
     *          'text-style' => ['alignment' => Jc::END],
     *          'text' => 'A',
     *        ]
     *
     *      The cell width and style can be set with `cell.width` (an integer)
     *      and `cell.style`, e.g.
     *
     *        [
     *          …,
     *          'cell' => [
     *            'width' => 24,
     *            'style' => ['alignment' => Jc::END]],
     *          ]
     *        ]
     */
    private function addTableRow(Table $table, array $values, array $options = []): Row
    {
        $row = $table->addRow(null, $options);
        foreach ($values as $value) {
            if (is_scalar($value)) {
                $row->addCell()->addText($value);
            } elseif (is_array($value) && isset($value['text']) && is_scalar($value['text'])) {
                $row
                    ->addCell($value['cell']['width'] ?? null, $value['cell']['style'] ?? null)
                    ->addTextRun($value['text-style'] ?? [])->addText($value['text']);
            } elseif (null === $value) {
                $row->addCell()->addText('');
            } else {
                throw new \RuntimeException(sprintf('Cannot handle table cell value with type %s: %s', gettype($value), json_encode($value)));
            }
        }

        return $row;
    }

    private function addTableHeaderRow(Table $table, array $values, array $options = []): Row
    {
        $options += [
            'tblHeader' => true,
        ];

        return $this->addTableRow($table, $values, $options);
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'formatting' => [
                'link' => [
                    'font_style' => [
                        // @see https://phpword.readthedocs.io/en/latest/styles.html#font
                        'styleName' => 'InternetLink',
                    ],
                ],
            ],
        ]);
    }
}

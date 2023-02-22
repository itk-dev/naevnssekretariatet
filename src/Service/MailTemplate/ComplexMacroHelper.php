<?php

namespace App\Service\MailTemplate;

use App\Entity\Agenda;
use App\Entity\AgendaBroadcast;
use App\Entity\Board;
use App\Entity\CaseEntity;
use App\Entity\ComplaintCategory;
use App\Entity\HearingPost;
use App\Repository\BoardMemberRepository;
use PhpOffice\PhpWord\Element\Link;
use PhpOffice\PhpWord\Element\Row;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Font;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ComplexMacroHelper
{
    private readonly array $options;

    public function __construct(private readonly RouterInterface $router, private readonly TranslatorInterface $translator, private readonly BoardMemberRepository $memberRepository, array $options)
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
            $entity instanceof HearingPost => $this->buildHearingPostMacros($entity),
            $entity instanceof AgendaBroadcast => $this->buildAgendaBroadcastMacros($entity),
            default => []
        };

        return $values;
    }

    public function getHyperlinkStyle(): array
    {
        return [
            $this->options['formatting']['hyperlink']['style_name'],
            $this->options['formatting']['hyperlink']['styles'],
        ];
    }

    private function buildCaseMacros(CaseEntity $case): array
    {
        $values = [];
        // Note: Setting text on the link will break the link.
        $values['case.link'] = new ComplexMacro(
            $this->createLink(
                $this->router->generate('case_show', ['id' => $case->getId()], RouterInterface::ABSOLUTE_URL),
                $this->translator->trans('Open case in browser', [], 'case')
            ),
            'Link to the case'
        );

        $hearingPostFormUrl = preg_replace_callback(
            '/%(?P<key>[^%]+)%/',
            static fn ($matches) => urlencode(match ($matches['key']) {
                'CASE_ID' => $case->getId(),
                default => ''
            }),
            (string) $this->options['hearing_post_form_url']
        );
        $values['hearing_post_form.url'] = new ComplexMacro(
            (new TextRun())
                ->addText($hearingPostFormUrl),
            'Hearing post form url'
        );

        $values['hearing_post_form.link'] = new ComplexMacro(
            $this->createLink(
                $hearingPostFormUrl,
                $this->options['hearing_post_form_link_text'] ?: $this->translator->trans('Open hearing post form', [], 'case')
            ),
            'Hearing post form link'
        );

        // Complaint categories comma separated names
        $text = new Text();

        $value = implode(', ', array_map(static fn(ComplaintCategory $complaintCategory) => $complaintCategory->getName(), $case->getComplaintCategories()->toArray()));

        $text->setText($value);

        $values['complaintCategories.names'] = new ComplexMacro(
            $text,
            'Comma separated complaint category names'
        );

        return $values;
    }

    private function buildAgendaMacros(Agenda $agenda): array
    {
        $values = [];
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
            // @see https://github.com/PHPOffice/PHPWord/blob/develop/src/PhpWord/SimpleType/TblWidth.php#L36
            //Width in Fiftieths of a Percent
            'unit' => TblWidth::PERCENT,
            'cellMargin' => 0,
            'spacing' => 0,
            'width' => 100 * 50,
        ]);

        $count = 1;
        foreach ($agenda->getAgendaItems() as $agendaItem) {
            $text =
                sprintf('%s–%s &lt; %s',
                $agendaItem->getStartTime()->format('H:i'),
                $agendaItem->getEndTime()->format('H:i'),
                $agendaItem->getTitle(),
            )
            ;

            if ($agendaItem->getMeetingPoint()) {
                $text .= sprintf(', %s', $agendaItem->getMeetingPoint());
            }

            $this->addTableRow($table, [
                [
                    'text' => $count.'.',
                    'cell' => [
                        'width' => 5 * 50,
                    ],
                ],
                [
                    'text' => $text,
                    'cell' => [
                        'width' => 95 * 50,
                    ],
                ],
            ]);

            ++$count;
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

    public function createLink(string $url, string $text = null): Link
    {
        $linkFontStyle = (new Font())
            ->setStyleName($this->options['formatting']['hyperlink']['style_name'])
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
                throw new \RuntimeException(sprintf('Cannot handle table cell value with type %s: %s', gettype($value), json_encode($value, JSON_THROW_ON_ERROR)));
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
                    'style_name' => 'Hyperlink',
                    'styles' => [
                        // See https://phpword.readthedocs.io/en/latest/styles.html#font for styles options.
                        // @see https://www.colorhexa.com/0563c1
                        'color' => '0563C1',
                        'underline' => Font::UNDERLINE_SINGLE,
                    ],
                ],
            ],
            'hearing_post_form_link_text' => '',
        ])
        ->setRequired('hearing_post_form_url')
        ;
    }

    private function buildHearingPostMacros(HearingPost $hearingPost): array
    {
        return $this->buildCaseMacros($hearingPost->getHearing()->getCaseEntity());
    }

    private function buildAgendaBroadcastMacros(AgendaBroadcast $agendaBroadcast): array
    {
        return $this->buildAgendaMacros($agendaBroadcast->getAgenda());
    }
}

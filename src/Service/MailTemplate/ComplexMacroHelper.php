<?php

namespace App\Service\MailTemplate;

use App\Entity\CaseEntity;
use PhpOffice\PhpWord\Element\Link;
use PhpOffice\PhpWord\Element\Row;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use Symfony\Component\Routing\RouterInterface;

class ComplexMacroHelper
{
    public function __construct(private RouterInterface $router)
    {
    }

    /**
     * @param $entity
     *
     * @return array|ComplexMacro[]
     */
    public function buildMacros($entity): array
    {
        $values = [];

        if ($entity instanceof CaseEntity) {
            $table = new Table([
                'unit' => TblWidth::TWIP,
                'borderSize' => 1,
            ]);

            $this->addTableHeaderRow($table, [
                'Name',
                [
                    'text-style' => ['alignment' => Jc::CENTER],
                    'text' => 'A',
                ],
                ['text' => 'B'],
                ['text-style' => ['alignment' => Jc::END], 'text' => 'C'],
            ]);

            $this->addTableRow($table, [
                'Hmm …',
                1,
                2,
                3,
            ]);

            $this->addTableRow($table, [
                'Hmm …',
                1,
                ['text-style' => ['alignment' => Jc::END], 'text' => 2],
                3,
            ]);

            $values['some_list'] = new ComplexMacro($table, 'List of stuff');

            // Note: Setting text on the link will break the link.
            $values['case.link'] = new ComplexMacro(
                new Link($this->router->generate('case_show', ['id' => $entity->getId()], RouterInterface::ABSOLUTE_URL)),
                'Url with link to the case'
            );
        }

        return $values;
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
            } else {
                throw new \RuntimeException(sprintf('Cannot handle table cell value %s', json_encode($value)));
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
}

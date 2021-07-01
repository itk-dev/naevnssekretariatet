<?php

namespace App\Tests\Service;

use App\Entity\CaseEntity;
use App\Entity\Municipality;
use App\Repository\CaseEntityRepository;
use App\Service\CaseManager;
use PHPUnit\Framework\TestCase;

class CaseManagerTest extends TestCase
{
    private $mockCaseRepository;
    private $caseManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockCaseRepository = $this->createMock(CaseEntityRepository::class);

        $this->caseManager = new CaseManager($this->mockCaseRepository);
    }

    public function testGenerateCaseNumberFirstCase()
    {
        $mockMunicipality = $this->createMock(Municipality::class);

        $this->mockCaseRepository
            ->expects($this->once())
            ->method('findLatestCase')
            ->with($mockMunicipality)
            ->willReturn(null);

        $date = new \DateTime();
        $year = $date->format('Y');

        $result = $this->caseManager->generateCaseNumber($mockMunicipality);

        $this->assertSame($year.'-0001', $result);
    }

    public function testGenerateCaseNumberPreviousYear()
    {
        $mockCase = $this->createMock(CaseEntity::class);

        $mockMunicipality = $this->createMock(Municipality::class);

        $this->mockCaseRepository
            ->expects($this->once())
            ->method('findLatestCase')
            ->with($mockMunicipality)
            ->willReturn($mockCase);

        $mockDate = $this->createMock(\DateTime::class);

        $mockCase
            ->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn($mockDate);

        $date = new \DateTime();
        $currentYear = $date->format('Y');

        $previousYear = (int) $currentYear - 1;
        $previousYearString = (string) $previousYear;

        $mockDate
            ->expects($this->once())
            ->method('format')
            ->with('Y')
            ->willReturn($previousYearString);

        $result = $this->caseManager->generateCaseNumber($mockMunicipality);

        $this->assertSame($currentYear.'-0001', $result);
    }

    public function testGetIncrementedCaseCounter()
    {
        $testCaseNumber = '2021-0001';

        $result = $this->caseManager->getIncrementedCaseCounter($testCaseNumber);

        $this->assertSame('0002', $result);
    }
}

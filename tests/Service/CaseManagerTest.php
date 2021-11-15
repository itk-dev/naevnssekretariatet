<?php

namespace App\Tests\Service;

use App\Entity\CaseEntity;
use App\Entity\Municipality;
use App\Repository\CaseEntityRepository;
use App\Service\CaseManager;
use App\Service\WorkflowService;
use PHPUnit\Framework\TestCase;

class CaseManagerTest extends TestCase
{
    private $mockCaseRepository;
    private $mockWorkflowService;
    private $caseManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockCaseRepository = $this->createMock(CaseEntityRepository::class);
        $this->mockWorkflowService = $this->createMock(WorkflowService::class);

        $this->caseManager = new CaseManager(
            $this->mockCaseRepository,
            $this->mockWorkflowService
        );
    }

    public function testGenerateCaseNumberFirstCase()
    {
        $mockMunicipality = $this->createMock(Municipality::class);

        $this->mockCaseRepository
            ->expects($this->once())
            ->method('findLatestCaseByMunicipality')
            ->with($mockMunicipality)
            ->willReturn(null);

        $date = new \DateTime();
        $year = $date->format('Y');

        $actual = $this->caseManager->generateCaseNumber($mockMunicipality);

        $expected = $year.'-0001';

        $this->assertSame($expected, $actual);
    }

    public function testGenerateCaseNumberPreviousYear()
    {
        $mockCase = $this->createMock(CaseEntity::class);

        $mockMunicipality = $this->createMock(Municipality::class);

        $this->mockCaseRepository
            ->expects($this->once())
            ->method('findLatestCaseByMunicipality')
            ->with($mockMunicipality)
            ->willReturn($mockCase);

        $mockDate = $this->createMock(\DateTime::class);

        $mockCase
            ->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn($mockDate);

        $currentDate = new \DateTime();
        $currentYear = $currentDate->format('Y');

        // Subtract a year from DateTime and get the year
        $previousYearDate = $currentDate->sub(new \DateInterval('P1Y'));
        $previousYear = $previousYearDate->format('Y');

        $mockDate
            ->expects($this->once())
            ->method('format')
            ->with('Y')
            ->willReturn($previousYear);

        $actual = $this->caseManager->generateCaseNumber($mockMunicipality);

        $expected = $currentYear.'-0001';

        $this->assertSame($expected, $actual);
    }

    public function testGetIncrementedCaseCounter()
    {
        $testCaseNumber = '2021-0001';

        $actual = $this->caseManager->getIncrementedCaseCounter($testCaseNumber);

        $expected = '0002';

        $this->assertSame($expected, $actual);
    }
}

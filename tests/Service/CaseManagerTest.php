<?php

namespace App\Tests\Service;

use App\Entity\CaseEntity;
use App\Entity\Municipality;
use App\Repository\CaseEntityRepository;
use App\Service\CaseManager;
use App\Service\WorkflowService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockFactory;

class CaseManagerTest extends TestCase
{
    private $mockCaseRepository;
    private $mockWorkflowService;
    private $caseManager;
    private $mockEntityManager;
    private $lockFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockCaseRepository = $this->createMock(CaseEntityRepository::class);
        $this->mockWorkflowService = $this->createMock(WorkflowService::class);
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $this->lockFactory = $this->createMock(LockFactory::class);

        $this->caseManager = new CaseManager(
            $this->mockCaseRepository,
            $this->mockEntityManager,
            $this->lockFactory,
            $this->mockWorkflowService
        );
    }

    public function testGenerateCaseNumberFirstCase()
    {
        $mockMunicipality = $this->createMock(Municipality::class);

        $this->mockCaseRepository
            ->expects($this->once())
            ->method('findLatestCase')
            ->willReturn(null)
        ;

        $date = new \DateTime();
        $year = $date->format('y');

        $actual = $this->caseManager->generateCaseNumber($mockMunicipality);

        $expected = $year.'-000001';

        $this->assertSame($expected, $actual);
    }

    public function testGenerateCaseNumberPreviousYear()
    {
        $mockCase = $this->createMock(CaseEntity::class);

        $mockMunicipality = $this->createMock(Municipality::class);

        $this->mockCaseRepository
            ->expects($this->once())
            ->method('findLatestCase')
            ->willReturn($mockCase)
        ;

        $mockDate = $this->createMock(\DateTime::class);

        $mockCase
            ->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn($mockDate)
        ;

        $currentDate = new \DateTime();
        $currentYear = $currentDate->format('y');

        // Subtract a year from DateTime and get the year
        $previousYearDate = $currentDate->sub(new \DateInterval('P1Y'));
        $previousYear = $previousYearDate->format('y');

        $mockDate
            ->expects($this->once())
            ->method('format')
            ->with('y')
            ->willReturn($previousYear)
        ;

        $actual = $this->caseManager->generateCaseNumber($mockMunicipality);

        $expected = $currentYear.'-000001';

        $this->assertSame($expected, $actual);
    }

    public function testGetIncrementedCaseCounter()
    {
        $testCaseNumber = '21-000001';

        $actual = $this->caseManager->getIncrementedCaseCounter($testCaseNumber);

        $expected = '000002';

        $this->assertSame($expected, $actual);
    }
}

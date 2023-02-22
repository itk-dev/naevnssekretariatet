<?php

namespace App\Tests\Service;

use App\Entity\CaseEntity;
use App\Repository\BoardRepository;
use App\Repository\CaseEntityRepository;
use App\Repository\MunicipalityRepository;
use App\Service\CaseManager;
use App\Service\DocumentUploader;
use App\Service\WorkflowService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class CaseManagerTest extends TestCase
{
    private \App\Repository\BoardRepository&\PHPUnit\Framework\MockObject\MockObject $mockBoardRepository;
    private \App\Repository\CaseEntityRepository&\PHPUnit\Framework\MockObject\MockObject $mockCaseRepository;
    private \App\Service\WorkflowService&\PHPUnit\Framework\MockObject\MockObject $mockWorkflowService;
    private \App\Service\CaseManager $caseManager;
    private \Doctrine\ORM\EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject $mockEntityManager;
    private \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\Lock\LockFactory $lockFactory;
    private \App\Repository\MunicipalityRepository&\PHPUnit\Framework\MockObject\MockObject $mockMunicipalityRepository;
    private \PHPUnit\Framework\MockObject\MockObject&\Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor;
    private \App\Service\DocumentUploader&\PHPUnit\Framework\MockObject\MockObject $documentUploader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockBoardRepository = $this->createMock(BoardRepository::class);
        $this->mockCaseRepository = $this->createMock(CaseEntityRepository::class);
        $this->mockWorkflowService = $this->createMock(WorkflowService::class);
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $this->lockFactory = $this->createMock(LockFactory::class);
        $this->mockMunicipalityRepository = $this->createMock(MunicipalityRepository::class);
        $this->propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $this->documentUploader = $this->createMock(DocumentUploader::class);

        $this->caseManager = new CaseManager(
            $this->mockBoardRepository,
            $this->mockCaseRepository,
            $this->mockEntityManager,
            $this->lockFactory,
            $this->mockMunicipalityRepository,
            $this->propertyAccessor,
            $this->mockWorkflowService,
            $this->documentUploader
        );
    }

    public function testGenerateCaseNumberFirstCase()
    {
        $this->mockCaseRepository
            ->expects($this->once())
            ->method('findLatestCase')
            ->willReturn(null)
        ;

        $date = new DateTime();
        $year = $date->format('y');

        $actual = $this->caseManager->generateCaseNumber();

        $expected = $year.'-000001';

        $this->assertSame($expected, $actual);
    }

    public function testGenerateCaseNumberPreviousYear()
    {
        $mockCase = $this->createMock(CaseEntity::class);

        $this->mockCaseRepository
            ->expects($this->once())
            ->method('findLatestCase')
            ->willReturn($mockCase)
        ;

        $currentDate = new DateTime();
        $currentYear = $currentDate->format('y');

        // Subtract a year from DateTime and get the year
        $previousYearDate = $currentDate->sub(new \DateInterval('P1Y'));

        $mockCase
            ->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn($previousYearDate)
        ;

        $actual = $this->caseManager->generateCaseNumber();

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

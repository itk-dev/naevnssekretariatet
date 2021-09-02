<?php

namespace App\Tests\Logging;

use App\Entity\CaseEntity;
use App\Entity\LogEntry;
use App\Entity\User;
use App\Logging\EntityListener\AbstractEntityListener;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\UuidV4;

class AbstractListenerCreateLogEntryTest extends TestCase
{
    private $mockListener;
    private $testAction;
    private $mockCase;
    private $mockArgs;
    private $mockUnitOfWork;
    private $mockUsername;
    private $mockCaseID;
    private $mockSecurity;
    private $expectedDataArray;
    private $mockEntityManager;

    public function testCreateLogEntryOnStringChange()
    {
        $this->setUpNumberOfUowCalls(1);
        $this->setUpExpects();

        $changeArray = [
            'caseType' => [
                null,
                'TestCaseType',
            ],
        ];

        $this->mockUnitOfWork
            ->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->mockCase)
            ->willReturn($changeArray);

        $logEntry = $this->mockListener->createLogEntry($this->testAction, $this->mockCase, $this->mockArgs);

        $this->expectedDataArray = [
            'caseType' => 'TestCaseType',
        ];

        $this->assertPropertiesOfLogEntry($logEntry);
    }

    public function testCreateLogEntryOnIntegerChange()
    {
        $this->setUpNumberOfUowCalls(1);
        $this->setUpExpects();

        $changeArray = [
            'size' => [
                null,
                '6',
            ],
        ];

        $this->mockUnitOfWork
            ->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->mockCase)
            ->willReturn($changeArray);

        $logEntry = $this->mockListener->createLogEntry($this->testAction, $this->mockCase, $this->mockArgs);

        $this->expectedDataArray = [
            'size' => '6',
        ];

        $this->assertPropertiesOfLogEntry($logEntry);
    }

    public function testCreateLogEntryOnCreatedAtChange()
    {
        $this->setUpNumberOfUowCalls(1);
        $this->setUpExpects();

        $caseDateTime = new \DateTime();

        $changeArray = [
            'createdAt' => [
                null,
                $caseDateTime,
            ],
        ];

        $this->mockUnitOfWork
            ->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->mockCase)
            ->willReturn($changeArray);

        $logEntry = $this->mockListener->createLogEntry($this->testAction, $this->mockCase, $this->mockArgs);

        $this->expectedDataArray = [
            'createdAt' => $caseDateTime->format('d-m-Y H:i:s'),
        ];

        $this->assertPropertiesOfLogEntry($logEntry);
    }

    public function testCreateLogEntryRemoveActionOnScalarTypes()
    {
        $this->setUpNumberOfUowCalls(2);
        $this->setUpExpects();

        $changeArray = [];

        $this->mockEntityManager
            ->expects($this->exactly(2))
            ->method('getUnitOfWork')
            ->willReturn($this->mockUnitOfWork);

        $this->mockUnitOfWork
            ->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->mockCase)
            ->willReturn($changeArray);

        $mockStringKey = 'someMockStringKey';
        $mockStringValue = 'someMockStringValue';

        $mockIntKey = 'someMockIntKey';
        $mockIntValue = 5;

        $originalDataArray = [
            $mockStringKey => $mockStringValue,
            $mockIntKey => $mockIntValue,
        ];

        $this->mockUnitOfWork
            ->expects($this->once())
            ->method('getOriginalEntityData')
            ->with($this->mockCase)
            ->willReturn($originalDataArray);

        $logEntry = $this->mockListener->createLogEntry($this->testAction, $this->mockCase, $this->mockArgs);

        $this->expectedDataArray = $originalDataArray;

        $this->assertPropertiesOfLogEntry($logEntry);
    }

    public function testCreateLogEntryNoChange()
    {
        $this->setUpNumberOfUowCalls(1);
        $this->setUpExpects();

        $changeArray = [
            'subboard' => [
                null,
                null,
            ],
        ];

        $this->mockUnitOfWork
            ->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->mockCase)
            ->willReturn($changeArray);

        $logEntry = $this->mockListener->createLogEntry($this->testAction, $this->mockCase, $this->mockArgs);

        $this->expectedDataArray = [];

        $this->assertPropertiesOfLogEntry($logEntry);
    }

    public function testCreateLogEntryException()
    {
        $this->setUpNumberOfUowCalls(1);
        $this->expectException(ItkDevLoggingException::class);

        $changeArray = [
            'name' => [
                'OnlyBeforeName',
            ],
        ];

        $this->mockUnitOfWork
            ->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->mockCase)
            ->willReturn($changeArray);

        $logEntry = $this->mockListener->createLogEntry($this->testAction, $this->mockCase, $this->mockArgs);
    }

    public function setUpExpects()
    {
        $mockUser = $this->createMock(User::class);

        $this->mockSecurity
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($mockUser);

        $this->mockUsername = 'TestUser';

        $mockUser
            ->expects($this->once())
            ->method('getUsername')
            ->willReturn($this->mockUsername);

        $caseUuidv4 = new UuidV4();

        $this->mockCaseID = $caseUuidv4->__toString();

        $this->mockCase
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($caseUuidv4);
    }

    private function setUpNumberOfUowCalls(int $numberOfUnitOfWorkCalls)
    {
        $this->mockSecurity = $this->createMock(Security::class);

        $this->mockListener = $this->getMockBuilder(AbstractEntityListener::class)
            ->setMethodsExcept(['createLogEntry'])
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->mockSecurity])
            ->getMock();

        $this->testAction = 'TestAction';
        $this->mockCase = $this->createMock(CaseEntity::class);
        $this->mockArgs = $this->createMock(LifecycleEventArgs::class);

        $this->mockEntityManager = $this->createMock(EntityManager::class);

        $this->mockArgs
            ->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->mockEntityManager);

        $this->mockArgs
            ->expects($this->once())
            ->method('getObject')
            ->willReturn($this->mockCase);

        $this->mockUnitOfWork = $this->createMock(UnitOfWork::class);

        $this->mockEntityManager
            ->expects($this->exactly($numberOfUnitOfWorkCalls))
            ->method('getUnitOfWork')
            ->willReturn($this->mockUnitOfWork);

        $this->expectedDataArray = [];
    }

    public function assertPropertiesOfLogEntry(LogEntry $logEntry)
    {
        // Assert all properties are as expected
        $this->assertSame($this->mockCaseID, $logEntry->getCaseID());
        $this->assertSame(get_class($this->mockCase), $logEntry->getEntityType());
        $this->assertSame($this->mockCaseID, $logEntry->getEntityID());
        $this->assertSame($this->testAction, $logEntry->getAction());
        $this->assertSame($this->mockUsername, $logEntry->getUser());
        $this->assertSame($this->expectedDataArray, $logEntry->getData());
    }
}

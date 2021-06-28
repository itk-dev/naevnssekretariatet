<?php

namespace App\Tests\Logging;

use App\Entity\CaseEntity;
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
    private $caseUuidv4;
    private $mockSecurity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSecurity = $this->createMock(Security::class);

        $this->mockListener = $this->getMockBuilder(AbstractEntityListener::class)
            ->setMethodsExcept(['handleLoggableEntities', 'createLogEntry'])
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->mockSecurity])
            ->getMock();

        $this->testAction = 'TestAction';
        $this->mockCase = $this->createMock(CaseEntity::class);
        $this->mockArgs = $this->createMock(LifecycleEventArgs::class);

        $mockEntityManager = $this->createMock(EntityManager::class);

        $this->mockArgs
            ->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($mockEntityManager);

        $this->mockArgs
            ->expects($this->once())
            ->method('getObject')
            ->willReturn($this->mockCase);

        $this->mockUnitOfWork = $this->createMock(UnitOfWork::class);

        $mockEntityManager
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($this->mockUnitOfWork);
    }

    public function testCreateLogEntryOnStringChange()
    {
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

        $expectedDataArray = [
            'caseType' => 'TestCaseType',
        ];

        // Assert all properties are as expected
        $this->assertSame($this->mockCaseID, $logEntry->getCaseID());
        $this->assertSame(get_class($this->mockCase), $logEntry->getEntityType());
        $this->assertSame($this->mockCaseID, $logEntry->getEntityID());
        $this->assertSame($this->testAction, $logEntry->getAction());
        $this->assertSame($this->mockUsername, $logEntry->getUser());
        $this->assertSame($expectedDataArray, $logEntry->getData());
    }

    public function testCreateLogEntryOnIntegerChange()
    {
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

        $expectedDataArray = [
            'size' => '6',
        ];

        // Assert all properties are as expected
        $this->assertSame($this->mockCaseID, $logEntry->getCaseID());
        $this->assertSame(get_class($this->mockCase), $logEntry->getEntityType());
        $this->assertSame($this->mockCaseID, $logEntry->getEntityID());
        $this->assertSame($this->testAction, $logEntry->getAction());
        $this->assertSame($this->mockUsername, $logEntry->getUser());
        $this->assertSame($expectedDataArray, $logEntry->getData());
    }

    public function testCreateLogEntryOnIdChange()
    {
        $this->setUpExpects();

        $changeArray = [
            'id' => [
                null,
                $this->caseUuidv4,
            ],
        ];

        $this->mockUnitOfWork
            ->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->mockCase)
            ->willReturn($changeArray);

        $logEntry = $this->mockListener->createLogEntry($this->testAction, $this->mockCase, $this->mockArgs);

        $expectedDataArray = [
            'id' => $this->mockCaseID,
        ];

        // Assert all properties are as expected
        $this->assertSame($this->mockCaseID, $logEntry->getCaseID());
        $this->assertSame(get_class($this->mockCase), $logEntry->getEntityType());
        $this->assertSame($this->mockCaseID, $logEntry->getEntityID());
        $this->assertSame($this->testAction, $logEntry->getAction());
        $this->assertSame($this->mockUsername, $logEntry->getUser());
        $this->assertSame($expectedDataArray, $logEntry->getData());
    }

    public function testCreateLogEntryOnCreatedAtChange()
    {
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

        $expectedDataArray = [
            'createdAt' => $caseDateTime->format('d-m-Y H:i:s'),
        ];

        // Assert all properties are as expected
        $this->assertSame($this->mockCaseID, $logEntry->getCaseID());
        $this->assertSame(get_class($this->mockCase), $logEntry->getEntityType());
        $this->assertSame($this->mockCaseID, $logEntry->getEntityID());
        $this->assertSame($this->testAction, $logEntry->getAction());
        $this->assertSame($this->mockUsername, $logEntry->getUser());
        $this->assertSame($expectedDataArray, $logEntry->getData());
    }

    public function testCreateLogEntryNoChange()
    {
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

        $expectedDataArray = [];

        // Assert all properties are as expected
        $this->assertSame($this->mockCaseID, $logEntry->getCaseID());
        $this->assertSame(get_class($this->mockCase), $logEntry->getEntityType());
        $this->assertSame($this->mockCaseID, $logEntry->getEntityID());
        $this->assertSame($this->testAction, $logEntry->getAction());
        $this->assertSame($this->mockUsername, $logEntry->getUser());
        $this->assertSame($expectedDataArray, $logEntry->getData());
    }

    public function testCreateLogEntryException()
    {
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

        $this->caseUuidv4 = new UuidV4();

        $this->mockCaseID = $this->caseUuidv4->__toString();

        $this->mockCase
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($this->caseUuidv4);
    }
}

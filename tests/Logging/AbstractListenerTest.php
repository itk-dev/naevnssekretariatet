<?php

namespace App\Tests\Logging;

use App\Entity\CaseEntity;
use App\Entity\Municipality;
use App\Entity\User;
use App\Logging\EntityListener\AbstractEntityListener;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\UuidV4;

class AbstractListenerTest extends TestCase
{
    private $mockSecurity;
    private $mockListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSecurity = $this->createMock(Security::class);

        $this->mockListener = $this->getMockBuilder(AbstractEntityListener::class)
            ->setMethodsExcept(['handleLoggableEntities', 'createLogEntry'])
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->mockSecurity])
            ->getMock();
    }

    public function testCreateLogEntryException()
    {
        $this->expectException(ItkDevLoggingException::class);

        $testAction = 'TestAction';
        $mockCase = $this->createMock(CaseEntity::class);
        $mockArgs = $this->createMock(LifecycleEventArgs::class);

        $mockEntityManager = $this->createMock(EntityManager::class);

        $mockArgs
            ->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($mockEntityManager);

        $mockMunicipality = $this->createMock(Municipality::class);

        $mockArgs
            ->expects($this->once())
            ->method('getObject')
            ->willReturn($mockMunicipality);

        $changeArray = [
            'name' => [
                'OnlyBeforeName',
            ],
        ];

        $mockUnitOfWork = $this->createMock(UnitOfWork::class);

        $mockEntityManager
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($mockUnitOfWork);

        $mockUnitOfWork
            ->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($mockMunicipality)
            ->willReturn($changeArray);

        $this->mockListener->createLogEntry($testAction, $mockCase, $mockArgs);
    }

    public function testSimpleTypeCreateLogEntry()
    {
        // Test name update of a Municipality

        $testAction = 'TestAction';
        $mockCase = $this->createMock(CaseEntity::class);
        $mockArgs = $this->createMock(LifecycleEventArgs::class);

        $mockEntityManager = $this->createMock(EntityManager::class);

        $mockArgs
            ->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($mockEntityManager);

        $mockMunicipality = $this->createMock(Municipality::class);

        $mockArgs
            ->expects($this->once())
            ->method('getObject')
            ->willReturn($mockMunicipality);

        $changeArray = [
            'name' => [
                'BeforeName',
                'AfterName',
            ],
        ];

        $mockUnitOfWork = $this->createMock(UnitOfWork::class);

        $mockEntityManager
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($mockUnitOfWork);

        $mockUnitOfWork
            ->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($mockMunicipality)
            ->willReturn($changeArray);

        $mockCaseID = $this->createMock(UuidV4::class);

        $mockCaseIDString = 'testCaseId';

        $mockCaseID
            ->expects($this->once())
            ->method('__toString')
            ->willReturn($mockCaseIDString);

        $mockCase
            ->expects($this->once())
            ->method('getId')
            ->willReturn($mockCaseID);

        $mockMunicipalityID = $this->createMock(UuidV4::class);

        $mockMunicipalityIDString = 'testMunicipalityId';

        $mockMunicipalityID
            ->expects($this->once())
            ->method('__toString')
            ->willReturn($mockMunicipalityIDString);

        $mockMunicipality
            ->expects($this->once())
            ->method('getId')
            ->willReturn($mockMunicipalityID);

        $mockUser = $this->createMock(User::class);

        $this->mockSecurity
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($mockUser);

        $mockUsername = 'TestUser';

        $mockUser
            ->expects($this->once())
            ->method('getUsername')
            ->willReturn($mockUsername);

        $logEntry = $this->mockListener->createLogEntry($testAction, $mockCase, $mockArgs);

        $expectedDataArray = [
            'name' => 'AfterName',
        ];

        // Assert all properties are as expected:
        $this->assertSame($mockCaseIDString, $logEntry->getCaseID());
        $this->assertSame(get_class($mockMunicipality), $logEntry->getEntityType());
        $this->assertSame($mockMunicipalityIDString, $logEntry->getEntityID());
        $this->assertSame($testAction, $logEntry->getAction());
        $this->assertSame($mockUsername, $logEntry->getUser());
        $this->assertSame($expectedDataArray, $logEntry->getData());
    }

    public function testHandleLoggablePropertiesException()
    {
        $this->expectException(ItkDevLoggingException::class);

        $mockEntity = $this->createMock(Municipality::class);

        $mockEntity
            ->expects($this->exactly(1))
            ->method('getLoggableProperties')
            ->willReturn(['testProperty']);

        $this->mockListener->handleLoggableEntities($mockEntity);
    }

    public function testHandleLoggableProperties()
    {
        $mockMunicipality = $this->createMock(Municipality::class);

        $loggableProperties = [
            'id',
            'name',
        ];

        $mockMunicipality
            ->expects($this->once())
            ->method('getLoggableProperties')
            ->willReturn($loggableProperties);

        $mockMunicipalityID = $this->createMock(UuidV4::class);

        $mockMunicipalityName = 'TestMunicipality';

        $mockMunicipality
            ->expects($this->once())
            ->method('getId')
            ->willReturn($mockMunicipalityID);

        $mockMunicipality
            ->expects($this->once())
            ->method('getName')
            ->willReturn($mockMunicipalityName);

        $result = $this->mockListener->handleLoggableEntities($mockMunicipality);

        $expectedArray = [
            'id' => $mockMunicipalityID,
            'name' => 'TestMunicipality',
        ];

        $this->assertSame($expectedArray, $result);
    }
}

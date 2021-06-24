<?php

namespace App\Tests\Logging;

use App\Entity\LogEntry;
use App\Entity\Municipality;
use App\Entity\ResidentComplaintBoardCase;
use App\Logging\EntityListener\AbstractRelatedToCaseListener;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class AbstractRelatedToCaseListenerTest extends TestCase
{
    public function testLogActivity()
    {
        $mockListener = $this->getMockBuilder(AbstractRelatedToCaseListener::class)
            ->setMethodsExcept(['logActivity'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockMunicipality = $this->createMock(Municipality::class);

        $mockCaseOne = $this->createMock(ResidentComplaintBoardCase::class);
        $mockCaseTwo = $this->createMock(ResidentComplaintBoardCase::class);

        $caseCollection = new ArrayCollection([
            $mockCaseOne,
            $mockCaseTwo,
        ]);

        $mockMunicipality
            ->expects($this->once())
            ->method('getCaseEntities')
            ->willReturn($caseCollection);

        $testAction = 'TestAction';

        $mockArgs = $this->createMock(LifecycleEventArgs::class);

        $mockEntityManager = $this->createMock(EntityManager::class);

        $mockArgs
            ->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($mockEntityManager);

        $mockArgs
            ->expects($this->once())
            ->method('getObject')
            ->willReturn($mockMunicipality);

        $mockLogEntryOne = $this->createMock(LogEntry::class);
        $mockLogEntryTwo = $this->createMock(LogEntry::class);

        $mockListener
            ->expects($this->exactly(2))
            ->method('createLogEntry')
            ->withConsecutive(
                [$testAction, $mockCaseOne, $mockArgs],
                [$testAction, $mockCaseTwo, $mockArgs]
            )
            ->willReturnOnConsecutiveCalls(
                $mockLogEntryOne,
                $mockLogEntryTwo
            );

        $mockEntityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive([$mockLogEntryOne], [$mockLogEntryTwo]);

        $mockEntityManager
            ->expects($this->once())
            ->method('flush');

        $mockListener->logActivity($testAction, $mockArgs);
    }
}

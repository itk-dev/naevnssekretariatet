<?php

namespace App\Tests\Logging;

use App\Entity\Municipality;
use App\Logging\EntityListener\AbstractEntityListener;
use App\Logging\ItkDevGetFunctionNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\UuidV4;

class AbstractListenerHandleLoggablePropertiesTest extends TestCase
{
    private $mockListener;

    protected function setUp(): void
    {
        parent::setUp();

        $mockSecurity = $this->createMock(Security::class);

        $this->mockListener = $this->getMockBuilder(AbstractEntityListener::class)
            ->setMethodsExcept(['handleLoggableEntities'])
            ->enableOriginalConstructor()
            ->setConstructorArgs([$mockSecurity])
            ->getMock()
        ;
    }

    public function testHandleLoggablePropertiesException()
    {
        $this->expectException(ItkDevGetFunctionNotFoundException::class);

        $mockEntity = $this->createMock(Municipality::class);

        $mockEntity
            ->expects($this->exactly(1))
            ->method('getLoggableProperties')
            ->willReturn(['testProperty'])
        ;

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
            ->willReturn($loggableProperties)
        ;

        $mockMunicipalityID = $this->createMock(UuidV4::class);

        $mockMunicipalityName = 'TestMunicipality';

        $mockMunicipality
            ->expects($this->once())
            ->method('getId')
            ->willReturn($mockMunicipalityID)
        ;

        $mockMunicipality
            ->expects($this->once())
            ->method('getName')
            ->willReturn($mockMunicipalityName)
        ;

        $result = $this->mockListener->handleLoggableEntities($mockMunicipality);

        $expectedArray = [
            'id' => $mockMunicipalityID,
            'name' => 'TestMunicipality',
        ];

        $this->assertSame($expectedArray, $result);
    }
}

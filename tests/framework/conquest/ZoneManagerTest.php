<?php

namespace tests\framework\conquest;

use tests\TestCaseBase;
use framework\conquest\ZoneManager;

/**
 * Description of ZoneManagerTest
 *
 * @author chris
 */
class ZoneManagerTest extends TestCaseBase
{
    private $conquestRepositoryMock;
    private $zoneRepositoryMock;
    private $strikeRepositoryMock;
    private $manager;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->conquestRepositoryMock = $this->getMockBuilder(\dal\managers\ConquestRepository::class)
                ->setMethods(['GetCurrentConquest'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->zoneRepositoryMock = $this->getMockBuilder(\dal\managers\ZoneRepository::class)
                ->setMethods(['GetAllZones'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->strikeRepositoryMock = $this->getMockBuilder(\dal\managers\StrikeRepository::class)
                ->setMethods(['GetStrikesByZone'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->manager = new ZoneManager($this->conquestRepositoryMock, $this->zoneRepositoryMock, $this->strikeRepositoryMock);
    }

    public function testConquestManager()
    {
        $conquest = new \dal\models\ConquestModel();
        $conquest->id = 1;
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);
        
        $zone = new \dal\models\ZoneModel();
        $zone->conquest = $conquest;
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetAllZones')
                ->with($conquest)
                ->willReturn([$zone]);
        
        $strike = new \dal\models\StrikeModel();
        $strike->id = 1;
        $this->strikeRepositoryMock->expects($this->once())
                ->method('GetStrikesByZone')
                ->with($zone)
                ->willReturn([$strike]);
        
        $result = $this->manager->GetStrikeTable();
        
        var_dump($result);
    }
}

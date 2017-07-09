<?php

namespace tests\framework\conquest;

use tests\TestCaseBase;
use framework\conquest\ConquestManager;

/**
 * Description of ConquestManagerTest
 *
 * @author chris
 */
class ConquestManagerTest extends TestCaseBase
{
    private $conquestRepositoryMock;
    private $zoneRepositoryMock;
    private $nodeRepositoryMock;
    private $strikeRepositoryMock;
    private $manager;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->conquestRepositoryMock = $this->getMockBuilder(\dal\managers\ConquestRepository::class)
                ->setMethods(['GetConquestByDate'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->zoneRepositoryMock = $this->getMockBuilder(\dal\managers\ZoneRepository::class)
                ->setMethods(['GetAllZonesByConquest'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->nodeRepositoryMock = $this->getMockBuilder(\dal\managers\NodeRepository::class)
                ->setMethods(['GetAllNodesByConquest'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->strikeRepositoryMock = $this->getMockBuilder(\dal\managers\StrikeRepository::class)
                ->setMethods(['GetStrikesByConquest'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->manager = new ConquestManager($this->conquestRepositoryMock, $this->zoneRepositoryMock, $this->nodeRepositoryMock, $this->strikeRepositoryMock);
    }

    public function testConquestManager()
    {
        $conquest = new \dal\models\ConquestModel();
        $conquest->id = 1;
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetConquestByDate')
                ->willReturn($conquest);
        
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetAllZonesByConquest')
                ->with($conquest)
                ->willReturn([]);
        $this->manager->GetLastPhaseStats();
    }

}

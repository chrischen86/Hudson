<?php

namespace tests\framework\conquest;

use tests\TestCaseBase;
use framework\conquest\ConquestManager;
use DateTime;
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
                ->setMethods(['GetConquestByDate', 'GetConquests'])
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

    public function testGetHistory()
    {
        $conquest = new \dal\models\ConquestModel();
        $conquest->id = 1;
        $this->conquestRepositoryMock->expects($this->exactly(3))
                ->method('GetConquests')
                ->willReturn([$conquest]);
        
        $this->zoneRepositoryMock->expects($this->exactly(3))
                ->method('GetAllZonesByConquest')
                ->with($conquest)
                ->willReturn([]);
        
        $this->nodeRepositoryMock->expects($this->exactly(3))
                ->method('GetAllNodesByConquest')
                ->with($conquest)
                ->willReturn([]);
        
        $this->strikeRepositoryMock->expects($this->exactly(3))
                ->method('GetStrikesByConquest')
                ->with($conquest)
                ->willReturn([]);
        $this->manager->GetHistory(new DateTime('07/28/2017'), new DateTime('08/17/2017'));
    }
}

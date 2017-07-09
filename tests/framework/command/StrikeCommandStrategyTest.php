<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\StrikeCommandStrategy;

/**
 * Description of StrikeCommandStrategyTest
 *
 * @author chris
 */
class StrikeCommandStrategyTest extends TestCaseBase
{
    /** @var StrikeCommandStrategy */
    private $command;
    private $conquestRepositoryMock;
    private $zoneRepositoryMock;
    private $nodeRepositoryMock;
    private $strikeRepositoryMock;
    private $slackApiMock;
    private $statusCommandStrategyMock;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->conquestRepositoryMock = $this->getMockBuilder(\dal\managers\ConquestRepository::class)
                ->setMethods(['GetCurrentConquest'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->zoneRepositoryMock = $this->getMockBuilder(\dal\managers\ZoneRepository::class)
                ->setMethods(['GetZone', 'CreateZone'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->nodeRepositoryMock = $this->getMockBuilder(\dal\managers\NodeRepository::class)
                ->setMethods(['CreateNode'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->strikeRepositoryMock = $this->getMockBuilder(\dal\managers\StrikeRepository::class)
                ->setMethods(['CreateStrike'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();
        $this->statusCommandStrategyMock = $this->getMockBuilder(\framework\command\StatusCommandStrategy::class)
                ->setMethods(['Process', 'SendResponse'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->command = new StrikeCommandStrategy($this->conquestRepositoryMock, $this->zoneRepositoryMock, $this->nodeRepositoryMock, $this->strikeRepositoryMock, $this->slackApiMock, $this->statusCommandStrategyMock);
    }

    public function testZoneSetupSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $conquest->id = 1;
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);
        $zone = new \dal\models\ZoneModel();
        $zone->conquest = $conquest;
        $zone->zone = 9;
        $this->zoneRepositoryMock->method('GetZone')
                ->will($this->onConsecutiveCalls(null, $zone));
        $this->nodeRepositoryMock->expects($this->exactly(10))
                ->method('CreateNode');
        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($this->equalTo('Strike map has been setup for zone ' . $zone->zone));
        $this->statusCommandStrategyMock->expects($this->once())
                ->method('SendResponse');
        
        $payload = array(
            'channel' => 'ADFAS',
            'text' => 'setup zone ' . $zone->zone,
        );
        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    public function testZoneSetupFailure()
    {
        $conquest = new \dal\models\ConquestModel();
        $conquest->id = 1;
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);
        $zone = new \dal\models\ZoneModel();
        $zone->conquest = $conquest;
        $zone->zone = 9;
        $zone->is_owned = false;
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);
        $this->nodeRepositoryMock->expects($this->never())
                ->method('CreateNode');
        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($this->equalTo('Zone *' . $zone->zone . "* has not yet been completed/removed.  Please mark it as done or lost before trying again.\n" .
                                "Hint: zone # (done|lost)"));

        $payload = array(
            'channel' => 'ADFAS',
            'text' => 'setup zone ' . $zone->zone,
        );
        $this->command->Process($payload);
        $this->command->SendResponse();
    }

}

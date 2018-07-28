<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\StrikeCommandStrategy;
use framework\conquest\ConquestManager;
use dal\enums\StateEnum;

/**
 * Description of StrikeCommandStrategyTest
 *
 * @author chris
 */
class StrikeCommandStrategyTest extends TestCaseBase
{
    /** @var StrikeCommandStrategy */
    private $command;
    private $coreRepositoryMock;
    private $conquestRepositoryMock;
    private $zoneRepositoryMock;
    private $nodeRepositoryMock;
    private $strikeRepositoryMock;
    private $consensusRepositoryMock;
    private $slackApiMock;
    private $statusCommandStrategyMock;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->coreRepositoryMock = $this->getMockBuilder(\dal\repositories\CoreRepository::class)
                ->setMethods(['GetState'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->conquestRepositoryMock = $this->getMockBuilder(\dal\repositories\ConquestRepository::class)
                ->setMethods(['GetCurrentConquest'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->zoneRepositoryMock = $this->getMockBuilder(\dal\repositories\ZoneRepository::class)
                ->setMethods(['GetZone', 'CreateZone'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->nodeRepositoryMock = $this->getMockBuilder(\dal\repositories\NodeRepository::class)
                ->setMethods(['CreateNode'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->strikeRepositoryMock = $this->getMockBuilder(\dal\repositories\StrikeRepository::class)
                ->setMethods(['CreateStrike'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->consensusRepositoryMock = $this->getMockBuilder(\dal\repositories\ConsensusRepository::class)
                ->setMethods(['CreateStrike'])
                ->setConstructorArgs([$adapter])
                ->getMock();

        $conquestManager = new ConquestManager($this->conquestRepositoryMock, $this->zoneRepositoryMock, $this->nodeRepositoryMock, $this->strikeRepositoryMock, $this->consensusRepositoryMock);
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();
        $this->statusCommandStrategyMock = $this->getMockBuilder(\framework\command\StatusCommandStrategy::class)
                ->setMethods(['Process', 'SendResponse'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->command = new StrikeCommandStrategy($this->coreRepositoryMock, $conquestManager, $this->slackApiMock, $this->statusCommandStrategyMock);
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

        $coreState = new \dal\models\CoreModel();
        $coreState->state = StateEnum::Coordinating;
        $this->coreRepositoryMock->expects($this->once())
                ->method('GetState')
                ->willReturn($coreState);

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
        $coreState = new \dal\models\CoreModel();
        $coreState->state = StateEnum::Coordinating;
        $this->coreRepositoryMock->expects($this->once())
                ->method('GetState')
                ->willReturn($coreState);
        $payload = array(
            'channel' => 'ADFAS',
            'text' => 'setup zone ' . $zone->zone,
        );
        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    public function testTrainingZoneSetupSuccess()
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
                ->with($this->equalTo('Training zone ' . $zone->zone . ' has been setup'));
        $this->statusCommandStrategyMock->expects($this->once())
                ->method('SendResponse');

        $coreState = new \dal\models\CoreModel();
        $coreState->state = StateEnum::Training;
        $this->coreRepositoryMock->expects($this->once())
                ->method('GetState')
                ->willReturn($coreState);

        $payload = array(
            'channel' => 'ADFAS',
            'text' => 'setup zone ' . $zone->zone,
        );
        $this->command->Process($payload);
        $this->command->SendResponse();
    }
}

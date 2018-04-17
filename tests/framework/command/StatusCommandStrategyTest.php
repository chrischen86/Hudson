<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\StatusCommandStrategy;

/**
 * Description of StatusCommandStrategyTest
 *
 * @author chris
 */
class StatusCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $coreRepositoryMock;
    private $conquestRepositoryMock;
    private $zoneRepositoryMock;
    private $strikeRepositoryMock;
    private $slackApiMock;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->coreRepositoryMock = $this->getMockBuilder(\dal\managers\CoreRepository::class)
                ->setMethods(['GetMessageChannel', 'SetMessageProperties'])
                ->setConstructorArgs([$adapter])
                ->getMock();
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
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();
        $this->command = new StatusCommandStrategy($this->coreRepositoryMock,
                $this->conquestRepositoryMock, $this->zoneRepositoryMock,
                $this->strikeRepositoryMock, $this->slackApiMock);
    }

    public function testStatusSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);
        $zone = new \dal\models\ZoneModel();
        $zone->conquest = $conquest;
        $zone->zone = 1;
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetAllZones')
                ->willReturn([$zone]);
        $node = new \dal\models\NodeModel();
        $node->zone = $zone;
        $node->node = 10;
        $user = new \dal\models\UserModel();
        $user->name = 'Test User';
        $strike = $this->CreateStrike($node, $user);
        $this->strikeRepositoryMock->expects($this->exactly(1))
                ->method('GetStrikesByZone')
                ->willReturn([$strike]);
        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($this->equalTo('Here are the active zones I am tracking:'))
                ->willReturn((object)array('body' => (object)array('ts' => 1, 'channel' => 'test')));
        $coreState = new \dal\models\CoreModel();
        $coreState->state = \StateEnum::Coordinating;
        
        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'status',
        );
        $this->command->Process($payload);

        $this->coreRepositoryMock->expects($this->once())
                ->method('GetMessageChannel')
                ->willReturn('TESTCHANNEL');
        $this->coreRepositoryMock->expects($this->once())
                ->method('SetMessageProperties')
                ->with(1, 'test');
        $this->command->SendResponse();
    }

    private function CreateStrike($node, $user)
    {
        $strike = new \dal\models\StrikeModel();
        $strike->node = $node;
        $strike->user = $user;
        return $strike;
    }

}

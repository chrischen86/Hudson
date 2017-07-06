<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\ClearCommandStrategy;

/**
 * Description of ClearCommandStrategyTest
 *
 * @author chris
 */
class ClearCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $conquestRepositoryMock;
    private $zoneRepositoryMock;
    private $nodeRepositoryMock;
    private $strikeRepositoryMock;
    private $slackApiMock;
    private $statusCommandMock;

    protected function setUp()
    {
        $adapter = new \dal\NullDataAccessAdapter();
        $this->conquestRepositoryMock = $this->getMockBuilder(\dal\managers\ConquestRepository::class)
                ->setMethods(['GetCurrentConquest'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->zoneRepositoryMock = $this->getMockBuilder(\dal\managers\ZoneRepository::class)
                ->setMethods(['GetZone'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->nodeRepositoryMock = $this->getMockBuilder(\dal\managers\NodeRepository::class)
                ->setMethods(['GetNode', 'UpdateNode'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->strikeRepositoryMock = $this->getMockBuilder(\dal\managers\StrikeRepository::class)
                ->setMethods(['GetStrike', 'ClearStrike'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();
        $this->statusCommandMock = $this->getMockBuilder(\framework\command\StatusCommandStrategy::class)
                ->setMethods(['Process', 'SendResponse'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->command = new ClearCommandStrategy($this->conquestRepositoryMock,
                $this->zoneRepositoryMock, $this->nodeRepositoryMock,
                $this->strikeRepositoryMock, $this->slackApiMock,
                $this->statusCommandMock);
    }

    public function testClearStrikeSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);

        $zone = new \dal\models\ZoneModel();
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);

        $node = new \dal\models\NodeModel();
        $this->nodeRepositoryMock->expects($this->once())
                ->method('GetNode')
                ->willReturn($node);
        
        $strike = new \dal\models\StrikeModel();
        $this->strikeRepositoryMock->expects($this->once())
                ->method('GetStrike')
                ->willReturn($strike);

        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'clear 1.2',
            'user' => 'TEST USER',
        );

        $this->strikeRepositoryMock->expects($this->once())
                ->method('ClearStrike');
        $this->statusCommandMock->expects($this->once())
                ->method('Process');

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

}

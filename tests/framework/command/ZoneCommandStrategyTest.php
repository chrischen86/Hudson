<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\ZoneCommandStrategy;

/**
 * Description of ZoneCommandStrategyTest
 *
 * @author chris
 */
class ZoneCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $conquestRepositoryMock;
    private $zoneRepositoryMock;
    private $nodeRepositoryMock;
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
                ->setMethods(['GetZone', 'UpdateZone'])
                ->setConstructorArgs([$adapter])
                ->getMock();
        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();
        $this->statusCommandMock = $this->getMockBuilder(\framework\command\StatusCommandStrategy::class)
                ->setMethods(['Process', 'SendResponse'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->command = new ZoneCommandStrategy($this->conquestRepositoryMock,
                $this->zoneRepositoryMock, $this->slackApiMock,
                $this->statusCommandMock);
    }

    public function testZoneClearSuccess()
    {
        $conquest = new \dal\models\ConquestModel();
        $this->conquestRepositoryMock->expects($this->once())
                ->method('GetCurrentConquest')
                ->willReturn($conquest);

        $zone = new \dal\models\ZoneModel();
        $this->zoneRepositoryMock->expects($this->once())
                ->method('GetZone')
                ->willReturn($zone);

        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'zone 1 is ours',
            'user' => 'TEST USER',
        );

        $this->zoneRepositoryMock->expects($this->once())
                ->method('UpdateZone');
        $this->statusCommandMock->expects($this->once())
                ->method('Process');

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

}

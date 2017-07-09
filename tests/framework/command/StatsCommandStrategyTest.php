<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\StatsCommandStrategy;

/**
 * Description of StatsCommandStrategyTest
 *
 * @author chris
 */
class StatsCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $conquestManagerMock;
    private $slackApiMock;

    protected function setUp()
    {
        $this->conquestManagerMock = $this->getMockBuilder(\framework\conquest\ConquestManager::class)
                ->setMethods(['GetLastPhaseStats'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();

        $this->command = new StatsCommandStrategy($this->conquestManagerMock,
                $this->slackApiMock);
    }

    public function testGetLastPhaseStatsSuccess()
    {
        $stats = new \framework\conquest\StatsDto();
        
        $conquest = new \dal\models\ConquestModel();
        $conquest->date = new \DateTime();
        $stats->conquests = [$conquest];
        
        $zone = new \dal\models\ZoneModel();
        $stats->zones = [$zone];
        
        $strike = new \dal\models\StrikeModel();
        $stats->strikes = [$strike];
        
        $this->conquestManagerMock->expects($this->once())
                ->method('GetLastPhaseStats')
                ->willReturn($stats);
        
        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'stats',
            'user' => 'TEST USER',
        );

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

}

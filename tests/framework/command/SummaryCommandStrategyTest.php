<?php

namespace tests\framework\command;
use tests\TestCaseBase;

use framework\command\SummaryCommandStrategy;
/**
 * Description of SummaryCommandStrategyTest
 *
 * @author chris
 */
class SummaryCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $conquestManagerMock;
    private $slackApiMock;

    protected function setUp()
    {
        $this->conquestManagerMock = $this->getMockBuilder(\framework\conquest\ConquestManager::class)
                ->setMethods(['GetSummaryStats'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();

        $this->command = new SummaryCommandStrategy($this->conquestManagerMock,
                $this->slackApiMock);
    }

    public function testGetSummaryStatsSuccess()
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
                ->method('GetSummaryStats')
                ->willReturn($stats);
        
        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'summary',
            'user' => 'TEST USER',
        );

        $this->command->Process($payload);
        $this->command->SendResponse();
    }
    
    
    public function testGetSummaryStatsForceSuccess()
    {
        $stats = new \framework\conquest\StatsDto();
        $stats->forDate = new \DateTime();
        $stats->endDate = new \DateTime();
        $conquest = new \dal\models\ConquestModel();
        $conquest->date = new \DateTime();
        $stats->conquests = [$conquest];
        
        $zone = new \dal\models\ZoneModel();
        $stats->zones = [$zone];
        
        $strike = new \dal\models\StrikeModel();
        $stats->strikes = [$strike];
        
        $this->conquestManagerMock->expects($this->once())
                ->method('GetSummaryStats')
                ->willReturn($stats);
        
        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'summary !force',
            'user' => 'U0KJBUYDC',
        );

        $this->command->Process($payload);
        $this->command->SendResponse();
    }    
}

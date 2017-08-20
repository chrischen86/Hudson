<?php

namespace tests\framework\command;

use tests\TestCaseBase;
use framework\command\SummaryHistoryCommandStrategy;
use DateTime;

/**
 * Description of SummaryCommandStrategyTest
 *
 * @author chris
 */
class SummaryHistoryCommandStrategyTest extends TestCaseBase
{
    private $command;
    private $conquestManagerMock;
    private $imageChartApiMock;
    private $slackApiMock;

    protected function setUp()
    {
        $this->conquestManagerMock = $this->getMockBuilder(\framework\conquest\ConquestManager::class)
                ->setMethods(['GetHistory'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->imageChartApiMock = $this->getMockBuilder(\framework\google\ImageChartApi::class)
                ->setMethods(['CreateLineChart'])
                ->getMock();

        $this->slackApiMock = $this->getMockBuilder(\framework\slack\SlackApi::class)
                ->setMethods(['SendMessage'])
                ->getMock();

        $this->command = new SummaryHistoryCommandStrategy($this->conquestManagerMock, $this->imageChartApiMock, $this->slackApiMock);
    }

    public function testGetSummaryHistoryStatsSuccess()
    {
        $stats = new \framework\conquest\StatsDto();

        $conquest = new \dal\models\ConquestModel();
        $conquest->date = new \DateTime();
        $stats->conquests = [$conquest];

        $zone = new \dal\models\ZoneModel();
        $stats->zones = [$zone];

        $strike = new \dal\models\StrikeModel();
        $stats->strikes = [$strike];
        
        $stats->forDate = new DateTime();

        $this->conquestManagerMock->expects($this->once())
                ->method('GetHistory')
                ->willReturn([$stats]);

        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'summary since 2017/08/04',
            'user' => 'TEST USER',
        );

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    public function testGetSummaryHistoryStatsBetweenSuccess()
    {
        $stats = new \framework\conquest\StatsDto();

        $conquest = new \dal\models\ConquestModel();
        $conquest->date = new \DateTime();
        $stats->conquests = [$conquest];

        $zone = new \dal\models\ZoneModel();
        $stats->zones = [$zone];

        $strike = new \dal\models\StrikeModel();
        $stats->strikes = [$strike];
        
        $stats->forDate = new DateTime();

        $this->conquestManagerMock->expects($this->once())
                ->method('GetHistory')
                ->willReturn([$stats]);

        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'summary between 2017/08/04 and 2017/08/20',
            'user' => 'TEST USER',
        );

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

}

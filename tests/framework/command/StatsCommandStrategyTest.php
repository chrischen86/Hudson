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

        $this->command = new StatsCommandStrategy($this->conquestManagerMock, $this->slackApiMock);
    }
    
    public function testAchivementsSuccess()
    {
        $stats = new \framework\conquest\StatsDto();

        $conquest = new \dal\models\ConquestModel();
        $conquest->date = new \DateTime();
        $conquest->phase = 1;
        $stats->conquests = [$conquest];

        $zone = new \dal\models\ZoneModel();
        $zone->battle_count = 1;
        $zone->zone = 5;
        $stats->zones = [$zone];

        
        $user = $this->CreateUser('TESTUSER', 'ASDF');
        $user2 = $this->CreateUser('TESTUSER2', 'ASDF2');
        $user3 = $this->CreateUSER('TESTUSER3', 'ASDF3');
        $user4 = $this->CreateUser('TESTUSER4', 'ASDF4');
        
        $strike = $this->CreateStrike($user);        
        $strike2 = $this->CreateStrike($user2);
        $strike3 = $this->CreateStrike($user3);
        $stats->strikes = [$strike, $strike, $strike, $strike2, $strike2, $strike3];

        $this->conquestManagerMock->expects($this->once())
                ->method('GetLastPhaseStats')
                ->willReturn($stats);

        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'stats',
            'user' => 'TEST USER',
        );

        $dateString = 'Here is the summary for the conquest on *' . $conquest->date->format('Y-m-d') . '* phase *' . $conquest->phase . '*: ';

        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($dateString, [
                    array(
                        'color' => '#FDC528',
                        'text' => '',
                        'fields' => [array(
                            'title' => 'Zones',
                            'value' => "I have tracked a total of *1* unique zones.\nThe most highly contested region(s) include zones *5* that were fought over for a total of *1* time(s)!"
                        )],
                        'mrkdwn_in' => ['fields']
                    ),
                    array(
                        'color' => '#FDC528',
                        'text' => '',
                        'fields' => [array(
                            'title' => 'Members Summary',
                            'value' => "A total of *3* members have participated in this phase!\n<@TESTUSER>, <@TESTUSER2>, <@TESTUSER3>\n\nWe could not have done it without you!"
                        )],
                        'mrkdwn_in' => ['fields']
                    ),
                    array(
                        'color' => '#FDC528',
                        'text' => '',
                        'fields' => [array(
                            'title' => 'Achievements',
                            'value' => "<@TESTUSER>: 3 hits!  Smashing!\n<@TESTUSER2>: 2 hits!  Amazing!\n<@TESTUSER3>: 1 hits!  Spectacular!\n"
                        )],
                        'mrkdwn_in' => ['fields']
                    )
        ]);

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    public function testGetLastPhaseStatsSuccess()
    {
        $stats = new \framework\conquest\StatsDto();

        $conquest = new \dal\models\ConquestModel();
        $conquest->date = new \DateTime();
        $conquest->phase = 1;
        $stats->conquests = [$conquest];

        $zone = new \dal\models\ZoneModel();
        $zone->battle_count = 1;
        $zone->zone = 5;
        $stats->zones = [$zone];

        $strike = new \dal\models\StrikeModel();
        $user = new \dal\models\UserModel();
        $user->name = 'TESTUSER';
        $user->id = 'ASDF';
        $strike->user = $user;
        $strike->user_id = $user->id;
        $stats->strikes = [$strike];

        $this->conquestManagerMock->expects($this->once())
                ->method('GetLastPhaseStats')
                ->willReturn($stats);

        $payload = array(
            'channel' => 'TESTCHANNEL',
            'text' => 'stats',
            'user' => 'TEST USER',
        );

        $dateString = 'Here is the summary for the conquest on *' . $conquest->date->format('Y-m-d') . '* phase *' . $conquest->phase . '*: ';

        $this->slackApiMock->expects($this->once())
                ->method('SendMessage')
                ->with($dateString, [
                    array(
                        'color' => '#FDC528',
                        'text' => '',
                        'fields' => [array(
                            'title' => 'Zones',
                            'value' => "I have tracked a total of *1* unique zones.\nThe most highly contested region(s) include zones *5* that were fought over for a total of *1* time(s)!"
                        )],
                        'mrkdwn_in' => ['fields']
                    ),
                    array(
                        'color' => '#FDC528',
                        'text' => '',
                        'fields' => [array(
                            'title' => 'Members Summary',
                            'value' => "A total of *1* members have participated in this phase!\n<@TESTUSER>\n\nWe could not have done it without you!"
                        )],
                        'mrkdwn_in' => ['fields']
                    ),
                    array(
                        'color' => '#FDC528',
                        'text' => '',
                        'fields' => [array(
                            'title' => 'Achievements',
                            'value' => "<@TESTUSER>: 1 hits!  Smashing!\n"
                        )],
                        'mrkdwn_in' => ['fields']
                    )
        ]);

        $this->command->Process($payload);
        $this->command->SendResponse();
    }

    private function CreateStrike($user)
    {
        $strike = new \dal\models\StrikeModel();
        $strike->user = $user;
        $strike->user_id = $user->id;
        return $strike;
    }
}

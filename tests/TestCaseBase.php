<?php

namespace tests;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../AutoloadBootstrapper.php';

use DI;
use PHPUnit\Framework\TestCase;
use Config;
use DI\ContainerBuilder;
use framework\command\CommandStrategyFactory;

/**
 * Description of TestCaseBase
 *
 * @author chris
 */
class TestCaseBase extends TestCase
{
    protected $container;

    public function __construct($name = null, array $data = array(),
                                $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->container = $this->BuildContainer();
    }

    private function CreateMockAdapter()
    {
        $adapter = $this->getMockBuilder(\dal\DataAccessAdapter::class)
                ->setMethods(['query', 'query_single'])
                ->disableOriginalConstructor()
                ->getMock();
        return $adapter;
    }

    protected function BuildContainer()
    {
        $container = new ContainerBuilder();

        $container->addDefinitions([
            'ConquestChannel' => Config::$ConquestChannel,
            'IDataAccessAdapter' => $this->CreateMockAdapter(),
            'CoreRepository' => DI\object('dal\managers\CoreRepository')
                    ->constructor(DI\get('IDataAccessAdapter')),
            'ConquestRepository' => DI\object('dal\managers\ConquestRepository')
                    ->constructor(DI\get('IDataAccessAdapter')),
            'ZoneRepository' => DI\object('dal\managers\ZoneRepository')
                    ->constructor(DI\get('IDataAccessAdapter')),
            'NodeRepository' => DI\object('dal\managers\NodeRepository')
                    ->constructor(DI\get('IDataAccessAdapter')),
            'StrikeRepository' => DI\object('dal\managers\StrikeRepository')
                    ->constructor(DI\get('IDataAccessAdapter')),
            'UserRepository' => DI\object('dal\managers\UserRepository')
                    ->constructor(DI\get('IDataAccessAdapter')),
            'ConsensusRepository' => DI\object('dal\managers\ConsensusRepository')
                    ->constructor(DI\get('IDataAccessAdapter')),
            'ISlackApi' => DI\object('framework\slack\NullSlackApi'),
            'ImageChartApi' => DI\object('framework\google\ImageChartApi'),
            'StatusCommandStrategy' => DI\object('framework\command\StatusCommandStrategy')
                    ->constructor(DI\get('CoreRepository'), DI\get('ConquestRepository'), DI\get('ZoneRepository'), DI\get('StrikeRepository'), DI\get('ISlackApi')),
            'ConquestManager' => DI\object('framework\conquest\ConquestManager')
                    ->constructor(DI\get('ConquestRepository'), DI\get('ZoneRepository'), DI\get('NodeRepository'), DI\get('StrikeRepository'), DI\get('ConsensusRepository')),
            'SlackFileManager' => DI\object('framework\system\SlackFileManager')
                    ->constructor(DI\get('ISlackApi')),
            'framework\command\ICommandStrategy' => [
                        DI\object('framework\command\InitCommandStrategy')
                        ->constructor(DI\get('CoreRepository'), DI\get('ISlackApi')),
                        DI\object('framework\command\StrikeCommandStrategy')
                        ->constructor(DI\get('CoreRepository'), DI\get('ConquestManager'), DI\get('ISlackApi'), DI\get('StatusCommandStrategy')),
                DI\get('StatusCommandStrategy'),
                        DI\object('framework\command\NodeCallCommandStrategy')
                        ->constructor(DI\get('ConquestRepository'), DI\get('ZoneRepository'), DI\get('NodeRepository'), DI\get('StrikeRepository'), DI\get('UserRepository'), DI\get('ISlackApi'), DI\get('StatusCommandStrategy')),
                        DI\object('framework\command\HoldCommandStrategy')
                        ->constructor(DI\get('ConquestRepository'), DI\get('ZoneRepository'), DI\get('NodeRepository'), DI\get('ISlackApi'), DI\get('StatusCommandStrategy')),
                        DI\object('framework\command\ZoneCommandStrategy')
                        ->constructor(DI\get('ConquestRepository'), DI\get('ZoneRepository'), DI\get('ISlackApi'), DI\get('StatusCommandStrategy')),
                        DI\object('framework\command\ClearCommandStrategy')
                        ->constructor(DI\get('ConquestRepository'), DI\get('ZoneRepository'), DI\get('NodeRepository'), DI\get('StrikeRepository'), DI\get('ISlackApi'), DI\get('StatusCommandStrategy')),
                        DI\object('framework\command\CancelCommandStrategy')
                        ->constructor(DI\get('ConquestRepository'), DI\get('ZoneRepository'), DI\get('ISlackApi'), DI\get('StatusCommandStrategy')),
                        DI\object('framework\command\StatsCommandStrategy')
                        ->constructor(DI\get('ConquestManager'), DI\get('ISlackApi')),
                        DI\object('framework\command\SummaryCommandStrategy')
                        ->constructor(DI\get('ConquestManager'), DI\get('ISlackApi')),
                        DI\object('framework\command\LeadCommandStrategy')
                        ->constructor(DI\get('ConquestRepository'), DI\get('UserRepository'), DI\get('ISlackApi'), DI\get('ConquestChannel')),
                        DI\object('framework\command\TrainingModeCommandStrategy')
                        ->constructor(DI\get('CoreRepository'), DI\get('ISlackApi')),
                        DI\object('framework\command\ConquestModeCommandStrategy')
                        ->constructor(DI\get('CoreRepository'), DI\get('ISlackApi')),
                        DI\object('framework\command\SummaryHistoryCommandStrategy')
                        ->constructor(DI\get('ConquestManager'), DI\get('ImageChartApi'), DI\get('ISlackApi')),
                        DI\object('framework\command\ArchiveUserCommandStrategy')
                        ->constructor(DI\get('UserRepository'), DI\get('ISlackApi')),
                        DI\object('framework\command\UserListCommandStrategy')
                        ->constructor(DI\get('UserRepository'), DI\get('ISlackApi')),
                        DI\object('framework\system\FileListCommandStrategy')
                        ->constructor(DI\get('SlackFileManager'), DI\get('ISlackApi')),
                        DI\object('framework\system\DeleteFileCommandStrategy')
                        ->constructor(DI\get('SlackFileManager'), DI\get('ISlackApi')),
            ],
            'CommandStrategyFactory' => DI\factory(function($strategies)
            {
                return new CommandStrategyFactory($strategies);
            })->parameter('strategies', DI\get('framework\command\ICommandStrategy')),
            'ReactionProcessor' => DI\object('framework\ReactionProcessor')->constructor(DI\get('ConquestManager')),
        ]);

        return $container->build();
    }

    protected function BuildJarvisMessage($text)
    {
        $user = Config::$BotId;
        $message = '{
                        "type": "message",
                        "subtype": null,
                        "user": "U0KJBUYDC",
                        "text": "<@' . $user . '> ' . $text . '",
                        "channel": "C350WUH9R",
                        "event_ts": 1497763686.985701
                    }';

        return json_decode($message, 1);
    }

    protected function BuildMessage($text)
    {
        $message = '{
                        "type": "message",
                        "subtype": null,
                        "user": "U0KJBUYDC",
                        "text": "' . $text . '",
                        "channel": "C350WUH9R",
                        "event_ts": 1497763686.985701
                    }';

        return json_decode($message, 1);
    }

    protected function CreateUser($name, $id = '', $vip = '')
    {
        $user = new \dal\models\UserModel();
        $user->name = $name;
        $user->id = $id;
        $user->vip = $vip;
        return $user;
    }

}

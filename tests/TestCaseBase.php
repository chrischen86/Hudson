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
            'DataAccessAdapter' => $this->CreateMockAdapter(),
            'CoreRepository' => DI\object('dal\managers\CoreRepository')
                    ->constructor(DI\get('DataAccessAdapter')),
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
            'ISlackApi' => DI\object('framework\slack\NullSlackApi'),
            'framework\command\ICommandStrategy' => [
                DI\object('framework\command\ClearCommandStrategy'),
                        DI\object('framework\command\InitCommandStrategy')
                        ->constructor(DI\get('CoreRepository'), DI\get('ISlackApi')),
            ],
            'CommandStrategyFactory' => DI\factory(function($strategies)
            {
                return new CommandStrategyFactory($strategies);
            })->parameter('strategies', DI\get('framework\command\ICommandStrategy')),
        ]);

        return $container->build();
    }

    protected function BuildJarvisMessage($text)
    {
        $user = Config::$BotId;
        $message = '{
                        "event": {
                                "type": "message",
                                "subtype": null,
                                "user": "U0KJBUYDC",
                                "text": "<@' . $user . '> ' . $text . '",
                                "channel": "C350WUH9R",
                                "event_ts": 1497763686.985701
                        }
                    }';

        return $message;
    }

}

<?php

namespace app;

use DI;
use framework\command\CommandStrategyFactory;

return [
    'IDataAccessAdapter' => DI\object('dal\DataAccessAdapter'),
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
    'ISlackApi' => DI\object('framework\slack\SlackApi'),
    'framework\command\ICommandStrategy' => [
        DI\object('framework\command\ClearCommandStrategy'),
                DI\object('framework\command\InitCommandStrategy')
                ->constructor(DI\get('CoreRepository'), DI\get('ISlackApi')),
    ],
    'CommandStrategyFactory' => DI\factory(function($strategies)
    {
        return new CommandStrategyFactory($strategies);
    })->parameter('strategies', DI\get('framework\command\ICommandStrategy')),
];

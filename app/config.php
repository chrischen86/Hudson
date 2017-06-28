<?php

namespace app;
use DI;
use framework\command\CommandStrategyFactory;

return [
    'IDataAccessAdapter' => DI\object('dal\DataAccessAdapter'),
    'CoreRepository' => DI\object('dal\managers\CoreRepository')
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
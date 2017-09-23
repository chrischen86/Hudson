<?php

namespace tests\framework;

require_once __DIR__ . '/../TestCaseBase.php';

use tests\TestCaseBase;
use framework\CommandStrategyFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of CommandProcessorFactoryTest
 *
 * @author chris`
 */
class CommandProcessorFactoryTest extends TestCaseBase
{
    /** @var CommandStrategyFactory */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->container->get('CommandStrategyFactory');
    }

    public function testNoMatchingStrategy()
    {
        $message = $this->BuildJarvisMessage('asdfsdafas');
        
        print_r($message);
        
        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertNull($strategy);
    }

    public function testCreateClearStrategy()
    {
        $message = $this->BuildJarvisMessage('clear 2.9');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\ClearCommandStrategy::class, $strategy);
    }

    public function testCreateInitiateStrategy()
    {
        $message = $this->BuildJarvisMessage('init ASC');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\InitCommandStrategy::class, $strategy);
    }

    public function testCreateStrikeStrategy()
    {
        $message = $this->BuildJarvisMessage('setup zone 9');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\StrikeCommandStrategy::class, $strategy);
    }

    public function testRequireJarvisForStrategy()
    {
        $message = $this->BuildMessage('setup zone 9');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertNull($strategy);
    }

    public function testCreateStatusStrategy()
    {
        $message = $this->BuildJarvisMessage('status');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\StatusCommandStrategy::class, $strategy);
    }

    public function testCreateNodeCallStrategy()
    {
        $message = $this->BuildMessage('1.1');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\NodeCallCommandStrategy::class, $strategy);
    }

    public function testCreateHoldCommandStrategy()
    {
        $message = $this->BuildJarvisMessage('hold 1.5');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\HoldCommandStrategy::class, $strategy);
    }

    public function testCreateZoneCommandStrategy()
    {
        $message = $this->BuildMessage('zone 1 done');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\ZoneCommandStrategy::class, $strategy);
    }

    public function testCreateClearCommandStrategy()
    {
        $message = $this->BuildJarvisMessage('clear 2.3');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\ClearCommandStrategy::class, $strategy);
    }

    public function testCreateCancelCommandStrategy()
    {
        $message = $this->BuildJarvisMessage('cancel zone 1');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\CancelCommandStrategy::class, $strategy);
    }

    public function testCreateStatsCommandStrategy()
    {
        $message = $this->BuildJarvisMessage('stats');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\StatsCommandStrategy::class, $strategy);
    }

    public function testCreateSummaryCommandStrategy()
    {
        $message = $this->BuildJarvisMessage('summary');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\SummaryCommandStrategy::class, $strategy);
    }

    public function testCreateLeadCommandStrategy()
    {
        $message = $this->BuildJarvisMessage('lead');

        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\LeadCommandStrategy::class, $strategy);
    }

    public function testCreateSummaryHistoryCommandStrategy()
    {
        $message = $this->BuildJarvisMessage('summary since 2017/08/20');
        
        $strategy = $this->factory->GetCommandStrategy($message);
        $this->assertInstanceOf(\framework\command\SummaryHistoryCommandStrategy::class, $strategy);
    }

}

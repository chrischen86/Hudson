<?php

namespace tests\dal;

require_once __DIR__ . '/../TestCaseBase.php';

use tests\TestCaseBase;
use dal\SqlFilter;
use dal\SqlParam;
use dal\SqlOperator;

/**
 * Description of FilterTest
 *
 * @author chris
 */
class FilterTest extends TestCaseBase
{
    public function testSimpleSqlStringFilter()
    {
        $filter = new SqlFilter();

        $param = new SqlParam("property1", 1, SqlParam::$TEXT);
        $filter->addParam($param);

        $actual = $filter->toWhereClause();
        $expected = "(property1 = '1')";
        $this->assertEquals($expected, $actual);
    }

    public function testSimpleSqlNumberFilter()
    {
        $filter = new SqlFilter();

        $param = new SqlParam("property1", 1, SqlParam::$NUMBER);
        $filter->addParam($param);

        $actual = $filter->toWhereClause();
        $expected = "(property1 = 1)";
        $this->assertEquals($expected, $actual);
    }

    public function testCombinedSqlStringFilter()
    {
        $filter = new SqlFilter();

        $param = new SqlParam("property1", 1, SqlParam::$TEXT);
        $filter->addParam($param);
        $param2 = new SqlParam("property2", 2, SqlParam::$NUMBER);
        $filter->addParam($param2, SqlOperator::$OR);

        $actual = $filter->toWhereClause();
        $expected = "(property1 = '1' OR property2 = 2)";
        $this->assertEquals($expected, $actual);
    }

    public function testNestedSqlStringFilter()
    {
        $filter = new SqlFilter();

        $param = new SqlParam("property1", 1, SqlParam::$TEXT);
        $filter->addParam($param);
        
        $filter2 = new SqlFilter();
        $filter2->addParam($param);
        $param2 = new SqlParam("property2", 2, SqlParam::$NUMBER);
        $filter2->addParam($param2, SqlOperator::$OR);
        $filter->addParam($filter2);

        $actual = $filter->toWhereClause();
        $expected = "(property1 = '1' AND (property1 = '1' OR property2 = 2))";
        $this->assertEquals($expected, $actual);
    }

}

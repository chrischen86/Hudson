<?php

namespace tests\dal;

require_once __DIR__ . '/../TestCaseBase.php';

use tests\TestCaseBase;
use dal\SqlFilter;
use dal\SqlParam;
use dal\SqlOperator;
use dal\SqlPredicate;
use dal\SqlGroupBy;

/**
 * Description of FilterTest
 *
 * @author chris
 */
class SqlPredicateTest extends TestCaseBase
{
    public function testSimpleSqlPredicate()
    {
        $table = "rifts";
        $predicate = new SqlPredicate($table);
        $filter = new SqlFilter();
        $param = new SqlParam("property1", 1, SqlParam::$TEXT);
        $filter->addParam($param);
        $predicate->setFilter($filter);

        $actual = $predicate->toQuery();
        $expected = "SELECT * FROM rifts WHERE (property1 = '1')";
        $this->assertEquals($expected, $actual);
    }

    public function testSqlPredicateWithGrouping()
    {
        $table = "rifts";
        $predicate = new SqlPredicate($table);
        $filter = new SqlFilter();
        $param = new SqlParam("property1", 1, SqlParam::$TEXT);
        $filter->addParam($param);
        $predicate->setFilter($filter);

        $groupBy = new SqlGroupBy("property2");
        $predicate->setGroupBy($groupBy);
        $predicate->setSelect(["property1", "property2"]);

        $actual = $predicate->toQuery();
        $expected = "SELECT property1, property2, COUNT(property2) AS group_property2 FROM rifts WHERE (property1 = '1') GROUP BY property2";
        $this->assertEquals($expected, $actual);
    }

}

<?php

namespace dal;

use dal\ISqlFilter;

/**
 * Description of SqlClause
 *
 * @author chris
 */
class SqlParam implements ISqlFilter
{
    public static $TEXT = 'text';
    public static $NUMBER = 'number';
    public static $DATE = 'date';
    private $type;
    private $name;
    private $value;

    public function __construct($name, $value, $type = "text")
    {
        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
    }

    public function getProperty()
    {
        return $this->name;
    }

    public function getValue()
    {
        switch ($this->type)
        {
            case SqlParam::$TEXT:
                return sprintf("'%s'", $this->value);
            default:
                return $this->value;
        }
    }

    public function any()
    {
        return true;
    }

    public function toWhereClause()
    {
        return sprintf("%s = %s", $this->getProperty(), $this->getValue());
    }

}

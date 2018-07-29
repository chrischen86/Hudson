<?php

namespace dal;

/**
 * Description of ISqlFilter
 *
 * @author chris
 */
interface ISqlFilter
{
    public function toWhereClause();
    public function any();
}

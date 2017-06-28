<?php

namespace dal;

/**
 *
 * @author chris
 */
interface IDataAccessAdapter
{
    public function query($sql);
    public function query_single($sql);
}

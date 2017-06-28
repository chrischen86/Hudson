<?php
namespace dal;

/**
 * Description of NullDataAccessAdapter
 *
 * @author chris
 */
class NullDataAccessAdapter implements IDataAccessAdapter
{
    public function query($sql)
    {
        return null;
    }
    
    public function query_single($sql)
    {
        return null;
    }
}

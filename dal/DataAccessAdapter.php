<?php

namespace dal;

use Config;

/**
 * Description of DataAccessAdapter
 *
 * @author chris
 */
class DataAccessAdapter implements IDataAccessAdapter
{
    private function getConnection()
    {
        return new \mysqli(Config::$Servername, Config::$Username, Config::$Password, Config::$Dbname);
    }

    public function query($sql)
    {
        $connection = $this->getConnection();
        $result = $connection->query($sql);
        if ($result->num_rows > 0)
        {
            $data = array();
            while ($row = $result->fetch_assoc())
            {
                $data[] = $row;
            }
            $connection->close();
            return $data;
        }
        $id = $connection->insert_id;
        $connection->close();
        return $id;
    }

    public function query_single($sql)
    {
        $connection = $this->getConnection();
        $result = $connection->query($sql);
        if ($result->num_rows > 0)
        {
            $data = array();
            while ($row = $result->fetch_assoc())
            {
                $data[] = $row;
            }
            $connection->close();
            return $data[0];
        }
        $id = $connection->insert_id;
        $connection->close();
        return $id;
    }

}

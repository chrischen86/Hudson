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
        $connection->close();
        return null;
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
        $connection->close();
        return null;
    }

    public function CreateRift()
    {
        $sql = "INSERT INTO test(`username`, `date_created`) VALUES ('test', UTC_TIMESTAMP())";
        $this->conn->query($sql);
    }

}

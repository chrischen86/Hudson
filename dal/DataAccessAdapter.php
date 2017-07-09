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
    private $conn;

    function __construct()
    {
        $this->conn = new \mysqli(Config::$Servername, Config::$Username, Config::$Password, Config::$Dbname);
    }

    public function query($sql)
    {
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0)
        {
            $data = array();
            while ($row = $result->fetch_assoc())
            {
                $data[] = $row;
            }
            return $data;
        }
        return null;
    }

    public function query_single($sql)
    {
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0)
        {
            $data = array();
            while ($row = $result->fetch_assoc())
            {
                $data[] = $row;
            }
            return $data[0];
        }
        return null;
    }

    public function GetRifts()
    {
        $sql = "SELECT * FROM test";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0)
        {
            $data = array();
            while ($row = $result->fetch_assoc())
            {
                $data[] = $row;
            }

            var_dump($data);
            return $data;
        }
        else
        {
            echo "0 results";
            return "OK";
        }
    }

    public function CreateRift()
    {
        $sql = "INSERT INTO test(`username`, `date_created`) VALUES ('test', UTC_TIMESTAMP())";
        $this->conn->query($sql);
    }

    public function UpsertUser($id, $userName, $vip = 0)
    {
        $sql = "INSERT INTO users(`id`, `name`, `vip`) VALUES ('$id', '$userName', $vip)" .
                " ON DUPLICATE KEY UPDATE name='$userName', vip=$vip";
        $result = $this->conn->query($sql);
    }

    public function GetUser($id)
    {
        $sql = "SELECT * from users WHERE id='$id'";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0)
        {
            return $result->fetch_assoc();
        }
        return null;
    }

}

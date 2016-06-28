<?php

namespace Storage\Db;

use Application\Config;

class Mysql
{
    private $config;
    /** @var \mysqli */
    private $dbh;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function connect()
    {
        $this->dbh = new \mysqli(
            $this->config->get(Config::KEY_DB_HOST),
            $this->config->get(Config::KEY_DB_USER),
            $this->config->get(Config::KEY_DB_PASS),
            $this->config->get(Config::KEY_DB_NAME)
        );
    }

    public function query($sql)
    {
        $this->dbh->query($sql);
    }

    public function fetch($sql)
    {
        $data = [];

        $result = $this->dbh->query($sql);
        while ($obj = $result->fetch_object()) {
            $data[] = $obj;
        }
        $result->close();

        return $data;
    }

    public function fetchOne($sql)
    {
        $data = $this->fetch($sql);
        return isset($data[0]) ? $data[0] : null;
    }

    public function close()
    {
        $this->dbh->close();
    }
}

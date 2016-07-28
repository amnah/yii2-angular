<?php
namespace Helper;

use Codeception\Module;
use Codeception\Configuration;
use Codeception\Exception\ModuleException;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class FastDb extends Module
{
    /**
     * Application config file must be set.
     * @var array
     */
    protected $config = [
        'dump' => null,
    ];

    /**
     * @inheritdoc
     */
    public function _initialize()
    {
        // compute datbase info
        $match = preg_match("/host=(.*);dbname=(.*)/", env("DB_DSN"), $matches);
        if (!$match) {
            return;
        }
        $host = $matches[1];
        $name = $matches[2] . "_test";
        $user = env("DB_USER");
        $pass = env("DB_PASS");

        // compute dump file
        $dumpFile = $this->config['dump'] ?: "tests/_data/dump.sql";
        $dumpFile = Configuration::projectDir() . $dumpFile;
        if (!file_exists($dumpFile)) {
            throw new ModuleException(__CLASS__, "Dump file does not exist [ $dumpFile ]");
        }

        // dump
        $cmd = "mysql -h $host -u $user -p$pass $name < $dumpFile";
        $start = microtime(true);
        $output = shell_exec($cmd);
        $end = microtime(true);
        $diff = round(($end - $start) * 1000, 2);

        // output debug info
        $className = get_called_class();
        codecept_debug("$className - Importing db [ $name ] [ $diff ms ]");

        // check for error
        if ($output) {
            throw new ModuleException(__CLASS__, "Failed to import db [ $cmd ]");
        }
    }
}

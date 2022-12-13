<?php

namespace System\Librairies
{

    use PDO;

    /**
     * Class Database
     * Database connection
     * @package Core\Ext
     */
    class Database
    {

        private static ?Database $_instance = null;
        private ?PDO $db = null;

        /**
         * Database constructor.
         * @param array $conf settings of database connection
         */
        private function __construct(array $conf)
        {
            if (is_array($conf))
            {
                $this->db = new PDO($conf['ConnectionString'], $conf['User'], $conf['Password']);
            }
        }

        /**
         * @return Database instance of database with default settings
         */
        public static function getInstance(): self
        {
            if (is_null(self::$_instance))
            {

                self::$_instance = new database(CONF['Database']);

            }

            return self::$_instance;
        }

        /**
         * @return PDO instance of Database
         */
        public static function getDB(): PDO
        {

            $bdd = self::getInstance();
            return $bdd->db;

        }
    }
}

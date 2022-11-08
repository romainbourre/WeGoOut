<?php

namespace WebApp\Librairies
{

    use System\Librairies\Database;

    /**
     * Class AppSettings
     * Get and manage App settings from database
     * @package App\Ext
     * @author Romain Bourré
     */
    class AppSettings
    {

        private $appConfig = array();

        /**
         * AppSettings constructor.
         * @param $ref string reference of settings row from database
         */
        public function __construct(string $ref = "DEFAULT")
        {

            if (!is_null($ref))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('SELECT * FROM SETTINGS WHERE set_ref = :ref');
                $request->bindValue(':ref', $ref);
                $request->execute();

                $this->appConfig = $request->fetch();

            }

        }

        /**
         * Get a minimum number of participant for one event
         * @return mixed minimum number of participant
         */
        public function getParticipantMinNumber()
        {
            return $this->appConfig['SET_NBRMINPARTICIPANT'];
        }


        /**
         * Get a maximum number of participant for one event
         * @return mixed maximum number of participant
         */
        public function getParticipantMaxNumber()
        {
            return $this->appConfig['SET_NBRMAXPARTICIPANT'];
        }

        /**
         * Get minimum age for user registration
         * @return mixed minimum age of user
         */
        public function getMinAgeUser()
        {
            return $this->appConfig['SET_AGE_MIN'];
        }

        /**
         * Get default distance for list of events
         * @return int default distance
         */
        public function getDefaultDistance(): int
        {
            return (int)$this->appConfig['SET_DEFAULT_DISTANCE'];
        }

        /**
         * Get minimum length of application password
         * @return int minimum length
         */
        public function getPasswordMinLength(): int
        {
            return (int)$this->appConfig['SET_PWD_MIN_LENGTH'];
        }
    }
}
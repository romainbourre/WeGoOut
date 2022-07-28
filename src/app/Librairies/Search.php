<?php

namespace App\Librairies
{

    use Domain\Entities\Event;
    use Domain\Entities\User;
    use System\Librairies\Database;

    /**
     * One result of research
     * Class Search
     * @package App\extensions
     */
    class Search
    {

        private $research = null;
        private $result = null;
        private $relevance = null;

        /**
         * Search constructor.
         * @param string $research term of research
         * @param object $result one result object of research
         * @param int $relevance relevance of research
         */
        public function __construct(string $research, $result, int $relevance = 0)
        {
            $this->research = $research;
            $this->result = $result;
            $this->relevance = $relevance;
        }

        /**
         * Research term
         * @return null|string
         */
        public function getResearch()
        {
            return $this->research;
        }

        /**
         * One result of research
         * @return null|object
         */
        public function getResult()
        {
            return $this->result;
        }

        /**
         * Relevance of result
         * @return int|null
         */
        public function getRelevance()
        {
            return $this->relevance;
        }

        /**
         * Research term in database
         * @param string $research
         * @return object
         */
        public static function found(string $research)
        {

            return new class($research)
            {

                /**
                 * @var string research text
                 */
                private $research;

                /**
                 *  constructor.
                 * @param string $research
                 */
                public function __construct(string $research)
                {
                    $this->research = $research;
                }

                /**
                 * Research users and events from $research
                 * @return iterable|null
                 */
                public function all(): ?iterable
                {

                    $results = array();

                    $results = array_merge($this->user(), $this->event());

                    return $results;

                }

                /**
                 * Search user from $research
                 * @return iterable|null list of users
                 */
                public function user(): ?iterable
                {

                    $bdd = Database::getDB();

                    $request = $bdd->prepare('SELECT DISTINCT USER_ID FROM USER LEFT JOIN META_USER_CLI USING(USER_ID) LEFT JOIN META_USER_PRO USING(USER_ID) WHERE lower(concat(CLI_FIRSTNAME, " ", CLI_LASTNAME)) LIKE lower(concat(:search,\'%\')) OR lower(concat(CLI_LASTNAME, " ", CLI_FIRSTNAME)) LIKE lower(concat(:search,\'%\')) OR lower(PRO_NAME) like lower(concat(:search,\'%\')) OR lower(USER_EMAIL) LIKE lower(:search)');
                    $request->bindValue(':search', $this->research);

                    if ($request->execute())
                    {

                        $users = array();

                        while ($result = $request->fetch())
                        {

                            $users[] = new Search($this->research, User::loadUserById($result['USER_ID']));

                        }

                        return $users;

                    }

                    return null;

                }

                /**
                 * Research events from $research
                 * @return array|null list of events
                 */
                public function event()
                {

                    $bdd = Database::getDB();
                    $me = $_SESSION['USER_DATA'];

                    $request = $bdd->prepare('SELECT * FROM ( SELECT distinct tab1.EVENT_ID, tab1.EVENT_TITLE FROM EVENT tab1 LEFT JOIN USER USING(user_id) LEFT JOIN GUEST tab2 ON tab1.event_id = tab2.event_id LEFT JOIN FRIENDS tab3 ON tab1.user_id = tab3.user_id OR tab1.user_id = tab3.user_id_1 WHERE EVENT_DATETIME_CANCEL is null AND EVENT_DATETIME_DELETE is null AND EVENT_VALID = 1 AND USER_DATETIME_DELETE is null AND USER_VALID = 1 AND ( tab1.user_id = :userId OR tab1.event_circle = 1 OR ( tab1.event_circle = 2 AND (tab1.event_guest_only != 1 OR tab1.event_guest_only is null ) AND ( tab3.user_id = :userId OR tab3.user_id_1 = :userId ) AND tab3.fri_datetime_demand is not null AND tab3.fri_datetime_accept is not null AND tab3.fri_datetime_delete is null  ) OR ( tab1.event_circle = 2 AND tab1.event_guest_only = 1 AND tab2.user_id = :userId AND tab2.guest_datetime_send is not null AND tab2.guest_datetime_delete is null ) ) ) as EVENTS WHERE lower(EVENTS.EVENT_TITLE) like lower(concat(\'%\', :search, \'%\'))');
                    $request->bindValue(':userId', $me->getID());
                    $request->bindValue(':search', $this->research);

                    if ($request->execute())
                    {

                        $events = array();

                        while ($result = $request->fetch())
                        {

                            $events[] = new Search($this->research, new Event($result['EVENT_ID']));

                        }

                        return $events;

                    }

                    return null;

                }

            };

        }
    }
}
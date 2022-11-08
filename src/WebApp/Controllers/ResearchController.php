<?php

namespace WebApp\Controllers
{

    use Business\Ports\AuthenticationContextInterface;
    use WebApp\Librairies\Search;

    /**
     * Research of the apps System
     * Class SearchNav
     * @package App\controllers
     */
    class ResearchController extends AppController
    {
        public function __construct(private readonly AuthenticationContextInterface $authenticationContext)
        {
            parent::__construct();
        }


        /**
         * Load stylesheets and scripts
         */
        public function getView()
        {
            $this->addJsScript('js-search.js');
            $this->addCssStyle('css-search.css');
        }

        /**
         * Research term in all target
         * @param string $research
         * @return string
         */
        private function all(string $research): string
        {
            $results = array();
            if (!empty($research))
            {
                $results = Search::found($this->authenticationContext, $research)->all();
            }
            return $this->getAutocompleteView($results);
        }

        /**
         * Research users from term
         * @param string $research
         * @return string
         */
        private function user(string $research): string
        {
            $results = array();
            if (!empty($research))
            {
                $results = Search::found($this->authenticationContext, $research)->user();
            }
            return $this->getAutocompleteView($results);
        }

        /**
         * Recsearh events from term
         * @param string $research
         * @return string
         */
        private function event(string $research): string
        {
            $results = array();
            if (!empty($research))
            {
                $results = Search::found($this->authenticationContext, $research)->event();
            }
            return $this->getAutocompleteView($results);
        }

        /**
         * Get view of list of results
         * @param iterable $results
         * @return string
         */
        public function getAutocompleteView(iterable $results): string
        {
            return $this->render('searchnav.view-result', compact('results'));
        }

        /**
         * Ajax router
         * @param string $action
         * @return string|null
         */
        public function ajaxRouter(string $action): ?string
        {

            if (isset($_POST['research']))
            {

                switch ($action)
                {

                    case "all":
                        return $this->all($_POST['research']);

                    case "user":
                        return $this->user($_POST['research']);

                    case "event":
                        return $this->event($_POST['research']);

                }

            }

            return null;
        }
    }
}
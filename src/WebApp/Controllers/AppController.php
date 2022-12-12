<?php

namespace WebApp\Controllers
{


    use Business\Entities\Event;
    use Business\Entities\Notifications;
    use Business\Entities\User;
    use ReflectionClass;
    use Slim\Psr7\Request;
    use System\Controllers\Controller;
    use WebApp\Attributes\Page;
    use WebApp\Exceptions\MandatoryParamMissedException;
    use WebApp\Librairies\Emitter;

    /**
     * Class AppController
     * Controller of application
     * @package App\Controllers
     */
    abstract class AppController extends Controller
    {

        /**
         * AppController constructor.
         */
        public function __construct()
        {
            $this->initListeners();
            $this->parsePageAttributes();
        }

        private function parsePageAttributes(): void
        {
            $reflection = new ReflectionClass($this);
            $attributes = $reflection->getAttributes(Page::class);
            foreach ($reflection->getMethods() as $method) {
                $attributes = array_merge($attributes, $method->getAttributes(Page::class));
            }
            foreach ($attributes as $attribute) {
                $page = $attribute->newInstance();
                /** @var Page $page */
                $this->addCssStyle($page->css);
                $this->addJsScript($page->js);
            }
        }

        protected function extractValueFromQuery(Request $request, string $valueName): ?string
        {
            $params = $request->getQueryParams();
            return $params[$valueName] ?? null;
        }

        /**
         * @throws MandatoryParamMissedException
         */
        protected function extractValueFromBodyOrThrow(Request $request, string $valueName): string
        {
            $value = $this->extractValueFromBody($request, $valueName);
            if ($value == null) {
                throw new MandatoryParamMissedException($valueName);
            }
            return $value;
        }

        protected function extractValueFromBody(Request $request, string $valueName): ?string
        {
            $params = $request->getParsedBody();
            if (!isset($params[$valueName])) {
                return null;
            }
            return htmlspecialchars($params[$valueName]);
        }

        /**
         * Load listener of web application
         */
        private function initListeners()
        {
            $emitter = Emitter::getInstance();

            // EVENTS NOTIFICATIONS ------------------------------------------------------------------------------------------- //

            $emitter->on('event.user.ask', function (Event $event, User $user)
            { // USER SEND REQUEST TO PARTICIPATE TO AN EVENT
                Notifications::manager($event->getUser())->add()->event("request", $event, $user);
            });

            $emitter->on('event.user.unrequest', function (Event $event, User $user)
            { // USER CANCELED REQUEST TO PARTICIPATE
                Notifications::manager($event->getUser())->add()->event("unrequest", $event, $user);
            });

            $emitter->on('event.user.accept', function (Event $event, User $userAccepted, User $userWhoAccept)
            { // CREATOR OR ORGANISER ACCEPT A REQUEST TO PARTICIPATE TO AN EVENT
                Notifications::manager($userAccepted)->add()->event("accept", $event, $userWhoAccept);
            });

            $emitter->on('event.user.subscribe', function (Event $event, User $user)
            { // USER PARTICIPATE TO AN EVENT
                Notifications::manager($event->getUser())->add()->event("subscribe", $event, $user);
            });

            $emitter->on('event.user.unsubscribe', function (Event $event, User $user)
            { // USER DON'T PARTICIPATE NOW
                Notifications::manager($event->getUser())->add()->event("unsubscribe", $event, $user);
            });

            $emitter->on('event.user.delete', function (Event $event, User $userDeleted, User $userWhoDelete)
            { // USER IS DELETED FROM AN EVENT
                Notifications::manager($userDeleted)->add()->event("delete", $event, $userWhoDelete);
            });

            $emitter->on('event.pub.add', function (Event $event, User $userWhoLeavePub)
            { // A USER LEAVE A PUBLICATION IN AN EVENT

                if (!$userWhoLeavePub->equals($event->getUser())) Notifications::manager($event->getUser())->add()->publication("publication", $event, $userWhoLeavePub);

                // TODO : $organizer = send notification to organizer

                $participants = $event->getParticipants($userWhoLeavePub, 1);

                foreach ($participants as $participant)
                {

                    if (!$userWhoLeavePub->equals($participant)) Notifications::manager($participant)->add()->publication("publication", $event, $userWhoLeavePub);

                }

            });

            $emitter->on('event.review.add', function (Event $event, User $userWhoLeaveReview)
            { // A USER LEAVE A REVIEW IN AN EVENT

                if (!$userWhoLeaveReview->equals($event->getUser())) Notifications::manager($event->getUser())->add()->review("review", $event, $userWhoLeaveReview);

                $participants = $event->getParticipants($userWhoLeaveReview, 1);

                // TODO : $organizer = send notification to organizer

                foreach ($participants as $participant)
                {

                    if (!$userWhoLeaveReview->equals($participant)) Notifications::manager($participant)->add()->review("review", $event, $userWhoLeaveReview);

                }

            });

            // USERS NOTIFICATIONS -------------------------------------------------------------------------------------------- //

            $emitter->on('user.welcome', function (User $welcomeUser)
            { // A USER HAS JUST REGISTERED
                Notifications::manager($welcomeUser)->add()->enjoy("welcome");
            });

            $emitter->on('user.friend.request', function (User $requested, User $applicant)
            { // SEND NOTIFICATIONS WHEN USER SEND A FRIEND REQUEST
                Notifications::manager($requested)->add()->user("request", $applicant);
            });

            $emitter->on('user.friend.unrequest', function (User $unrequested, User $applicant)
            { // SEND NOTIFICATION WHEN USER CANCELED FRIEND REQUEST
                Notifications::manager($unrequested)->add()->user("unrequest", $applicant);
            });

            $emitter->on('user.friend.accept', function (User $accepted, User $applicant)
            { // SEND NOTIFICATION WHEN USER ACCEPT FRIEND
                Notifications::manager($accepted)->add()->user("accept", $applicant);
            });

            $emitter->on('user.friend.delete', function (User $deleted, User $applicant)
            { // SEND NOTIFICATION WHEN USER DELETE FRIEND
                Notifications::manager($deleted)->add()->user("delete", $applicant);
            });

            // INVITATION NOTIFICATION

            $emitter->on('event.send.invitation', function (Event $event, User $invitedUser, User $userWhoInvite)
            {
                Notifications::manager($invitedUser)->add()->event("send.invitation", $event, $userWhoInvite);
            });

            $emitter->on('event.delete.invitation', function (Event $event, User $disinvitedUser, User $userWhoDisinvite)
            {
                Notifications::manager($disinvitedUser)->add()->event("delete.invitation", $event, $userWhoDisinvite);
            });
        }

    }
}

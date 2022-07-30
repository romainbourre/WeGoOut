<?php

namespace App\Controllers
{


    use App\Exceptions\NotConnectedUserException;
    use App\Librairies\Emitter;
    use Domain\Entities\Event;
    use Domain\Entities\Notifications;
    use Domain\Entities\User;
    use Domain\Entities\UserCli;
    use System\Controllers\Controller;

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

            $emitter->on('user.friend.request', function (UserCli $requested, UserCli $applicant)
            { // SEND NOTIFICATIONS WHEN USER SEND A FRIEND REQUEST
                Notifications::manager($requested)->add()->user("request", $applicant);
            });

            $emitter->on('user.friend.unrequest', function (UserCli $unrequested, UserCli $applicant)
            { // SEND NOTIFICATION WHEN USER CANCELED FRIEND REQUEST
                Notifications::manager($unrequested)->add()->user("unrequest", $applicant);
            });

            $emitter->on('user.friend.accept', function (UserCli $accepted, UserCli $applicant)
            { // SEND NOTIFICATION WHEN USER ACCEPT FRIEND
                Notifications::manager($accepted)->add()->user("accept", $applicant);
            });

            $emitter->on('user.friend.delete', function (UserCli $deleted, UserCli $applicant)
            { // SEND NOTIFICATION WHEN USER DELETE FRIEND
                Notifications::manager($deleted)->add()->user("delete", $applicant);
            });

            // INVITATION NOTIFICATION

            $emitter->on('event.send.invitation', function (Event $event, UserCli $invitedUser, UserCli $userWhoInvite)
            {
                Notifications::manager($invitedUser)->add()->event("send.invitation", $event, $userWhoInvite);
            });

            $emitter->on('event.delete.invitation', function (Event $event, UserCli $disinvitedUser, UserCli $userWhoDisinvite)
            {
                Notifications::manager($disinvitedUser)->add()->event("delete.invitation", $event, $userWhoDisinvite);
            });
        }

    }
}
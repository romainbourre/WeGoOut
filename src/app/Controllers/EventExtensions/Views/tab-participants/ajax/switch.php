<?php

use Domain\Entities\User;

switch ($action) {
    case "filter.update":
        echo $this->getViewParticipantsFilter();
        break;
    case "filter.all":
        echo $this->getViewParticipantsList(0);
        break;
    case "filter.valid":
        echo $this->getViewParticipantsList(1);
        break;
    case "filter.wait":
        echo $this->getViewParticipantsList(2);
        break;
    case "filter.invited":
        echo $this->getViewParticipantsList(3);
        break;
    case "part.accept":
        $this->setParticipantAsValid(User::loadUserById($_POST['userId']));
        break;
    case "part.delete":
        $this->unsetParticipant($_POST['userId']);
        break;
    case "part.invite":
        $this->sendInvitation();
        break;

}
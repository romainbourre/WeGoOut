<?php

switch ($action) {
    case "publications":
        echo $this->getAjaxPublications();
        break;
    case "new.publication":
        $this->setAjaxNewPublication();
        break;
}
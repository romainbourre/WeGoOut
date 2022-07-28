<?php

namespace App\Controllers;

/**
 * Class Maintenance
 * Represent maintenance web page
 * @package App\Controllers
 */
class MaintenanceController extends AppController {

    public function getView() {

        $content =  "Maintenance";

        echo $this->render('templates.template', compact('content'));

    }

}
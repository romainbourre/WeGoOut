<?php

switch ($action) {
    case "reviews.form":
        echo $this->getViewReviewsForm();
        break;
    case "reviews.update":
        echo $this->getViewReviewsList();
        break;
    case "reviews.new":
        $this->saveNewReview();
        break;
}
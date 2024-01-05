<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$questions = json_decode(file_get_contents("questions.json"));

foreach($questions as $q) {
    $qData = json_decode(file_get_contents("answers/$q->id.json"));
    $correctAnswerIndex = 0;
    if($qData == NULL) {
        echo "File for <i>$q->id</i> was not found or has incorrect json.<br>";
        continue;
    }
    
    $wrongPoints = false;
    $wrongType = false;
    foreach($qData as $answer) {
        foreach($answer->pairs as $pair) {
            if(!$wrongType && gettype($pair->response) != "string") {
                echo "A response from <i>$q->id</i> has the wrong type.<br>";
            }
        }
        if($answer->correct == true) {
            if(!$wrongPoints && $q->points[$correctAnswerIndex] != $answer->points) {
                echo "Incorrect points value in <i>$q->id</i>.<br>";
                $wrongPoints = true;
            }
            $correctAnswerIndex++;
        }
    }
    if($correctAnswerIndex != count($q->points)) {
        echo "Incorrect point amount in <i>$q->id</i>.<br>";
    }
}

echo "<br>Other than that everything seems good!";

?>
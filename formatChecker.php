<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$questions = json_decode(file_get_contents("questions.json"));
$problems = 0;
ob_start();

foreach($questions as $q) {
    // Account for headers.
    if(!isset($q->id)) continue;

    if(!file_exists("answers/$q->id.json")) {
        echo "File for <i>$q->id</i> was not found or has incorrect json.<br>";
        $problems++;
        continue;
    }
    $qData = json_decode(file_get_contents("answers/$q->id.json"));
    $correctAnswerIndex = 0;
    
    $wrongPoints = false;
    $wrongType = false;
    foreach($qData as $answer) {
        foreach($answer->pairs as $pair) {
            if(!$wrongType && gettype($pair->response) != "string") {
                echo "A response from <i>$q->id</i> has the wrong type.<br>";
                $problems++;
            }
        }
        if($answer->correct == true) {
            if(!$wrongPoints && $q->points[$correctAnswerIndex] != $answer->points) {
                echo "Incorrect points value in <i>$q->id</i>. ";
                echo "Questions has <b>" .$q->points[$correctAnswerIndex]. "</b> while $q->id.json has <b>$answer->points</b>.<br>";
                $problems++;
                $wrongPoints = true;
            }
            $correctAnswerIndex++;
        }
    }
    if($correctAnswerIndex != count($q->points)) {
        echo "Incorrect point amount in <i>$q->id</i>.<br>";
        $problems++;
    }
}

$problemText = ob_get_contents();
ob_end_clean();

if($problems > 0) {
    echo "Found <b>$problems</b> problem" .($problems > 1 ? "s" : ""). ".<br>";
    echo $problemText;
    echo "<br>Other than that everything seems good!";
}
else echo "Everything seems completely and utterly fine :)";


?>
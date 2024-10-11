<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$questions = json_decode(file_get_contents("questions.json"));
$problems = 0;
ob_start();

// Check each question in the questions.json file on the following points:
foreach($questions as $q) {
    // Check if it isn't a header title thingy
    if(!isset($q->id)) continue;

    // Check if the answer file exists
    if(!file_exists("answers/$q->id.json")) {
        echo "File for <i>$q->id</i> was not found.<br>";
        $problems++;
        continue;
    }
    $qData = json_decode(file_get_contents("answers/$q->id.json"));
    $correctAnswerIndex = 0;

    // Check if the answer file is parsable
    if($qData == NULL) {
        echo "File has unparsable JSON.<br>";
        $problems++;
        continue;
    }

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

    // Check if the answer file has the same point amount as the questions file
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
else echo "Everything seems completely and utterly fine :)<br><br>";


$answerFiles = scandir("answers", 0, );
foreach($answerFiles as $fileName) {
    if($fileName[0] == '.' || $fileName == "freepoints.json" || $fileName == "public.json") continue;
    if((fileperms("answers/$fileName") & 0xfff) != 0o0600) {
        echo "Answer file $fileName has <b>wrong permissions</b>!!<br>";
    }
}

?>
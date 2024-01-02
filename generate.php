<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "players.php";

// Handle entered name.
if(isset($_POST["name"])) {
    if(!ctype_alnum($_POST["name"])) {
        echo "<div>Please only use letters or numbers in your name thanks.</div>";
        printNameRequest();
        die;
    }
    setcookie("name", $_POST["name"]);
    getPlayerData($_POST["name"]);
}

// Ask for a name.
if(!isset($_COOKIE["name"]) && !isset($_POST["name"])) {
    printNameRequest();
    die;
}

// Get user info.
if(isset($_COOKIE["name"])) $name = $_COOKIE["name"];
else $name = $_POST["name"];
$player = getPlayerData($name);

// Check provided answer. This has to happen here to update the user info.
if(isset($_POST["question"]) && isset($_POST["answer"])) {
    $checkedAnswer = checkAnswer($player, $_POST["question"], $_POST["answer"]);
}

// Generate user info thingy.
echo "<div>";
echo "Hello <b>$name</b>.<br>You currently have <b>$player->points</b> points, from <b>$player->answers</b> correct answers.";
echo "</div><br><br>";

// Parse question file.
$qFile = fopen("questions.json", "r");
$questions = json_decode(fread($qFile, 5000));
fclose($qFile);

// Generate questions.
foreach($questions as $q) {
    echo "<div id=\"$q->id\">";
    generateAnswers($q);
    echo "<div>$q->html</div>";
    echo "<form method=\"post\" action=\"#$q->id\">";
    echo "<label for=\"$q->id-answer\">Answer: </label>";
    echo "<input type=\"text\" name=\"answer\" id=\"$q->id-answer\">";
    echo "<input type=\"hidden\" name=\"question\" value=\"$q->id\">";
    echo "<br><input type=\"submit\">";
    if(isset($checkedAnswer) && $_POST["question"] == $q->id) {
        echo "<br><br><b>You answered:</b> " . $_POST["answer"] . "<br>";
        echo "<br>$checkedAnswer";
    }
    echo "</form></div>";
}

fclose($player->file);

function generateAnswers($question) {
    $questionCount = count($question->points);
    echo "<div class=\"points\">";
    
    // Singular answer questions:
    if($questionCount == 1) {
        echo "<b>Answer: </b><div>" . $question->points[0] . " point" .
            ($question->points[0] > 1 ? "s" : "") . 
        ".</div>";
    }
    // Multi answer questions:
    else {
        echo "<b>Answers: </b>";
        for($i = 0; $i < $questionCount; $i++) {
            echo "<div>" . ($i + 1) . ": " . $question->points[$i] . " point" . 
                ($question->points[$i] > 1 ? "s" : "") . // Gotta have that responsive pluralization
            ".</div>";
        }
    }
    echo "</div>";
}

function checkAnswer($player, $questionId, $userAnswer) {
    $ansFile = fopen("answers/$questionId.json", "r");
    $answers = json_decode(fread($ansFile, 2000));
    fclose($ansFile);

    // Prepare for nested bullshit
    foreach($answers as $answerIndex=>$answer) {
        foreach($answer->pairs as $pair) {
            foreach($pair->patterns as $pattern) {
                // Check answer.
                if(stristr($userAnswer, $pattern)) {
                    // If it matches and it's a correct answer, store it.
                    if($answer->correct == true) {
                        addAnswerToPlayer($player, $questionId, $answerIndex, $answer->points);
                    }
                    return $pair->response;
                }
            }
        }
    }

    return "Nope :)";
}

function printNameRequest() {
    echo "<div><form method=\"post\">";
    echo "<label for=\"name\">Please provide a name :)</label><br>";
    echo "<input type=\"text\" name=\"name\" id=\"name\">";
    echo "</form></div>";
}

?>
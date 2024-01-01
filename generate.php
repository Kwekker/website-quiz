<?php

include "check.php";

$qFile = fopen("questions.json", "r");
$questions = json_decode(fread($qFile, 5000), true);
fclose($qFile);

$answerQ = $_POST["question"];
$answer = $_POST["answer"];

if(isset($_POST["name"])) {
    if(!ctype_alnum($_POST["name"])) {
        echo "<div>Please only use letters or numbers in your name thanks.</div>";
        printNameRequest();
        die;
    }
    setcookie("name", $_POST["name"]);
}

if(!isset($_COOKIE["name"]) && !isset($_POST["name"])) {
    // Ask for a name.
    printNameRequest();
    die;
}

// Generate user info thingy.
if(isset($_COOKIE["name"])) $name = $_COOKIE["name"];
else $name = $_POST["name"];
$points = 420;
$cAnswers = 69;

echo "<div>";
echo "Hello <b>$name</b>.<br>You currently have <b>$points</b> points, from <b>$cAnswers</b> correct answers.";
echo "</div>";


// Generate questions.
foreach($questions as $q) {
    $id = $q["id"];
    echo "<div id=\"$id\">";
    generateAnswers($q);
    echo "<div>" . $q["html"] . "</div>";
    echo "<form method=\"post\" action=\"#$id\">";
    echo "<label for=\"$id-answer\">Answer: </label>";
    echo "<input type=\"text\" name=\"answer\" id=\"$id-answer\">";
    echo "<input type=\"hidden\" name=\"question\" value=\"$id\">";
    echo "<br><br><input type=\"submit\">";
    if($answerQ == $id) {
        echo "<br>" . checkAnswer($answerQ, $answer);
    }
    echo "</form></div>";
}

function generateAnswers($question) {
    $questionCount = count($question["points"]);
    echo "<div class=\"points\"> <b>Answer:</b>";
    
    // Singular answer questions:
    if($questionCount == 1) {
        echo "<div>" . $question["points"][0] . " points. </div>";
    }
    // Multi answer questions:
    else { 
        for($i = 0; $i < $questionCount; $i++) {
            echo "<div>" . ($i + 1) . ": " . $question["points"][$i] . " points</div>";
        }
    }
    echo "</div>";
}

function checkAnswer($questionId, $userAnswer) {
    $ansFile = fopen("answers/$questionId.json", "r");
    $answers = json_decode(fread($ansFile, 2000), true);
    fclose($ansFile);

    // Prepare for nested bullshit
    foreach($answers as $answer) {
        foreach($answer["pairs"] as $pair) {
            foreach($pair["patterns"] as $pattern) {
                if(stristr($userAnswer, $pattern)) {
                     return $pair["response"];
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
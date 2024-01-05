<?php

ini_set('error_log', "err.log");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "players.php";

// TODO: Support for malicious shit like having an invalid name as a cookie.
// TODO: QUESTION NAMES CAN BE PATHS!!! Make sure to refuse symbols in question names!!!
// TODO: Same with names in cookies!!!!
function generateQuestions() {

    if(isset($_POST["reset"]) && $_POST["reset"] == "true") {
        unset($_COOKIE["name"]);
        setcookie("name", "", -1);
    }
    
    // Handle name already set.
    if(isset($_COOKIE["name"])) {
        $name = $_COOKIE["name"];
        if(checkName($name) != false) {
            echo "<div>Stop manually changing your cookies you nerd.";
            echo "<form method='post'><input type='hidden' name='reset' value='true'><input type='submit' value='I didn&#39;t???'></form>";
            echo "</div>";
            error_log("Weird cookie name [$name]");
            return;
        }
    }
    // Handle entered name.
    else if(isset($_POST["name"])) {
        $name = strtolower($_POST["name"]);

        // Check if name is valid.
        $checkedName = checkName($name);
        if($checkedName != false) {
            echo $checkedName;
            printNameRequest();
            checkLeaderboard();
            return;
        }

        // Check if name already exists.
        if(file_exists("people/$name.csv") && !isset($_POST["thatsme"])) {
            echo "<div>That name was already taken. If that wasn't you, please use a different name.<br><form method='POST'>";
            echo "<input type='hidden' name='thatsme' value='true'>";
            echo "<input type='hidden' name='name' value='$name'>";
            echo "<input type='submit' value='Yeah, that was me.'>";
            echo "</form></div>";
            printNameRequest();
            checkLeaderboard();
            return;
        }

        setcookie("name", $name);
    }
    // Ask for a name.
    else {
        printNameRequest();
        checkLeaderboard();
        return;
    }

    // Get user info.
    $player = getPlayerData($name);

    // Check provided answer. This has to happen here to update the user info.
    $checkedAnswer = false;
    if(isset($_POST["question"]) && isset($_POST["answer"])) {
        if(!ctype_alnum($_POST["question"]) || strlen($_POST["question"]) > 15) {
            echo "<div>Kindly fuck off :)</div>";
            return;
        }
        if(strlen($_POST["answer"]) > 200 || strchr($_POST["answer"], '\n')) {
            $checkedAnswer = "Your answer is either too long or cringe.";
        }
        else {
            
            [$checkedAnswer,$isCorrect,$answerIndex] = checkAnswer($player, $_POST["question"], $_POST["answer"]);
            
            // Log answer.
            $correctText = $isCorrect ? "CORRECT:$answerIndex" : "WRONG";
            file_put_contents("allAnswers.csv", 
                date("d M y H:i:s") . ",$correctText,$player->name," .$_POST["question"]. ",\"" .$_POST["answer"]. "\"\n",
                FILE_APPEND | LOCK_EX
            );
        }
    }

    fclose($player->file);

    // Make sure the player's rank has been set.
    checkLeaderboard($player);

    // Generate user info thingy.
    echo "<div>";
    echo "Hello <b>$name</b>.<br>You currently have <b>$player->points</b> points, from <b>$player->answerCount</b> correct answers. This puts you in position <b>$player->rank</b> on the leaderboard.";
    echo "</div><br><br>";

    // Parse question file.
    $questions = json_decode(file_get_contents("questions.json"));

    // Generate questions.
    foreach($questions as $question) {
        generateQuestion($question, $player, $checkedAnswer);
    }
}

function generateQuestion($q, $player, $checkedAnswer = false) {
    echo "<div id='$q->id'";

    // Add 'completed' class if the user has answered all answers of this question.
    if(isset($player->answers[$q->id]) && count($player->answers[$q->id]) == count($q->points))
        echo " class='completed'";
    echo ">";

    // Print answers and points on the right side.
    generateAnswers($player, $q);

    // Print question html.
    echo "<div>$q->html</div>";

    // Print the answer form with a hidden input that provides the question id.
    echo "<form method='post' action='#$q->id'>";
    echo "<label for='$q->id-answer'>Answer: </label>";
    echo "<input type='text' name='answer' id='$q->id-answer'>";
    echo "<input type='hidden' name='question' value='$q->id'>";
    echo "<br><input type='submit'>";

    // Check the answer and provide a response if there is one.
    if($checkedAnswer != false && $_POST["question"] == $q->id) {
        echo "<br><br><b>You answered:</b> " . htmlspecialchars($_POST["answer"]) . "<br>";
        echo "<br>$checkedAnswer";
    }
    echo "</form></div>";
}

function generateAnswers($player, $question) {
    // Count the amount of point values the question has in questions.json.
    $answerCount = count($question->points);
    echo "<div class='points'>";
    
    // Singular answer questions:
    if($answerCount == 1) {
        echo "<b>Answer: </b><div";
        // Check if the question has been answered already.
        if(isset($player->answers[$question->id]) && $player->answers[$question->id][0] == 0)
            echo " class='answered'";

        echo ">" . $question->points[0] . " point";

        // Pluralize 'point' if necessary.
        if($question->points[0] > 1) echo "s"; 

        echo ".</div>";
    }
    // Multi answer questions:
    else {
        echo "<b>Answers: </b>";
        for($i = 0; $i < $answerCount; $i++) {
            echo "<div";
            // Check if the question has been answered already.
            if(isset($player->answers[$question->id]) && in_array($i, $player->answers[$question->id]))
                echo " class='answered'";
            echo ">" . ($i + 1) . ": " . $question->points[$i] . " point";
            if($question->points[$i] > 1) echo "s"; // Gotta have that responsive pluralization
            echo ".</div>";
        }
    }
    echo "</div>";
}

function checkAnswer($player, $questionId, $userAnswer) {
    $ansFile = fopen("answers/$questionId.json", "r");
    $answers = json_decode(fread($ansFile, 2000));
    fclose($ansFile);

    $correctAnswerIndex = 0;
    // Prepare for nested bullshit.
    // Check every pattern starting from the top to the bottom of the file.
    foreach($answers as $answer) {
        foreach($answer->pairs as $pair) {
            foreach($pair->patterns as $pattern) {
                // Check answer.
                if(stristr($userAnswer, $pattern)) {
                    $ret = "";

                    // Check if it's correct or already answered.
                    if(
                        $answer->correct == true 
                        && addAnswerToPlayer($player, $questionId, $correctAnswerIndex, $answer->points)
                    ) $ret .= "(<i>You already answered this question with this answer.</i>)<br><br>";
                        
                    // If it matches and it's a correct answer, store it.
                    $ret .= $pair->response;
                        
                    return [$ret,$answer->correct,$correctAnswerIndex];
                }
            }
        }
        if($answer->correct) $correctAnswerIndex++;
    }

    return ["Nope :)",false,-1];
}

function checkName($name) {
    if(!ctype_alnum($name))
        return "<div>Please only use letters or numbers in your name thanks.</div>";
    if(strlen($name) > 20)
        return "<div>Your name is too long.</div>";
    if(strtolower($name) == "jochem")
        return "<div>There can only be one.</div>";
    return false;
}

function printNameRequest() {
    echo "<div><form method='post'>";
    echo "<label for='name'>Please provide a name :)</label><br>";
    echo "<input type='text' name='name' id='name'><br>";
    echo "<input type='submit'>";
    echo "</form></div>";
}

?>
<?php

ini_set('error_log', "err.log");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "players.php";

// TODO: Maybe make it so that times.json is locked during the entire procedure so no goofy things happen.
function generateQuestions() {

    if(isset($_POST["reset"]) && $_POST["reset"] == "true") {
        unset($_COOKIE["name"]);
        setcookie("name", "", -1);
    }
    
    // Handle name already set.
    if(isset($_COOKIE["name"])) {
        $name = $_COOKIE["name"];
        if(checkName($name) != false || !file_exists("people/" .strtolower($name). ".csv")) {
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

        // Check if too many name requests.
        $times = json_decode(file_get_contents("times.json"), true);
        if($times != false && time() - $times["__GLOBAL__"] < 1) {
            echo "<div>Too many new names at the moment. New names are rate limited to prevent bullshittery. Wait like 2 seconds and try again.";
            echo "<br>If this problem persists, please <a href='/aboutme#contact'>tell me about it</a> or <a href='/com'>leave a comment about it</a>, thanks :).</div>";
            checkLeaderboard();
            printNameRequest();
            return;
        }

        // Check if name already exists.
        if(file_exists("people/$name.csv") && !isset($_POST["thatsme"])) {
            echo "<div>That name was already taken. If that wasn't you, please use a different name.<br><form method='POST'>";
            echo "<input type='hidden' name='thatsme' value='true'>";
            echo "<input type='hidden' name='name' value='$name'>";
            echo "<input type='submit' value='Yeah, that was me.'>";
            echo "</form></div>";
            checkLeaderboard();
            printNameRequest();
            return;
        }

        // Name is accepted
        if($times != false) {
            $times["__GLOBAL__"] = time();
            // Initialize name to 0 to make sure it's in the times.json file but it doesn't get flagged as a brute-forcer.
            $times[$name] = 0;
            file_put_contents("times.json", json_encode($times), LOCK_EX);
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


    // Check if this user isn't brute-forcing (very, very cringe).
    if(!isset($times)) $times = json_decode(file_get_contents("times.json"), true);

    if(time() < $times[$name]) {
        $secs =  $times[$name] - time();
        echo "<div>You still have to wait a bit, slow down please.<br>You can reload the page in $secs seconds.. (A button will appear)<br>";
        echo "<a href='.' class='button sec8' style='animation-delay: " .$secs. ".2s'>Back to the questions</a></div>";
        return;
    }
    else if(time() - $times[$name] < 1 || isset($_GET["bruteforce"])) {
        echo "<div>Ok yeah no you are answering questions WAY too fast. You're getting an 8 second timeout. Please relax.<br>";
        echo "<span style='font-size:0px;'>Also you can bet your silly ass this is getting logged you nerd.</span><br>";
        echo "Still waiting.. <a href='.' class='button sec8'>Back to the questions</a></div>";

        // Log the time except way larger for a time-out.
        $times[$name] = time() + 8;
        file_put_contents("times.json", json_encode($times), LOCK_EX);

        // Log error.
        if(isset($_POST["question"]) && isset($_POST["answer"])) error_log("BRUTE_FORCE: $name," . $_POST["question"] .",". $_POST["answer"]);
        else error_log("BRUTE_FORCE: $name," . " no questions answered.");
        return;
    }

    // Check provided answer. This has to happen here to update the user info.
    $checkedAnswer = false;
    if(isset($_POST["question"]) && isset($_POST["answer"])) {
        // Log the time
        $times[$name] = time();
        file_put_contents("times.json", json_encode($times), LOCK_EX);

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
                date("d M y H:i:s") . ",$player->name," .$_POST["question"]. ",$correctText,\"" .$_POST["answer"]. "\"\n",
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
    if($player->rank == 1) echo " Congrats :).";
    echo "</div><br>";

    // Parse question file.
    $questions = json_decode(file_get_contents("questions.json"));

    // Generate questions.
    foreach($questions as $question) {
        if($checkedAnswer != false && isset($question->id) && $_POST["question"] == $question->id)
            generateQuestion($question, $player, $checkedAnswer, $isCorrect);
        else 
            generateQuestion($question, $player);
            
    }
}

function generateQuestion($q, $player, $checkedAnswer = false, $isCorrect = false) {
    // Handle headers. These don't have an id and are just titles for sections.
    if(!isset($q->id)) {
        echo "<div class='heading'>$q->html</div>";
        return;
    }

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
    if($checkedAnswer != false) {
        echo "<br><br><hr><br><b>You answered:</b> " . htmlspecialchars($_POST["answer"]);
        echo "<br><br>$checkedAnswer";
        // Add win animation elements.
        if($isCorrect) echo "<div class='yippee'></div><div class='yippee alt'></div>";
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
                if (
                    (!isset($pair->exact) || $pair->exact == false) && stristr($userAnswer, $pattern) 
                    || isset($pair->exact) && $pair->exact == true && $userAnswer == $pattern
                ) {
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
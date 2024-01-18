<?php

$leaderboard = false;

function getPlayerData($name) {
    $newPlayer = !file_exists("people/$name.csv");
    $file = fopen("people/$name.csv", "a+");

    // CSV Line:
    // time,QuestionId,answerIndex,points
    // 0    1          2           3

    $points = 0;
    $answerCount = 0;
    $answers = [];
    $line = fgetcsv($file);

    while($line != false) {
        $answerCount++;
        $points += intval($line[3]);

        // Add the answered answers to an array indexed by question ID.
        // If the array is this: ["question1" => [0,2], "question2" => [0]],
        //   it means that question1 has been answered with answers 0 and 2,
        //   and question2 has been answered with answer 0.
        if(!isset($answers[$line[1]]))
            $answers[$line[1]] = [$line[2]];
        else
            array_push($answers[$line[1]], $line[2]);

        $lastLine = $line;
        $line = fgetcsv($file);
    }

    // Only check the leaderboard position if the user is not answering a question.
    // If they are it will be set in the answer checking function.
    $rank = -1;
    if(!isset($_POST["question"]) && $newPlayer == false) $rank = getLeaderboardPosition($name);
    else if($newPlayer == true) $rank = addNewLeaderboardName($name);

    return (object) array(
        'name' => $name,
        'file' => $file, 
        'answerCount' => $answerCount, 
        'answers' => $answers, 
        'points' => $points, 
        'rank' => $rank,
    );
}

function addAnswerToPlayer($player, $questionId, $answerIndex, $points) {
    
    // Check if answer has been answered before.
    if(isset($player->answers[$questionId])) {
        if(in_array($answerIndex, $player->answers[$questionId]))
            return true;
        else 
            array_push($player->answers[$questionId], $answerIndex);
    }
    else $player->answers[$questionId] = [$answerIndex];

    $fields = [time(), $questionId, $answerIndex, $points];
    fputcsv($player->file, $fields);

    // Update data in current memory.
    $player->points += $points;
    $player->answerCount++;

    updateLeaderboardPosition($player);

    return false;
}

function updateLeaderboardPosition($player) {
    // Get current leaderboard
    $file = fopen("leaderboard.json", "c+");
    if(flock($file, LOCK_EX) == false) {
        fclose($file);
        return false;
    }
    global $leaderboard;
    $leaderboard = json_decode(fread($file, 10000));
    $leaderboardLength = count($leaderboard);

    // Find old and new index.
    $oldIndex = 0;
    $newIndex = -1;
    while($oldIndex < $leaderboardLength && $leaderboard[$oldIndex]->name != $player->name) {
        // Check for the new index while we're at it.
        if($newIndex < 0 && $leaderboard[$oldIndex]->points <= $player->points) 
            $newIndex = $oldIndex;
        $oldIndex++;
    }
    if($oldIndex == $leaderboardLength) return false;

    // Find new index AFTER the old index in the off chance that you got negative points.
    if($newIndex < 0) {
        do $newIndex++;
        while($newIndex < $leaderboardLength && $leaderboard[$newIndex]->points > $player->points);
    }

    // Move this player up if they just gained more points than the people above them.
    if($oldIndex != $newIndex) {
        // Remove old entry.
        array_splice($leaderboard, $oldIndex, 1); 
        // Move the new index if necessary.
        if($newIndex > $oldIndex) $newIndex--;
        // Add new entry.
        array_splice($leaderboard, $newIndex, 0, [(object)['name' => $player->name, 'points' => $player->points]]);
    }
    else $leaderboard[$oldIndex]->points = $player->points;

    // Find this player's rank.
    while($newIndex >= 0 && $leaderboard[$newIndex]->points == $player->points) $newIndex--;
    $player->rank = $newIndex + 2;
    
    // Write the new array into the file.
    fseek($file, 0);
    fwrite($file, json_encode($leaderboard));
    ftruncate($file, ftell($file));
    fclose($file);

}

function getLeaderboardPosition($name) {
    // Get, lock and read the file.
    $file = fopen("leaderboard.json", "r");
    if(flock($file, LOCK_SH) == false) {
        fclose($file);
        return false;
    }
    global $leaderboard;
    $leaderboard = json_decode(fread($file, 10000));
    fclose($file);

    // Find the leaderboard position, taking equal places into account.
    // Points   8 8 6 5 3 2 2 2 1
    // Position 1 1 3 4 5 6 6 6 9
    $currentPos = -2;
    $prevPoints = -1;
    foreach($leaderboard as $i => $line) {
        if($line->points != $prevPoints) $currentPos = $i + 1;
        if($line->name == $name) break;
        $prevPoints = $line->points;
    }

    return $currentPos;
}

function addNewLeaderboardName($name) {
    // Get and lock the file.
    $file = fopen("leaderboard.json", "r+");
    if(flock($file, LOCK_EX) == false) {
        fclose($file);
        return false;
    }
    global $leaderboard;
    $leaderboard = json_decode(fread($file, 10000));

    // Add the new player to the list.
    array_push($leaderboard, (object)['name' => $name, 'points' => 0]);

    // Get the last rank.
    $rank = count($leaderboard);
    while($rank > 0 && $leaderboard[$rank - 1]->points == 0) $rank--;

    // Write the new array into the file.
    fseek($file, 0);
    fwrite($file, json_encode($leaderboard));
    ftruncate($file, ftell($file));
    fclose($file);

    return $rank + 1;
}

// Check if the leaderboard has been read before.
function checkLeaderboard($player = NULL) {
    if($player == NULL) {
        global $leaderboard;
        $file = fopen("leaderboard.json", "r");
        if(flock($file, LOCK_SH) == false) {
            fclose($file);
            return false;
        }
        global $leaderboard;
        $leaderboard = json_decode(fread($file, 10000));
        fclose($file);
    }
    else if($player->rank == -1) {
        $player->rank = getLeaderboardPosition($player->name);
    }
}


?>
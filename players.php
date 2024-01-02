<?php

function getPlayerData($name) {
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

        $line = fgetcsv($file);
    }

    return (object) array('file' => $file, 'answerCount' => $answerCount, 'answers' => $answers, 'points' => $points);
}

function addAnswerToPlayer($player, $questionId, $answerIndex, $points) {
    
    // Check if answer has been answered before.
    if(isset($player->answers[$questionId])) {
        if(in_array($answerIndex, $player->answers[$questionId]))
        return true;
    else array_push($player->answers[$questionId], $answerIndex);
    }
    else $player->answers[$questionId] = [$answerIndex];

    $fields = [time(), $questionId, $answerIndex, $points];
    fputcsv($player->file, $fields);

    // Update data in current memory.
    $player->points += $points;
    $player->answerCount++;

    return false;
}


?>

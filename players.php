<?php

function getPlayerData($name) {
    $file = fopen("people/$name.csv", "a+");

    // CSV Line:
    // time,QuestionId,answerIndex,points
    // 0    1          2           3

    $points = 0;
    $answers = 0;
    $line = fgetcsv($file);
    while($line != false) {
        $answers++;
        $points += intval($line[3]);

        $line = fgetcsv($file);
    }

    return (object) array('file' => $file, 'answers' => $answers, 'points' => $points);
}

function addAnswerToPlayer($player, $questionId, $answerIndex, $points) {
    $fields = [time(), $questionId, $answerIndex, $points];

    fputcsv($player->file, $fields);

    // Update data in current memory.
    $player->points += $points;
    $player->answers++;
}


?>

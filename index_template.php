<?php
    // This file is a template of what your index.php needs for the quiz. My own index.php is based on this.
    // This php block is REQUIRED to be all the way at the top of your page. No spaces or comments before it.
    include "generate.php";
    if(headers_sent()) echo "fuck!!";
    $player = getPlayer();
?>


<!-- Here you can put your standard header bs -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <!-- Include the quiz's stylesheet: -->
    <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>


<!-- Put some of your own bs here -->

<!-- The leaderboard code: -->
    <?php
        // If $player is a string, something went wrong in getPlayer().
        // The string is an error message, and is therefore printed to the user.
        if(is_string($player)) {
            $questionHTML = $player;
        }
        else {
            // Start an output buffer (all echoes go into this buffer instead.)
            ob_start();
            generateQuestions($player);
            // Put the buffer contents into $questionHTML
            $questionHTML = ob_get_contents();
            ob_end_clean();
            // This has to happen here because generateQuestions also fetches the leaderboard data.
        }

        if($leaderboard != false && count($leaderboard) >= 3):
            $pos2 = $leaderboard[0]->points == $leaderboard[1]->points ? 1 : 2;
            $pos3 = $leaderboard[1]->points == $leaderboard[2]->points ? $pos2 : 3;
    ?>
        <div class="leaderboard">
            <h3>Leaderboard</h3>
            <!-- index.php shows how I did the cool podium leaderboard thing. For the template I just made it a list. -->
            <ol>
                <li><?php echo $leaderboard[0]->name ."<b>". $leaderboard[0]->points ."</b>" ?></li>
                <!-- The value thing is necessary because first, second and third place could have equal points, putting
                    them at the same ranking. I want to do this right. -->
                <li value="<?php echo $pos2;?>"> <?php echo $leaderboard[1]->name ."<b>". $leaderboard[1]->points ."</b>"?></li>
                <li value="<?php echo $pos2;?>"> <?php echo $leaderboard[2]->name ."<b>". $leaderboard[2]->points ."</b>"?></li>
            </ol>
        </div>
        <br>
        <?php else: ?>
        <h3>Leaderboard</h3>
        <div style="font-size:20px;text-align:center;">There will be a leaderboard here when there are enough players for that.</div>
        <br>
    <?php endif; ?>
<!-- End of the leaderboard code -->

<!-- You can put a description of the quiz here, or copy mine from index.php idc -->

<!-- The actual questions/name prompt gets inserted here. -->
    <div id="questions">
        <?php echo $questionHTML; ?>
    </div>

</body>
</html>
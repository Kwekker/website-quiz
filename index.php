<?php
  include "generate.php";
  if(headers_sent()) echo "fuck!!";
  $player = getPlayer();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="A quiz with questions and obscure references nobody will get.">
  <meta property="og:title" content="Jochem's quiz"/>
  <meta property="og:type" content="website"/>
  <meta property="og:url" content="https://jochemleijenhorst.com/quiz"/>
  <meta property="og:image" content="https://jochemleijenhorst.com/quiz/thumbnail.png"/>
  <meta property="og:site_name" content="jochemleijenhorst.com"/>
  <meta property="og:description" content="You can do a cool quiz :)"/>
  <title>Jochem's quiz</title>
  <link rel="stylesheet" href="/main.css?r" type="text/css">
  <link rel="stylesheet" href="style.css" type="text/css">
  <link rel="icon" href="../pictures/gewoon een eend.png" type="image/gif">

</head>

<body>
  <h1>
    <div class="animated">
      Quiz
    </div>
    <br>
    Jochem's quiz page!!!
  </h1>

  <article class="topPart">
    <a href="/" class="button">Main menu</a>
    <br><br>

    <h2>
      A quiz with really obscure questions
    </h2>
    <?php
      if(is_string($player)) {
        $questionHTML = $player;
      }
      else {
        ob_start();
        generateQuestions($player);
        $questionHTML = ob_get_contents();
        ob_end_clean();
      }

      if($leaderboard != false && count($leaderboard) >= 3):
        $pos2 = $leaderboard[0]->points == $leaderboard[1]->points ? 1 : 2;
        $pos3 = $leaderboard[1]->points == $leaderboard[2]->points ? $pos2 : 3;
    ?>
    <div class="leaderboard">
        <h3>Leaderboard</h3>
        <div class="top">
          <b>1</b>
          <?php echo $leaderboard[0]->name ."<b>". $leaderboard[0]->points ."</b>" ?>
        </div>
        <div class="notop">
          <span>
            <b><?php echo $pos2 ."</b>". $leaderboard[1]->name ."<b>". $leaderboard[1]->points ."</b>"?>
          </span>
          <span>
            <b><?php echo $pos3 ."</b>". $leaderboard[2]->name ."<b>". $leaderboard[2]->points ."</b>"?>
          </span>
        </div>
    </div>
    <br>
    <?php else: ?>
    <h3>Leaderboard</h3>
    <div style="font-size:20px;text-align:center;">There will be a leaderboard here when there are enough players for that.</div>
    <br>
    <?php endif; ?>

  </article>

  <article>
    <div class="unuglify">

      <?php
        if(isset($_GET["fb"])) echo "<h2>Thank you for the feedback!</h2>";
        else if(isset($_GET["bfb"])) echo "<h2>Something went wrong while adding your feedback! Sorry lol!</h2>"
      ?>

      <h3>How it works</h3>

      Scroll through the list of dumb questions. If you know the answer to any of the questions, put the answer into the accompanying answer field.<br>
      The person with the most points gets to be on the <a href="/" class="hidingnext fakelink">home page</a>!!!
      <div class="textBlock">
        This could be you! <br>
        <img src="img/advertisement.png" alt="An image of my homepage with the text “henry is incredibly cool because they're in first place on the quiz leaderboard.”"><br>
        (Please imagine your name is Henry when viewing this image)
      </div>
      <span><details>
        <!-- The details tag is an amazing addition to html I love it. -->
        <summary>A few details about answering (if you care):</summary>
        <ul>
          <li>All answers are logged for my personal enjoyment, as well as improving the answer responses, so don't put your credit card information in them.</li>
          <li><b>Not every 'question' is actually a question.</b> Some are just quotes or images that reference something. In these cases, just answer with what you think it is referencing.</li>
          <li>You can only answer 1 question at a time.</li>
          <li>You are not going to get every reference here. If you have no idea wtf one of these questions means, just scroll to the next one.</li>
          <li>Answers are case-insensitive. This is not true for questions with the <i>exact</i> tag.</li>
          <li>The program only checks if your answer <i>includes</i> the correct answer. If the answer to a question is "blue" and you answer with "it's blue", it'll get accepted. This is not true for questions with the <i>exact</i> tag.</li>
          <li>Every correct answer gives you an amount of points. The amount of points is based on how cool I think it is that you can answer that specific question with that specific answer.</li>
          <li>Some questions have multiple correct answers. For example, the answer to "What is 1+1?" could be both "2" and "two". I have tried to add every possible way of writing the correct answer to every question.</li>
          <li>Some questions have multiple correct answers that give a separate amount of points. For example, The answer to "What is x in x^2=x could be both 1 and -1. The amount of separate correct answers is shown in the top right of the question, or the bottom left on mobile.</li>
        </ul>
      </details></span>

      <p>
        I have tried to keep the questions as ungoogleable as possible, but still please just don't try.
        You can google <i>how</i> to answer the questions of course, like for example looking up Ohm's Law is completely fine. However, looking up the exact words I used in my questions to find out what they're about or what they're referencing is kind of cringe.
      <p> <!--No closer needed. The p element is just chill like that-->
        I'll probably update the questions regularly, when I come up with/find the time to make more.<br>
        The last update to the questions was on
        <?php echo date("d M Y", filemtime("questions.json"));?>.
      <p>
        If you don't like one of the questions, you can press the <a href="/experiments/comments?but"><button>This question is dumb</button></a> button. You can then tell me why you don't like the question, and I might change it :).
      </p>

      <br>
      tl;dr just answer the fuckin questions.
    </div>
    <div id="questions">
      <?php echo $questionHTML; ?>
    </div>
  </article>

</body>
</html>
<?php
  require_once "generate.php";

  ini_set('error_log', "err.log");
  error_reporting(E_ALL);


  if(isset($_POST["question"]) && (isset($_POST["reason"]) || isset($_POST["typedReason"]))) {
    if(!file_exists("answers/" .$_POST["question"]. ".json")) {
      header("Location: /quiz?bfb");
      die;
    }

    if(
      isset($_POST["typedReason"]) && strlen($_POST["typedReason"]) > 1000 || 
      isset($_POST["givenName"]) && strlen($_POST["givenName"]) > 30 ||
      isset($_POST["reason"]) && strlen($_POST["reason"]) > 30
    ) {
      header("Location: /quiz?bfb");
      die;
    }

    $name = isset($_POST["givenName"]) ? $_POST["givenName"] : "ANON";
    $reason = isset($_POST["reason"]) ? $_POST["reason"] : "none";
    $typedReason = isset($_POST["typedReason"]) ? $_POST["typedReason"] : "none";
    $typedReason = str_replace("\n","\n\t", $typedReason);
    
    $newLine = time() .",$name,". $_POST["question"] .",$reason:\n\t$typedReason\n";
    sendNotif($_POST["question"] . " reported for " . $reason, "By $name", "/quiz");

    file_put_contents("reports.log", $newLine, LOCK_EX | FILE_APPEND);

    header("Location: /quiz?fb");
    die;
  }
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Obscure references nobody will get.">
  <title>Report a question</title>
  <link rel="stylesheet" href="/main.css" type="text/css">
  <link rel="stylesheet" href="style.css" type="text/css">
  <link rel="icon" href="../pictures/gewoon een eend.png" type="image/gif">

</head>

<body>
  <h1>
    <div class="animated">
      Really?
    </div>
    <br>
    A dumb question?
  </h1>

  <article class="topPart">
    <a href="/quiz" class="button">Back to the Quiz</a>

    <h2>So you think that question is dumb?</h2>
    
  </article>

  <article>
    <div class="unuglify">
      <?php if(isset($_POST["question"])): ?>
      <h3>Reporting a question</h3>
      If you think one of the questions on my quiz page is dumb, you can tell me here! <br>
      Just give me the reason, and I'll probably change it!
      If I agree with you of course! Otherwise I won't! <br>
      <a href="/quiz">Shit wait, I came here on accident! The question isn't actually that dumb after all!</a>
      <br><br>
      You have accused the following question of being dumb:
      <div id="questions">
        <?php

          // Now that I think about it, questions in questions.json should really be keyed by their ID shouldn't they?
          // Anyway..
          $questions = json_decode(file_get_contents("questions.json"));
          foreach($questions as $q) 
            if(isset($q->id) && $q->id == $_POST["question"]) break;

          if(isset($_POST["answer"])) generateQuestion($q, NULL, "You can't answer the question here you nerd");
          else generateQuestion($q);
          // echo "<div>";
          // echo generateAnswers(NULL, $q);
          // echo $q->html;
          // echo "</div>"
        ?>
        <br>
        <div>
          <form method="POST">
            <label for="name">Your name (leave empty for anonymity):</label><br>
            <input type="text" name="givenName" id="name" style="margin-left:0;" value="<?php if(isset($_POST["name"])) echo $_POST["name"]; ?>">
            <br><br>
            Choose:<br>
            <input type="radio" name="reason" id="wording" value="wording">
            <label for="wording">The question itself is dumb</label><br>
            
            <input type="radio" name="reason" id="points" value="points">
            <label for="points">The amount of points is dumb</label><br>
            
            <input type="radio" name="reason" id="answerRight" value="answerRight">
            <label for="answerRight">My answer should be correct</label><br>
            
            <input type="radio" name="reason" id="answerWrong" value="answerWrong">
            <label for="answerWrong">The correct answer is wrong</label><br>
            
            <input type="radio" name="reason" id="fuck" value="fuck">
            <label for="fuck">Idk just fuck this question in general</label><br>
            
            <input type="radio" name="reason" id="other" value="other">
            <label for="other">Something else</label><br>
            
            <br>
            <input type="hidden" name="question" value="<?php echo $_POST["question"]; ?>">
            <label for="reason">Explain (doesn't have to be an essay):</label> <br>
            <textarea name="typedReason" id="reason" cols="30" rows="10" style="font-family:Arial, Helvetica, sans-serif"></textarea><br>
            <input type="submit">
          </form>

        </div>
      </div>
      <?php else: ?>
        How did you even get here? You're only supposed to get here by pressing the "This question is dumb" button on any of the questions in the quiz..
      <?php endif; ?>
    </div>
  </article>
  
</body>
</html>
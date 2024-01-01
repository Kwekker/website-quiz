<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="description" content="Obscure references nobody will get.">
  <title>Jochem's quiz</title>
  <link rel="stylesheet" href="/main.css" type="text/css">
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
    <a href="..">
      <button class="button"> Main menu </button>
    </a>
    <br><br>

    <h2 style="width:90%;margin-left: 5%;"> 
      A quiz with really obscure questions
    </h2>
    
  </article>

<!-- 
  TODO:
    Reference style
    Reference storage
    Reference validation
    Names
    Leaderboard
    Counting
-->

  <article>
    <div class="unuglify">
      <h3>How it works</h3>
      Scroll through the list of dumb questions. If you know the answer to any of the questions, put the answer into the accompanying answer field.<br>
      Not every 'question' is actually a question. Some are just quotes or images that reference something. In these cases, just answer with what you think it is referencing.<br>
      A few details about answering:
      <ul>
        <li>Answers are case-insensitive.</li>
        <li>Most of the time the program only checks if your answer <i>includes</i> the correct answer. If the answer to a question is "blue" and you answer with "it's blue", it'll get accepted.</li>
        <li>Most questions have multiple correct answers. For example, the answer to "what color is the sky" could be both "blue" and "black", because it's not always day time. Sometimes multiple answers even give you an amount of points individually.</li>
      </ul>
      Every correct answer gives you an amount of points. The amount of points is based on how cool I think it is that you can answer that specific question with that specific answer.
      <br><br>
      I have tried to keep the questions as ungoogleable as possible, but still please just don't try; it ruins the whole purpose of this page.
      <br><br>
      TL;DR Just answer the fuckin questions.
    </div>
    <div id="questions">
      <?php include "generate.php" ?>
    </div>
  </article>
  
</body>
</html>
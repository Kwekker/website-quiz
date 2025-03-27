# The Quiz

This is a quiz I made for my website. You can check it out [here](https://jochemleijenhorst.com/quiz).

It is made entirely in HTML/CSS/PHP. No JavaScript required.

This repository contains the code and files for *my* quiz, for *my* questions specifically (excluding the answer files). But, I've made a way to turn it into a more generic quiz, and add your own questions and style, for your own website!

This readme will cover how it works, and how to turn it into your own thing.

## Quick overview of the files
- [index.php](index.php) contains the webpage.
- [index_template.php](index_template.php) contains a bare-bones version of index.php, with only the necessary quiz code.
- [generate.php](generate.php) generates the questions, player information, leaderboard, and checks answers. If you want to change the text in any of these elements, this is the file you need to edit.
- [players.php](players.php) is used by generate.php to update. You probably don't need to change this file.
- [reporter.php](reporter.php) is used to report questions (gets used when someone presses the "This question is dumb" button).

- [style.css](style.css) contains the styles for the questions. You can edit this file to change how questions look.
- [questions.json](questions.json) contains the questions in the form of a json file. More on this [later](#questionsjson).
- [formatChecker.php](formatChecker.php) is a program that checks if you messed up anything in the answer files or the questions.json file. This file is *very* useful.



## File formats

### questions.json
questions.json is a json file with an array. Each element of the array is an object with the following keys:
- **id**: A unique id for this question. This id is visible to the user, so make it related to the *question*, not the answer.
- **html**: The html of the question. This can literally just be a string of text. It can contain things like images and &lt;b&gt; tags, because it's literally inserted into the html.
- **points**: An array of point amounts. Some questions have multiple correct answers that each give you a separate amount of points. These have to be in the same order as the points of correct answers in the answer file of the question. You can use [formatChecker.php](formatChecker.php) to check if all questions have the correct amount of points. Points are both in the questions.json file as well as the answer files for performance reasons.

Example of a question:
```json
{
    "id": "cowcolors",
    "html": "Which colors do cows have?",
    "points": [2, 2, 3]
}
```
This question has 3 correct answers, two of which give 2 points and one of which gives 3 points.

An element of the array can also have only a **html** key in it. If that is the case, it becomes a *heading*. The html of a *heading* is placed within a div with the *heading* class between the question divs. This is very useful for structuring your questions with titles in between them, or like writing some context for the next few questions.

Example of a heading:
```json
{
    "html": "<h3>Cow questions</h3>Time for some questions about cows."
},
```

### Answer files.
Answer files are a bit more complicated than the questions file. An answer file is a json file within the [answers](answers/) directory. The file name must be `[question_id].json`. Every question needs an answer file.

An answer file contains a json array. This array has *answer objects* in it. An *answer object* has
- a `correct` boolean,
- a `points` integer if the `correct` boolean is true,
- and a `pairs` array.

A `pairs` array is an array of `pair` objects. A `pair` object contains a match-response pair. It has a `patterns` array, which contains pattern strings, and a `response` string. This response is given to the user whenever they answer with something that matches one of the `patterns`. Concretely, a `pair` object has: 
- a `patterns` array of strings,
- an optional `exact` boolean,
- an optional `regex` boolean,
- and a `response` string.

The `patterns` array contains strings that are matched to the answer the user gives. If one of the patterns matches the user's answer, the answer is counted as correct and the points are granted. Matching means that the answer of the user `contains` the pattern. This is case insensitive. If the pattern is "Brown" and the user answered "I think it's broWN" it's counted as correct.

Setting `exact` to true makes it so that the user's answer needs to exactly match the pattern, with case sensitivity. When `exact` is true, and the pattern is "brown", the answer "I think it's brown" will **not** match.

Setting `regex` to true interprets all patterns in the `patterns` array as regular expressions. The regular expression needs to include the slashes and the flags (it's directly plugged into the php `preg_match` function). You *need* to add the `i` flag after the final slash if you want this to be case insensitive. Since it's json, if you want to use tags like `\w`, you need double backslashes (`\\w`).

**Note:**
Answers are checked against patterns in order of occurrence in the answer file. This means that, if there is an *incorrect* answer object before the *correct* answer objects, the answer will be counted as *incorrect* whenever one of the patterns in the *incorrect* answer object matches, even if it also matches one in the *correct* answer object. This is sometimes useful for filtering out dumb answers you disagree with.

An example of an answer file for the cow question, with some comments. Responses are tagged with an id in a comment for some explanation after this example. These ids are only used here for documentation purposes, because writing out the full responses in a table is kinda messy.
```json
[
  { // Answer object
    "correct": false,
    "pairs": [
      { // Pair object
        "patterns": ["purple"],
        // [NotPurple]
        "response": "Haha funny milka cow haha no"
      }
    ]
  },
  { // Answer object
    "correct": true,
    "points": 2,
    "pairs": [
      { // Pair object
        "patterns": ["pitch-black"],
        // [CorrectPitchBlack]
        "response": "I wouldn't be that hyped about the blackness of cows honestly, they're black in a normal way, not pitch black, but I'll give you the point."
      },
      { // Another pair object
        "patterns": ["black", "ebony"],
        // [CorrectBlack]
        "response": "Nice!! Very well done."
      }
    ]
  },
  {// Answer object
    "correct": true,
    "points": 2,
    "pairs": [
      {
        "patterns": ["white"],
        // [CorrectWhite]
        "response": "Well done!!! You know your cows well!!"
      }
    ]
  },
  {// Answer object
    "correct": true,
    "points": 3,
    "pairs": [
      {
        "patterns": ["brown"],
        // [CorrectBrown]
        "response": "Woww, you named a fancy cow color! You get 3 points for that!"
      }
    ]
  },
  {// Answer object
    "correct": false,
    "pairs": [
      {
        "patterns": ["yellow"],
        // [NotYellow]
        "response": "No, cows are not yellow. Maybe go outside to look at some cows for once"
      }
      {
        "regex": true,
        "patterns": ["/gr[ae]y/i"],
        // [NotGrey]
        "response": "I actually honestly don't know if grey cows exist"
      },
      {
        "patterns": ["green", "blue", "pink", "red", "indigo"],
        // [NotThatColor]
        "response": "No, cows are not that color."
      },
    ]
  }
]
```

The example will give the following responses, if it were answered with the following answers in the following order, by the same player:
| Answer           | Response     | Points | Comment |
|------------------|--------------|---------|-------|
| Black            | CorrectBlack |2| |
| brown            | CorrectBrown |3| |
| Black and white  | CorrectBlack |0| The "black" answer object is first in the file, and is thus prioritised. Also grants no points since the "black" answer was already answered before. |
| Purple and white | NotPurple |0| The "purple" answer object blocks the "white" answer object since it is first in the file. |
| green and yellow | NotYellow|0| The yellow pair comes before the other color pairs, and is thus prioritised.|
| The color is gray| NotGrey|0| The regex matches "grey" and "gray".
| Red and green    | NotThatColor|0|         | 

**Sidenote**: The usual structure of an answer file is one correct answer object and one incorrect answer object with a bunch of response pairs to help the player along. When the question has more than one correct answer that gives points (rare), more correct answer objects can be added. Since incorrect answers don't give points, you basically never need more than one incorrect answer object. The only reason to have multiple incorrect answer objects is to filter out bad answers *before* checking good answers, like with "purple" in the example above.

<!-- TODO: Write about the [PERSONAL] and [STYLE] things. -->
<!-- TODO: Change main.css to be more generic. -->
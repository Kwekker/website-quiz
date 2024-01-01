# The idea

References thing you know what it is

- Have one .ref file per reference
  - All of these files are private
  - File names are IDs for references
  - File type is .ref which is automatically concatenated to the user request to prevent hackermen trying to get the code to read a file that isn't a reference file
  - A ref file contains a list of keywords and messages. If the line starts with a ! it's a winning list of keywords, if the line starts with an ? it's a losing list of keywords.
    The reference is guessed correctly if the stripped answer *contains* one of the keywords.
    Stripped in this context means all caps & interpunction is removed.

- The reference information is stored in a single JSON file. A reference has these keys:
  - id: the .ref filename
  - text: Potential text information given to the user that can be used to guess the reference
  - img: Potential image link
  - audio: Potential audio link
  - obscurity: 0-5
  

- Leaderboard system
  - Everyone has a name, stored with caps, identified without (like minecraft)
  - Name file is JSON and public
  - No passwords because fuck that
  - Per name the references they already got are stored, along with the total score
  - Leaderboard of 5 or something is shown with js probably
  - 
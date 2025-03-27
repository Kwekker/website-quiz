# This file removes all jochemleijenhorst.com specific code and adds all necessary files.
# I know there are probably better ways of doing this but I can't be bothered to use any of them.


import os
import pathlib
import shutil

answer = input("This program will completely wipe the img folder, questions.json file, and all player data, so you can start fresh on your own quiz.\nAre you okay with that? [y/n] ")

if (answer != "y" and answer != "Y"):
    print("\nOkay then, I won't wipe anything (◕‿◕✿).\n")
    exit()

print("\nOkay then, here we go!\n")


print("Creating new leaderboard file..")
with open("leaderboard.json", "w") as file:
    file.write("[]")

print("Creating times.json file..")
with open("times.json", "w") as file:
    # Set the global time to the epoch lmao.
    # This is literally just to rate limit account creation btw
    file.write("{\"__GLOBAL__\":0}")

print("Creating player directory..")
shutil.rmtree("people")
os.makedirs("people")

print("Creating reports.log..")
with open("reports.log", "w") as file:
    file.write("")

print("Wiping questions.json..")
with open("questions.json", "w") as file:
    file.write("[]")

print("Wiping img folder..")
shutil.rmtree("img")
os.makedirs("img")


print("\nDone!! :D")
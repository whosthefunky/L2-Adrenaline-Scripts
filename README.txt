Adrenaline scripts are written in delphi/pascal syntax so If you want to better understand them or be able to modify them you could read something about those languages.

I'm gonna be doing a 1-80 leveler script for Interlude that includes some quests like pet quests, nobless, etc.

TODO list:
- Add array of hunting zones for exping and array of mobs that have useful drops and spoils and specify the path to go to each.
- killAggroMobs doesn't work in Interlude, find another way.
- The killAggroMobs procedure should check for mobs attacking not only you but any party member and kill them.
- There should be an array with of hunting grounds to having to change only the name or to write a number corresponding to the hunting grund
and depending of the huntingGround string it would go to a different position.
- The moveInCity procedure currently checks a range of 250 around every respawn of the city. It would be better for it to work in all the city.
Let's say the city has a range of 5000 (the range I think is the radius of a circle), it would need small circles in the different locations of the
city and they should completely fill the city range. It would start by the shops, if you are in the range of a shop you move to the door,
then move to a place of the square of the city in which the previous shops where. The difficult part will be checking if the city range is completely filled,
the dumb way to do it would be whenever the script doesn't work you check the location and increase the range of the part of the city in which you are.
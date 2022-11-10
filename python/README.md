# `slimebot`
`slimebot` acts as an interface between Discord itself and the actual `slime-priest` back end since I don't want to deal with trying to get parallelism working with Laravel.

## Structure
The code consists of a Flask webserver which accepts commands from the `slime-priest` backend, and the `discord.py` code which handles Discord related stuff like the sending and receiving of messages.

The webserver communicates with the Discord bot, which runs in a separate thread, via a queue which is continually checked.

### Reasoning
It is structured this way due to a number of restrictions on how Flask and `discord.py` work.
 - Flask needs to run in the main thread
    - `discord.py` may also prefer to run in the main thread, but I haven't had any issues yet 
 - `discord.py` needs any Discord related actions to be performed within its event loop
 - Custom events seem to take a comparatively long time to be handled by `discord.py`'s event loop (~5s)

from bot import Bot
from flask import Flask, request
import command_queue as q
from discord import Embed

bot = Bot()

app = Flask(__name__)

WIN_COLOUR = 0x16e5b4
LOSE_COLOUR = 0xff4655


@app.route("/")
def hello_world():
    return "slime-priest (c) 2022 Adam Stamp"


@app.get('/status')
async def status():
    return {
        'queue_length': bot.queue.length(),
        'bot_up': bot.running
    }


@app.post("/send")
async def send():
    body = request.get_json()
    bot.enqueue(q.SendTextCommand(body['message'], int(body['channel_id'])))
    return '', 204


@app.post('/send/embed/post-match')
async def sendEmbedPostMatch():
    body = request.get_json()
    message = body['message']
    was_win = message['our_score'] > message['their_score']

    embed = Embed(title='Match Won' if was_win else 'Match Lost',
                  colour=WIN_COLOUR if was_win else LOSE_COLOUR,
                  description='{} / {} / {} mins'.format(message['map'], message['mode'], round(message['duration'] / 1000 / 60)))
    embed.set_author(name='{} : {}'.format(
        message['our_score'], message['their_score']))
    embed.set_footer(text=message['server'])
    bot.enqueue(q.SendEmbedCommand(embed, int(body['channel_id'])))

    return '', 204


@app.post('/send/html')
async def sendHTML():
    body = request.get_json()
    bot.enqueue(q.SendHTMLCommand(body['message']['url'], int(body['channel_id']),
                body['message']['filename'], body['message']['format'], body['message']['loading_message'], body['message']['error_message']))

    return '', 204

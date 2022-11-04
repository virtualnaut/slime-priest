from bot import Bot
from flask import Flask, request
import command_queue as q

bot = Bot()

app = Flask(__name__)


@app.route("/")
def hello_world():
    return "slime-preist (c) 2022 Adam Stamp"


@app.get('/status')
async def status():
    return {
        'queue_length': bot.queue.length(),
        'bot_up': bot.running
    }


@app.post("/send")
async def send():
    body = request.get_json()
    bot.enqueue(q.SendCommand(body['message'], int(body['channel_id'])))
    return '', 204

import os
from threading import Thread
import discord
from dotenv import load_dotenv
from discord.ext import tasks
import command_queue as q

load_dotenv()

# How often to read from the API command queue in seconds.
QUEUE_READ_RATE = 1


class Bot:
    queue = q.Queue()
    running = False

    def __init__(self):
        intents = discord.Intents.default()
        intents.message_content = True
        intents.reactions = True
        intents.members = True
        intents.guilds = True

        self.client: discord.Client = discord.Client(
            intents=intents)

        # Loop to read commands from the queue.
        @tasks.loop(seconds=1)
        async def handle_commands():
            while not self.queue.empty():
                item = self.queue.dequeue()

                if (isinstance(item, q.SendCommand)):
                    await self.client.get_channel(item.channel_id).send(item.message)

        @self.client.event
        async def on_ready():
            print(f'Bot now running, I am {self.client.user}')
            self.running = True
            handle_commands.start()

        @self.client.event
        async def on_disconnect():
            print('Disconnected :(')
            self.running = False

        # Start the bot in a thread.
        self.thread = Thread(target=self.client.run, args=(
            os.getenv('BOT_KEY'),), daemon=True)
        self.thread.start()

    # Add a command to the queue.
    def enqueue(self, command: q.AbstractQueuedCommand):
        self.queue.enqueue(command)

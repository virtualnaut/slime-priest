import os
import io
import requests
from enum import Enum
from threading import Thread

import imgkit
import discord
from dotenv import load_dotenv
from discord.ext import tasks

import command_queue as q
from permissions import Command, has_permission, get_commands
from log import log


class Level(Enum):
    Success = 'success'
    Error = 'error'
    Warn = 'warn'


EMBED_COLOURS = {
    Level.Success: 0x2BB256,
    Level.Error: 0xB22B45,
    Level.Warn: 0xCA9E2D
}

COMMAND_USAGES = {
    Command.Help: f'~{Command.Help.value}',
    Command.Track: f'~{Command.Track.value} <player name>#<player tagline>',
    Command.Untrack: f'~{Command.Untrack.value}',
    Command.PersonOfInterest: f'~{Command.PersonOfInterest.value} add|remove <player name>#<player tagline>'
}

COMMAND_DESCRIPTIONS = {
    Command.Help: 'Display this help message',
    Command.Track: 'Change the user to check games for',
    Command.Untrack: 'Disable tracking',
    Command.PersonOfInterest: 'Add a person to include in stats'
}


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

                if (isinstance(item, q.SendTextCommand)):
                    await self.client.get_channel(item.channel_id).send(item.message)
                if (isinstance(item, q.SendEmbedCommand)):
                    await self.client.get_channel(item.channel_id).send(embed=item.message)
                if (isinstance(item, q.SendHTMLCommand)):
                    message = await self.client.get_channel(item.channel_id).send(embed=discord.Embed(title=item.loading_message))

                    file = discord.File(io.BytesIO(imgkit.from_url(
                        item.url, False, options={
                            'format': item.format,
                            'width': 700
                        })), '{}.{}'.format(item.filename, item.format))

                    await self.client.get_channel(item.channel_id).send(file=file)
                    await message.delete()

        @self.client.event
        async def on_ready():
            print(f'Bot now running, I am {self.client.user}')
            self.running = True
            handle_commands.start()

        @self.client.event
        async def on_disconnect():
            print('Disconnected :(')
            self.running = False

        @self.client.event
        async def on_message(message):
            if message.author == self.client.user:
                return

            if (message.content.startswith('~')):
                await self.handle_user_command(message)

        # Start the bot in a thread.
        self.thread = Thread(target=self.client.run, args=(
            os.getenv('BOT_KEY'),), daemon=True)
        self.thread.start()

    # Add a command to the queue.
    def enqueue(self, command: q.AbstractQueuedCommand):
        self.queue.enqueue(command)

    async def handle_user_command(self, message: discord.Message):
        command = message.content[1:].split(' ')

        if command[0] in [command.value for command in Command] and has_permission(message.author.id, command[0]):

            if command[0] == Command.Help.value:
                commands = get_commands(message.author.id)

                description = ''

                for command in commands:
                    description += '`' + \
                        COMMAND_USAGES[command] + '`: ' + \
                        COMMAND_DESCRIPTIONS[command] + '\n\n'

                await message.channel.send(embed=discord.Embed(title='Available Commands', description=description))

            if command[0] == Command.Track.value:
                if not len(command) >= 2:
                    await self.send_embed(message.channel, 'No player was given', Level.Error)
                    return

                player = command[1].split('#')

                if not len(player) == 2:
                    await self.send_embed(message.channel, 'Invalid player', Level.Error)
                    return

                response = requests.post(
                    f'http://slimeweb/api/poi/tracking/{player[0]}/{player[1]}')

                if response.status_code >= 200 and response.status_code < 300:
                    await self.send_embed(message.channel, f'Tracked player set to `{command[1]}`', Level.Success)
                else:
                    if '-v' in command:
                        await self.send_embed(message.channel, f'There was a problem finding player `{command[1]}`', Level.Error, f'```Code: {str(response.status_code)}```')
                    else:
                        await self.send_embed(message.channel, f'There was a problem finding player `{command[1]}`', Level.Error)

            if command[0] == Command.Untrack.value:
                response = requests.delete('http://slimeweb/api/poi/tracking')

                if response.status_code >= 200 and response.status_code < 300:
                    await self.send_embed(message.channel, f'Tracking is disabled', Level.Success)
                else:
                    if '-v' in command:
                        await self.send_embed(message.channel, 'Problem while trying to disable tracking', Level.Error, f'```Code: {str(response.status_code)}```')
                    else:
                        await self.send_embed(message.channel, 'Problem while trying to disable tracking', Level.Error)

            if command[0] == Command.PersonOfInterest.value:
                if not len(command) >= 3 or command[1] not in ['add', 'remove']:
                    await self.send_embed(message.channel, 'Can\'t read command\nUsage: `~poi add|remove <player name>#<player tagline>`', Level.Error)
                    return

                player = command[2].split('#')

                if not len(player) == 2:
                    await self.send_embed(message.channel, 'Invalid player', Level.Error)
                    return

                if command[1] == 'add':
                    response = requests.post(
                        f'http://slimeweb/api/poi', json={'name': player[0], 'tag': player[1]})

                    feedback = f'Created new person of interest `{command[2]}`'

                elif command[1] == 'remove':
                    response = requests.delete(
                        f'http://slimeweb/api/poi/destroy/{player[0]}/{player[1]}')

                    feedback = f'Removed person of interest `{command[2]}`'

                if response.status_code >= 200 and response.status_code < 300:
                    await self.send_embed(message.channel, feedback, Level.Success)
                else:

                    if response.status_code == 409:
                        feedback = f'Player `{command[2]}` is already of interest'
                    else:
                        feedback = f'There was a problem finding player `{command[2]}`'

                    if '-v' in command:
                        await self.send_embed(message.channel, feedback, Level.Error, f'```Code: {str(response.status_code)}```')
                    else:
                        await self.send_embed(message.channel, feedback, Level.Error)
        else:
            await self.send_embed(
                message.channel, 'You don\'t have permission to use that!', Level.Error)

    async def send_embed(self, channel: discord.TextChannel, content: str, level: Level, description=None):
        await channel.send(embed=discord.Embed(title=content, colour=EMBED_COLOURS[level], description=description))

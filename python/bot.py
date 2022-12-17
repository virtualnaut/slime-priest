import os
import io
import re
from enum import Enum
from threading import Thread

import imgkit
import discord
import requests
from dotenv import load_dotenv
from discord.ext import tasks

import command_queue as q
from permissions import Command, SubCommand, has_permission, get_commands, command_is
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
    Command.PersonOfInterest: [
        f'~{Command.PersonOfInterest.value} add|remove <player name>#<player tagline>', f'~{Command.PersonOfInterest.value} list'],
    Command.Status: f'~{Command.Status.value}',
    Command.Resend: f'~{Command.Resend.value}'
}

COMMAND_DESCRIPTIONS = {
    Command.Help: 'Display this help message',
    Command.Track: 'Change the user to check games for',
    Command.Untrack: 'Disable tracking',
    Command.PersonOfInterest: [
        'Add or remove a person to include in stats', 'List the current people of interest'],
    Command.Status: 'Get the tracking status',
    Command.Resend: 'Resend the summary for the last match.'
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

                    try:
                        file = discord.File(io.BytesIO(imgkit.from_url(
                            item.url, False, options={
                                'format': item.format,
                                'width': 700
                            })), '{}.{}'.format(item.filename, item.format))
                    except OSError:
                        log('ERROR')
                        await message.delete()
                        await self.client.get_channel(item.channel_id).send(embed=discord.Embed(title=item.error_message, colour=EMBED_COLOURS[Level.Error]))

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

        if command[0] in [command.value for command in Command] and has_permission(message.author.id, command):

            if command_is(command, Command.Help):
                await self.do_help_command(message, command)
            elif command_is(command, Command.Track):
                await self.do_track_command(message, command)
            elif command_is(command, Command.Untrack):
                await self.do_untrack_command(message, command)
            elif command_is(command, Command.PersonOfInterest, SubCommand.List):
                await self.do_poi_list_command(message, command)
            elif command_is(command, Command.PersonOfInterest):
                await self.do_poi_command(message, command)
            elif command_is(command, Command.Status):
                await self.do_status_command(message, command)
            elif command_is(command, Command.Resend):
                await self.do_resend_command(message, command)

        else:
            await self.send_embed(
                message.channel, 'You don\'t have permission to use that!', Level.Error)

    async def send_embed(self, channel: discord.TextChannel, content: str, level: Level, description=None):
        await channel.send(embed=discord.Embed(title=content, colour=EMBED_COLOURS[level], description=description))

    async def do_help_command(self, message: discord.Message, args: list):
        commands = get_commands(message.author.id)

        description = ''

        for command in commands:
            base_command = command if type(command) == Command else command[0]
            usages = COMMAND_USAGES[base_command]

            if type(usages) == list:
                for entry in range(len(usages)):
                    description += '`' + \
                        COMMAND_USAGES[base_command][entry] + '`: ' + \
                        COMMAND_DESCRIPTIONS[base_command][entry] + '\n\n'
            elif type(usages) == str:
                description += '`' + \
                    COMMAND_USAGES[base_command] + '`: ' + \
                    COMMAND_DESCRIPTIONS[base_command] + '\n\n'

        await message.channel.send(embed=discord.Embed(title='Available Commands', description=description))

    async def do_track_command(self, message: discord.Message, args: list):
        if (len(args) == 1):
            response = requests.post(
                f'http://slimeweb/api/poi/tracking/{message.author.id}')

        if (len(args) == 2):
            # Should be syntax ~track NAME#TAG
            # or ~track DISCORD_ID

            player = args[1].split('#')

            if len(player) == 2:
                response = requests.post(
                    f'http://slimeweb/api/poi/tracking/{player[0]}/{player[1]}')

            elif len(player) == 1 and re.match('^\d+$', player[0][2:-1]):
                response = requests.post(
                    f'http://slimeweb/api/poi/tracking/{player[0][2:-1]}')
            else:
                await self.send_embed(message.channel, 'Invalid player', Level.Error)
                return

        if response.status_code >= 200 and response.status_code < 300:

            response_content = response.json()

            if response_content['changed']:
                await self.send_embed(
                    message.channel, 'Tracked player set to `{}#{}`'.format(response_content['person']['name'], response_content['person']['tag']), Level.Success)
            else:
                await self.send_embed(
                    message.channel, '`{}#{}` is already being tracked'.format(response_content['person']['name'], response_content['person']['tag']), Level.Warn)
        else:
            await self.send_embed(
                message.channel,
                f'There was a problem finding player `{args[1]}`',
                Level.Error,
                f'```Code: {str(response.status_code)}```' if '-v' in args else None)

    async def do_untrack_command(self, message: discord.Message, args: list):
        response = requests.delete('http://slimeweb/api/poi/tracking')

        if response.status_code >= 200 and response.status_code < 300:
            await self.send_embed(message.channel, f'Tracking has been disabled', Level.Success)
        else:
            await self.send_embed(
                message.channel,
                'Problem while trying to disable tracking',
                Level.Error,
                f'```Code: {str(response.status_code)}```' if '-v' in args else None)

    async def do_poi_command(self, message: discord.Message, args: list):
        if not len(args) >= 3 or args[1] not in ['add', 'remove']:
            await self.send_embed(message.channel, 'Can\'t read args\nUsage: `~poi add|remove <player name>#<player tagline>`', Level.Error)
            return

        player = args[2].split('#')

        if not len(player) == 2:
            await self.send_embed(message.channel, 'Invalid player', Level.Error)
            return

        if args[1] == 'add':
            response = requests.post(
                f'http://slimeweb/api/poi', json={'name': player[0], 'tag': player[1]})

            feedback = f'Created new person of interest `{args[2]}`'

        elif args[1] == 'remove':
            response = requests.delete(
                f'http://slimeweb/api/poi/destroy/{player[0]}/{player[1]}')

            feedback = f'Removed person of interest `{args[2]}`'

        if response.status_code >= 200 and response.status_code < 300:
            await self.send_embed(message.channel, feedback, Level.Success)
        else:

            if response.status_code == 409:
                feedback = f'Player `{args[2]}` is already of interest'
            else:
                feedback = f'There was a problem finding player `{args[2]}`'

            await self.send_embed(
                message.channel,
                feedback,
                Level.Error,
                f'```Code: {str(response.status_code)}```' if '-v' in args else None)

    async def do_poi_list_command(self, message: discord.Message, args: list):

        response = requests.get(f'http://slimeweb/api/poi')

        if response.status_code >= 200 and response.status_code < 300:
            content = ''
            people = sorted(response.json(), key=lambda person: person['name'])
            for person in people:
                content += (':green_circle:' if person['is_tracking'] else ':red_circle:') \
                    + ' {} `#{}`\n'.format(person['name'], person['tag'])

            await self.send_embed(
                message.channel,
                'People of Interest',
                Level.Success,
                content)
        else:
            await self.send_embed(
                message.channel,
                'There was a problem getting the list of people of interest',
                Level.Error,
                f'```Code: {str(response.status_code)}```' if '-v' in args else None)

    async def do_status_command(self, message: discord.Message, args: list):
        response = requests.get(f'http://slimeweb/api/poi/tracking/status')

        if response.status_code >= 200 and response.status_code < 300:
            content = response.json()
            status = content['status']

            if (status == 'active'):
                tracking_value = ':green_circle: Active'
            elif (status == 'idle'):
                tracking_value = ':orange_circle: Idle'
            elif (status == 'offline'):
                tracking_value = ':red_circle: Offline'
            else:
                tracking_value = ':grey_question: Unknown'

            embed = discord.Embed(
                title='Status', color=EMBED_COLOURS[Level.Success])
            embed.add_field(name='Tracking Status', value=tracking_value)
            embed.add_field(name='Tracked User', value=content['tracked_person']['name'] +
                            ' `#' + content['tracked_person']['tag'] + '`' if content['tracked_person'] else ':x:')

            await message.channel.send(embed=embed)
        else:
            await self.send_embed(
                message.channel,
                'There was a problem getting the status',
                Level.Error,
                f'```Code: {str(response.status_code)}```' if '-v' in args else None)

    async def do_resend_command(self, message: discord.Message, command: list):

        response = requests.get('http://slimeweb/api/bot/resend')

        if response.status_code < 200 or response.status_code >= 300:
            await self.send_embed(
                message.channel, 'There was a problem resending the match summary', Level.Error)

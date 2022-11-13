from enum import Enum
import requests

from typing import Optional


class Command(Enum):
    Help = 'help'
    Track = 'track'
    Untrack = 'untrack'
    PersonOfInterest = 'poi'
    Status = 'status'


class SubCommand(Enum):
    Add = 'add'
    Remove = 'remove'
    List = 'list'


class Role(Enum):
    Admin = 'admin'
    Trusted = 'trusted'
    User = 'user'


roles = {
    Role.Admin.value: [Command.Help, Command.Track, Command.Untrack, Command.PersonOfInterest, Command.Status],
    Role.Trusted.value: [Command.Help, Command.Track, Command.Untrack, (Command.PersonOfInterest, [SubCommand.Add, SubCommand.List])],
    Role.User.value: [Command.Help,
                      (Command.PersonOfInterest, [SubCommand.List])]
}


def get_commands(user):
    response = requests.get(f'http://slimeweb/api/discord/user/{user}')

    if response.status_code == 200:
        return roles[response.json()['role']]
    else:
        return roles[Role.User.value]


def has_permission(user, command):
    allowed = False
    for permissible in get_commands(user):
        if type(permissible) == Command:
            if command[0] == permissible.value:
                allowed = True
                break

        elif type(permissible) == tuple:
            if command[0] == permissible[0].value and len(command) > 1 in [sub.value for sub in permissible[1]]:
                allowed = True
                break

    return allowed


def command_is(requested: list[str], command: Command, sub_command: Optional[SubCommand] = None):
    if sub_command:
        return requested[0] == command.value and requested[1] and requested[1] == sub_command.value

    return requested[0] == command.value

from enum import Enum
import requests


class Command(Enum):
    Help = 'help'
    Track = 'track'
    Untrack = 'untrack'
    PersonOfInterest = 'poi'


class Role(Enum):
    Admin = 'admin'
    Trusted = 'trusted'
    User = 'user'


roles = {
    Role.Admin.value: [Command.Help, Command.Track, Command.Untrack, Command.PersonOfInterest],
    Role.Trusted.value: [Command.Help, Command.Track, Command.Untrack, Command.PersonOfInterest],
    Role.User.value: [Command.Help]
}


def get_commands(user):
    response = requests.get(f'http://slimeweb/api/discord/user/{user}')

    if response.status_code == 200:
        return roles[response.json()['role']]
    else:
        return roles[Role.User.value]


def has_permission(user, command):
    return command in [c.value for c in get_commands(user)]

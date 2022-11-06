
FILE = './bot.log'


def log(message):
    with open(FILE, 'w+') as file:
        file.write(str(message) + '\n')

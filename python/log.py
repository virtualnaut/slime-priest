
FILE = './bot.log'


def log(message):
    with open(FILE, 'a+') as file:
        file.write(str(message) + '\n')

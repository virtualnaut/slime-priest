from enum import Enum
from discord import Embed


class QueuedCommandCategory(Enum):
    SEND_TEXT = 0
    SEND_EMBED = 1
    SEND_HTML = 2


class AbstractQueuedCommand:
    category: QueuedCommandCategory


class SendTextCommand(AbstractQueuedCommand):
    category = QueuedCommandCategory.SEND_TEXT

    def __init__(self, message: str, channel_id: int):
        self.message = message
        self.channel_id = channel_id


class SendEmbedCommand(AbstractQueuedCommand):
    category = QueuedCommandCategory.SEND_EMBED

    def __init__(self, message: Embed, channel_id: int):
        self.message = message
        self.channel_id = channel_id


class SendHTMLCommand(AbstractQueuedCommand):
    category = QueuedCommandCategory.SEND_HTML

    def __init__(self, url: str, channel_id: int, filename: str, image_format: str, loading_message: str, error_message: str):
        self.url = url
        self.channel_id = channel_id
        self.filename = filename
        self.format = image_format
        self.loading_message = loading_message
        self.error_message = error_message


class Queue:
    contents: list[AbstractQueuedCommand] = []

    def enqueue(self, item):
        self.contents.append(item)

    def dequeue(self):
        if not self.empty():
            return self.contents.pop(0)

    def empty(self):
        return not len(self.contents)

    def length(self):
        return len(self.contents)

from enum import Enum


class QueuedCommandCategory(Enum):
    SEND = 0


class AbstractQueuedCommand:
    category: QueuedCommandCategory


class SendCommand(AbstractQueuedCommand):
    category = QueuedCommandCategory.SEND

    def __init__(self, message: str, channel_id: int):
        self.message = message
        self.channel_id = channel_id


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

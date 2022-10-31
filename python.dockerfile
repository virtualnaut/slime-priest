FROM python:3.9

RUN mkdir /bot
WORKDIR /bot

ADD ./python /bot

RUN pip install -r /bot/requirements.txt

ENTRYPOINT /bot/init.sh
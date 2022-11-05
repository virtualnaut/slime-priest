FROM python:3.9

RUN mkdir /bot
WORKDIR /bot

ADD ./python /bot

RUN pip install -r /bot/requirements.txt
RUN apt-get update && apt-get install -y wkhtmltopdf

ENTRYPOINT /bot/init.sh
FROM python:3.9-buster

RUN mkdir /bot
WORKDIR /bot

ADD ./python /bot

RUN pip install -r /bot/requirements.txt
RUN apt-get update && apt-get install -y wkhtmltopdf

##### Uncomment this to install the patched version of wkhtmltopdf
# RUN apt-get update
# RUN apt-get install xfonts-base -y
# RUN apt-get install xfonts-75dpi -y

# RUN cd ~
# RUN wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.buster_amd64.deb
# RUN dpkg -i wkhtmltox_0.12.6-1.buster_amd64.deb

ENTRYPOINT /bot/init.sh
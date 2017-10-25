FROM ubuntu:latest

RUN apt-get -y update
RUN apt-get -y install git
RUN apt-get -y install npm
RUN apt-get -y install nodejs-legacy
RUN apt-get -y install sqlite3
RUN apt-get clean

RUN git clone https://github.com/Cryptkeeper/Minetrack.git

WORKDIR /Minetrack

RUN git checkout prod

RUN npm install

RUN chmod +x scripts/start.sh

ADD data/config.json config.json
ADD data/servers.json servers.json

CMD ./scripts/start.sh

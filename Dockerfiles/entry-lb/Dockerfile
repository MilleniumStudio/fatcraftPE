FROM ubuntu:latest

ENV MYSQL_HOST "127.0.0.1"
ENV MYSQL_PORT 3306
ENV MYSQL_USER "root"
ENV MYSQL_PASS "root"
ENV MYSQL_DATA "database"

RUN apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys 3FA7E0328081BFF6A14DA29AA6A19B38D3D831EF && \
echo "deb http://download.mono-project.com/repo/ubuntu xenial main" | tee /etc/apt/sources.list.d/mono-official.list

RUN apt-get -y update && \
 apt-get -y install mono-complete && \
 apt-get clean

ADD install /home/minecraft

WORKDIR /home/minecraft

CMD ["mono", "FatForward.exe"]
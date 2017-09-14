FROM ubuntu:latest

EXPOSE 19132/tcp
EXPOSE 19132/udp

ENV DEBIAN_FRONTEND noninteractive
ENV SERVER_PORT 19132
ENV SERVER_NAME "pocketmine-base"
ENV SERVER_TYPE "type"
ENV SERVER_ID 0

# app vars
ENV APP_DIR /home/minecraft
ENV LOGFILE $APP_DIR/server.log

ADD settings/bashrc /etc/bash.bashrc
ADD settings/bashrc /etc/skel/.bashrc
ADD settings/vimrc /etc/vim/vimrc
ADD settings/profile /etc/profile
ADD settings/motd /etc/motd

# Link vi to vim (otherwise ric no happy)
RUN mkdir /home/minecraft/ && ln -sf vim /usr/bin/vi

# Set Root to bash not ash and overwrite .bashrc
RUN sed -i 's/root:\/bin\/ash/root:\/bin\/bash/' /etc/passwd && \
    cp /etc/skel/.bashrc /root/.bashrc
RUN sed -i 's/minecraft:\/bin\/ash/minecraft:\/bin\/bash/' /etc/passwd && \
    cp /etc/skel/.bashrc /home/minecraft/.bashrc

RUN rm -rf /tmp/* /var/tmp/* /var/cache/apk/*

WORKDIR /home/minecraft

RUN apt-get -y update && \
 apt-get -y install curl make bison libtool-bin autoconf bzip2 g++ inetutils-ping screen netcat && \
 apt-get clean

ADD install /home/minecraft

CMD ./restarter.sh

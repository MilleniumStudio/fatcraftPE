FROM ubuntu:latest

EXPOSE 19132/tcp
EXPOSE 19132/udp

ENV SERVER_PORT 19132
ENV SERVER_NAME auto

# app vars
ENV JAVA_APP_DIR /home/minecraft
ENV LOGFILE $JAVA_APP_DIR/server.log

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
 apt-get -y install wget && \
 wget -q https://raw.githubusercontent.com/PocketMine/php-build-scripts/master/installer.sh && \
 chmod +x installer.sh && \
 ./installer.sh -r && \
 rm installer.sh  && \
 apt-get -y remove wget && \
 apt-get clean

ADD install /home/minecraft

CMD ./start.sh --no-wizard --server-port=$SERVER_PORT --server-name=$SERVER_NAME

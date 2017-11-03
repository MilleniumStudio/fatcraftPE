FROM mono:5.4-slim

ENV SERVER_PORT 19132

ENV MYSQL_HOST "127.0.0.1"
ENV MYSQL_PORT 3306
ENV MYSQL_USER "root"
ENV MYSQL_PASS "root"
ENV MYSQL_DATA "database"

ADD install /home/minecraft

CMD mono Program.exe
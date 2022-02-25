# https://hub.docker.com/r/linuxserver/libreoffice
FROM linuxserver/libreoffice:7.2.2

# https://github.com/unoconv/unoconv#installing-unoconv
# RUN apk add --update --no-cache unoconv npm

# https://github.com/sfoxdev/docker-unoconv-alpine/blob/master/Dockerfile
ENV UNO_URL https://raw.githubusercontent.com/dagwieers/unoconv/master/unoconv

RUN apk add --update --no-cache npm \
    && wget $UNO_URL -O /usr/local/bin/unoconv \
    && chmod +x /usr/local/bin/unoconv \
    && ln -s /usr/bin/python3 /usr/bin/python

# Unoconv is deprecated (cf.
# https://github.com/unoconv/unoconv#unoconv-is-deprecated), but works great. In
# the future we should use https://github.com/unoconv/unoserver/ and
# https://github.com/damian66/node-unoserver

# https://github.com/unoconv/unoserver#installation
# https://stackoverflow.com/a/62555259
# ENV PYTHONUNBUFFERED=1
# RUN apk add --update --no-cache python3 npm \
#     && python3 -m ensurepip \
#     && pip3 install --no-cache --upgrade pip unoserver

EXPOSE 9980

WORKDIR /app

COPY app/server.js app/package*.json /app
RUN npm install

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

CMD [ "docker-entrypoint.sh" ]

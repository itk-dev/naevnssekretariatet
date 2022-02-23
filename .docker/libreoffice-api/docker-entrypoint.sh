#!/bin/sh
set -e

# https://github.com/unoconv/unoserver#usage
# unoserver &

# Start unoconv
# https://github.com/unoconv/unoconv#start-your-own-unoconv-listener
unoconv --listener &
# sleep 20

# Start the api server
node server.js

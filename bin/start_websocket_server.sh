#!/bin/bash
# http://stackoverflow.com/questions/27062668/shell-script-with-a-cron-job-to-start-a-program-if-not-running/27063586#27063586
# http://stackoverflow.com/a/27063586
cd "$(dirname "$0")"
PF='./pidfile'
if kill -0 $(< "$PF"); then # process in pidfile is yours and running
    exit 0
else
    echo "starting websocket server..."
    echo $$ > "$PF"
    exec ./wgs_server.sh
fi

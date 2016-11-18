#!/bin/bash
cd "$(dirname "$0")"
cd ../../../
php module/Websockets/server/GWS_ServerMain.php

#!/bin/bash

# Tor proxy
proxy="127.0.0.1:9051"

min=3
max=10

while :
do
    sudo /etc/init.d/tor reload && /usr/bin/php -f vote.php -- --id="$1" --proxy="$proxy"

    if [ $? -ne 0 ]; then
        echo "[FAIL] Exit code is: $?"
    fi

    r=$(( $RANDOM % ($max - $min +1) + $min ))
    seconds=$(( $r * 60));

    echo "Waiting: $r min"
    sleep "$seconds"
done

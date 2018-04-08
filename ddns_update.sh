#!/bin/sh

ipv4_address=$(curl -s -4 http://ifconfig.co)
ipv6_address=$(curl -s -6 http://ifconfig.co)

curl --data-urlencode host=abcdef \
	--data-urlencode pass=XXXXXXXXX \
        --data-urlencode ipv4_address=$ipv4_address \
	--data-urlencode ipv6_address=$ipv6_address \
	https://abc.exmaple.com/ddns/update.php

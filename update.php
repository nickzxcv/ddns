<?php
# PHP script for very simple dynamic DNS updates 
#
# this script was published in http://pablohoffman.com/articles and 
# released to the public domain by Pablo Hoffman on 27 Aug 2006
#
# support for IPv4 and IPv6 by Nick Schmalenberger on April 8, 2018

# CONFIGURATION BEGINS -------------------------------------------------------
# define host and passwords here 
$hosts = array(
    'abcdef' => 'XXXXXXXXX',
);
$zone = "ddns.example.com"; # the dynamic DNS zone
$dnsserver = "abc.example.com"; # authorative DNS server for the zone above
$keyfile="somefile.key"; # the dns update key file
# CONFIGURATION ENDS ---------------------------------------------------------


$remote_ip = $_SERVER['REMOTE_ADDR'];
$host = $_POST['host'];
$pass = $_POST['pass'];
$ipv4_address = $_POST['ipv4_address'];
$ipv6_address = $_POST['ipv6_address'];
$tmpfile = trim(`mktemp /tmp/nsupdate.XXXXXX`);

if ((!$host) or (!$pass) or (!($hosts[$host] == $pass))) { 
    echo "FAILED";
    exit; 
}

if (!$ipv4_address) {
    if (filter_var($remote_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPv4)) {
        $ipv4_address=$remote_ip;
    }
}

if (!$ipv6_address) {
    if (filter_var($remote_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPv6)) {
        $ipv6_address=$remote_ip;
    }
}

$old_ipv4 = trim(`host -t A $host.$zone | cut -d ' ' -f 4`);
$old_ipv6 = trim(`host -t AAAA $host.$zone | cut -d ' ' -f 5`);
if ($ipv4_address and $ipv4_address != $old_ipv4) {
    $change_ipv4=TRUE;
}
if ($ipv6_address and $ipv6_address != $old_ipv6) {
    $change_ipv6=TRUE;
}

$nsucmd = "server $dnsserver\nzone $zone\n";

if ($change_ipv4) {
    echo "IPv4: $ipv4_address\n";
    $nsucmd = $nsucmd . "update delete $host.$zone A\nupdate add $host.$zone 3600 A $ipv4_address\n";
}
if ($change_ipv6) {
    echo "IPv6: $ipv6_address\n";
    $nsucmd = $nsucmd . "update delete $host.$zone AAAA\nupdate add $host.$zone 3600 AAAA $ipv6_address\n";
}

$nsucmd = $nsucmd . "send\n";

$fp = fopen($tmpfile, 'w');
fwrite($fp, $nsucmd);
fclose($fp);
`/usr/bin/nsupdate -k $keyfile $tmpfile`;
unlink($tmpfile);
echo "OK\n";
?>

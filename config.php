<?php

//      Domain,        Provider, Parameter
// e.g. "example.com", "NOIP",   "Token"

$domains = array (
    array("example.com", "ZONE", "/path/to/zone_directory"),
    array("example.net", "DNSOWL", "apikey"),
);

//       Username, Password,   Host
// e.g. "John",    "12345678", "john.example.com"

$users = array (
    array("username1", "password1", "*.example.com"),
    array("username2", "password2", "wild*.example.com"),
    array("username3", "password3", "subdomain.example.net"),
);

?>
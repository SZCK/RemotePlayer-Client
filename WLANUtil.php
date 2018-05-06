#!/usr/bin/php
<?php

    // WLAN Config Util
    
    function SetupWLAN($ssid, $password){
        system("sudo wpa_passphrase $ssid $password >> /etc/wpa_supplicant/wpa_supplicant.conf");
        system("sudo wpa_cli reconfigure");
    }
    
    $ssid = $argv[1]; $pasword = $argv[2];
    SetupWLAN($ssid, $password);

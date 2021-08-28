<?php

$secure_gate_check=true;

if ($_SERVER['SCRIPT_FILENAME']=='/usr/local/vesta/web/inc/mail-wrapper.php') $secure_gate_check=false; // it can be executed only from cli

if ($_SERVER['SCRIPT_FILENAME']=='/usr/local/vesta/web/reset/mail/index.php') $secure_gate_check=false; // it's accessible only from localhost
if ($_SERVER['SCRIPT_FILENAME']=='/usr/local/vesta/web//reset/mail/index.php') $secure_gate_check=false;

if ($_SERVER['SCRIPT_FILENAME']=='/usr/local/vesta/web/api/index.php') $secure_gate_check=false; // api has its own security check
if ($_SERVER['SCRIPT_FILENAME']=='/usr/local/vesta/web//api/index.php') $secure_gate_check=false;

if ($_SERVER['SCRIPT_FILENAME']=='/usr/local/vesta/web/reset/mail/set-ar.php') $secure_gate_check=false; // commercial addon for changing auto-reply from Roundcube, not included in this fork, also accessible only from localhost
if ($_SERVER['SCRIPT_FILENAME']=='/usr/local/vesta/web//reset/mail/set-ar.php') $secure_gate_check=false;
if ($_SERVER['SCRIPT_FILENAME']=='/usr/local/vesta/web/reset/mail/get-ar.php') $secure_gate_check=false;
if ($_SERVER['SCRIPT_FILENAME']=='/usr/local/vesta/web//reset/mail/get-ar.php') $secure_gate_check=false;
if (substr($_SERVER['SCRIPT_FILENAME'], 0, 28)=='/usr/local/vesta/web/custom/') $secure_gate_check=false; // custom scripts like git webhooks
if (substr($_SERVER['SCRIPT_FILENAME'], 0, 29)=='/usr/local/vesta/web//custom/') $secure_gate_check=false;

if (substr($_SERVER['SCRIPT_FILENAME'], 0, 21)=='/usr/local/vesta/bin/') $secure_gate_check=false; // allow executing v-* PHP scripts from bash
if (substr($_SERVER['SCRIPT_FILENAME'], 0, 29)=='/usr/local/vesta/softaculous/') $secure_gate_check=false; // allow softaculous
if (substr($_SERVER['SCRIPT_FILENAME'], 0, 33)=='/usr/local/vesta/web/softaculous/') $secure_gate_check=false; // allow softaculous
if (substr($_SERVER['SCRIPT_FILENAME'], 0, 34)=='/usr/local/vesta/web//softaculous/') $secure_gate_check=false; // allow softaculous

$check_file="/usr/local/vesta/conf_web/allow_ip_for_secret_url.conf";
if (file_exists($check_file)) {
    $file_content=file($check_file);
    if (is_array($file_content)) {
        foreach ($file_content as $line) {
            if (trim($line) == $_SERVER['REMOTE_ADDR']) {$secure_gate_check=false; break;}
        }
    }
}

if ($secure_gate_check==true) {
    if (!isset($login_url_loaded)) {
        $login_url_loaded=1;
        if (file_exists('/usr/local/vesta/web/inc/login_url.php')) {
            require_once('/usr/local/vesta/web/inc/login_url.php'); // get secret url
            if (isset($_GET[$login_url])) {                         // check if user opened secret url
                $Domain=$_SERVER['HTTP_HOST'];
                $Port = strpos($Domain, ':');
                if ($Port !== false)  $Domain = substr($Domain, 0, $Port);
                setcookie($login_url, '1', time() + 31536000, '/', $Domain, true); // set secret cookie
                header ("Location: /login/");
                exit;
            }
            if (!isset($_COOKIE[$login_url])) exit; // die if secret cookie is not set
        }
    }
}

// Preventing all CSRF
if ($secure_gate_check==true) {
    if ($_SERVER['REQUEST_METHOD']=='POST') {
        $host_arr=explode(":", $_SERVER['HTTP_HOST']);
        $hostname=$host_arr[0];
        $port = $_SERVER['SERVER_PORT'];
        $expected_http_origin="https://".$hostname.":".$port;
        if ($_SERVER['HTTP_ORIGIN'] != $expected_http_origin) {
            die ("Nope.");
        }
    }
}

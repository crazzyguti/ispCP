<?php
//   -------------------------------------------------------------------------------
//  |             VHCS(tm) - Virtual Hosting Control System                         |
//  |              Copyright (c) 2001-2006 by moleSoftware		                    |
//  |     http://vhcs.net | http://www.molesoftware.com           				    |
//  |                                                              	                |
//  | This program is free software; you can redistribute it and/or                 |
//  | modify it under the terms of the MPL General Public License                   |
//  | as published by the Free Software Foundation; either version 1.1              |
//  | of the License, or (at your option) any later version.                        |
//  |                                                                               |
//  | You should have received a copy of the MPL Mozilla Public License             |
//  | along with this program; if not, write to the Open Source Initiative (OSI)    |
//  | http://opensource.org | osi@opensource.org                    				|
//  |                                                                               |
//   -------------------------------------------------------------------------------




include_once (realpath($include_path.'/adodb/adodb.inc.php'));

//include_once (realpath($include_path.'/adodb/tohtml.inc.php'));

include_once (realpath($include_path.'/adodb/adodb-pager.inc.php'));

$cfg['DB_TYPE'] = $cfg['DATABASE_TYPE'];

$cfg['DB_HOST'] = $cfg['DATABASE_HOST'];

$cfg['DB_USER'] = $cfg['DATABASE_USER'];

$cfg['DB_PASS'] = decrypt_db_password($cfg['DATABASE_PASSWORD']);

$cfg['DB_NAME'] = $cfg['DATABASE_NAME'];


if ($cfg['DB_TYPE'] === 'pgsql') {

  $sql = &ADONewConnection('postgres7');

} else if ($cfg['DB_TYPE'] === 'mysql') {

  $sql = &ADONewConnection('mysql');

}

@$sql -> Connect($cfg['DB_HOST'], $cfg['DB_USER'], $cfg['DB_PASS'], $cfg['DB_NAME']) OR

	system_message('ERROR: Unable to connect MySQL server !<br>MySQL returns: '.$sql -> ErrorMsg() );

/* No longer needed */
unset($cfg['DB_USER']);

unset($cfg['DB_PASS']);
/* unset for safety */

function execute_query (&$sql, $query) {
  check_query($query);
  $rs = $sql -> Execute($query);
  if (!$rs) system_message($sql -> ErrorMsg());
  return $rs;
}

function exec_query(&$sql, $query, $data) {
  check_query($query);
  $stmt = $sql->Prepare($query);
  $rs = $sql->Execute($query, $data);
  if (!$rs) system_message($sql->ErrorMsg());
  return $rs;
}

function quoteIdentifier($identifier)
{
  global $cfg;

  switch ($cfg['DB_TYPE']) {
    case 'pgsql':
      return '"' . $identifier . '"';
    case 'mysql':
      return '`' . $identifier . '`';
    default: // is there a standard?
      return $identifier;
  }
}

function pg_get_record_id(&$sql, $table, $oid) {

  $query = "select id from $table where oid = '$oid'";

  $rs = execute_query($sql, $query);

  return $rs -> fields['id'];

}

function match_sqlinjection($value) {
	if (preg_match("/((DELETE)|(INSERT)|(UPDATE)|(ALTER)|(CREATE)|( TABLE)|(DROP))\s[A-Za-z0-9 ]{0,200}(\s(FROM)|(INTO)|(TABLE)\s)/i", $value, $imatch)>0)
		return true; else return false;
}

function check_query() {
	if (phpversion() > '4.2.2') {
		foreach($_REQUEST as $key=>$value) {
			if (!is_array($value)) {
				if (match_sqlinjection($value)) {
					write_log("Possible SQL injection detected: $key=>$value. <b>$imatch[0]</b> Script terminated.");
					system_message("Possible SQL injection detected: $key=>$value. <b>$imatch[0]</b> Script terminated.");
					die();
				}
			} else {
				foreach($value as $key1=>$val) {
					if (!is_array($val)) {
						if (match_sqlinjection($val)) {
							write_log("Possible SQL injection detected: $key=>$val <b>$imatch[0]</b>.  Script terminated.");
							system_message("Possible SQL injection detected: $key=>$val <b>$imatch[0]</b>.  Script terminated.");
							die();
						}
					}
				}
			}
		}
	}
}
?>

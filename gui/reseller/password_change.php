<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2011 by ispCP | http://isp-control.net
 * @version 	SVN: $Id$
 * @link 		http://isp-control.net
 * @author 		ispCP Team
 *
 * @license
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 * Portions created by the ispCP Team are Copyright (C) 2006-2011 by
 * isp Control Panel. All Rights Reserved.
 */

require '../include/ispcp-lib.php';

check_login(__FILE__);

$cfg = ispCP_Registry::get('Config');

$tpl = ispCP_TemplateEngine::getInstance();
$template = 'password_change.tpl';

if (isset($_POST['uaction']) && $_POST['uaction'] === 'updt_pass') {
	if (empty($_POST['pass']) || empty($_POST['pass_rep']) || empty($_POST['curr_pass'])) {
		set_page_message(tr('Please fill up all data fields!'), 'warning');
	} else if ($_POST['pass'] !== $_POST['pass_rep']) {
		set_page_message(tr('Passwords do not match!'), 'warning');
	} else if (!chk_password($_POST['pass'])) {
		if ($cfg->PASSWD_STRONG) {
			set_page_message(
				sprintf(
					tr('The password must be at least %s chars long and contain letters and numbers to be valid.'),
					$cfg->PASSWD_CHARS
				),
				'warning'
			);
		} else {
			set_page_message(
				sprintf(
					tr('Password data is shorter than %s signs or includes not permitted signs!'),
					$cfg->PASSWD_CHARS
				),
				'warning'
			);
		}
	} else if (check_udata($_SESSION['user_id'], $_POST['curr_pass']) === false) {
		set_page_message(tr('The current password is wrong!'), 'error');
	} else {
		// Correct input password
		$upass = crypt_user_pass(htmlentities($_POST['pass']));

		$_SESSION['user_pass'] = $upass;

		$user_id = $_SESSION['user_id'];
		// Begin update admin-db
		$query = "
			UPDATE
				`admin`
			SET
				`admin_pass` = ?
			WHERE
				`admin_id` = ?
		";

		$rs = exec_query($sql, $query, array($upass, $user_id));

		set_page_message(tr('User password updated successfully!'), 'success');
	}
}

// static page messages
gen_logged_from($tpl);

$tpl->assign(
	array(
		'TR_PAGE_TITLE'			=> tr('ispCP - Reseller/Change Password'),
		'TR_CHANGE_PASSWORD' 	=> tr('Change password'),
		'TR_PASSWORD_DATA' 		=> tr('Password data'),
		'TR_PASSWORD' 			=> tr('Password'),
		'TR_PASSWORD_REPEAT' 	=> tr('Repeat password'),
		'TR_UPDATE_PASSWORD' 	=> tr('Update password'),
		'TR_CURR_PASSWORD' 		=> tr('Current password'),
		// The entries below are for Demo versions only
		'PASSWORD_DISABLED'		=> tr('Password change is deactivated!'),
		'DEMO_VERSION'			=> tr('Demo Version!')
	)
);

gen_reseller_mainmenu($tpl, 'main_menu_general_information.tpl');
gen_reseller_menu($tpl, 'menu_general_information.tpl');

gen_page_message($tpl);

$tpl->display($template);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug();
}
unset_messages();

function check_udata($id, $pass) {
	$sql = ispCP_Registry::get('Db');

	$query = "
		SELECT
			`admin_id`, `admin_pass`
		FROM
			`admin`
		WHERE
			`admin_id` = ?
		AND
			`admin_pass` = ?
	";

	$rs = exec_query($sql, $query, array($id, md5($pass)));

	return (($rs->recordCount()) != 1) ? false : true;
}
?>
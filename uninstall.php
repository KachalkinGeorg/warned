<?php
// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

include_once(root . "/plugins/warned/lib/common.php");

$db_update = array(
	array(
		'table'  => 'users',
		'action' => 'modify',
		'fields' => array(
			array('action' => 'drop', 'name' => 'warn_ban'),
		)
	),

	array(
		'table'  => 'warned',
		'action' => 'drop',
	)
);

if ($_REQUEST['action'] == 'commit') {
	if (fixdb_plugin_install('warned', $db_update, 'deinstall')) {
		plugin_mark_deinstalled('warned');
	}
	remove_warned_urls();
} else {
	$text = 'Cейчас плагин будет удален.<br>Внимание!<br>При удалении плагина, пользователям, которым была выдано предупреждения и получивший бан, будут разбанены.<br> Вы уверены?';
	generate_install_page('warned', $text, 'deinstall');
}
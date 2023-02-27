<?php
if (!defined('NGCMS'))
{
	die ('HAL');
}

LoadPluginLang('warned', 'config', '', '', '#');

include_once(root . "/plugins/warned/lib/common.php");

pluginsLoadConfig();
function plugin_warned_install($action) {
	global $lang;
	
	if ($action != 'autoapply')
	
	$db_update = array(
		array(
			'table'  => 'users',
			'action' => 'cmodify',
			'fields' => array(
				array('action' => 'cmodify', 'name' => 'warn_ban', 'type' => 'tinyint(1)', 'params' => "default '0'"),
			)
		),
		array(
			'table'  => 'warned',
			'action' => 'cmodify',
			'key'    => 'primary key (id)',
			'fields' => array(
				array('action' => 'cmodify', 'name' => 'id', 'type' => 'int(8)', 'params' => 'NOT NULL AUTO_INCREMENT'),
				array('action' => 'cmodify', 'name' => 'warn_user', 'type' => 'int(10)', 'params' => 'NOT NULL'),
				array('action' => 'cmodify', 'name' => 'warn_text', 'type' => 'blob', 'params' => 'NOT NULL'),
				array('action' => 'cmodify', 'name' => 'warn_from', 'type' => 'varchar(50)', 'params' => 'NOT NULL'),
				array('action' => 'cmodify', 'name' => 'time', 'type' => 'int(10)', 'params' => 'UNSIGNED NOT NULL')
			)
		)
	);
	
	switch ($action) {
		case 'confirm':
			generate_install_page('warned', $lang['warned']['install']);
			break;
		case 'autoapply':
		case 'apply':
			if (fixdb_plugin_install('warned', $db_update, 'install', ($action == 'autoapply') ? true : false)) {
				plugin_mark_installed('warned');
				create_warned_urls();
			} else {
				return false;
			}
			
            $params = array(
                'warned_pm' 	=> '0',
				'warned_pm_del' => '0',
				'warn_num_ban' 	=> '3',
                'num_news' 		=> '10',
				'acces' 		=> 'Вам было выдано несколько предупреждений, которые Вы проигнорировали, из-за чего доступ к сайту был заблокирован автоматически. Проверьте личные сообщения о причине предупреждений, обратитесь к администратору и возможно Вам откроют доступ.',
            );
            foreach ($params as $k => $v) {
                extra_set_param('warned', $k, $v);
            }
            extra_commit_changes();
			
			break;
	}

	return true;
}
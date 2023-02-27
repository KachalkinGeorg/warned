<?php

// Protect against hack attempts
if (!defined('NGCMS')) die ('HAL');

LoadPluginLang('warned', 'main', '', '', '#');

register_plugin_page('warned','user_id','warn');

function warn($params){
	global $tpl, $template, $twig, $mysql, $SYSTEM_FLAGS, $config, $userROW, $lang, $CurrentHandler;
	
	$warned_user = isset($params['id'])?abs(intval($params['id'])):abs(intval($_REQUEST['id']));
	
	$row = $mysql->record('SELECT * FROM '.uprefix.'_users WHERE id = '.$warned_user.' LIMIT 1');
	
	$SYSTEM_FLAGS['info']['title']['group'] = 'Предупреждение пользователю '.$row['name'].'';

	if($warned_user == 0){
		return msg(array("type" => "info", "text" => 'Ошибка!!! Не выбрано кого предупреждать.<br><a href=/>Вернуться</a><br>'));
	}

	$num_w = 'SELECT * FROM '.prefix.'_warned WHERE warn_user = '. $warned_user.' ORDER BY id DESC';

	$num_warn = 0;
	foreach ($mysql->select($num_w) as $row_w) {
		$num_warn++;
	}
	$warn_num_b = pluginGetVariable('warned', 'warn_num_ban') ? pluginGetVariable('warned', 'warn_num_ban') : '3';
	if ($warn_num_b > $num_warn){
		$num_warned = 'Имеются предупреждений - '.$num_warn.'';
		}else{
		$num_warned = 'Бан уже выдан.';
	}
	if (is_array($userROW) && ($userROW['status'] == 1 || $userROW['status'] == 2)) { 
	$warn = 'Внимание!<br>При достижении предупреждений <b style="color:red">'.$warn_num_b.'</b>, пользователю будет выдан автоматический бан.<br><br><b>'.$num_warned.'</b><br><br>
	<form action="" method="post">
	<b>Введите текст предупреждения для <b style="color:red">'.$row['name'].'</b>:</b><br><br>
	<textarea name="warned_descr" cols="80" rows="7"></textarea><br>
	<br><input type="submit" value="Выписать предупреждение" name="warned_ok" class="btn">
	</form>';
	}

	if (isset($_REQUEST['warned_ok'])){
		
	$warned_descr = addslashes(trim($_REQUEST['warned_descr']));

	if ($warned_descr != ''){
		$time = time()+($config['date_adjust']*60);
		$mysql->query("INSERT INTO ".prefix."_warned (warn_user, warn_text, warn_from, time) values ('".$warned_user."', '".$warned_descr."', '".$userROW['name']."', '".$time."')");
		$ban='';
		$warn_num_ban = pluginGetVariable('warned', 'warn_num_ban') ? pluginGetVariable('warned', 'warn_num_ban') : '3';

		$num_wa = 'SELECT * FROM '.prefix.'_warned WHERE warn_user = '. $warned_user.' ORDER BY id DESC';

		$num_warn = 0;
		foreach ($mysql->select($num_wa) as $row_wa) {
			$num_warn++;
		}

		if ($num_warn == $warn_num_ban){
			$mysql->query("UPDATE ".uprefix."_users SET warn_ban='1' WHERE id='$warned_user'");
		}
		
		$warned_pm = pluginGetVariable('warned', 'warned_pm') ? pluginGetVariable('warned', 'warned_pm') : '0';
		if($warned_pm == 1){
			$mysql->query("INSERT INTO ".prefix."_pm (subject, message, from_id, to_id, date, viewed, folder) values ('Предупреждение!', 'Вам выписано предупреждение по следующей причине: <br><font color=red>".$warned_descr."</font> <br>ВНИМАНИЕ!<br>Есть вероятность получить автоматическую блокировку Вашего аккаунта.', '".$userROW['id']."', '$warned_user', '$time', '0', 'inbox')");
			$mysql->query("UPDATE ".prefix."_users set pm_all=pm_all+1, pm_unread=pm_unread+1  where id='$warned_user'");
		}

		return msg(array("type" => "info", "text" => 'Предупреждения выписаны<br><a href=/>Вернуться</a><br>'));
	}else{
		return msg(array('type' => 'error', 'text' => 'Не введён текст предупреждения. <a href=/warned/user_id/'.$warned_user.'/>Вернуться</a><br>'));
		
	}
	
	}
	
	if (isset($userROW['id']) && (intval($userROW['id']) > 0)) {
		$template['vars']['mainblock'] = $warn;
	}
}



LoadPluginLibrary('uprofile', 'lib');
loadPluginLibrary('comments', 'lib');

class WarnedUserFilter extends p_uprofileFilter {

	function showProfile($userID, $SQLrow, &$tvars) {
		global $lang, $template, $mysql, $twig;

	$tpath = locatePluginTemplates(array('skins/warned','skins/warned_entries'), 'warned', pluginGetVariable('warned', 'localsource'));
	$xt = $twig->loadTemplate($tpath['skins/warned_entries'].'skins/warned_entries.tpl');
	
	$warn = 'SELECT warn_text, warn_from, time FROM '.prefix.'_warned WHERE warn_user = '. $userID.' ORDER BY id DESC';
	$users = $mysql->select($warn);
	
	$num_warn = 0;
	foreach ($users as $row_w) {
		$tVars = array(
			'time' => date("d.m.y H:i:s", $row_w['time']),
			'warn_text' => $row_w['warn_text'],
			'warn_from' => $row_w['warn_from'],
		);
	
		$entries .= $xt->render($tVars);
		$num_warn++;
	}
	
	$xg = $twig->loadTemplate($tpath['skins/warned'].'skins/warned.tpl');

	$tVars = array(
		'num_warn' 	=> $num_warn,
		'entries' => $entries
	);
	
	$tvars['user']['warned'] = $xg->render($tVars);
	
	}
}

pluginRegisterFilter('plugin.uprofile', 'warned', new WarnedUserFilter);

class WarnedCommentsFilter extends FilterComments  {

	function showComments($newsID, $commRec, $comnum, &$tvars) {
		global $userROW;
		
	if (is_array($userROW) && ($userROW['status'] == 1 || $userROW['status'] == 2)) { 
		$warlink = '<a href="/warned/user_id/'.$commRec['author_id'].'/">Предупреждение</a>'; 
	}else{
		$warlink = '';
	}
	
	$tvars['vars']['warlink'] = $warlink;
	
	}
	
}
	
pluginRegisterFilter('comments', 'warned', new WarnedCommentsFilter);


class WarnedAccessNewsFilter extends NewsFilter  {

	function onAfterShow($mode) {

		global $mysql, $template, $userROW;
		
		$row = $mysql->record('SELECT * FROM '.uprefix.'_users WHERE id = '. db_squote($userROW['id']) .' LIMIT 1');
		$acces = pluginGetVariable('warned', 'acces') ? pluginGetVariable('warned', 'acces') : 'Доступ запрещен';
		if ($row['warn_ban'] == 1) $template['vars']['mainblock'] = $acces;
		
		return 1;
	}
	
	function onAfterNewsShow($newsID, $SQLnews, $mode = array()) {

		global $mysql, $template, $userROW;
		
		$row = $mysql->record('SELECT * FROM '.uprefix.'_users WHERE id = '. db_squote($userROW['id']) .' LIMIT 1');
		$acces = pluginGetVariable('warned', 'acces') ? pluginGetVariable('warned', 'acces') : '';
		if ($row['warn_ban'] == 1) $template['vars']['mainblock'] = $acces;

		return 1;
	}
}

register_filter('news', 'warned', new WarnedAccessNewsFilter);
?>
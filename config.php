<?php

if (!defined('NGCMS')) die ('HAL');

pluginsLoadConfig();
LoadPluginLang('warned', 'config', '', '', '#');

switch ($_REQUEST['action']) {
	case 'about':			about();		break;
	case 'list_ban':		list_ban();		break;
	case 'clear':			clear();		break;
	default: main();
}

function clear()
{
	global $mysql, $lang, $userROW;
	$clear = (int)$_REQUEST['clear'];
	
	$warn_user = $mysql->record('SELECT * FROM '.prefix.'_warned WHERE id = '.$clear.' LIMIT 1');
	
	if(pluginGetVariable('warned', 'warned_pm_del') == 1){
		$time = time()+($config['date_adjust']*60);
		$mysql->query("INSERT INTO ".prefix."_pm (subject, message, from_id, to_id, date, viewed, folder) values ('Снято предупреждение', 'Предупреждение: <font color=red>".$warn_user['warn_text']."</font><br>Было удалено администрацией!<br>Ваш аккаунт разблокирован.', '".$userROW['id']."', '".$warn_user['warn_user']."', '$time', '0', 'inbox')");
		$mysql->query("UPDATE ".uprefix."_users set pm_all=pm_all+1, pm_unread=pm_unread+1 where id='".$warn_user['warn_user']."'");
	}

	$mysql->query("delete from ".prefix."_warned where id = ".$clear."");
	$mysql->query("UPDATE ".uprefix."_users SET warn_ban='0' WHERE id='".$warn_user['warn_user']."'");
	
	$row = $mysql->record('SELECT * FROM '.uprefix.'_users WHERE id = '.$warn_user['warn_user'].' LIMIT 1');
 	msg(array("type" => "info", "text" => $lang['warned']['cleares']));
	return print_msg( 'delete', ''.$lang['warned']['warned'].'', 'Предупреждение для '.$row['name'].' с причиной <text style="color:red">'.$warn_user['warn_text'].'</text> было снято успешно.', 'javascript:history.go(-1)' );
}

function about()
{global $twig, $lang, $breadcrumb;
	$tpath = locatePluginTemplates(array('main', 'about'), 'warned', 1);
	$breadcrumb = breadcrumb('<i class="fa fa-universal-access btn-position"></i><span class="text-semibold">'.$lang['warned']['warned'].'</span>', array('?mod=extras' => '<i class="fa fa-puzzle-piece btn-position"></i>'.$lang['extras'].'', '?mod=extra-config&plugin=warned' => '<i class="fa fa-universal-access btn-position"></i>'.$lang['warned']['warned'].'',  '<i class="fa fa-exclamation-circle btn-position"></i>'.$lang['warned']['about'].'' ) );

	$xt = $twig->loadTemplate($tpath['about'].'about.tpl');
	$tVars = array();
	$xg = $twig->loadTemplate($tpath['main'].'main.tpl');
	
	$about = 'версия 0.1';
	
	$tVars = array(
		'global' => 'О плагине',
		'header' => $about,
		'entries' => $xt->render($tVars)
	);
	
	print $xg->render($tVars);
}

function list_ban()
{global $twig, $mysql, $parse, $lang, $userROW, $breadcrumb;
	$tpath = locatePluginTemplates(array('main', 'list_ban', 'list_ban_entries'), 'warned', 1);
	$breadcrumb = breadcrumb('<i class="fa fa-universal-access btn-position"></i><span class="text-semibold">'.$lang['warned']['warned'].'</span>', array('?mod=extras' => '<i class="fa fa-puzzle-piece btn-position"></i>'.$lang['extras'].'', '?mod=extra-config&plugin=additional_news' => '<i class="fa fa-universal-access btn-position"></i>'.$lang['warned']['warned'].'',  ''.$lang['warned']['list'].'' ) );

	$news_per_page = pluginGetVariable('warned', 'num_news');
	
	if (($news_per_page < 2)||($news_per_page > 2000)) $news_per_page = 10;
	
	$pageNo = intval($_REQUEST['page'])?$_REQUEST['page']:0;
	if ($pageNo < 1) $pageNo = 1;
	if (!$start_from) $start_from = ($pageNo - 1)* $news_per_page;
	
	$count = $mysql->result('SELECT count(*) as count FROM '.prefix.'_warned');
	$countPages = ceil($count / $news_per_page);
	
	$ban = $mysql->select('SELECT w.id, w.warn_user, w.warn_text, w.warn_from, w.time, u.name FROM '.prefix.'_warned w inner JOIN '.prefix.'_users u ON (w.warn_user = u.id) order by w.id DESC LIMIT '.$start_from.', '.$news_per_page);

	foreach ($ban as $row){
		$xe = $twig->loadTemplate($tpath['list_ban_entries'].'list_ban_entries.tpl');

		$tVars = array (
			'id' => $row['id'],
			'warn_text' => $row['warn_text'],
			'warn_from' => $row['warn_from'],
			'time' => strftime('%d.%m.%Y %H:%M', $row['time']),
			'name' => '<a href="?mod=users&action=editForm&id='.$userROW['id'].'" target="_blank"/>'.$row['name'].'</a>',
			'del' => '<div class="btn-group btn-group-sm" role="group"><a class="btn btn-outline-danger" href="?mod=extra-config&plugin=warned&action=clear&clear='.$row['id'].'"><i class="fa fa-trash-o"></i></a></div>',

		);
		
		$entries .= $xe->render($tVars);
	}
	
	$xt = $twig->loadTemplate($tpath['list_ban'].'list_ban.tpl');
	
	$tVars = array(
		'pagesss' => generateAdminPagelist( array(	'current' => $pageNo,
													'count' => $countPages,
													'url' => '?mod=extra-config&plugin=warned&action=list_ban&page=%page%'
													)
		),
		'entries' => $entries 
	);
	
	$xg = $twig->loadTemplate($tpath['main'].'main.tpl');
	
	$tVars = array(
		'global' => 'Список выданных предупреждений',
		'header' => '<i class="fa fa-exclamation-circle"></i> <a href="?mod=extra-config&plugin=warned&action=about">'.$lang['warned']['about'].'</a>',
		'active2' => 'active',
		'entries' => $xt->render($tVars)
	);
	
	print $xg->render($tVars);
}

function main()
{global $twig, $lang, $breadcrumb;
	
	$tpath = locatePluginTemplates(array('main', 'general.from'), 'warned', 1);
	$breadcrumb = breadcrumb('<i class="fa fa-universal-access btn-position"></i><span class="text-semibold">'.$lang['warned']['warned'].'</span>', array('?mod=extras' => '<i class="fa fa-puzzle-piece btn-position"></i>'.$lang['extras'].'', '?mod=extra-config&plugin=warned' => '<i class="fa fa-universal-access btn-position"></i>'.$lang['warned']['warned'].'' ) );

	if (isset($_REQUEST['submit'])){
		pluginSetVariable('warned', 'warned_pm', intval($_REQUEST['warned_pm']));
		pluginSetVariable('warned', 'warned_pm_del', intval($_REQUEST['warned_pm_del']));
		pluginSetVariable('warned', 'warn_num_ban', $_REQUEST['warn_num_ban']);
		pluginSetVariable('warned', 'num_news', $_REQUEST['num_news']);
		pluginSetVariable('warned', 'acces', $_REQUEST['acces']);
		pluginsSaveConfig();
		msg(array("type" => "info", "info" => "сохранение прошло успешно"));
		return print_msg( 'info', ''.$lang['warned']['warned'].'', 'Cохранение прошло успешно', 'javascript:history.go(-1)' );
	}
	
	$warned_pm = pluginGetVariable('warned', 'warned_pm');
	$warned_pm = '<option value="0" '.($warned_pm==0?'selected':'').'>'.$lang['noa'].'</option><option value="1" '.($warned_pm==1?'selected':'').'>'.$lang['yesa'].'</option>';
	$warned_pm_del = pluginGetVariable('warned', 'warned_pm_del');
	$warned_pm_del = '<option value="0" '.($warned_pm_del==0?'selected':'').'>'.$lang['noa'].'</option><option value="1" '.($warned_pm_del==1?'selected':'').'>'.$lang['yesa'].'</option>';
	
	$warn_num_ban = pluginGetVariable('warned', 'warn_num_ban');
	$num_news = pluginGetVariable('warned', 'num_news');
	$acces = pluginGetVariable('warned', 'acces');
	
	$xt = $twig->loadTemplate($tpath['general.from'].'general.from.tpl');
	$xg = $twig->loadTemplate($tpath['main'].'main.tpl');
	
	$tVars = array(
		'warned_pm' => $warned_pm,
		'warned_pm_del' => $warned_pm_del,
		'warn_num_ban' => $warn_num_ban,
		'num_news' => $num_news,
		'acces' => $acces,
	);
	
	$tVars = array(
		'global' => 'Общие',
		'header' => '<i class="fa fa-exclamation-circle"></i> <a href="?mod=extra-config&plugin=warned&action=about">'.$lang['warned']['about'].'</a>',
		'active1' => 'active',
		'entries' => $xt->render($tVars)
	);
	
	print $xg->render($tVars);
}

?>
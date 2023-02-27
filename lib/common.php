<?php


function create_warned_urls()
{

 			$ULIB = new urlLibrary();
			$ULIB->loadConfig();
			$ULIB->registerCommand('warned', 'user_id',
				array ('vars' =>
						array(	'id' => array('matchRegex' => '\d+', 'descr' => array('russian' => 'Предупреждения')),
						),
						'descr'	=> array ('russian' => 'Выдать предупреждения'),
				)
			);
			$ULIB->saveConfig();
			
			$UHANDLER = new urlHandler();
			$UHANDLER->loadConfig();
			$UHANDLER->registerHandler(0,
				array (
				'pluginName' => 'warned',
				'handlerName' => 'user_id',
				'flagPrimary' => true,
				'flagFailContinue' => false,
				'flagDisabled' => false,
				'rstyle' => 
				array (
				  'rcmd' => '/warned/user_id/{id}/',
				  'regex' => '#^/warned/user_id/(\\d+)/$#',
				  'regexMap' => 
				  array (
					1 => 'id',
				  ),
				  'reqCheck' => 
				  array (
				  ),
				  'setVars' => 
				  array (
				  ),
				  'genrMAP' => 
				  array (
					0 => 
					array (
					  0 => 0,
					  1 => '/warned/user_id/',
					  2 => 0,
					),
					1 => 
					array (
					  0 => 1,
					  1 => 'id',
					  2 => 0,
					),
					2 => 
					array (
					  0 => 0,
					  1 => '/',
					  2 => 0,
					),
				  ),
				),
			  )
			);
    $UHANDLER->saveConfig();
}

function remove_warned_urls()
{
    $ULIB = new urlLibrary();
    $ULIB->loadConfig();
    $ULIB->removeCommand('warned', 'user_id');
    $ULIB->saveConfig();
    $UHANDLER = new urlHandler();
    $UHANDLER->loadConfig();
    $UHANDLER->removePluginHandlers('warned', 'user_id');
    $UHANDLER->saveConfig();
}

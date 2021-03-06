<?php
/*
	Redaxo-Addon SEO-CheckUp
	Boot (weitere Konfigurationen)
	v1.4.1
	by Falko Müller @ 2019-2021
	package: redaxo5
	
	Info:
	Basisdaten wie Autor, Version, Subpages etc. werden in der package.yml notiert.
	Klassen und lang-Dateien werden automatisch gefunden (Ordnernamen beachten).
	Dateibasierte Konfigurationswerte nicht hier vornehmen !!! -> rex_config dafür nutzen (siehe install.php) !!!
*/

//Variablen deklarieren
$mypage = $this->getProperty('package');
//$this->setProperty('name', 'Wert');

	//Berechtigungen deklarieren
	if (rex::isBackend() && is_object(rex::getUser())):
		rex_perm::register($mypage.'[]');
		//rex_perm::register($mypage.'[admin]');
	endif;


//Userrechte prüfen
$isAdmin = ( is_object(rex::getUser()) AND (rex::getUser()->hasPerm($mypage.'[admin]') OR rex::getUser()->isAdmin()) ) ? true : false;


//Addon Einstellungen
$config = rex_addon::get($mypage)->getConfig('config');			//Addon-Konfig einladen
/*
if (!$this->hasConfig()):
    $this->setConfig('url', 'http://www.example.com');
    $this->setConfig('ids', [1, 4, 5]);
endif;
*/


//Funktionen einladen/definieren
//Global für Backend+Frontend
global $a1544_mypage;
$a1544_mypage = $mypage;

require_once(rex_path::addon($mypage)."/functions/functions.inc.php");

//Backendfunktionen
if (rex::isBackend() && rex::getUser()):
	//Globale Einstellungen
	//Sprachauswahl zur Navigation hinzufügen
	$page = $this->getProperty('page');
		if (count(rex_clang::getAll(false)) > 1):
			$cids = rex_clang::getAll();
			foreach ($cids as $id => $cid):
				if (rex::getUser()->getComplexPerm('clang')->hasPerm($id)):
					$page['subpages']['default']['subpages']['clang-'.$id] = ['title' => $cid->getName()];
				endif;
			endforeach;
		endif;
	$this->setProperty('page', $page);
	
	
	//AJAX anbinden
	$ajaxPages = array('load-seoculist');
		if (rex_be_controller::getCurrentPagePart(1) == $mypage && in_array(rex_request('subpage', 'string'), $ajaxPages)):
			rex_extension::register('OUTPUT_FILTER', 'aFM_bindAjax');
		endif;	
	
	
	//SEO-CheckUp einbinden
	require_once(rex_path::addon($mypage)."/functions/functions_be_seo.inc.php");
	
	rex_view::addCssFile($this->getAssetsUrl('style.css'));
	rex_view::addJsFile($this->getAssetsUrl('script.js'));
	rex_view::addCssFile($this->getAssetsUrl('chartjs/Chart.min.css'));
	rex_view::addJsFile($this->getAssetsUrl('chartjs/Chart.min.js'));
	
	rex_extension::register('OUTPUT_FILTER', 'a1544_seocuJS');

	if (@$config['be_seo'] == "checked"):
		//Sidebar-CheckUp
		rex_extension::register('PACKAGES_INCLUDED', function($ep){
			global $a1544_mypage;
			$config = rex_addon::get($a1544_mypage)->getConfig('config');
			
			if (@$config['be_seo_sidebar_priority'] == "checked"):
				rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', 'a1544_showSEO', rex_extension::LATE);
			else:
				rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', 'a1544_showSEO', rex_extension::EARLY);
			endif;
			
			//bei Contentänderung Info über URL bereitstellen
			foreach(array("SLICE_ADDED", "SLICE_DELETED", "SLICE_UPDATED") as $e):
				rex_extension::register($e, function($ep){ $op = $ep->getSubject(); $cnt = "<script>window.location.replace(window.location.href+'&seocucnt=changed');</script>"; return $op.$cnt; }, rex_extension::EARLY);
			endforeach;
		}, rex_extension::LATE);
	endif;

endif;

//Frontendfunktionen
if (!rex::isBackend()):
	//require_once(rex_path::addon($mypage)."/functions/functions_fe.inc.php");
	
	//CSS/Skripte einbinden
	//rex_extension::register('OUTPUT_FILTER', 'a1544_addAssets');
endif;
?>
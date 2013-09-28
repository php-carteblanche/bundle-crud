<?php
/**
 * CarteBlanche - PHP framework package - AutoObject bundle
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/carte-blanche>
 */

namespace AutoObject\Controller;

use \CarteBlanche\CarteBlanche;
use \CarteBlanche\App\Abstracts\AbstractController;
use \CarteBlanche\Exception\NotFoundException;

/**
 * The default application controller
 *
 * Default data controller extending abstract \CarteBlanche\App\Abstracts\AbstractController class
 *
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
abstract class AutoObjectControllerAbstract extends AbstractController
{

	/**
	 * The directory where to search the views files
	 */
	static $views_dir = 'AutoObject/views/';

	protected function init()
	{
		$_action = $this->getContainer()->get('request')->getUrlArg('action');
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$em = $this->getContainer()->get('entity_manager');
		if (!$em) {
    		$this->getContainer()->load('entity_manager');
    		$em = $this->getContainer()->get('entity_manager');
    	}
		if ( 
			!in_array($_action, array('empty', 'install', 'check_system')) && 
			false===$this->isInstalled($_altdb) 
		) {
			header('Location: '.$this->getContainer()->get('router')->buildUrl(array(
				'action'=>'empty', 'altdb'=>$_altdb
			)));
			exit;
		}
	}

	/**
	 * Check if the application is installed (if the database exists)
	 *
	 * @param string $dbname The name of the database to search
	 * @return bool TRUE if the database file had been found, FALSE otherwise
	 */
	public function isInstalled($dbname = null)
	{
		if (empty($dbname)) $dbname='default';
        $em = $this->getContainer()->get('entity_manager');
        $se = $em->getStorageEngine($dbname);
        return $se->getAdapter()->isInstalled($dbname);
	}

}

// Endfile
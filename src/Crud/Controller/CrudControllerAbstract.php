<?php
/**
 * This file is part of the CarteBlanche PHP framework
 * (c) Pierre Cassat and contributors
 * 
 * Sources <http://github.com/php-carteblanche/bundle-crud>
 *
 * License Apache-2.0
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Crud\Controller;

use \CarteBlanche\CarteBlanche;
use \CarteBlanche\Abstracts\AbstractController;
use \CarteBlanche\Exception\NotFoundException;

/**
 * The default application controller
 *
 * Default data controller extending abstract \CarteBlanche\Abstracts\AbstractController class
 *
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
abstract class CrudControllerAbstract extends AbstractController
{

    /**
     * The directory where to search the views files
     */
    static $views_dir = 'Crud/views/';

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
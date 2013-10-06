<?php
/**
 * CarteBlanche - PHP framework package - Simple Viewer bundle
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License Apache-2.0 <http://www.apache.org/licenses/LICENSE-2.0.html>
 * Sources <http://github.com/php-carteblanche/carteblanche>
 */

namespace Crud;

use \CarteBlanche\CarteBlanche;
use \Library\Helper\Directory as DirectoryHelper;

class CrudBundle
{

    protected static $bundle_config_file = 'crud_config.ini';

    public function __construct()
    {
        $cfgfile = \CarteBlanche\App\Locator::locateConfig(self::$bundle_config_file);
        if (!file_exists($cfgfile)) {
            throw new ErrorException( 
                sprintf('CRUD bundle configuration file not found in "%s" [%s]!', $this->getPath('config_dir'), $cfgfile)
            );
        }
        $cfg = CarteBlanche::getContainer()->get('config')
            ->load($cfgfile, true, 'crud');
    }

}

// Endfile
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
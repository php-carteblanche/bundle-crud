<?php
/**
 * This file is part of the CarteBlanche PHP framework.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * License Apache-2.0 <http://github.com/php-carteblanche/carteblanche/blob/master/LICENSE>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Crud;

use \CarteBlanche\CarteBlanche;
use \CarteBlanche\Abstracts\AbstractBundle;
use \Library\Helper\Directory as DirectoryHelper;

class CrudBundle
    extends AbstractBundle
{

    /**
     * @param   array $options
     * @return  mixed
     */
    public function init(array $options = array())
    {
        parent::init($options);
    }

}

// Endfile
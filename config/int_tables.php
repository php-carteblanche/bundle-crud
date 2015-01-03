<?php
/**
 * This file is part of the CarteBlanche PHP framework.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * License Apache-2.0 <http://github.com/php-carteblanche/carteblanche/blob/master/LICENSE>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Internal tables user definitions (for documentation).
 */

$tables = array(

array(
    'table'=>'doc_rubrique',
    'editable'=>true,
    'structure'=>array(
        'id'=>array(
            'type'=>'integer',
            'null'=>false,
            'default'=>'',
            'index'=>'primary key asc',
        ),
        'parent_id'=>array(
            'type'=>'integer',
            'null'=>true,
            'default'=>'',
            'index'=>'',
            'related'=>'doc_rubrique:id',
        ),
        'is_menu'=>array(
            'type'=>'bit',
            'null'=>true,
            'default'=>'',
            'index'=>'',
        ),
        'title'=>array(
            'type'=>'varchar(255)',
            'null'=>true,
            'default'=>'',
            'index'=>false,
            'slug'=>true
        ),
        'content'=>array(
            'type'=>'mediumtext',
            'null'=>true,
            'default'=>'',
            'index'=>false,
            'markdown'=>true
        ),
        'created_at'=>array(
            'type'=>'datetime',
            'null'=>true,
            'default'=>'',
            'index'=>false,
        ),
        'updated_at'=>array(
            'type'=>'datetime',
            'null'=>true,
            'default'=>'',
            'index'=>false,
        ),
    ),
),

array(
    'table'=>'doc_article',
    'editable'=>true,
    'structure'=>array(
        'id'=>array(
            'type'=>'integer',
            'null'=>false,
            'default'=>'',
            'index'=>'primary key asc',
        ),
        'doc_rubrique_id'=>array(
            'type'=>'integer',
            'null'=>false,
            'default'=>'',
            'index'=>'',
            'related'=>'doc_rubrique:id',
        ),
        'title'=>array(
            'type'=>'varchar(255)',
            'null'=>true,
            'default'=>'',
            'index'=>false,
            'slug'=>true
        ),
        'content'=>array(
            'type'=>'longtext',
            'null'=>true,
            'default'=>'',
            'index'=>false,
            'markdown'=>true
        ),
        'created_at'=>array(
            'type'=>'datetime',
            'null'=>true,
            'default'=>'',
            'index'=>false,
        ),
        'updated_at'=>array(
            'type'=>'datetime',
            'null'=>true,
            'default'=>'',
            'index'=>false,
        ),
    ),
),

);

// Endfile
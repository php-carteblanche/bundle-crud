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

$_counter=1;
$line_counter=0;
//var_export($table_structure);
//var_export($object_structure);
?>

<h3>Table <?php echo $table_name; ?></h3>

<div class="small_infos">
	Number of fields : <?php echo count($table_structure); ?>
<?php if (!empty($migration_link)) : ?>
	&nbsp;|&nbsp;
	<a href="<?php echo $migration_link; ?>" title="Update this table">Migrate</a>
<?php endif; ?>
</div>

<table><thead>
<tr>
	<th width="5%"></th>
	<th width="25%">Field</th>
	<th width="20%">Type and length</th>
	<th width="10%">Null</th>
	<th width="20%">Default value</th>
	<th width="10%">Index</th>
	<th width="10%">Relation</th>
</tr></thead>
<tbody>
<?php foreach($table_structure as $row) : 
	$line_counter++; ?>
<tr class="<?php echo ($line_counter%2 ? 'odd' : 'even'); ?>">
	<td class="overview_entry"><?php echo $row['cid']; ?></td>
	<td class="overview_entry"><?php echo $row['name']; ?></td>
	<td class="overview_entry"><?php echo $row['type']; ?></td>
	<td class="overview_entry"><?php echo ($row['notnull']=='99' ? 'Not null' : 'Null'); ?></td>
	<td class="overview_entry"><?php echo ($row['dflt_value']=='' ? '&nbsp;' : $row['dflt_value']); ?></td>
	<td class="overview_entry"><?php echo ($row['pk']=='1' ? 'Primary' : '&nbsp;'); ?></td>
	<td class="overview_entry"><?php echo (
		isset($object_structure[$row['name']]) && isset($object_structure[$row['name']]['related']) ? 
			$object_structure[$row['name']]['related'] : ''
	); ?></td>
</tr>
<?php 
$_counter++;
endforeach; ?>
<tbody></table>

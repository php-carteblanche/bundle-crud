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

$_strlimit=80;
if (empty($table_entries)) $table_entries=array();
if (empty($table_fields)) $table_fields=array();
if (empty($table_structure)) $table_structure=array();
if (empty($relations)) $relations=array();
if (empty($slug_field)) $slug_field='id';
if (!isset($echo_title)) $echo_title=true;
if (!isset($toggler_buttons)) $toggler_buttons=array();
if (!is_array($toggler_buttons)) $toggler_buttons=array( $toggler_buttons );
$line_counter=0;
?>

<?php if ($echo_title!=false) : ?>
<h3>Table <em><?php echo $table_name; ?></em></h3>
<?php endif; ?>

<div class="small_infos">
<?php if ($total==0): ?>
	No entry found
<?php else: ?>
	Number of entries : <?php echo $total; ?>
<?php endif; ?>
<?php if (!empty($isolate_link)) : ?>
	&nbsp;|&nbsp;
	<a href="<?php echo $isolate_link; ?>" title="See only this table">Isolate this table</a>
<?php endif; ?>
	&nbsp;|&nbsp;
	<a href="<?php echo build_url(array(
		'action'=>'create', 'model'=>$table_name, 'controller'=>'crud', 'altdb'=>$altdb
	)); ?>" title="Create a new entry">Add a new <?php echo $table_name; ?></a>
</div>

<?php if (!empty($table_entries)) : ?>
<table><thead>
<tr>
<?php foreach($table_fields as $_field=>$fieldname) : ?>
	<?php if ($fieldname=='id'): ?>
	<th>#</th>
	<?php else: ?>
	<th><?php 
		echo str_replace('_', ' ', ucfirst($fieldname)); 
		if (isset($table_structure[$fieldname])) {
			if (isset($table_structure[$fieldname]['markdown']) && true === $table_structure[$fieldname]['markdown']) {
?>&nbsp;<small>[<abbr title="Content that can use Markdown syntax">MD</abbr>]</small><?php
			} elseif (isset($table_structure[$fieldname]['related'])) {
?>&nbsp;<small>[<abbr title="Field that is a relation with <?php echo $table_structure[$fieldname]['related']; ?>">rel</abbr>]</small><?php
			}
			if (isset($table_structure[$fieldname]['comment']) && strlen($table_structure[$fieldname]['comment'])) {
?>&nbsp;<small>[<abbr title="<?php echo $table_structure[$fieldname]['comment']; ?>">?</abbr>]</small><?php
			}
			if (isset($table_structure[$fieldname]['toggler']) && true === $table_structure[$fieldname]['toggler']) {
				$toggler_buttons[] = $fieldname;
			}
		}
	?></th>
	<?php endif; ?>
<?php endforeach; ?>
	<th></th>
	<th></th>
	<th></th>
<?php if (count($toggler_buttons)) :
		foreach($toggler_buttons as $_f) : ?>
	<th></th>
	<?php endforeach;
	endif; ?>
</tr></thead>
<tbody>
<?php foreach($table_entries as $row) : 
	$line_counter++; ?>
<tr class="<?php echo ($line_counter%2 ? 'odd' : 'even'); ?>">
<?php foreach($table_fields as $_field=>$fieldname) : 
	$related_field = false;
	$value = $row[$fieldname];
	$valstring = $value;
	$fieldname = str_replace('_id', '', $fieldname);
	if (array_key_exists($fieldname, $relations)) {
		if (!empty($row[ $fieldname ])) {
			$related_field = true;
			$rel_obj = $row[ $fieldname ];
			$value = isset($rel_obj[ $relations[$fieldname]['slug_field'] ]) ? 
				$rel_obj[ $relations[$fieldname]['slug_field'] ] : $rel_obj['id'];
			$related_table_name = $relations[$fieldname]['related_table'];
		}
	}
?>

	<?php if ($related_field) : ?>
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'model'=>$related_table_name, 'action'=>'read', 'id'=>$rel_obj['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="See this <?php echo $related_table_name; ?>"><?php 
			echo $value; ?></a></td>

	<?php elseif ($fieldname==$slug_field) : 
/*
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'read', 'id'=>$row['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="See this entry"><?php echo( !empty($valstring) ? 
		( (is_string($valstring) && strlen($valstring)>$_strlimit) ? 
			'<span class="comment">'.substr( strip_tags($valstring), 0, $_strlimit ).'</span>' : strip_tags($valstring) )
		: 'read' ); ?></a></td>
*/
	?>
	<td class="overview_entry">
		<strong><?php echo( !empty($valstring) ? 
		( (is_string($valstring) && strlen($valstring)>$_strlimit) ? 
			'<span class="comment">'.substr( strip_tags($valstring), 0, $_strlimit ).'</span>' : strip_tags($valstring) )
		: 'read' ); ?></strong></td>

	<?php elseif (isset($table_structure[$fieldname]['type']) && preg_match('/(.*)?blob/i', $table_structure[$fieldname]['type']) && strlen($valstring)) : ?>
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'controller'=>'data', 'model'=>$table_name, 'action'=>'seeblob', 'id'=>$row['id'], 'altdb'=>$altdb
		)); ?>" title="See this file">see file</a></td>

	<?php else: ?>
	<td class="overview_entry"><?php 

		if (isset($table_structure[$fieldname]) && isset($table_structure[$fieldname]['type']) && 'bit' === $table_structure[$fieldname]['type']) {
			if ($valstring==1)
				echo '<abbr title="Bit value setted on 1" class="toggler_on">ok</abbr>';
			else
				echo '<abbr title="Bit value setted on 0" class="toggler_off">x</abbr>';
		} else {
			echo( isset($valstring) ? 
			( (is_string($valstring) && strlen($valstring)>$_strlimit) ? 
				'<span class="comment">'.substr( strip_tags($valstring), 0, $_strlimit ).'</span>' : strip_tags($valstring) )
			: '' );
		}
	?></td>
	<?php endif; ?>

<?php endforeach; ?>
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'read', 'id'=>$row['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="View this entry">read</a>
	</td>
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'update', 'id'=>$row['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="Update this entry">edit</a>
	</td>
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'delete', 'id'=>$row['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="Delete this entry" onclick="return confirm('Are you sure you want to delete this entry?');">delete</a>
	</td>

<?php if (count($toggler_buttons)) : ?>
	<?php foreach($toggler_buttons as $_f) : ?>
		<?php if ($row[$_f]==1) : ?>
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'toggle', 'field'=>$_f, 'toggler'=>'off', 'id'=>$row['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="Set bit value <?php echo $_f; ?> on 0">deactivate</a>
	</td>
		<?php else : ?>
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'toggle', 'field'=>$_f, 'toggler'=>'on', 'id'=>$row['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="Set bit value <?php echo $_f; ?> on 1">activate</a>
	</td>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

</tr>
<?php endforeach; ?>
<tbody></table>
<?php endif; ?>

<?php if (!empty($pager)) echo $pager; ?>


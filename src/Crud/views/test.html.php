{%
$_strlimit=80;
if (empty($table_entries)) $table_entries=array();
if (empty($table_fields)) $table_fields=array();
if (empty($relations)) $relations=array();
if (empty($slug_field)) $slug_field='id';
if (empty($orderby)) $orderby='id';
if (empty($orderway)) $orderway='desc';
if (!isset($echo_title)) $echo_title=true;
$linechecker = $table_name.'_checker';
if (empty($table_structure)) $table_structure=array();
$line_counter=0;
%}

<?php if ($echo_title!=false) : ?>
<h3>Table <em><?php echo $table_name; ?></em></h3>
<?php endif; ?>

<?php if (!empty($table_entries)) : ?>

<table><thead>
<tr>

<?php foreach($table_fields as $_field=>$fieldname) : ?>
	<th>
			<a href="<?php echo build_url(array_merge($current_args,array(
				'orderby'=>$fieldname, 'orderway'=>'asc'
			))); ?>" title="Sort by that column">
				<?php echo str_replace('_', ' ', ucfirst($fieldname)); ?>
			</a>

	</th>
<?php endforeach; ?>
	<th></th>
	<th></th>
	<th></th>
</tr></thead>
<tbody>
<?php foreach($table_entries as $row) :
	$line_counter++; ?>
<tr class="<?php echo ($line_counter%2 ? 'odd' : 'even'); ?>">

<?php foreach($table_fields as $_field=>$fieldname) : 
	$related_field = $rel_obj = false;
	$value = $row[$fieldname];
	$valstring = $value;
?>

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
</tr>
<?php endforeach; ?>
<tbody></table>

<?php endif; ?>


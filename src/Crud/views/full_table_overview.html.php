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
/*
echo '<pre>';
echo "<br />table_entries: ".var_export($table_entries,1);
echo "<br />table_fields: ".var_export($table_fields,1);
echo "<br />relations: ".var_export($relations,1);
echo "<br />slug_field: ".var_export($slug_field,1);
echo "<br />orderby: ".var_export($orderby,1);
echo "<br />orderway: ".var_export($orderway,1);
echo "<br />echo_title: ".var_export($echo_title,1);
*/
if (empty($table_entries)) $table_entries=array();
if (empty($table_fields)) $table_fields=array();
if (empty($relations)) $relations=array();
if (empty($slug_field)) $slug_field='id';
if (empty($orderby)) $orderby='id';
if (empty($orderway)) $orderway='desc';
if (!isset($echo_title)) $echo_title=true;
$linechecker = $table_name.'_checker';
$lineprefix = $table_name.'_';
if (!isset($checked_ids)) $checked_ids=array();
if (empty($table_structure)) $table_structure=array();
if (!isset($toggler_buttons)) $toggler_buttons=array();
if (!is_array($toggler_buttons)) $toggler_buttons=array( $toggler_buttons );
if (!isset($current_args)) $current_args=array();
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
	&nbsp;|&nbsp;
	<a href="<?php echo build_url(
		array_merge($current_args, array('action'=>'csvExport', 'all'=>'true'))
	); ?>" title="Export the whole table entries in CSV format">Export in CSV</a>
</div>

<?php if (!empty($table_entries)) : ?>

<?php if (!empty($add_linecheck) && $add_linecheck===true) : ?>
	<form method="post" action="#" name="<?php echo $table_name; ?>">
<?php endif; ?>

<table><thead>
<tr>
<?php if (!empty($add_linecheck) && $add_linecheck===true) : ?>
	<th></th>
<?php endif; ?>
<?php foreach($table_fields as $_field=>$fieldname) : ?>
	<?php if ($fieldname=='id'): ?>
	<th>
		<?php if ($orderby=='id') : ?>
			<?php echo( $orderway=='asc' ? '[<strong>&darr;</strong>]' : '[<strong>&uarr;</strong>]'); ?>&nbsp;
			<a href="<?php echo build_url(array_merge($current_args,array(
				'orderby'=>'id', 'orderway'=>($orderway=='asc' ? 'desc' : 'asc')
			))); ?>" title="Change this column order way">#</a>
		<?php else: ?>
			<a href="<?php echo build_url(array_merge($current_args,array(
				'orderby'=>'id', 'orderway'=>'asc'
			))); ?>" title="Sort by that column">#</a>
		<?php endif; ?>
	</th>
	<?php else: ?>
	<th>
		<?php if ($orderby==$fieldname) : ?>
			<?php echo( $orderway=='asc' ? '[<strong>&darr;</strong>]' : '[<strong>&uarr;</strong>]'); ?>&nbsp;
			<a href="<?php echo build_url(array_merge($current_args,array(
				'orderby'=>$fieldname, 'orderway'=>($orderway=='asc' ? 'desc' : 'asc')
			))); ?>" title="Change this column order way">
				<?php echo str_replace('_', ' ', ucfirst($fieldname)); ?>
			</a>
		<?php else: ?>
			<a href="<?php echo build_url(array_merge($current_args,array(
				'orderby'=>$fieldname, 'orderway'=>'asc'
			))); ?>" title="Sort by that column">
				<?php echo str_replace('_', ' ', ucfirst($fieldname)); ?>
			</a>
		<?php endif; ?>
		<?php 
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
		?>
	</th>
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
<tr id="<?php echo $lineprefix.$line_counter; ?>" class="<?php echo ($line_counter%2 ? 'odd' : 'even'); ?> hover">
<?php if (!empty($add_linecheck) && $add_linecheck===true) : ?>
	<td class="overview_entry">
		<label class="invisible" for="<?php echo $linechecker.'-'.$row['id']; ?>">Check/unckeck this line</label>
		<input type="checkbox" id="<?php echo $linechecker.'-'.$row['id']; ?>" name="<?php echo $linechecker; ?>[]" value="<?php echo $row['id']; ?>" onchange="changeClassOnCheck('checked_on', '<?php echo $linechecker.'-'.$row['id']; ?>', '<?php echo $lineprefix.$line_counter; ?>');" />
	</td>
<?php endif; ?>
<?php foreach($table_fields as $_field=>$fieldname) : 
	$related_field = $rel_obj = false;
	$value = $row[$fieldname];
	$valstring = $value;
	$fieldname = str_replace('_id', '', $fieldname);
	if (array_key_exists($fieldname, $relations)) {
		$related_field = true;
		$related_table_name = $relations[$fieldname]['related_table'];
		if (!empty($row[ $fieldname ])) {
			$rel_obj = $row[ $fieldname ];
			$value = isset($rel_obj[ $relations[$fieldname]['slug_field'] ]) ? 
				$rel_obj[ $relations[$fieldname]['slug_field'] ] : $rel_obj['id'];
		}
	}
?>

	<?php if ($related_field===true && !empty($rel_obj)) : ?>
	<td class="overview_entry"
<?php if (!empty($add_linecheck) && $add_linecheck===true) : ?>
 onclick="toggleClassAndCheck('checked_on', '<?php echo $linechecker.'-'.$row['id']; ?>', '<?php echo $lineprefix.$line_counter; ?>');"
<?php endif; ?>
>
		<a href="<?php echo build_url(array(
			'model'=>$related_table_name, 'action'=>'read', 'id'=>$rel_obj['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="See this <?php echo $related_table_name; ?>"><?php 
			echo $value; ?></a></td>

	<?php elseif ($related_field===true && empty($rel_obj)) : ?>
	<td class="overview_entry"
<?php if (!empty($add_linecheck) && $add_linecheck===true) : ?>
 onclick="toggleClassAndCheck('checked_on', '<?php echo $linechecker.'-'.$row['id']; ?>', '<?php echo $lineprefix.$line_counter; ?>');"
<?php endif; ?>
>
		<abbr title="Not found related <?php echo $related_table_name; ?>"><?php echo $value; ?></abbr>
	</td>

	<?php elseif ($fieldname==$slug_field) : 
/*
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'read', 'id'=>$row['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="See this entry"><?php echo( isset($valstring) ? 
		( (is_string($valstring) && strlen($valstring)>$_strlimit) ? 
			'<span class="comment">'.substr( strip_tags($valstring), 0, $_strlimit ).'</span>' : strip_tags($valstring) )
		: '' ); ?></a></td>
*/
	?>
	<td class="overview_entry"
<?php if (!empty($add_linecheck) && $add_linecheck===true) : ?>
 onclick="toggleClassAndCheck('checked_on', '<?php echo $linechecker.'-'.$row['id']; ?>', '<?php echo $lineprefix.$line_counter; ?>');"
<?php endif; ?>
>
		<strong><?php echo( isset($valstring) ? 
		( (is_string($valstring) && strlen($valstring)>$_strlimit) ? 
			'<span class="comment">'.substr( strip_tags($valstring), 0, $_strlimit ).'</span>' : strip_tags($valstring) )
		: '' ); ?></strong></td>

	<?php else: ?>
	<td class="overview_entry"
<?php if (!empty($add_linecheck) && $add_linecheck===true) : ?>
 onclick="toggleClassAndCheck('checked_on', '<?php echo $linechecker.'-'.$row['id']; ?>', '<?php echo $lineprefix.$line_counter; ?>');"
<?php endif; ?>
><?php 
		if (isset($table_structure[$fieldname]) && isset($table_structure[$fieldname]['type']) && 'bit' === $table_structure[$fieldname]['type']) {
			if ($valstring==1)
				echo '<abbr title="Bit value setted on 1" class="toggler_on">ok</abbr>';
			else
				echo '<abbr title="Bit value setted on 0" class="toggler_off">x</abbr>';

		} elseif (isset($table_structure[$fieldname]) && isset($table_structure[$fieldname]['type']) && 'blob' === $table_structure[$fieldname]['type']) {
			if (!empty($valstring)) :
				$_doc = new \Tool\DocumentField(array(
					'document_content'=>$valstring,
					'max_width'=>100,
					'max_height'=>60,
					'document_url' => build_url(array(
						'controller'=>'data', 'action'=>'seeblob', 'model'=>$table_name, 'id'=>$row['id'], 'altdb'=>$altdb
					))
				));
				echo $_doc;			  
			endif;
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
		<?php if (!empty($_f) && $row[$_f]==1) : ?>
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'toggle', 'field'=>$_f, 'toggler'=>'off', 'id'=>$row['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="Set bit value <?php echo $_f; ?> on 0">deactivate</a>
	</td>
		<?php elseif (!empty($_f)) : ?>
	<td class="overview_entry">
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'toggle', 'field'=>$_f, 'toggler'=>'on', 'id'=>$row['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="Set bit value <?php echo $_f; ?> on 1">activate</a>
	</td>
		<?php else : ?>
	<td></td>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>

</tr>

<?php if (!empty($add_linecheck) && $add_linecheck===true && !empty($checked_ids) && in_array($row['id'], $checked_ids)) : ?>
<script type="text/javascript">
toggleClassAndCheck('checked_on', '<?php echo $linechecker.'-'.$row['id']; ?>', '<?php echo $lineprefix.$line_counter; ?>');
</script>
<?php endif; ?>

<?php endforeach; ?>
<tbody>
<tfoot>
<tr>
	<th colspan="<?php echo count($table_fields)+3+(count($toggler_buttons))+(!empty($add_linecheck) && $add_linecheck===true ? 1 : 0); ?>">
<?php if (!empty($add_linecheck) && $add_linecheck===true) : ?>
		<input type="checkbox" id="<?php echo $linechecker; ?>_all" name="<?php echo $linechecker; ?>_all" value="1" title="Check all/Uncheck all" onchange="checkAll(this.form, '<?php echo $linechecker; ?>');" />
		<label for="<?php echo $linechecker; ?>_all" class="nofloat">Check/unckeck all</label>
		&nbsp;|&nbsp;
<?php endif; ?>
		For selection: 
		<a href="#" onclick="var _form = document.<?php echo $table_name; ?>, _url = '<?php echo build_url(
			array_merge($current_args, array('action'=>'csvExport'))
		); ?>'; _form.action=_url; _form.submit();" title="Export selected lines in CSV format">Export in CSV</a>
	</th>
</tr></tfoot>
</table>

<?php if (!empty($add_linecheck) && $add_linecheck===true) : ?>
	</form>
<?php endif; ?>

<?php endif; ?>

<?php if (!empty($pager)) echo $pager; ?>

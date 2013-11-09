<?php
/*
echo '<pre>';
echo "<br />object: ".var_export($object,1);
echo "<br />fields: ".var_export($fields,1);
echo "<br />structure: ".var_export($table_structure,1);
echo "<br />relations: ".var_export($relations,1);
*/
if (empty($object)) $object=array();
if (empty($fields)) $fields=array();
if (empty($table_structure)) $table_structure=array();
if (empty($relations)) $relations=array();

//var_export($object);

?>

<?php if (!empty($object) && !empty($object['id'])) : ?>
<div class="small_infos_right">
		<a href="<?php echo build_url(array(
			'controller'=>'data', 'table'=>$table_name, 'action'=>'table', 'altdb'=>$altdb
		)); ?>" title="See the list of all entries">list all</a>
	&nbsp;|&nbsp;
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'read', 'id'=>$object['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="View this entry">read</a>
	&nbsp;|&nbsp;
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'delete', 'id'=>$object['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="Delete this entry" onclick="return confirm('Are you sure you want to delete this entry?');">delete</a>
</div>
<?php endif; ?>

<?php echo $form; ?>

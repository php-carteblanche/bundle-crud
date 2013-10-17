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
if (empty($separator)) $separator='&nbsp;|&nbsp;';

//var_export($object);

?>

<div class="small_infos_right">
		<a href="<?php echo build_url(array(
			'controller'=>'data', 'table'=>$table_name, 'action'=>'table', 'altdb'=>$altdb
		)); ?>" title="See the list of all entries">list all</a>
	&nbsp;|&nbsp;
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'update', 'id'=>$object['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="Update this entry">edit</a>
	&nbsp;|&nbsp;
		<a href="<?php echo build_url(array(
			'model'=>$table_name, 'action'=>'delete', 'id'=>$object['id'], 'controller'=>'crud', 'altdb'=>$altdb
		)); ?>" title="Delete this entry" onclick="return confirm('Are you sure you want to delete this entry?');">delete</a>
</div>

<div class="small_infos_left">
<?php if (!empty($relations)) : 
	$tmp_related=array();
	foreach($relations as $_rel=>$data) 
	{
		if (!empty($object[ $_rel ])) 
		{
			$rel_obj = $object[ $_rel ];
			$related_table_name = $data['related_table'];
			$_relname = str_replace('_id', '', $_rel);
			if (!isset($tmp_related[$_relname])) $tmp_related[$_relname] = array();
			if (!empty($data['is_many']) && $data['is_many']===true) 
			{
				foreach($object[ $_rel ] as $_subobj) 
				{
					$tmp_related[$_relname][] = array(
						'id'=>$_subobj['id'],
						'name'=>isset($_subobj[ $data['slug_field'] ]) && !empty($_subobj[ $data['slug_field'] ]) ? 
							$_subobj[ $data['slug_field'] ] : 'N&deg; '.$_subobj['id'],
						'table'=>$related_table_name
					);
				}
			} else {
				$tmp_related[$_relname][] = array(
					'id'=>$rel_obj['id'],
					'name'=>isset($rel_obj[ $data['slug_field'] ]) && !empty($rel_obj[ $data['slug_field'] ]) ? 
						$rel_obj[ $data['slug_field'] ] : 'N&deg; '.$rel_obj['id'],
					'table'=>$related_table_name
				);
			}
		}
	}
?>
	<ul>
	<?php foreach($tmp_related as $_relname=>$_relvals) : ?>
		<li>Related <?php echo $_relname; ?> : 
			<?php foreach($_relvals as $_i=>$data) : ?>
				<a href="<?php echo build_url(array(
					'controller'=>'crud','model'=>$data['table'], 'id'=>$data['id'], 'altdb'=>$altdb
				)); ?>" title="See this entry"><?php echo $data['name']; ?></a>
				<?php if ($_i<count($_relvals)-1) { echo $separator; } ?>
			<?php endforeach; ?>
		</li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>	
</div>

<br class="clear" />

<div class="content">

<?php foreach($object as $_var=>$_val) if (
		in_array($_var, $fields) AND
		($_var!='id' && !preg_match('/(.*)?_id/i', $_var)) AND
		!in_array($_var, array('created_at', 'updated_at'))
	) : ?>

	<div class="left_label">
	<?php echo $_var.': '; ?>
	</div>
	<div class="right_value">
		<?php if (preg_match('/(.*)?blob/i', $table_structure[$_var]['type']) && !empty($_val)) :
			$_doc = new \Tool\DocumentField(array(
				'document_content'=>$_val,
				'max_width'=>200,
				'max_height'=>200,
				'document_url' => build_url(array(
					'controller'=>'data', 'action'=>'seeblob', 'model'=>$table_name, 'id'=>$object['id'], 'altdb'=>$altdb
    			))
			));
			echo $_doc;			  
		elseif (is_url($_val)) :
			echo '<a href="'.$_val.'" title="See this page">'.$_val.'</a>',
				'<a href="'.$_val.'" target="_blank" title="See this page in a new window">
					<img src="img/out.gif" alt="[new window]" class="outlink" /></a><br />';
		elseif (is_email($_val)) :
			echo '<a href="mailto:'.$_val.'" title="Contact this email adress">'.$_val,
					'<img src="img/mail.gif" alt="[email]" class="outlink" /></a><br />';
		else :
			echo @$_val.'<br />';
		endif; ?>
	</div>
	<div class="clearer">&nbsp;</div>
<?php endif; ?>

</div>

<div class="small_infos_right">
<?php if (!empty($object['created_at'])) : ?>
	Created on <?php echo strftime('%c', strtotime($object['created_at'])); ?>
<?php endif; ?>
<?php if (!empty($object['updated_at'])) : ?>
	&nbsp;|&nbsp;
	Last updated on <?php echo strftime('%c', strtotime($object['updated_at'])); ?>
<?php endif; ?>
</div>

<br class="clear" />

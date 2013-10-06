<?php
/**
 * CarteBlanche - PHP framework package - AutoObject bundle
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License Apache-2.0 <http://www.apache.org/licenses/LICENSE-2.0.html>
 * Sources <http://github.com/php-carteblanche/carteblanche>
 */

namespace Crud\Controller;

use \CarteBlanche\CarteBlanche,
    \CarteBlanche\Exception\NotFoundException,
    \CarteBlanche\Library\AutoObject\AutoObjectMapper;

use \AutoObject\Controller\AutoObjectControllerAbstract;

/**
 * The default application controller
 *
 * Default data controller extending abstract \CarteBlanche\Abstracts\AbstractController class
 *
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class DataController extends AutoObjectControllerAbstract
{

	/**
	 * The home page of the controller
	 *
	 * @param numeric $offset The offset used for the tables dump
	 * @param numeric $limit The limit used for the tables dump
	 * @param string $table The name of a table to isolate it
	 * @param misc $show ??
	 * @return string The home page view content
	 */
	public function indexAction($offset = 0, $limit = 5, $table = null, $show = null)
	{
		$this->getContainer()->get('router')->setReferer();
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$tables = AutoObjectMapper::getObjectsStructure( $_altdb );
		$ctt='';

		$search_str = $this->getContainer()->get('request')->getArgument('search', '', true, ENT_NOQUOTES);
		$_args=array(
			'controller'=>'data', 'offset'=>$offset,'limit'=>$limit,'table'=>$table,'altdb'=>$_altdb,
			'orderby'=>null, 'orderway'=>null
		);
		$url_args = CarteBlanche::getConfig('routing.arguments_mapping');
		foreach ($_args as $_arg_var=>$_arg_val) {
			if (!empty($_arg_val)) {
				if (in_array($_arg_var, $url_args))
					$args[ array_search($_arg_var, $url_args) ] = $_arg_val;
				else
					$args[ $_arg_var ] = $_arg_val;
			}
		}
		$searchbox = new \Tool\SearchBox(array(
			'hiddens'=>$args, 'search_str'=>$search_str, 'advanced_search'=>true
		));
		$ctt = (string) $searchbox;

		$models=array();
		$search_fields_by_tables=array();
		$search_tables=array();
		if (!empty($tables)) {
			foreach($tables as $_table) {
				if (true===$_table->isEditable()) {
					$_model = $_table->getModel();
					$search_tables[] = $_table->getTableName();
					$search_fields_by_tables[$_table->getTableName()] = $_table->getFieldsByType();					
					$models[$_table->getTableName()] = $_model;
				}
			}
		}

		if (!empty($search_str)) {
			$advanced_search = new \Tool\AdvancedSearch(array(
				'search_str' 	=> $search_str,
				'field'			=> $search_fields_by_tables,
				'table'			=> $search_tables
			));
            $ems = AutoObjectMapper::getEntityManager($_altdb);
            $em = end($ems);
            $db = CarteBlanche::getContainer()->get('entity_manager')
                ->getStorageEngine($em->getDatabaseName());
            $advanced_search->setStorageEngine($db);
			$query = $advanced_search->getQuerySearchString();
			$search_tables = $advanced_search->getSearchTables();
			$search_str = $advanced_search->getCleanedSearchString();
		}

		foreach ($models as $_table_name => $_model) {

			if (
				empty($search_tables) ||
				(!empty($search_tables) && in_array($_table_name, $search_tables))
			){
				$total = $_model->count($search_str);
				$pager = new \Tool\Pager(
					array(
						'altdb'=>$_altdb,
						'table_name'=>$_table_name,
						'total'=>$total,
						'limit'=>$limit,
						'offset'=>(!empty($table) && $table==$_table_name) ? $offset : 0,
						'url_args'=>array('controller'=>'data','table'=>$_table_name,'altdb'=>$_altdb)
					)
				);
	
				$ctt .= $this->view(
					self::$views_dir.'table_overview.htm',
					array(
						'altdb'=>$_altdb,
						'table_name'=>$_table_name,
						'isolate_link'=> ($total!=0) ? 
							$this->getContainer()->get('router')->buildUrl(array(
								'controller'=>'data', 'action'=>'table', 'table'=>$_table_name, 'altdb'=>$_altdb,
								'search'=>$search_str
							)) : null,
						'slug_field'=>$_model->getSlugField(),
						'table_fields'=>$_model->getFieldsList(),
						'table_structure'=>$_model->getTableStructure(),
						'table_entries'=>(!empty($table) && $table==$_table_name) ?
							$_model->dump($offset, $limit, true, null, 'asc', $search_str) : 
							$_model->dump(0, $limit, true, null, 'asc', $search_str),
						'relations'=>$_model->getObjectRelations(),
						'total'=>$total,
						'pager'=> ($total>$limit) ? $pager : '',
					)
				);
			} else {
				$total = $_model->count();
				$ctt .= $this->view(
					self::$views_dir.'table_overview.htm',
					array(
						'altdb'=>$_altdb,
						'table_name'=>$_table_name,
						'isolate_link'=> ($total!=0) ? 
							$this->getContainer()->get('router')->buildUrl(array(
								'controller'=>'data', 'action'=>'table', 'table'=>$_table_name, 'altdb'=>$_altdb,
								'search'=>$search_str
							)) : null,
						'slug_field'=>$_model->getSlugField(),
						'table_fields'=>$_model->getFieldsList(),
						'table_structure'=>$_model->getTableStructure(),
						'table_entries'=>array(),
						'relations'=>$_model->getObjectRelations(),
						'total'=>0,
						'pager'=>'',
					)
				);
			}
		}
		
		return array('raw_content.htm', array(
			'content'=> $ctt
		));
	}

	/**
	 * Help page explaining how to use the the app search box
	 *
	 * @param string $return The last page path
	 * @return string The help page view content
	 */
	public function searchHelpAction($return = null)
	{
		$_txt = new \Tool\Text(array(
			'original_str'=>file_get_contents(__DIR__.'/../views/search_help.md'),
			'markdown'=>true,
		));

		return array('raw_content.htm', array(
			'title'=>'Advanced search help',
			'content'=> $this->view( self::$views_dir.'search_help.htm', array( 
				'content'=>$_txt,
				'return'=>$return 
			) )
		));
	}

	/**
	 * Standalone view of a single table
	 *
	 * @param numeric $offset The offset used for the tables dump
	 * @param numeric $limit The limit used for the tables dump
	 * @param string $table The name of a table to isolate it
	 * @param string $orderby The field to order the table view
	 * @param string $orderway The way to order the table view (default is 'asc')
	 * @return string The view content
	 */
	public function tableAction($offset = 0, $limit = 10, $table = null, $orderby = 'id', $orderway = 'asc')
	{
		$this->getContainer()->get('router')->setReferer();
		if (empty($table)) return self::indexAction( $offset, $limit );
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$tables = AutoObjectMapper::getObjectsStructure( $_altdb );
		if (!isset($tables[$table]))
			throw new NotFoundException(
				sprintf('Unknown table "%s" in entity manager "%s"!', $table, $_altdb)
			);

		$table_structure = $tables[$table];

		$_show = $this->getContainer()->get('request')->getUrlArg('show');
		if (!empty($_show) && is_numeric($_show)) $offset = $_show;

		$search_str = $this->getContainer()->get('request')->getArgument('search', '', true, ENT_NOQUOTES);
		$_args=array(
			'controller'=>'data', 'action'=>'table','offset'=>$offset,'limit'=>$limit,'table'=>$table,'altdb'=>$_altdb,
			'orderby'=>$orderby, 'orderway'=>$orderway
		);
		$url_args = CarteBlanche::getConfig('routing.arguments_mapping');
		foreach ($_args as $_arg_var=>$_arg_val) {
			if (!empty($_arg_val)) {
				if (in_array($_arg_var, $url_args))
					$args[ array_search($_arg_var, $url_args) ] = $_arg_val;
				else
					$args[ $_arg_var ] = $_arg_val;
			}
		}
		$searchbox = new \Tool\SearchBox(array(
			'hiddens'=>$args, 'search_str'=>$search_str, 'advanced_search'=>true
		));
		$ctt = (string) $searchbox;

		if (!empty($table_structure)) {
			$_model = $table_structure->getModel(true, true);
			$total = $_model->count($search_str);

			$check_name = $table.'_checker';
			$checked_ids = $this->getContainer()->get('request')->getPost($check_name, null);
			if (empty($checked_ids))
				$checked_ids = $this->getContainer()->get('request')->getGet($check_name, null);

			$pager = new \Tool\Pager(
				array(
					'altdb'=>$_altdb,
					'table_name'=>$table,
					'total'=>$total,
					'limit'=>$limit,
					'offset'=>$offset,
					'url_args'=>array('controller'=>'data','action'=>'table','table'=>$table,'altdb'=>$_altdb)
				)
			);

			$ctt .= $this->view(
				self::$views_dir.'full_table_overview.htm',
				array(
					'echo_title'=>false,
					'add_linecheck'=>true,
					'checked_ids'=>$checked_ids,
					'current_args'=>array(
						'controller'=>'data', 'action'=>'table','offset'=>$offset,'limit'=>$limit,'table'=>$table,'altdb'=>$_altdb,
						'search'=>$search_str
					),
					'orderby'=>$orderby,
					'orderway'=>$orderway,
					'altdb'=>$_altdb,
					'table_name'=>$table,
					'slug_field'=>$_model->getSlugField(),
					'table_fields'=>$_model->getFieldsList(),
					'table_structure'=>$_model->getTableStructure(),
					'table_entries'=>$_model->dump($offset, $limit, true, $orderby, $orderway, $search_str),
					'relations'=>$_model->getObjectRelations(),
					'total'=>$total,
					'pager'=>$pager,
				)
			);
		}
		
		return array('raw_content.htm', array(
			'content'=> $ctt,
			'title'=>'Table '.$table
		));
	}

	/**
	 * CSV export of entries of a table
	 *
	 * @param string $table The name of a table to isolate it
	 * @param string $check_name The checkboxes name to select entries (by default "{$table}_checker")
	 * @return void Force downloading the file
	 */
	public function csvExportAction($table = null, $check_name = null)
	{
		if (empty($table)) return self::indexAction();
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$tables = AutoObjectMapper::getObjectsStructure( $_altdb );
		if (!isset($tables[$table]))
			throw new NotFoundException(
				sprintf('Unknown table "%s" in entity manager "%s"!', $table, $_altdb)
			);
		$table_structure = $tables[$table];
		$ctt = '';

		$current_args = array(
			'controller'=>'data', 'table'=>$table, 'altdb'=>$_altdb, 'action'=>'table',
			'offset'=>$this->getContainer()->get('request')->getUrlArg('offset'),
			'limit'=>$this->getContainer()->get('request')->getUrlArg('limit'),
			'orderby'=>$this->getContainer()->get('request')->getUrlArg('orderby'),
			'orderway'=>$this->getContainer()->get('request')->getUrlArg('orderway'),
		);

		if (empty($check_name)) $check_name = $table.'_checker';
		$ids = $this->getContainer()->get('request')->getPost($check_name, null);
		if (empty($ids))
			$ids = $this->getContainer()->get('request')->getGet($check_name, null);
		$all = $this->getContainer()->get('request')->getGet('all', false);

		if (!empty($table_structure) && (!empty($ids) || 'true'==$all)) {
			$_model = $table_structure->getModel();
			$entries = !empty($ids) ? $_model->readCollection($ids) : $_model->dump();
			$csvfile_name = $table.'_export';

			if (!empty($entries)) {
				$headers=array();
				$collection=array();
				foreach ($entries[0] as $fieldname=>$v){
					if (is_string($fieldname)) $headers[] = $fieldname;
				}
				
				// clear blob contents
				$blobs = $_model->getFieldsByType('blob');
				if (!empty($blobs)) {
					$skip_blob = $this->getContainer()->get('request')->getGet('skip_blob', false);
					if (!$skip_blob) {
						$to_url = $this->getContainer()->get('router')->buildUrl(
							array_merge($current_args, array($check_name=>$ids, 'all'=>$all)),
							null, '&');
						$skip_url = $this->getContainer()->get('router')->buildUrl(
							array_merge($current_args, array(
								'action'=>'csvExport', $check_name=>$ids, 'all'=>$all, 'skip_blob'=>'1'
							)),
							null, '&');
						$zip_url = $this->getContainer()->get('router')->buildUrl(
							array_merge($current_args, array(
								'action'=>'zipExport', $check_name=>$ids, 'all'=>$all
							)),
							null, '&');
						$this->getContainer()->get('session')->setFlash(
							"info:The data contains blob informations (<em>file content</em>) that can not be written in a CSV file!"
							."<ul>"
							."<li><a href=\"".$skip_url."\">Clic here to make your export skipping these file contents</a></li>"
							."<li><a href=\"".$zip_url."\">Clic here to make your export as a ZIP archive containing the CSV export data and the attached files</a></li>"
							."</ul>"
						);
						$this->getContainer()->get('session')->commit();
						$this->getContainer()->get('router')->redirect( $to_url, true );
						exit;
					} else {
						foreach ($entries as $_id=>$_data) {
							foreach ($blobs as $_fieldname) {
								$_doc = new \Tool\DocumentField(array(
									'document_content'=>$_data[$_fieldname],
									'display_image'=>false,
									'html_content'=>false,
								));
								$_data[$_fieldname] = $_doc;
							}
							$collection[$_id] = $_data;
						}
					}
				} else {
					$collection = $entries;
				}

				$csv = new \Tool\Exporter(
					array(
						'format'=>'CSV',
						'dataCollection'=>$collection,
						'dataFields'=>$headers,
						'formater_options' => array(
							'file_name' => $csvfile_name
						)
					)
				);
	
				if (true===$csv->export()) {
					$file = $csv->getExportedFileName();
					$this->getContainer()->get('response')->download( $file, 'application/csv' );
				} else {
					$this->getContainer()->get('session')->setFlash("error:ERROR - An error occured while trying to build a CSV export file!");
				}
			} else {
				$this->getContainer()->get('session')->setFlash("error:ERROR - Not enough data to build a CSV export file!");
			}
		} else {
			$this->getContainer()->get('session')->setFlash("error:ERROR - Not enough argument to build a CSV export file!");
		}
		
		$this->getContainer()->get('session')->commit();
		$this->getContainer()->get('router')->redirect( $this->getContainer()->get('router')->buildUrl($current_args));
	}

	/**
	 * ZIP export of entries of a table with attached dependencies
	 *
	 * @param string $table The name of a table to isolate it
	 * @param string $check_name The checkboxes name to select entries (by default "{$table}_checker")
	 * @return void Force downloading the archive
	 */
	public function zipExportAction($table = null, $check_name = null)
	{
		if (empty($table)) return self::indexAction();
		$request = $this->getContainer()->get('request');
		$_altdb = $request->getUrlArg('altdb');
		$tables = AutoObjectMapper::getObjectsStructure( $_altdb );
		if (!isset($tables[$table]))
			throw new NotFoundException(
				sprintf('Unknown table "%s" in entity manager "%s"!', $table, $_altdb)
			);
		$table_structure = $tables[$table];
		$ctt = '';

		$current_args = array(
			'controller'=>'data', 'table'=>$table, 'altdb'=>$_altdb, 'action'=>'table',
			'offset'=>$request->getUrlArg('offset'),
			'limit'=>$request->getUrlArg('limit'),
			'orderby'=>$request->getUrlArg('orderby'),
			'orderway'=>$request->getUrlArg('orderway'),
		);

		if (empty($check_name)) $check_name = $table.'_checker';
		$ids = $request->getPost($check_name, null);
		if (empty($ids))
			$ids = $request->getGet($check_name, null);
		$all = $request->getGet('all', false);

		if (!empty($table_structure) && (!empty($ids) || 'true'==$all)) {
			$_model = $table_structure->getModel();
			$entries = !empty($ids) ? $_model->readCollection($ids) : $_model->dump();
			$csvfile_name = $table.'_export';
			$zip_name = $table.'_export';
			$zip_content = array();
			$zip_path = CarteBlanche::getPath('tmp_path').$zip_name.'/';

			if (!empty($entries)) {
				if (!@file_exists($zip_path) && !mkdir($zip_path)) {
					trigger_error('Can not create ZIP dir', E_USER_ERROR);
				}
				$headers=array();
				$collection=array();
				foreach($entries[0] as $fieldname=>$v){
					if (is_string($fieldname)) $headers[] = $fieldname;
				}
				
				// clear blob contents
				$blobs = $_model->getFieldsByType('blob');
				if (!empty($blobs)) {
					foreach ($entries as $_id=>$_data) {
						foreach ($blobs as $_fieldname) {
							if (!empty($_data[$_fieldname])) {
								$clientname = $zip_path.$_fieldname.'_'.$table.$_id;
								$_doc = \CarteBlanche\Library\File::createFromContent(
									$_data[$_fieldname], $clientname
								);
								$_data[$_fieldname] = $_doc->getBasename();
								$zip_content[] = $_doc->getRealPath();
							}
						}
						$collection[$_id] = $_data;
					}
				} else {
					$collection = $entries;
				}

				$csv = new \Tool\Exporter(
					array(
						'format'=>'CSV',
						'dataCollection'=>$collection,
						'dataFields'=>$headers,
						'formater_options' => array(
							'exported_file' =>$zip_path.$csvfile_name.'.csv'
						)
					)
				);
				if (true===$csv->export()) {
					$zip_content[] = $csv->getExportedFileName();
				} else {
					trigger_error('Can not create CSV export', E_USER_ERROR);
				}

				$zip = new \Tool\Exporter(
					array(
						'format'=>'ZIP',
						'dataCollection'=>$zip_content,
						'formater_options' => array(
							'file_name' => $zip_name,
							'dir_name' => $zip_path,
							'clean_source'=>true
						)
					)
				);

				if (true===$zip->export()) {
					$file = $zip->getExportedFileName();
					$this->getContainer()->get('response')->download( $file, 'application/zip' );
				} else {
					$this->getContainer()->get('session')->setFlash("error:ERROR - An error occured while trying to build a ZIP archive!");
				}
			} else {
				$this->getContainer()->get('session')->setFlash("error:ERROR - Not enough data to build a ZIP archive file!");
			}
		} else {
			$this->getContainer()->get('session')->setFlash("error:ERROR - Not enough argument to build a ZIP archive file!");
		}
		
		$this->getContainer()->get('session')->commit();
		$this->getContainer()->get('router')->redirect( $this->getContainer()->get('router')->buildUrl($current_args));
	}

	public function emptyAction( $_altdb )
	{
		$this->getContainer()->get('router')->redirect();
	}

	/**
	 * Page to visualize a blob content (file)
	 *
	 * @param int $id The blob primary ID
	 * @return string The view content
	 * WIP : not finished, we must not write errors in case of no blob ... (find a way ?)
	 */
	public function seeblobAction($id = null)
	{
		if (empty($id)) return self::indexAction();
		$_mod = $this->getContainer()->get('request')->getUrlArg('model');
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$_structure = AutoObjectMapper::getAutoObject( $_mod, $_altdb );

		if (isset($_structure)) {
			$model = $_structure->getModel();
			$object = $model->read( $id );
			if ($model->exists()) {
				$filectt=$clientname=null;
				foreach ($model->getTableStructure() as $field_name=>$field) {
					if (preg_match('/(.*)?blob/i', $field['type'], $matches)) {
						if (!empty($object[$field_name])) {
							$filectt = $object[$field_name];
							$clientname = $field_name.'_'.$_mod.$id;
						}
					}
				}
				if (!empty($filectt)) {
					$_file = \CarteBlanche\Library\File::createFromContent( $filectt );
					if ($_file) {
						if ($_file->isImage()) {
							$this->getContainer()->get('response')->flush( $filectt );
							exit;
						} else {
							$_file->setClientFilename( $clientname );
							$this->getContainer()->get('response')
								->download( $_file->getRealPath(), $_file->getMime(),  $_file->getClientFilename() );
							exit;
/*
    					$this->view(
  			  			'blob.htm', array(
	  	  					'file_content'=>$filectt,
	    					), true, true);
  						exit;
*/
						}
					}
				} else {
					throw new NotFoundException("No blob found in object [$_mod:$id]!");
				}
			} else {
				throw new NotFoundException("No object found [$_mod:$id]!");
			}
		} else {
			throw new NotFoundException("No model name requested or structure not found [$_mod]!");
		}
	}

	/**
	 * Page of tables structure overview
	 *
	 * @return string The view content
	 */
	public function tables_structureAction()
	{
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$SQLITE = $this->getContainer()->get('entity_manager')->getStorageEngine();
		$tables = AutoObjectMapper::getObjectsStructure( $_altdb );

		$ctt='';
		if (!empty($tables)) {
			foreach ($tables as $table) {
				$results = $SQLITE->table_infos($table->getTableName());
				$ctt .= $this->view(
					self::$views_dir.'table_structure.htm', array(
						'altdb'=>$_altdb,
						'table_name' => $table->getTableName(),
						'table_structure'=>$results,
						'object_structure'=>$table->getStructureENtry('structure'),
						'migration_link' => $this->getContainer()->get('router')->buildUrl(array(
							'controller'=>'data', 'action'=>'migrate', 'table'=>$table->getTableName(), 'altdb'=>$_altdb
						)),
					)
				);
			}
		}

		return array('raw_content.htm', array(
			'content'=> $ctt,
			'title' => "Tables structure of database"
		));
	}

	/**
	 * Page of database migration
	 *
	 * @param string $table The table name to migrate
	 * @param string $datafile The file where to search the new structure
	 * @return string The view content
	 */
	public function migrateAction( $table=null, $datafile=null )
	{
		$sqlite = sqlite_libversion();
		if ($sqlite[0]=='2') {
			$this->getContainer()->get('session')->setFlash("error:ERROR - Your system is running a version of SQLite which does not allow 'alter' procedure [version: {$sqlite}]!");
			$this->getContainer()->get('session')->commit();
			$this->getContainer()->get('router')->redirect('');
		}

		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		if (empty($table) && empty($datafile)) 
			$this->getContainer()->get('router')->redirect( $this->getContainer()->get('router')->buildUrl('altdb',$_altdb) );
		$SQLITE = $this->getContainer()->get('database');
		$tables = AutoObjectMapper::getObjectsStructure( $_altdb );

		if (!empty($tables)) {
			foreach ($tables as $_table) {
				if ($_table['table']==$table) {
					$absent_fields=array();
					$table_infos = $SQLITE->table_infos($_table['table']);
					foreach($_table['structure'] as $_field=>$_field_data) {
						$found=false;
						foreach($table_infos as $_structure_field) {
							if ($_structure_field['name']==$_field) $found=true;
						}
						if (!$found) $absent_fields[$_field] = $_field_data;
					}
					if (!empty($absent_fields)) {
						$err = $SQLITE->add_fields( $_table['table'], $absent_fields );
						if (!$err)
							$this->getContainer()->get('session')->setFlash("error:ERROR - An error occured while creating fields '".join("', '", $absent_fields)."' in table '{$table['table']}'!");
						else
							$this->getContainer()->get('session')->setFlash("ok:OK - Fields '".join("', '", $absent_fields)."' created in table '$table'");
					}
				}
			}
		}
		$this->getContainer()->get('session')->commit();
		$this->getContainer()->get('router')->redirect( $this->getContainer()->get('router')->buildUrl(array(
			'action'=>'tables_structure', 'altdb'=>$_altdb
		)) );
	}

}

// Endfile
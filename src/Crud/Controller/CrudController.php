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

namespace Crud\Controller;

use \CarteBlanche\CarteBlanche,
    \CarteBlanche\Abstracts\AbstractController,
    \CarteBlanche\Exception\NotFoundException,
    \CarteBlanche\Library\AutoObject\AutoObjectMapper;

use \Crud\Controller\CrudControllerAbstract;

/**
 * The default CRUD controller
 *
 * Default CRUD controller extending the abstract \CarteBlanche\Abstracts\AbstractController class
 *
 * @author 		Piero Wbmstr <piwi@ateliers-pierrot.fr>
 */
class CrudController
    extends CrudControllerAbstract
{

	/**
	 * The home page of the controller
	 *
	 * @param int $id The primary ID of the object to read
	 * @return string The view content
	 * @see self::read()
	 */
	public function indexAction($id = null)
	{
		return self::readAction( $id );
	}

	/**
	 * Page for uninstalled application
	 *
	 * @param string $altdb The alternative database
	 */
	public function emptyAction($altdb = null)
	{
		$this->getContainer()->get('router')->redirect();
	}

	/**
	 * Create a new object
	 *
	 * @return string The view content
	 */
	public function createAction()
	{
		$_mod = $this->getContainer()->get('request')->getUrlArg('model');
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$_object = AutoObjectMapper::getAutoObject( $_mod, $_altdb );

		if ($_object) {
			$model = $_object->getModel();
			$object = $model->createEmpty();
			$form = new \Tool\Form(array(
				'form_id'=>'create_'.$_mod, 
				'fields'=>$_object->getFields(), 
			));
			list($values, $errors) = $form->treatPost();

			if (!empty($values) && empty($errors)) {
				$model->create($values);
				if ($model->exists()) {
					$this->getContainer()->get('session')
					    ->setFlash("ok:OK - New '$_mod' entry '{$model->id}' created")
					    ->commit();
					if (!empty($_altdb)) {
                        $this->getContainer()->get('router')->redirect(array(
                            'controller'=>'data', 'action'=>'table', 'table'=>$_mod, 'altdb'=>$_altdb, 'show'=>$model->id
                        ));
					} else {
                        $this->getContainer()->get('router')->redirect(array(
                            'controller'=>'data', 'action'=>'table', 'table'=>$_mod, 'show'=>$model->id
                        ));
                    }
				} else {
					$this->getContainer()->get('session')
					    ->setFlash("error:ERROR - An error occured while creating new '$_mod'!")
					    ->commit();
				}
			}
            return array(self::$views_dir.'update_entry', array(
                'altdb'=>$_altdb,
                'form'=>$form,
                'table_name'=>$_mod,
                'object'=>$object,
				'title'=>'Creation of a new '.$_mod,
            ));
		} else {
			throw new NotFoundException("No model name requested or structure not found [$_mod]!");
		}
	}

	/**
	 * Must return the content of the object
	 *
	 * @param int $id The primary ID of the object
	 * @return string The view content
	 */
	public function readAction($id = null)
	{
		$_mod = $this->getContainer()->get('request')->getUrlArg('model');
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$_object = AutoObjectMapper::getAutoObject( $_mod, $_altdb );

		if ($_object) {
			$model = $_object->getModel();
			$object = $model->read($id, true);
			if ($model->exists()) {
				$slug = $model->getSlugField();
				foreach ($_object->getStructureEntry('structure') as $fieldname=>$field) {
					if (isset($field['markdown']) && $field['markdown']===true) {
						if (isset($object[$fieldname])) {
							$_txt = new \Tool\Text(array(
								'original_str'=>$object[$fieldname],
								'markdown'=>true,
							));
							$object[$fieldname] = $_txt;
						}
					}
				}
				if ($slug && isset($object[$slug])) {
					$title = $object[$slug];
					unset($object[$slug]);
				} else {
					$title = 'Reading '.$_mod.' '.$id;
				}

				if ($toggled_fields = $model->getSpecialFields('toggler')) {
					foreach ($toggled_fields as $_toggler) {
						$toggler = new \Tool\Toggler(array(
							'name'=>$_toggler,
							'value'=>$object[$_toggler],
							'table_name'=>$_mod, 
							'object_id'=>$object['id'], 
							'db_name'=>$_altdb,
						));
						$object[$_toggler] = $toggler;
					}
				}

                return array(self::$views_dir.'read_entry', array(
                    'altdb'=>$_altdb,
                    'table_name'=>$_mod,
                    'object'=>$object,
                    'fields'=>$model->getFieldsList(),
                    'table_structure'=>$model->getTableStructure(),
                    'relations'=>$model->getObjectRelations(),
					'title'=>$title,
                ));
			} else {
				throw new NotFoundException("No object found [$_mod:$id]!");
			}
		} else {
			throw new NotFoundException("No model name requested or structure not found [$_mod]!");
		}
	}

	/**
	 * Update an existing object
	 *
	 * @param int $id The primary ID of the object
	 * @return string The view content
	 */
	public function updateAction($id = null)
	{
		$_mod = $this->getContainer()->get('request')->getUrlArg('model');
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$_object = AutoObjectMapper::getAutoObject( $_mod, $_altdb );

		if ($_object) {
			$model = $_object->getModel();
			$object = $model->read($id);
			if ($model->exists()) {
				$form = new \Tool\Form(array(
					'form_id'=>'update_'.$_mod, 
					'fields'=>$_object->getFields(), 
					'values'=>$object, 
				));
				list($values, $errors) = $form->treatPost($_object);
				if (empty($values)) $values=array();
				if (!empty($values) && empty($errors)) {
					$model->update($id, $values);
					$this->getContainer()->get('session')
					    ->setFlash("ok:OK - '$_mod' entry '$id' updated")
					    ->commit();
					if (!empty($_altdb)) {
						$this->getContainer()->get('router')->redirect(array(
						    'controller'=>'data', 'action'=>'table', 'table'=>$_mod, 'altdb'=>$_altdb, 'show'=>$model->id
						));
					} else {
						$this->getContainer()->get('router')->redirect(array(
                            'controller'=>'data', 'action'=>'table', 'table'=>$_mod, 'show'=>$model->id
                        ));
                    }
				}
                return array(self::$views_dir.'update_entry', array(
                    'altdb'=>$_altdb,
                    'form'=>$form,
                    'table_name'=>$_mod,
                    'object'=>$object,
					'title'=>(!empty($slug) && isset($object[$slug])) ? $object[$slug] : 'Edition of '.$_mod.' '.$id,
                ));
			} else {
				throw new NotFoundException("No object found [$_mod:$id]!");
			}
		} else {
			throw new NotFoundException("No model name requested or structure not found [$_mod]!");
		}
	}

	/**
	 * Delete an object
	 *
	 * @param int $id The primary ID of the object
	 * @return string The view content
	 */
	public function deleteAction($id = null)
	{
		$_mod = $this->getContainer()->get('request')->getUrlArg('model');
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$_object = AutoObjectMapper::getAutoObject( $_mod, $_altdb );

		if ($_object) {
			$model = $_object->getModel();
			if ($model->delete( $id )) {
				$this->getContainer()->get('session')
				    ->setFlash("ok:OK - '$_mod' entry '{$id}' deleted")
				    ->commit();
				if (!empty($_altdb)) {
					$this->getContainer()->get('router')->redirect(array(
						'controller'=>'data', 'action'=>'table', 'table'=>$_mod, 'altdb'=>$_altdb
					));
				} else {
					$this->getContainer()->get('router')->redirect(array(
						'controller'=>'data', 'action'=>'table', 'table'=>$_mod
					));
				}
			} else {
				$this->getContainer()->get('session')
				    ->setFlash("error:ERROR - An error occured while deleting '$_mod' entry '$id'")
				    ->commit();
			}
		} else {
			throw new NotFoundException("No model name requested or structure not found [$_mod]!");
		}
	}

	/**
	 * Toggle the value of an object
	 *
	 * @param int $id The primary ID of the object
	 * @param string $field The field to toggle
	 * @param enum(on/off) $toggler The value to set on the toggler
	 * @return string The view content
	 */
	public function toggleAction($id = null, $field = null, $toggler = 'off')
	{
		$_mod = $this->getContainer()->get('request')->getUrlArg('model');
		$_altdb = $this->getContainer()->get('request')->getUrlArg('altdb');
		$_object = AutoObjectMapper::getAutoObject( $_mod, $_altdb );
		$_toggler_val = 'off' == $toggler ? 0 : 1;

		if ($_object) {
			$model = $_object->getModel();
			$object = $model->read( $id );
			if ($model->exists()) {
				$toggler_fields = $model->getSpecialFields('toggler');
				if (in_array($field, $toggler_fields)) {
					$values = array_merge(
						$model->getData(),
						array($field=>$_toggler_val)
					);
					$model->update($id, $values);
					if ($model->exists()) {
						if ('off' == $toggler) {
							$this->getContainer()->get('session')
							    ->setFlash("ok:OK - '$_mod' entry '{$id}' disabled for field '{$field}'");
						} else {
							$this->getContainer()->get('session')
							    ->setFlash("ok:OK - '$_mod' entry '{$id}' enabled for field '{$field}'");
						}
					} else {
						$this->getContainer()->get('session')
						    ->setFlash("error:ERROR - An error occured while toggling field '$field' on model '$_mod' entry '$id'");
					}
				}
				$this->getContainer()->get('session')->commit();
				if (!empty($_altdb)) {
					$this->getContainer()->get('router')->redirect(array(
						'controller'=>'data', 'action'=>'table', 'table'=>$_mod, 'show'=>$model->id, 'altdb'=>$_altdb
					));
				} else {
					$this->getContainer()->get('router')->redirect(array(
						'controller'=>'data', 'action'=>'table', 'table'=>$_mod, 'show'=>$model->id
					));
				}
			} else {
				$this->getContainer()->get('session')
				    ->setFlash("error:ERROR - An error occured while deleting '$_mod' entry '$id'")
				    ->commit();
			}
		} else {
			throw new NotFoundException("No model name requested or structure not found [$_mod]!");
		}
	}

}

// Endfile
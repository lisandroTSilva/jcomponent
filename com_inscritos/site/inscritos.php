<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Inscritos
 * @author     Lisandro Tavares da Silva <lisandro.t.silva@gmail.com>
 * @copyright  2020 Lisandro Tavares da Silva
 * @license    GNU General Public License versão 2 ou posterior; consulte o arquivo License. txt
 */

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Controller\BaseController;

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Inscritos', JPATH_COMPONENT);
JLoader::register('InscritosController', JPATH_COMPONENT . '/controller.php');


// Execute the task.
$controller = BaseController::getInstance('Inscritos');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();

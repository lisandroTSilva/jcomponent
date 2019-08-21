<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Patrimonio
 * @author     Lisandro Tavares da Silva <lisandro.t.silva@gmail.com>
 * @copyright  2019 Lisandro Tavares da Silva
 * @license    GNU General Public License versão 2 ou posterior; consulte o arquivo License. txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_patrimonio')) {
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Patrimonio', JPATH_COMPONENT_ADMINISTRATOR);
JLoader::register('PatrimonioHelper', JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'patrimonio.php');

$controller = BaseController::getInstance('Patrimonio');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();

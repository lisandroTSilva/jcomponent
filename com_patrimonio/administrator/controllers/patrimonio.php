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

jimport('joomla.application.component.controllerform');

/**
 * Patrimonio controller class.
 *
 * @since  1.6
 */
class PatrimonioControllerPatrimonio extends \Joomla\CMS\MVC\Controller\FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'patrimonios';
		parent::__construct();
	}
}

<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Inscritos
 * @author     Lisandro Tavares da Silva <lisandro.t.silva@gmail.com>
 * @copyright  2020 Lisandro Tavares da Silva
 * @license    GNU General Public License versão 2 ou posterior; consulte o arquivo License. txt
 */
defined('_JEXEC') or die;

use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of Inscritos records.
 *
 * @since  1.6
 */
class InscritosModelInscrito extends \Joomla\CMS\MVC\Model\ListModel
{


	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.`id`',
				'ordering', 'a.`ordering`',
				'state', 'a.`state`',
				'created_by', 'a.`created_by`',
				'modified_by', 'a.`modified_by`',
				'nome', 'a.`nome`',
				'email', 'a.`email`',
				'celular', 'a.`celular`',
				'evento_id', 'a.`evento_id`',
			);
		}

		parent::__construct($config);
	}







	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('id', 'ASC');

		$context = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $context);

		// Split context into component and optional section
		$parts = FieldsHelper::extract($context);

		if ($parts) {
			$this->setState('filter.component', $parts[0]);
			$this->setState('filter.section', $parts[1]);
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');


		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'DISTINCT a.*'
			)
		);
		$query->from('`#__inscritos` AS a');

		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');
		// Join over the foreign key 'evento_id'
		$query->select('`#__eventos_3513956`.`nome_interno` AS #__eventos_fk_value_3513956');
		$query->join('LEFT', '#__eventos AS #__eventos_3513956 ON #__eventos_3513956.`id` = a.`evento_id`');


		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		} elseif (empty($published)) {
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(#__eventos_3513956.nome_interno LIKE ' . $search . ' )');
			}
		}


		// Filtering evento_id
		$filter_evento_id = $this->state->get("filter.evento_id");

		if ($filter_evento_id !== null && !empty($filter_evento_id)) {
			$query->where("a.`evento_id` = '" . $db->escape($filter_evento_id) . "'");
		}
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol && $orderDirn) {
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		//XXX_CUSTOM_ORDER_FOR_NESTED

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $oneItem) {

			if (isset($oneItem->evento_id)) {
				$values    = explode(',', $oneItem->evento_id);
				$textValue = array();

				foreach ($values as $value) {
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query
						->select('`#__eventos_3513956`.`nome_interno`')
						->from($db->quoteName('#__eventos', '#__eventos_3513956'))
						->where($db->quoteName('#__eventos_3513956.id') . ' = ' . $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results) {
						$textValue[] = $results->nome_interno;
					}
				}

				$oneItem->evento_id = !empty($textValue) ? implode(', ', $textValue) : $oneItem->evento_id;
			}
		}

		return $items;
	}
}

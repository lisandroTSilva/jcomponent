<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

use \Joomla\CMS\Factory;

class InscritosController extends \Joomla\CMS\MVC\Controller\BaseController
{
	public function display($cachable = false, $urlparams = false)
	{
		$app  = Factory::getApplication();
		$view = $app->input->getCmd('view', 'inscrito');
		$app->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}

	public function evento()
	{
		$input = JFactory::getApplication()->input;

		$eventoId = $input->get("evento_id", NULL, 'STRING');

		if (empty($eventoId)) {
			echo new JResponseJson([]);
			exit;
		}

		$db = JFactory::getDbo();
		$q = $db->getQuery(true);

		$q->select($db->quoteName(array('eventos.id', 'eventos.nome', 'eventos.data', 'eventos.nro_vagas')))
			->select('COUNT(`inscritos`.id) as registeredParticipants')
			->from($db->quoteName('#__eventos', 'eventos'))
			->join('LEFT', $db->quoteName('#__inscritos', 'inscritos') . ' ON ' . $db->quoteName('inscritos.evento_id') . ' = ' . $db->quoteName('eventos.id'))
			->where($db->quoteName('eventos.id') . ' = ' . $db->quote($eventoId))
			->group($db->quoteName('eventos.id'))
			->order('eventos.id DESC');

		$db->setQuery($q);

		try {
			$evento = $db->loadObjectList();
			$evento = is_array($evento) ? reset($evento) : array();
			echo new JResponseJson($evento);
		} catch (RuntimeException $e) {
			echo new JResponseJson(null, "Não foi possível obter o número de participantes.", true);
		}
	}

	public function eventos()
	{
		$db = JFactory::getDbo();
		$q = $db->getQuery(true);

		$q->select($db->quoteName(array('eventos.id', 'eventos.nome', 'eventos.data', 'eventos.nro_vagas')))
			->select('COUNT(`inscritos`.id) as registeredParticipants')
			->from($db->quoteName('#__eventos', 'eventos'))
			->join('LEFT', $db->quoteName('#__inscritos', 'inscritos') . ' ON ' .
				$db->quoteName('inscritos.evento_id') . ' = ' . $db->quoteName('eventos.id') . ' and ' .
				$db->quoteName('inscritos.state') . ' = 1')
			->where($db->quoteName('eventos.data_limite') . ' >= ' . $db->quote(date('Y-m-d H:i:s')))
			->where($db->quoteName('eventos.state') . ' = 1')
			->group($db->quoteName('eventos.id'))
			->order('eventos.id DESC');

		$db->setQuery($q);
		try {
			$eventos = $db->loadObjectList();
			$eventos = is_array($eventos) ? $eventos : array();
			echo new JResponseJson($eventos);
		} catch (RuntimeException $e) {
			echo new JResponseJson(null, "Algo inesperado ocorreu durante a consulta de eventos.", true);
		}
	}

	public function inscricao()
	{
		if ("OPTIONS" == $_SERVER['REQUEST_METHOD']) {
			exit(0);
		}
		$input = JFactory::getApplication()->input;

		$nome = $input->json->get("nome", NULL, 'STRING');
		$email = $input->json->get("email", NULL, 'STRING');
		$celular = $input->json->get("celular", NULL, 'STRING');
		$evento_id = $input->json->get("evento_id", NULL, 'STRING');

		if (empty($nome)) {
			echo new JResponseJson(null, "O campo Nome é obrigatório.", true);
			exit;
		}
		if (empty($email)) {
			echo new JResponseJson(null, "O campo Email é obrigatório.", true);
			exit;
		}
		if (empty($celular)) {
			echo new JResponseJson(null, "O campo Celular é obrigatório.", true);
			exit;
		}
		if (empty($evento_id)) {
			echo new JResponseJson(null, "Um evento não foi definido.", true);
			exit;
		}

		try {
			$inscricao = new stdClass();
			$inscricao->nome = $nome;
			$inscricao->email = $email;
			$inscricao->celular = $celular;
			$inscricao->evento_id = $evento_id;

			$result = JFactory::getDbo()->insertObject('#__inscritos', $inscricao);
			echo new JResponseJson(['result' => $result]);
		} catch (\Throwable $th) {
			$erro = "Algo inesperado ocorreu durante a consulta de eventos.";
			if ($th->getCode() == 1062) {
				$erro = "Você já foi inscrito anteriormente para este evento.";
			}
			echo new JResponseJson(null, $erro, true);
		}
	}
}

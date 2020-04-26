<?php
defined('_JEXEC') or die;

$doc = JFactory::getDocument();
$doc->addScript(JURI::root()."administrator/components/com_contatomodal/media/js/script.js");

$input = JFactory::getApplication()->input;
$task = $input->get('task');

$controller = JControllerLegacy::getInstance('Contatomodal');
$controller->execute($task);
$controller->redirect();

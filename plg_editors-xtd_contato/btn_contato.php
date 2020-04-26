<?php
defined('_JEXEC') or die;

class PlgButtonBtn_contato extends JPlugin
{
	protected $autoloadLanguage = true;

	public function onDisplay($name)
	{
		$user = JFactory::getUser();
        if ($user->authorise('core.create', 'com_contact')
            || $user->authorise('core.edit', 'com_contact')
            || $user->authorise('core.edit.own', 'com_contact')) {
            $link = 'index.php?option=com_contatomodal&amp;layout=modal&amp;tmpl=component&amp;'
            . JSession::getFormToken() . '=1&amp;editor=' . $name;

            $button = new JObject;
            $button->modal = true;
            $button->class = 'btn';
			$button->link = $link;
			$button->text = JText::_('PLG_EDITORS_XTD_BTN_CONTATO_LINK', "teste");
            $button->name = 'address';
            $button->options = "{handler: 'iframe', size: {x: 800, y: 500}}";

            return $button;
		}
	}
}
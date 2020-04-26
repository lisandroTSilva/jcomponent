<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.load_contato
 *
 * @copyright
 * @license     MIT
 */
defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('script', 'plg_content_load_contato/load_contato.js', ['version' => 'auto', 'relative' => true]);
JFactory::getLanguage()->load('plg_content_load_contato', JPATH_PLUGINS . '/content/load_contato');

$document = JFactory::getDocument();
$document->addScriptDeclaration("
    var PlgContato = PlgContato || {};
    PlgContato.baseUrl = '" . JUri::base(true) . "';
    PlgContato.ERROR_FALHA_EMAIL = '" . JText::_('PLG_CONTENT_LOAD_CONTATO_ERROR_FALHA_EMAIL') . "';
    PlgContato.ERROR_VALIDACAO_FORMULARIO = '" . JText::_('PLG_CONTENT_LOAD_CONTATO_ERROR_VALIDACAO_FORMULARIO') . "';
");

class PlgContentLoad_contato extends JPlugin {
	public function onAjaxLoadContato() {
		$input = JFactory::getApplication()->input;
		switch ($input->get('op')) {
			case 'mailSender':
				try {
					JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_contact/models', 'ContactModel');
					$model = JModelLegacy::getInstance('Contact', 'ContactModel', ['ignore_request' => true]);
					$model->setState('params', JFactory::getApplication()->getParams());

					self::_sendEmail([
						'contact_name' => $input->getString('nome'),
						'contact_email' => $input->getString('email'),
						'contact_subject' => $input->getString('assunto'),
						'contact_message' => $input->getString('mensagem'),
					], $model->getItem($input->getInt('contato_id')));

					$ret = new stdClass();
					$ret->message = JText::_('PLG_CONTENT_LOAD_CONTATO_MESSAGE_SEND_SUCCESS');
					return $ret;
				} catch (Exception $e) {
					throw $e;
				}
				break;
			default:
				$ret = new stdClass();
				$ret->message = 'Nenhuma ação encontrada.';
				return $ret;
		}
	}

	public function onContentPrepare($context, &$article, &$params, $page = 0) {
		if (strpos($article->text, 'form-contact') === false) {
			return true;
		}

		preg_match_all('/{form-contact\s*:\s*(\d*)\s*}/i', $article->text, $matches, PREG_SET_ORDER);

		if ($matches) {
			foreach ($matches as $match) {
				$matcheslist = explode(',', $match[1]);
				foreach ($matcheslist as $id) {
					$article->text = preg_replace(addcslashes("|$match[0]|", '()'), addcslashes($this->getLayoutFormulario($id), '\\$'), $article->text, 1);
				}
			}
		}
	}

	public function getLayoutFormulario($id_contato) {
		JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_contact/models', 'ContactModel');
		$model = JModelLegacy::getInstance('Contact', 'ContactModel', ['ignore_request' => true]);
		$model->setState('params', JComponentHelper::getParams('com_contact'));
		$contato = $model->getItem($id_contato);

		$PLG_CONTENT_LOAD_CONTATO_CAMPO_OBRIGATORIO = JText::_('PLG_CONTENT_LOAD_CONTATO_CAMPO_OBRIGATORIO');
		$PLG_CONTENT_LOAD_CONTATO_ENVIAR = JText::_('PLG_CONTENT_LOAD_CONTATO_ENVIAR');

		$PLG_CONTENT_LOAD_CONTATO_NOME = JText::_('PLG_CONTENT_LOAD_CONTATO_NOME');
		$PLG_CONTENT_LOAD_CONTATO_EMAIL = JText::_('PLG_CONTENT_LOAD_CONTATO_EMAIL');
		$PLG_CONTENT_LOAD_CONTATO_ASSUNTO = JText::_('PLG_CONTENT_LOAD_CONTATO_ASSUNTO');
		$PLG_CONTENT_LOAD_CONTATO_MENSAGEM = JText::_('PLG_CONTENT_LOAD_CONTATO_MENSAGEM');
		$PLG_CONTENT_LOAD_CONTATO_CONTACT_AS = JText::sprintf('PLG_CONTENT_LOAD_CONTATO_CONTACT_AS', $contato->name);

		return <<<EOD
            <form action="" class="PLG_CONTENT_LOAD_CONTATO form form-validator">
            <h2 class="form__title">{$PLG_CONTENT_LOAD_CONTATO_CONTACT_AS}</h2>

            <input type="hidden" name="contato_id" required="required" value="$id_contato">

            <div class="row">
                <div class="form-group input-group-lg col-sm-2">
                    <label for="nome" class="form__label">{$PLG_CONTENT_LOAD_CONTATO_NOME} *</label>
                    <input type="text" name="nome" id="nome" class="form-control" required>
                    <p class="validation-message text-danger">{$PLG_CONTENT_LOAD_CONTATO_CAMPO_OBRIGATORIO}</p>
                </div>
            </div>

            <div class="row">
                <div class="form-group input-group-lg col-sm-2">
                    <label for="email" class="form__label">{$PLG_CONTENT_LOAD_CONTATO_EMAIL} *</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                    <p class="validation-message text-danger">{$PLG_CONTENT_LOAD_CONTATO_CAMPO_OBRIGATORIO}</p>
                </div>
            </div>

            <div class="row">
                <div class="form-group input-group-lg col-sm-2">
                    <label for="assunto" class="form__label">{$PLG_CONTENT_LOAD_CONTATO_ASSUNTO} *</label>
                    <input type="text" name="assunto" id="assunto" class="form-control" required>
                    <p class="validation-message text-danger">{$PLG_CONTENT_LOAD_CONTATO_CAMPO_OBRIGATORIO}</p>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="mensagem" class="form__label">{$PLG_CONTENT_LOAD_CONTATO_MENSAGEM} *</label>
                    <textarea class="form-control" rows="7" name="mensagem" id="mensagem" required></textarea>
                    <p class="validation-message text-danger">{$PLG_CONTENT_LOAD_CONTATO_CAMPO_OBRIGATORIO}</p>
                </div>
            </div>

            <button class="btn btn-default form__button validator-check">{$PLG_CONTENT_LOAD_CONTATO_ENVIAR}</button>
            </form>
EOD;
	}

	private function _sendEmail($data, $contact, $copy_email_activated = false) {
		$app = JFactory::getApplication();

		if ($contact->email_to == '' && $contact->user_id != 0) {
			$contact_user = JUser::getInstance($contact->user_id);
			$contact->email_to = $contact_user->get('email');
		}

		$mailfrom = $app->get('mailfrom');
		$fromname = $app->get('fromname');
		$sitename = $app->get('sitename');

		$name = $data['contact_name'];
		$email = JStringPunycode::emailToPunycode($data['contact_email']);
		$subject = $data['contact_subject'];
		$body = $data['contact_message'];

		// Prepare email body
		$prefix = JText::sprintf('PLG_CONTENT_LOAD_CONTATO_ENQUIRY_TEXT', JUri::base(), $contact->name);
		$body = $prefix . "\n" . $name . ' <' . $email . '>' . "\r\n\r\n" . stripslashes($body);

		// Load the custom fields
		if (!empty($data['com_fields']) && $fields = FieldsHelper::getFields('com_contact.mail', $contact, true, $data['com_fields'])) {
			$output = FieldsHelper::render(
				'com_contact.mail',
				'fields.render',
				[
					'context' => 'com_contact.mail',
					'item' => $contact,
					'fields' => $fields,
				]
			);

			if ($output) {
				$body .= "\r\n\r\n" . $output;
			}
		}

		$mail = JFactory::getMailer();

		$mail->addRecipient($contact->email_to);
		$mail->addReplyTo($email, $name);
		$mail->setSender([$mailfrom, $fromname]);
		$mail->setSubject($sitename . ': ' . $subject);
		$mail->setBody($body);
		$sent = $mail->Send();

		if ($copy_email_activated == true && !empty($data['contact_email_copy'])) {
			$copytext = JText::sprintf('PLG_CONTENT_LOAD_CONTATO_COPYTEXT_OF', $contact->name, $sitename);
			$copytext .= "\r\n\r\n" . $body;
			$copysubject = JText::sprintf('PLG_CONTENT_LOAD_CONTATO_COPYSUBJECT_OF', $subject);

			$mail = JFactory::getMailer();
			$mail->addRecipient($email);
			$mail->addReplyTo($email, $name);
			$mail->setSender([$mailfrom, $fromname]);
			$mail->setSubject($copysubject);
			$mail->setBody($copytext);
			$sent = $mail->Send();
		}

		if (is_object($sent) && get_class($sent) == 'JException') {
			throw $sent;
		}

		return $sent;
	}
}

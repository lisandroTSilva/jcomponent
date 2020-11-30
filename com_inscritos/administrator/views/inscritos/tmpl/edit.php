<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Inscritos
 * @author     Lisandro Tavares da Silva <lisandro.t.silva@gmail.com>
 * @copyright  2020 Lisandro Tavares da Silva
 * @license    GNU General Public License versão 2 ou posterior; consulte o arquivo License. txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.keepalive');

// Import CSS
$document = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'media/com_inscritos/css/form.css');
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function() {

		js('input:hidden.evento_id').each(function() {
			var name = js(this).attr('name');
			if (name.indexOf('evento_idhidden')) {
				js('#jform_evento_id option[value="' + js(this).val() + '"]').attr('selected', true);
			}
		});
		js("#jform_evento_id").trigger("liszt:updated");
	});

	Joomla.submitbutton = function(task) {
		if (task == 'inscritos.cancel') {
			Joomla.submitform(task, document.getElementById('inscritos-form'));
		} else {
			if (task != 'inscritos.cancel' && document.formvalidator.isValid(document.id('inscritos-form'))) {
				Joomla.submitform(task, document.getElementById('inscritos-form'));
			} else {
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_inscritos&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="inscritos-form" class="form-validate form-horizontal">
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'ingressos')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'ingressos', JText::_('COM_INSCRITOS_TAB_INGRESSOS', true)); ?>
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_INSCRITOS_FIELDSET_INGRESSOS'); ?></legend>
				<?php echo $this->form->renderField('state'); ?>
				<?php echo $this->form->renderField('evento_id'); ?>
				<?php echo $this->form->renderField('nome'); ?>
				<?php echo $this->form->renderField('celular'); ?>
				<?php echo $this->form->renderField('email'); ?>
				<?php
				foreach ((array)$this->item->evento_id as $value) {
					if (!is_array($value)) {
						echo '<input type="hidden" class="evento_id" name="jform[evento_idhidden][' . $value . ']" value="' . $value . '" />';
					}
				}
				?>
				<?php if ($this->state->params->get('save_history', 1)) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
					</div>
				<?php endif; ?>
			</fieldset>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	<?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
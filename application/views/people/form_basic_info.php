<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_first_name') . ':', 'first_name', array('class' => 'required')); ?>
	<div class='form_field'>
		<?php echo form_input(
			array(
				'name' => 'first_name',
				'id' => 'first_name',
				'value' => $person_info->first_name
			)
		); ?>
	</div>
</div>
<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_last_name') . ':', 'last_name', array('class' => 'required')); ?>
	<div class='form_field'>
		<?php echo form_input(
			array(
				'name' => 'last_name',
				'id' => 'last_name',
				'value' => $person_info->last_name
			)
		); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_document') . ':', 'CPF/CNPJ'); ?>
	<div class='form_field'>
		<?php echo form_input(
			array(
				'name' => 'document',
				'id' => 'document',
				'value' => $person_info->document
			)
		); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_email') . ':', 'email'); ?>
	<div class='form_field'>
		<?php echo form_input(
			array(
				'name' => 'email',
				'id' => 'email',
				'value' => $person_info->email
			)
		); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_phone_number') . ':', 'phone_number'); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name' => 'phone_number',
			'id' => 'phone_number',
			'value' => $person_info->phone_number
		)); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_address_1') . ':', 'address_1'); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name' => 'address_1',
			'id' => 'address_1',
			'value' => $person_info->address_1
		)); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_address_2') . ':', 'address_2'); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name' => 'address_2',
			'id' => 'address_2',
			'value' => $person_info->address_2
		)); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_city') . ':', 'city'); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name' => 'city',
			'id' => 'city',
			'value' => $person_info->city
		)); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_state') . ':', 'state'); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name' => 'state',
			'id' => 'state',
			'value' => $person_info->state
		)); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_zip') . ':', 'zip'); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name' => 'zip',
			'id' => 'zip',
			'value' => $person_info->zip
		)); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_country') . ':', 'country'); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name' => 'country',
			'id' => 'country',
			'value' => $person_info->country
		)); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_comments') . ':', 'comments'); ?>
	<div class='form_field'>
		<?php echo form_textarea(
			array(
				'name' => 'comments',
				'id' => 'comments',
				'value' => $person_info->comments,
				'rows' => '5',
				'cols' => '17'
			)
		); ?>
	</div>
</div>

<div class="field_row clearfix">
	<?php echo form_label($this->lang->line('common_discount') . ':', 'Desconto'); ?>
	<div class='form_field'>
		<?php echo form_input(
			array(
				'name' => 'discount',
				'id' => 'discount',
				'value' => $person_info->desconto,
				'type' => 'number', // Define o tipo como número
				'min' => '0',       // Define o valor mínimo
				'max' => '99'       // Define o valor máximo
			)
		); ?>
	</div>
</div>

<div class="field_row clearfix">
    <?php echo form_label($this->lang->line('common_category').':', 'Categoria'); ?>
    <div class='form_field'>
        <?php echo form_input(array(
            'name' => 'category',
            'id' => 'category',
            'value' => @$person_info->categories  // Converte o array em uma string separada por vírgula
        )); ?>
    </div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('customers_account_number').':', 'account_number'); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'account_number',
		'id'=>'account_number',
		'value'=>@$person_info->account_number)
	);?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('customers_taxable').':', 'taxable'); ?>
	<div class='form_field'>
	<?php echo form_checkbox('taxable', '1', @$person_info->taxable == '' ? TRUE : (boolean)$person_info->taxable);?>
	</div>
</div>
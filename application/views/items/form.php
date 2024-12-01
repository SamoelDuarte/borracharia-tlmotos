<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box"></ul>
<?php
echo form_open('items/save/' . $item_info->item_id, array('id' => 'item_form', 'enctype' => 'multipart/form-data'));
?>
<fieldset id="item_basic_info">
	<legend><?php echo $this->lang->line("items_basic_information"); ?></legend>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_item_number') . ':', 'name', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'item_number',
					'id' => 'item_number',
					'value' => $item_info->item_number
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_name') . ':', 'name', array('class' => 'required wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'name',
					'id' => 'name',
					'value' => $item_info->name
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_category') . ':', 'category', array('class' => 'required wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'category',
					'id' => 'category',
					'value' => $item_info->category_name
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_supplier') . ':', 'supplier', array('class' => 'required wide')); ?>
		<div class='form_field' style="width: 100%;">
			<?php echo form_dropdown('supplier_id', $suppliers, $selected_supplier); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_cost_price') . ':', 'cost_price', array('class' => 'required wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'cost_price',
					'size' => '8',
					'id' => 'cost_price',
					'value' => $item_info->cost_price
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_unit_price') . ':', 'unit_price', array('class' => 'required wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'unit_price',
					'size' => '8',
					'id' => 'unit_price',
					'value' => $item_info->unit_price
				)
			); ?>
		</div>
	</div>

	<!-- <div class="field_row clearfix">
		<?php echo form_label('Preço Site:', 'Preço Site', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'sale_price',
					'size' => '8',
					'id' => 'sale_price',
					'value' => $item_info->sale_price
				)
			); ?>
		</div>
	</div> -->

	<div class="field_row clearfix">
		<?php echo form_label('Preço CNPJ:', 'valor_cnpj', array('class' => ' wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'valor_cnpj',
					'size' => '8',
					'type' => 'text',
					'value' => $item_info->valor_cnpj
				)
			); ?>
		</div>
	</div>

	<!-- <div class="field_row clearfix">
		<?php echo form_label('Altura(cm):', 'altura', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'altura',
					'id' => 'altura',
					'value' => $item_info->altura
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label('Largura(cm):', 'largura', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'largura',
					'id' => 'largura',
					'value' => $item_info->largura
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label('Comprimento(cm):', 'comprimento', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'comprimento',
					'id' => 'comprimento',
					'value' => $item_info->comprimento
				)
			); ?>
		</div>
	</div>
	<div class="field_row clearfix">
		<?php echo form_label('Peso (g):', 'peso', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_input(array('name' => 'peso', 'id' => 'peso', 'value' => $item_info->peso)); ?>
		</div>
	</div> -->


	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_tax_1') . ':', 'tax_percent_1', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'tax_names[]',
					'id' => 'tax_name_1',
					'size' => '8',
					'value' => isset($item_tax_info[0]['name']) ? $item_tax_info[0]['name'] : $this->config->item('default_tax_1_name')
				)
			); ?>
		</div>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'tax_percents[]',
					'id' => 'tax_percent_name_1',
					'size' => '3',
					'value' => isset($item_tax_info[0]['percent']) ? $item_tax_info[0]['percent'] : $default_tax_1_rate
				)
			); ?>
			%
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_tax_2') . ':', 'tax_percent_2', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'tax_names[]',
					'id' => 'tax_name_2',
					'size' => '8',
					'value' => isset($item_tax_info[1]['name']) ? $item_tax_info[1]['name'] : $this->config->item('default_tax_2_name')
				)
			); ?>
		</div>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'tax_percents[]',
					'id' => 'tax_percent_name_2',
					'size' => '3',
					'value' => isset($item_tax_info[1]['percent']) ? $item_tax_info[1]['percent'] : $default_tax_2_rate
				)
			); ?>
			%
		</div>
	</div>


	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_quantity') . ':', 'quantity', array('class' => 'required wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'quantity',
					'id' => 'quantity',
					'value' => $item_info->quantity
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_reorder_level') . ':', 'reorder_level', array('class' => 'required wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'reorder_level',
					'id' => 'reorder_level',
					'value' => $item_info->reorder_level
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_location') . ':', 'location', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_input(
				array(
					'name' => 'location',
					'id' => 'location',
					'value' => $item_info->location
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_description') . ':', 'description', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_textarea(
				array(
					'name' => 'description',
					'id' => 'description',
					'value' => $item_info->description,
					'rows' => '5',
					'cols' => '17'
				)
			); ?>
		</div>
	</div>

	<!-- <div class="field_row clearfix">
		<?php echo form_label('Enviar Para WC:', 'Enviar Para WC', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_checkbox(
				array(
					'name' => 'sinc_wc',
					'id' => 'sinc_wc',
					'value' => 1,
					'checked' => (isset($item_info->sinc_wc) && $item_info->sinc_wc == 1) ? true : false
				)
			); ?>
		</div>
	</div> -->

	<!-- <div class="field_row clearfix">
		<?php echo form_label('Produto em Destaque:', 'Produto em Destaque', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_checkbox(
				array(
					'name' => 'featured',
					'id' => 'featured',
					'value' => 1,
					'checked' => (isset($item_info->featured) && $item_info->featured == 1) ? true : false
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label('Produto em Promoção:', 'Produto em Promoção', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_checkbox(
				array(
					'name' => 'on_sale',
					'id' => 'on_sale',
					'value' => 1,
					'checked' => isset($item_info->on_sale) && $item_info->on_sale == 1 ? true : false
				)
			); ?>
		</div>
	</div> -->

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_allow_alt_desciption') . ':', 'allow_alt_description', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_checkbox(
				array(
					'name' => 'allow_alt_description',
					'id' => 'allow_alt_description',
					'value' => 1,
					'checked' => ($item_info->allow_alt_description) ? 1  : 0
				)
			); ?>
		</div>
	</div>

	<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_is_serialized') . ':', 'is_serialized', array('class' => 'wide')); ?>
		<div class='form_field'>
			<?php echo form_checkbox(
				array(
					'name' => 'is_serialized',
					'id' => 'is_serialized',
					'value' => 1,
					'checked' => ($item_info->is_serialized) ? 1 : 0
				)
			); ?>
		</div>
	</div>
	<!-- <div id="gallery_section">
		<legend>Galeria</legend>

		<div class="field_row clearfix">
			<?php echo form_label('Fotos (máx 5):', 'photos', array('class' => 'required wide')); ?>
			<div class='form_field'>
				<input type="file" name="photos[]" id="photos" accept="image/*" multiple="multiple" onchange="previewImages();" />
				<small>Selecione até 5 fotos. Uma delas será a capa.</small>
			</div>
		</div>

		<div class="field_row clearfix">
			<?php echo form_label('Selecionar Capa:', 'cover', array('class' => 'required wide')); ?>
			<div class='form_field' id="cover_selection">
				<?php
				if (!empty($item_images)) {
					foreach ($item_images as $index => $image) {
						// Verifica se essa imagem é a capa
						$checked = ($image['is_cover'] == 1) ? 'checked' : '';

						echo '<label>';
						echo '<input type="radio" name="cover" value="' . $index . '" ' . $checked . ' />';
						echo '<img src="' . base_url($image['image_path']) . '" style="max-width:100px;margin:10px;" />';
						echo '</label>';
					}
				}
				?>
			</div>
		</div>
	</div> -->


	<?php
	echo form_submit(
		array(
			'name' => 'submit_btn',  // Renomeado para 'submit_btn'
			'id' => 'submit_btn',     // Renomeado para 'submit_btn'
			'value' => $this->lang->line('common_submit'),
			'class' => 'submit_button float_right'
		)
	);
	?>
</fieldset>
<?php
echo form_close();
?>
<script>
	// Seleciona o botão de submit pelo ID
	var submitButton = document.getElementById('submit_btn');

	// Seleciona o formulário para ser enviado manualmente depois
	var form = document.getElementById('item_form');

	// Quando o botão for clicado
	submitButton.addEventListener('click', function(e) {
		// Prevenir o submit imediato


		// Mudar o texto do botão para 'Aguarde...'
		var dots = 0;
		submitButton.value = 'Aguarde';
		// submitButton.disabled = true; // Desativar o botão

		// Função para adicionar os pontos de espera "..."
		var interval = setInterval(function() {
			if (dots < 3) {
				submitButton.value += '.';
				dots++;
			} else {
				submitButton.value = 'Aguarde'; // Resetar texto e começar de novo
				dots = 0;
			}
		}, 500); // Adicionar um ponto a cada 500ms

		// Simulação de 6 segundos (6000ms)
		setTimeout(function() {
			clearInterval(interval); // Parar a animação
			submitButton.value = 'Enviar'; // Texto final de "Aguarde..."

			// Enviar o formulário manualmente após a animação de 6 segundos

		}, 6000); // 6 segundos
	});
</script>


<script type='text/javascript'>
	function previewImages() {
		var coverSelection = document.getElementById('cover_selection');
		coverSelection.innerHTML = ''; // Limpa as opções de seleção de capa anteriores

		// Obtém os arquivos selecionados
		var files = document.getElementById('photos').files;

		// Verifica se o usuário selecionou mais de 5 fotos
		if (files.length > 5) {
			alert('Você pode selecionar no máximo 5 fotos.');
			return;
		}

		// Cria uma função assíncrona para ler os arquivos um por um
		async function readFiles(index) {
			if (index >= files.length) return; // Para quando todos os arquivos foram processados

			var file = files[index];
			var reader = new FileReader();

			// Lê o arquivo como uma URL de dados (base64)
			reader.onload = function(e) {
				// Cria um botão de rádio para selecionar a capa
				var radioInput = document.createElement('input');
				radioInput.type = 'radio';
				radioInput.name = 'cover';
				radioInput.value = index;
				radioInput.id = 'cover_' + index;

				// Cria uma label para o botão de rádio
				var label = document.createElement('label');
				label.htmlFor = 'cover_' + index;

				// Cria uma imagem de pré-visualização
				var imgLabel = document.createElement('img');
				imgLabel.src = e.target.result; // Usa o resultado da leitura
				imgLabel.style.maxWidth = '100px'; // Define o tamanho máximo da imagem
				imgLabel.style.margin = '10px'; // Adiciona espaço ao redor da imagem

				// Adiciona o botão de rádio e a imagem na label
				label.appendChild(radioInput);
				label.appendChild(imgLabel);

				// Adiciona a label no container
				coverSelection.appendChild(label);

				// Chama a função para o próximo arquivo
				readFiles(index + 1);
			};

			// Lê o arquivo
			reader.readAsDataURL(file);
		}

		// Inicia a leitura do primeiro arquivo
		readFiles(0);
	}
	$(document).ready(function() {
		// Configuração do autocomplete para o campo de categoria
		$("#category").autocomplete("<?php echo site_url('items/suggest_category'); ?>", {
			max: 100,
			minChars: 0,
			delay: 10
		});
		$("#category").result(function(event, data, formatted) {});
		$("#category").search();

		// Validação e envio do formulário
		$('#item_form').validate({
			submitHandler: function(form) {
				$('#item_number').val($('#scan_item_number').val());
				$(form).ajaxSubmit({
					success: function(response) {
						tb_remove();
						post_item_form_submit(response);
					},
					dataType: 'json'
				});
			},
			errorLabelContainer: "#error_message_box",
			wrapper: "li",
			ignore: "#photos",
			rules: {
				name: "required",
				category: "required",
				supplier_id: "required",
				cost_price: {
					required: true,
					number: true
				},
				unit_price: {
					required: true,
					number: true
				},
				tax_percent: {
					required: true,
					number: true
				},
				quantity: {
					required: true,
					number: true
				},
				reorder_level: {
					required: true,
					number: true
				},
				sale_price: {
					required: function(element) {
						return $('#sinc_wc').is(':checked');
					},
					number: true,
					min: 0.01 // Valor mínimo permitido para sale_price
				},
				// Adicionar as regras para altura, largura e comprimento
				altura: {
					required: function(element) {
						return $('#sinc_wc').is(':checked'); // Campo obrigatório se 'Enviar Para WC' estiver marcado
					},
					number: true
				},
				largura: {
					required: function(element) {
						return $('#sinc_wc').is(':checked');
					},
					number: true
				},
				comprimento: {
					required: function(element) {
						return $('#sinc_wc').is(':checked');
					},
					number: true
				},
				peso: {
					required: function(element) {
						return $('#sinc_wc').is(':checked');
					},
					number: true
				}
				
			},
			messages: {
				name: "<?php echo $this->lang->line('items_name_required'); ?>",
				category: "<?php echo $this->lang->line('items_category_required'); ?>",
				supplier_id: "<?php echo $this->lang->line('items_supplier_required'); ?>",
				cost_price: {
					required: "<?php echo $this->lang->line('items_cost_price_required'); ?>",
					number: "<?php echo $this->lang->line('items_cost_price_number'); ?>"
				},
				unit_price: {
					required: "<?php echo $this->lang->line('items_unit_price_required'); ?>",
					number: "<?php echo $this->lang->line('items_unit_price_number'); ?>"
				},
				tax_percent: {
					required: "<?php echo $this->lang->line('items_tax_percent_required'); ?>",
					number: "<?php echo $this->lang->line('items_tax_percent_number'); ?>"
				},
				quantity: {
					required: "<?php echo $this->lang->line('items_quantity_required'); ?>",
					number: "<?php echo $this->lang->line('items_quantity_number'); ?>"
				},
				reorder_level: {
					required: "<?php echo $this->lang->line('items_reorder_level_required'); ?>",
					number: "<?php echo $this->lang->line('items_reorder_level_number'); ?>"
				},
				sale_price: {
					required: "O preço do site é obrigatório quando 'Enviar Para WC' está marcado.",
					number: "O preço do site deve ser um número válido.",
					min: "O preço do site deve ser maior que zero."
				},
				altura: {
					required: "A altura é obrigatória quando 'Enviar Para WC' está marcado.",
					number: "A altura deve ser um número válido."
				},
				largura: {
					required: "A largura é obrigatória quando 'Enviar Para WC' está marcado.",
					number: "A largura deve ser um número válido."
				},
				comprimento: {
					required: "O comprimento é obrigatório quando 'Enviar Para WC' está marcado.",
					number: "O comprimento deve ser um número válido."
				},
				peso: {
					required: "O peso é obrigatório quando 'Enviar Para WC' está marcado.",
					number: "O peso deve ser um número válido."
				}
			}
		});

		// Verifica o estado do checkbox 'Enviar Para WC' e ativa/desativa a validação
		$('#sinc_wc').change(function() {
			$('#item_form').validate().element('#altura');
			$('#item_form').validate().element('#largura');
			$('#item_form').validate().element('#comprimento');
		});
	});
</script>
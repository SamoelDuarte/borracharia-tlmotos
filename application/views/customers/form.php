<?php
echo form_open('customers/save/'.$person_info->person_id,array('id'=>'customer_form'));
?>


<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box"></ul>
<fieldset id="customer_basic_info">
<legend><?php echo $this->lang->line("customers_basic_information"); ?></legend>
<?php $this->load->view("people/form_basic_info"); ?>


<?php
echo form_submit(array(
	'name'=>'submit',
	'id'=>'submit',
	'value'=>$this->lang->line('common_submit'),
	'class'=>'submit_button float_right')
);
?>
</fieldset>
<?php 
echo form_close();
?>
<script type='text/javascript'>

    

//validation and submit handling
$(document).ready(function() {
    
    $("#category").autocomplete("<?php echo site_url('items/suggest_category');?>", {
        max: 100,
        minChars: 0,
        delay: 10,
        multiple: true, // Permite seleção múltipla
        multipleSeparator: ",", // Usado para separar itens selecionados quando múltiplos
        formatItem: function(data, i, n, value, term) {
            // Customiza o formato de exibição do item
            return value;
        },
        formatResult: function(data, value) {
            // Customiza o formato do resultado
            return value;
        }
    }).result(function(event, data, formatted) {
        // Captura a seleção do usuário
        console.log("Selecionado:", formatted);
    });
    $("#category").result(function(event, data, formatted){});
	$("#category").search();
    // Adicione uma regra de validação personalizada
    $.validator.addMethod("validaDocumento", function(value, element) {
        // Chame a função que valida CPF ou CNPJ
        return validaCpfCnpj(value);
    }, "CPF ou CNPJ inválido");

    // Inicialize a validação do formulário
    $('#customer_form').validate({
        submitHandler: function(form) {
            $(form).ajaxSubmit({
                success: function(response) {
                    tb_remove();
                    post_person_form_submit(response);
                },
                dataType: 'json'
            });
        },
        errorLabelContainer: "#error_message_box",
        wrapper: "li",
        rules: {
            first_name: "required",
            last_name: "required",
            email: "email",
            document: {
                required: true,
                validaDocumento: true // Use a nova regra de validação
            }
        },
        messages: {
            first_name: "<?php echo $this->lang->line('common_first_name_required'); ?>",
            last_name: "<?php echo $this->lang->line('common_last_name_required'); ?>",
            email: "<?php echo $this->lang->line('common_email_invalid_format'); ?>",
            document: "CPF ou CNPJ inválido" // Mensagem de erro personalizada para o campo 'document'
        }
    });
	function validaCpfCnpj(val) {
    // Limpa caracteres não numéricos
    val = val.replace(/[^0-9]/g, '');

    // Verifica se é CPF (11 dígitos)
    if (val.length === 11) {
        if (!isValidCPF(val)) {
            return false;
        }
    }
    // Verifica se é CNPJ (14 dígitos)
    else if (val.length === 14) {
        if (!isValidCNPJ(val)) {
            return false;
        }
    } else {
        return false; // Se não tem 11 ou 14 dígitos, é inválido
    }

    return true;
}

// Função para validar CPF
function isValidCPF(cpf) {
    cpf = cpf.replace(/[^\d]+/g, ''); // Remove caracteres não numéricos

    if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) {
        return false; // CPF deve ter 11 dígitos e não pode ter todos os dígitos iguais
    }

    let sum = 0;
    let remainder;

    for (let i = 1; i <= 9; i++) {
        sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
    }

    remainder = (sum * 10) % 11;

    if ((remainder === 10) || (remainder === 11)) {
        remainder = 0;
    }

    if (remainder !== parseInt(cpf.substring(9, 10))) {
        return false;
    }

    sum = 0;
    for (let i = 1; i <= 10; i++) {
        sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
    }

    remainder = (sum * 10) % 11;

    if ((remainder === 10) || (remainder === 11)) {
        remainder = 0;
    }

    return remainder === parseInt(cpf.substring(10, 11));
}

// Função para validar CNPJ
function isValidCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]+/g, ''); // Remove caracteres não numéricos

    if (cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) {
        return false; // CNPJ deve ter 14 dígitos e não pode ter todos os dígitos iguais
    }

    let size = cnpj.length - 2;
    let numbers = cnpj.substring(0, size);
    const digits = cnpj.substring(size);
    let sum = 0;
    let pos = size - 7;

    for (let i = size; i >= 1; i--) {
        sum += parseInt(numbers.charAt(size - i)) * pos--;
        if (pos < 2) {
            pos = 9;
        }
    }

    let result = sum % 11 < 2 ? 0 : 11 - (sum % 11);

    if (result !== parseInt(digits.charAt(0))) {
        return false;
    }

    size = size + 1;
    numbers = cnpj.substring(0, size);
    sum = 0;
    pos = size - 7;

    for (let i = size; i >= 1; i--) {
        sum += parseInt(numbers.charAt(size - i)) * pos--;
        if (pos < 2) {
            pos = 9;
        }
    }

    result = sum % 11 < 2 ? 0 : 11 - (sum % 11);

    return result === parseInt(digits.charAt(1));
}


});





</script>
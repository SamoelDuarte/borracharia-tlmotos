<?php $this->load->view("partial/header"); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<div class="container mt-5">
    <a href="#" title="Nova Categoria" data-toggle="modal" data-target="#newCategoryModal">
        <div class="big_button" style="float: left;">
            <span>Nova Categoria</span>
        </div>
    </a>
    <table id="categories_table" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Woocommerce</th>
                <th>Ações</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newCategoryModalLabel">Nova Categoria</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newCategoryForm">
                    <div class="form-group">
                        <label for="categoryName">Nome da Categoria</label>
                        <input type="text" class="form-control" id="categoryName" required>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="insertToWC">
                        <label class="form-check-label" for="insertToWC">Inserir no WooCommerce</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveNewCategory">Salvar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        let table = $('#categories_table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?php echo site_url('categories/get_categories_data'); ?>",
                "type": "POST"
            },
            "columns": [{
                    "data": "category_name"
                },
                {
                    "data": "wc_id",
                    "orderable": false,
                    "searchable": false
                },
                {
                    "data": "actions",
                    "orderable": false,
                    "searchable": false
                }
            ]
        });

        // Editar/Salvar funcionalidade
        $('#categories_table').on('click', '.edit-category', function() {
            let row = $(this).closest('tr');
            let data = table.row(row).data();
            let categoryId = $(this).data('id');

            // Converte o campo de nome em um input editável
            let categoryNameCell = row.find('td').eq(0);
            let currentName = categoryNameCell.text();
            categoryNameCell.html('<input type="text" class="form-control" value="' + currentName + '">');

            // Altera botões para Salvar e Cancelar
            $(this).removeClass('edit-category btn-warning').addClass('save-category btn-success').text('Salvar');
            row.find('.delete-category').removeClass('delete-category btn-danger').addClass('cancel-edit btn-secondary').text('Cancelar');
        });

        // Salvar funcionalidade
        $('#categories_table').on('click', '.save-category', function() {
            let row = $(this).closest('tr');
            let categoryId = $(this).data('id');
            let newName = row.find('input').val();

            // Envia a atualização para o backend
            $.ajax({
                url: '<?php echo site_url("categories/update"); ?>',
                type: 'POST',
                data: {
                    category_id: categoryId,
                    category_name: newName
                },
                success: function(response) {
                    var parsedResponse = JSON.parse(response);
                    if (parsedResponse.success) {
                        table.ajax.reload(); // Atualiza a tabela
                    } else {
                        alert(parsedResponse.message);
                    }
                }
            });
        });

        // Cancelar Edição
        $('#categories_table').on('click', '.cancel-edit', function() {
            table.ajax.reload();
        });

        // Enviar para WC com loading
        $('#categories_table').on('click', '.send-to-wc', function() {
            let categoryId = $(this).data('id');
            let button = $(this); // Guarda referência do botão

            // Desativa o botão e adiciona o spinner
            button.prop('disabled', true);
            let originalText = button.text(); // Salva o texto original
            button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');

            $.ajax({
                url: '<?php echo site_url("categories/send_to_wc"); ?>',
                type: 'POST',
                data: {
                    category_id: categoryId
                },
                success: function(response) {
                    var response = JSON.parse(response);
                    if (response.success) {
                        table.ajax.reload(); // Atualiza a tabela
                    } else {
                        alert(response.message);
                    }
                },
                complete: function() {
                    // Restaura o texto original e reativa o botão após a resposta
                    button.prop('disabled', false);
                    button.html(originalText);
                }
            });
        });


        // Salvar nova categoria
        $('#saveNewCategory').on('click', function() {
            let categoryName = $('#categoryName').val();
            let insertToWC = $('#insertToWC').is(':checked') ? 1 : 0; // 1 se checado, 0 se não

            $.ajax({
                url: '<?php echo site_url("categories/create"); ?>', // A URL para criar uma nova categoria
                type: 'POST',
                data: {
                    category_name: categoryName,
                    wc: insertToWC
                },
                success: function(response) {
                    var parsedResponse = JSON.parse(response);
                    if (parsedResponse.success) {
                        table.ajax.reload(); // Atualiza a tabela
                        $('#newCategoryModal').modal('hide'); // Fecha o modal
                        $('#newCategoryForm')[0].reset(); // Reseta o formulário
                    } else {
                        alert(parsedResponse.message);
                    }
                }
            });
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>
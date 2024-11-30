<?php $this->load->view("partial/header"); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
    /* Estilos para os status */
    .status-concluido {
        color: green;
        font-weight: bold;
    }

    .status-pendente {
        color: orange;
        font-weight: bold;
    }

    .status-processando {
        color: blue;
        font-weight: bold;
    }

    .status-em-espera {
        color: gray;
        font-weight: bold;
    }

    .status-cancelado {
        color: red;
        font-weight: bold;
    }

    .status-reembolsado {
        color: purple;
        font-weight: bold;
    }

    .status-falhou {
        color: darkred;
        font-weight: bold;
    }
</style>

<div class="container mt-5">
    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="startDate">Data Início</label>
            <input type="date" id="startDate" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="endDate">Data Fim</label>
            <input type="date" id="endDate" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="statusFilter">Status</label>
            <select id="statusFilter" class="form-control">
                <option value="">Todos</option>
                <option value="Concluído">Concluído</option>
                <option value="Pendente">Pendente</option>
                <option value="Processando">Processando</option>
                <option value="Em espera">Em espera</option>
                <option value="Cancelado">Cancelado</option>
                <option value="Reembolsado">Reembolsado</option>
                <option value="Falhou">Falhou</option>
            </select>
        </div>
    </div>

    <!-- Tabela -->
    <table id="ordersTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID do Pedido</th>
                <th>Status</th>
                <th>Data</th>
                <th>Valor Total</th>
                <th>Desconto</th>
                <th>Frete</th>
                <th>Método de Pagamento</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td class="status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                        <?php echo $order['status']; ?>
                    </td>
                    <td><?php echo $order['created_at']; ?></td>
                    <td><?php echo $order['total_amount']; ?></td>
                    <td><?php echo $order['discount_total']; ?></td>
                    <td><?php echo $order['shipping_cost']; ?></td>
                    <td><?php echo $order['payment_method']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- Script para inicializar o DataTable -->
<script>
    $(document).ready(function() {
        var table = $('#ordersTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Portuguese-Brasil.json"
            }
        });

        // Extensão do DataTables para filtros customizados
        $.fn.dataTable.ext.search.push(function(settings, data) {
            var minDate = $('#startDate').val();
            var maxDate = $('#endDate').val();
            var status = $('#statusFilter').val();
            var orderDate = data[2]; // Coluna de Data no formato "dd/mm/yyyy"
            var orderStatus = data[1]; // Coluna de Status

            // Converter a data da tabela para formato "yyyy-mm-dd"
            var parsedDate = orderDate.split('/').reverse().join('-');

            // Verificar se a data está no intervalo
            if ((minDate && parsedDate < minDate) || (maxDate && parsedDate > maxDate)) {
                return false;
            }

            // Verificar se o status corresponde
            if (status && orderStatus !== status) {
                return false;
            }

            return true;
        });

        // Atualizar tabela quando filtros forem alterados
        $('#startDate, #endDate, #statusFilter').on('change', function() {
            table.draw();
        });
    });
</script>



<?php $this->load->view("partial/footer"); ?>
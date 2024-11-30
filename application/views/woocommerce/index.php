<?php $this->load->view("partial/header"); ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<div class="container mt-5">
<h3>Total de Produtos no WooCommerce: <?php echo $total_products; ?></h3>

    <!-- Botão de recarregar a página -->
    <div class="d-flex justify-content-end mb-4">
        <button id="reloadPage" class="btn btn-success">Recarregar a Página</button>
    </div>

    <!-- Cartão para Itens Pendentes de Sincronização -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title">Itens Pendentes de Sincronização</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($pending_sync_items)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>ID do Item</th>
                                <th>Nome do Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_sync_items as $item): ?>
                                <tr>
                                    <td><?php echo $item['item_id']; ?></td>
                                    <td><?php echo $item['name']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Não há itens pendentes para sincronização.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cartão para Itens Pendentes de Atualização -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Itens Pendentes de Atualização</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($pending_update_items)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>ID do Item</th>
                                <th>Nome do Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_update_items as $item): ?>
                                <tr>
                                    <td><?php echo $item['item_id']; ?></td>
                                    <td><?php echo $item['name']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Não há itens pendentes para atualização.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- Script para recarregar a página -->
<script>
    $(document).ready(function() {
        $('#reloadPage').click(function() {
            location.reload(); // Recarrega a página
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>

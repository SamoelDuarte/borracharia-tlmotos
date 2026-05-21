<?php $this->load->view("partial/header"); ?>
<div id="page_title" style="margin-bottom:8px;">Cupom Reconstruido - POS <?php echo $sale_id; ?></div>
<div id="page_subtitle" style="margin-bottom:8px;">Venda removida da tabela principal (dados via historico de estoque)</div>

<div style="margin-bottom:12px;">
	<strong>Data:</strong> <?php echo $summary['sale_date']; ?><br />
	<strong>Vendido por:</strong> <?php echo $summary['employee_name']; ?><br />
	<strong>Itens:</strong> <?php echo $summary['items_purchased']; ?><br />
</div>

<table class="tablesorter report" id="sortable_table">
	<thead>
		<tr>
			<th>Item</th>
			<th>Categoria</th>
			<th>Qtd</th>
			<th>Preco Unit.</th>
			<th>Subtotal</th>
			<th>Total</th>
			<th>Imposto</th>
			<th>Lucro</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($details as $row) { ?>
		<tr>
			<td><?php echo $row['name']; ?></td>
			<td><?php echo $row['category']; ?></td>
			<td><?php echo $row['quantity_purchased']; ?></td>
			<td><?php echo to_currency($row['unit_price']); ?></td>
			<td><?php echo to_currency($row['subtotal']); ?></td>
			<td><?php echo to_currency($row['total']); ?></td>
			<td><?php echo to_currency($row['tax']); ?></td>
			<td><?php echo to_currency($row['profit']); ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<div id="report_summary" style="margin-top:14px;">
	<div class="summary_row">Subtotal: <?php echo to_currency($summary['subtotal']); ?></div>
	<div class="summary_row">Total: <?php echo to_currency($summary['total']); ?></div>
	<div class="summary_row">Impostos: <?php echo to_currency($summary['tax']); ?></div>
	<div class="summary_row">Lucro: <?php echo to_currency($summary['profit']); ?></div>
</div>

<?php $this->load->view("partial/footer"); ?>

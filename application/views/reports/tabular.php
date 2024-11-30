<?php
//OJB: Check if for excel export process
if ($export_excel == 1) {
	ob_start();
	$this->load->view("partial/header_excel");
} else {
	$this->load->view("partial/header");
}
?>

<style>
	.total-finish {
		-webkit-text-stroke: medium;
		display: flex;
		flex-direction: column;
	}
</style>
<div id="page_title" style="margin-bottom:8px;"><?php echo $title ?></div>
<div id="page_subtitle" style="margin-bottom:8px;"><?php echo $subtitle ?></div>
<div id="table_holder">
	<table class="tablesorter report" id="sortable_table">
		<thead>
			<tr>
				<?php foreach ($headers as $header) { ?>
					<th><?php echo $header; ?></th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php
			$totalStock = 0;
			$priceStockSale = 0;
			$priceStockCost = 0;


			$totalCost = 0; // Inicializando a variável $totalCost
			$totalSale = 0; // Inicializando a variável $totalSale

			foreach ($data as $row) { ?>
				<tr>
					<?php
					foreach ($row as $key => $cell) {
						switch ($key) {
							case "quantity":
								$totalStock += intval($cell);
								break;
							case "unit_price":
								$priceStockSale += floatval($cell);
								break;
							case "cost_price":
								$priceStockCost += floatval($cell);
								break;
							default:
								// Adicione um bloco "default" caso a chave não corresponda a nenhum caso.
								break;
						}
					?>
						<td><?php echo $cell; ?></td>
					<?php } ?>
				</tr>
			<?php
				$totalCost += (isset($row['quantity']) && isset($row['cost_price']))
					? $row['quantity'] * $row['cost_price']
					: 0;

				$totalSale += (isset($row['quantity']) && isset($row['unit_price']))
					? $row['quantity'] * $row['unit_price']
					: 0;
			} ?>

			<?php
			if (isset($has)) {


			?>
				<tr>
					<td>
						<label for=""><b>Estoque Total : </b><?php echo $totalStock ?></label>
					</td>
				</tr>
				<tr>
					<td>
						<label for=""><b>Total Preço de Venda : </b><?php echo number_format($totalSale, 2, ',', '.') ?></label>
					</td>
				</tr>
				<tr>
					<td>
						<label for=""><b>Total Preço de Compra : </b><?php echo number_format($totalCost, 2, ',', '.') ?></label>
					</td>
				</tr>
			<?php
			}
			?>

		</tbody>
	</table>
</div>
<div id="report_summary">
	<?php foreach ($summary_data as $name => $value) { ?>
		<div class="summary_row"><?php echo $this->lang->line('reports_' . $name) . ': ' . to_currency($value); ?></div>
	<?php } ?>
</div>
<?php
if ($export_excel == 1) {
	$this->load->view("partial/footer_excel");
	$content = ob_end_flush();

	$filename = trim($filename);
	$filename = str_replace(array(' ', '/', '\\'), '', $title);
	$filename .= "_Export.xls";
	header('Content-type: application/ms-excel');
	header('Content-Disposition: attachment; filename=' . $filename);
	echo $content;
	die();
} else {
	$this->load->view("partial/footer");
?>

	<script type="text/javascript" language="javascript">
		function init_table_sorting() {
			//Only init if there is more than one row
			if ($('.tablesorter tbody tr').length > 1) {
				$("#sortable_table").tablesorter();
			}
		}
		$(document).ready(function() {
			init_table_sorting();
		});
	</script>
<?php
} // end if not is excel export 
?>
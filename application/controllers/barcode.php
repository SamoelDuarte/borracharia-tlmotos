<?php
class Barcode extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		require_once FCPATH . 'vendor/autoload.php';
	
		ob_clean(); // Limpa qualquer saída anterior
	
		$barcode = $this->input->get('barcode');
		$text = $this->input->get('text', true);
		$width = (int) $this->input->get('width', true) ?: 250;
		$height = (int) $this->input->get('height', true) ?: 50;
	
		if (!$barcode || !$text) {
			show_error('Parâmetros "barcode" e "text" são obrigatórios.', 400);
		}
	
		$barcodeOptions = [
			'text' => $barcode,
			'barHeight' => $height,
			'factor' => $width / 100,
		];
	
		$rendererOptions = [
			'imageType' => 'png',
		];
	
		$barcode = \Laminas\Barcode\Barcode::factory('code128', 'image', $barcodeOptions, $rendererOptions);
	
		ob_start(); // Inicia o buffer de saída
		$barcode->render();
		$imageData = ob_get_clean(); // Obtém a saída gerada
	
		header('Content-Type: image/png');
		header('Content-Length: ' . strlen($imageData));
	
		echo $imageData;
	}

	public function generate() {
		require_once FCPATH . 'vendor/autoload.php';
	
		ob_clean(); // Limpa qualquer saída anterior
	
		$barcode = $this->input->get('barcode');
		$text = $this->input->get('text', true);
		$width = (int) $this->input->get('width', true) ?: 250;
		$height = (int) $this->input->get('height', true) ?: 50;
	
		if (!$barcode || !$text) {
			show_error('Parâmetros "barcode" e "text" são obrigatórios.', 400);
		}
	
		$barcodeOptions = [
			'text' => $barcode,
			'barHeight' => $height,
			'factor' => $width / 100,
		];
	
		$rendererOptions = [
			'imageType' => 'png',
		];
	
		$barcode = \Laminas\Barcode\Barcode::factory('code128', 'image', $barcodeOptions, $rendererOptions);
	
		ob_start(); // Inicia o buffer de saída
		$barcode->render();
		$imageData = ob_get_clean(); // Obtém a saída gerada
	
		header('Content-Type: image/png');
		header('Content-Length: ' . strlen($imageData));
	
		echo $imageData;
	}
}

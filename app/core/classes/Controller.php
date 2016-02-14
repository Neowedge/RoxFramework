<?php
namespace RoxFramework;

class Controller {

	protected $bundle;

	public function __construct($bundle) {
		$this->bundle = $bundle;
	}

	public function view($file,$params=array()) {
		if (!empty($params)) {
			extract($params);
		}
		
		include(APP_DIR."/view/{$this->bundle}/{$file}");
	}

	public function viewHtml($file) {
		include(APP_DIR."/view/{$this->bundle}/{$file}.html");
	}

	public function viewPHtml($file, $params=array()) {
		if (!empty($params)) {
			extract($params);
		}

		include(APP_DIR."/view/{$this->bundle}/{$file}.phtml");
	}

	public function viewImageFile($file) {
		header("Content-Type: image/png");
		header("Content-Length: " . filesize($file));

		//$fileParts = explode(%);

		//TODO
		//header
		//etc.
	}

	public function viewImage($image, $format, $params=array()) {

		switch ($format) {
			case 'jpg':
			case 'jpeg':

				header("Content-type: image/jpeg");
				imagejpeg($image, null, isset($params['quality']) ? $params['quality'] : 80);
				break;
		}
	}
}
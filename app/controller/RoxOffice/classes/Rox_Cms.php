<?php
namespace RoxOffice\Controllers;

include(APP_DIR.'/controller/RoxOffice/interfaces/Rox_IConfigParser.php');
include(APP_DIR.'/controller/RoxOffice/classes/Rox_EntityManager.php');
include(APP_DIR.'/controller/RoxOffice/classes/Rox_EntityField.php');
include(APP_DIR.'/controller/RoxOffice/classes/Rox_Layout.php');
include(APP_DIR.'/controller/RoxOffice/classes/Rox_Layout_Field.php');
include(APP_DIR.'/controller/RoxOffice/classes/Rox_Template.php');
include(APP_DIR.'/controller/RoxOffice/classes/Rox_Office.php');

final class Rox_Cms {
	public $Office;

	public function __construct($office) {
		$this->Office = $office;
	}

	static function create($configFile, $currentOffice) {
		$cms = null;

		$ext = explode('.', $configFile);
		$ext = end($ext);

		switch ($ext) {
			case 'xml':
				include(APP_DIR.'/controller/RoxOffice/classes/parsers/Rox_XmlConfigParser.php');
				$cms = self::parse('RoxOffice\Controllers\Rox_XmlConfigParser', $configFile, $currentOffice);
				break;

			case 'php':
				include(APP_DIR.'/controller/RoxOffice/classes/parsers/Rox_PhpConfigParser.php');
				$cms = self::parse('RoxOffice\Controllers\Rox_PhpConfigParser', $configFile, $currentOffice);
				break;
		}

		return $cms;
	}

	static function parse($parser, $configFile, $currentOffice) {
		$cms = $parser::parse($configFile, $currentOffice);
		return $cms;
	}
}
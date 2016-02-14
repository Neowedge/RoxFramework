<?php
namespace RoxOffice\Controllers;

class Rox_PhpConfigParser implements Rox_IConfigParser {
	static function parse($fileName, $officeName) {
		if (!file_exists($fileName)) {
			return false;
		}

		return include($fileName);
	}

	static function toFile(Rox_Cms $cms) {
		return false;
	}
}
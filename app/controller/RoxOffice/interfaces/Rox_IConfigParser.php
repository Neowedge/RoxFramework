<?php
namespace RoxOffice\Controllers;

interface Rox_IConfigParser {

	static function parse($fileName, $officeName);
	static function toFile(Rox_Cms $cms);
}

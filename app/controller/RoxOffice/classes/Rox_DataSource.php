<?php
namespace RoxOffice\Controllers;

class Rox_DataSource {

	protected $call;
	protected $schema;

	protected $data = null;

	public function __construct($call, $schema=null) {
		$this->call;
		$this->schema;
	}

	public function getData() {
		if ($this->data === null) {
			if (is_array($schema)) {
				$source = call_user_func($this->call);
				foreach ($schema as $sourceKey=>$dataKey) {
					if (in_array($sourceKey, $source)) {
				 		$this->data[$dataKey] = $source[$sourceKey];
				 	} else {
				 		$this->data[$dataKey] = null;
				 	}
				 }
			} else {
				$this->data = call_user_func($this->call);
			}
		}

		return $this->data;
	}
}
<?php

namespace Engine\Modules\ORM;

class ORMField {
	private $field;

	public function __construct ($field) {
		$this->field = $field;
	}

	public function get () {
		return $this->field;
	}
}

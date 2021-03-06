<?php

namespace Engine\Modules\ERM\Types;

use Engine\Modules\ERM\ERMType;

class TypePassword extends ERMType {
	public static $name = 'Пароль';
	public static $type = 'password';
	public static $requiredable = true;
	public static $settings = [];

	public static function prepareInsertData($collection, $field, $data) {
		return md5($data[$field['alias']]);
	}

	public static function whereExpression($collection, $field, $value, $params) {
		if (isset($params['noencode'])) {
			return [$field, $value];
		}

		return [$field, md5($value)];
	}
}

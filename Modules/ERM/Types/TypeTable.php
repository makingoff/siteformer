<?php

namespace Engine\Modules\ERM\Types;

use Engine\Modules\ERM\ERMType;

class TypeTable extends ERMType {
	public static $name = 'Таблица';
	public static $type = 'table';
	public static $requiredable = false;
	public static $settings = [
		'data' => [
			['', '', '', ''],
			['', '', '', ''],
			['', '', '', '']
		]
	];

	public static function getSqlField($params) {
		$defaultData = json_encode($params['defaultData']);

		return [
			'type' => 'TEXT',
			'default' => $defaultData,
			'null' => false
		];
	}

	public static function getDefaultData($settings) {
		return $settings['defaultData'];
	}

	public static function prepareInsertData($collection, $field, $data) {
		return json_encode($data[$field['alias']], true);
	}

	public static function prepareUpdateData($collection, $field, $currentData, $data) {
		return json_encode($data[$field['alias']], true);
	}

	public static function postProcessData($collection, $field, $data) {
		$data[$field['alias']] = parseJSON($data[$field['alias']]);

		return $data;
	}
}

<?php

namespace Engine\Modules\ERM\Types;

use Engine\Classes\Validate;
use Engine\Modules\ERM\ERMType;
use Engine\Modules\ORM\ORM;

class TypeCheckbox extends ERMType {
	public static $name = 'Флажки';
	public static $type = 'checkbox';
	public static $requiredable = false;
	public static $settings = [
		'values' => [[
			'label' => '',
			'checked' => false
		]]
	];

	public static function getSqlField($params) {
		$values = [];
		$defaultValue = 0;

		foreach ($params['values'] as $index => $param) {
			if ($param['checked'] === true) {
				$defaultValue |= 1 << $index;
			}
		}

		return [
			'type' => 'INT(60)',
			'default' => $defaultValue
		];
	}

	public static function validateSettings($settings, $fields, $currentAlias, $indexes = []) {
		return Validate::value([
			'values' => [
				'minlength' => 1,
				'collection' => [
					'label' => [
						'required' => true,
						'unique' => true,
						'skipempty' => true
					],
					'checked' => []
				]
			]
		], $settings, $indexes);
	}

	public static function prepareInsertData($collection, $field, $data) {
		if (gettype($data[$field['alias']]) === 'integer') return 1 << $data[$field['alias']];

		$value = 0;

		foreach ($data[$field['alias']] as $item) {
			$value |= 1 << $item;
		}

		return $value;
	}

	public static function getDefaultData($settings) {
		$data = 0;

		foreach ($settings['values'] as $index => $field) {
			if ($field['checked']) {
				$data |= 1 << $index;
			}
		}

		return $data;
	}

	public static function prepareDataForEditForm($value, $settings) {
		$data = [];

		foreach ($settings['values'] as $index => $row) {
			$data[] = [
				'label' => $row['label'],
				'checked' => $index & $value
			];
		}

		return $data;
	}

	public static function whereExpression($collection, $field, $value, $params = false) {
		$insertions = [];
		$options = [
			'collection' => $collection,
			'field' => $field
		];

		foreach ($value as $index => $item) {
			$insertions[] = ':field & :value' . $index;
			$options['value' . $index] = 1 << $item;
		}

		$joinStr = ' AND ';

		if ($params !== false) {
			if ($params === 'any') {
				$joinStr = ' OR ';
			}
		}

		return ORM::generateValue(implode($joinStr, $insertions), $options);
	}
}

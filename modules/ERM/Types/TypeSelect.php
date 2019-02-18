<?php

require_once __DIR__ . '/../../../classes/validate.php';
require_once __DIR__ . '/../ERMType.php';

class SFTypeSelect extends SFERMType {
  public static $name = 'Выпадайка';
  public static $type = 'select';
  public static $requiredable = false;
  public static $settings = [
    'checked' => -1,
    'values' => ['']
  ];

  public static function getSqlField($params) {
    return [
      'type' => 'ENUM("' . implode('", "', array_keys($params['values'])) . '")',
      'default' => $params['default']
    ];
  }

  public static function validateSettings($settings, $fields, $currentAlias, $indexes = []) {
    return SFValidate::value([
      'default' => array_keys($settings['values']),
      'values' => [
        'type' => 'array'
      ]
    ], $settings, $indexes);
  }

  public static function postProcessData($collection, $field, $data) {
    $data[$field['alias']] = [
      'title' => $field['settings']['values'][$data[$field['alias']]],
      'value' => $data[$field['alias']]
    ];

    return $data;
  }

  public static function getDefaultData($settings) {
    return $settings['default'];
  }
}
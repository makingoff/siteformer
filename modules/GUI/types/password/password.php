<?php if (!defined('ROOT')) die('You can\'t just open this file, dude');

require_once ENGINE . 'classes/validate.php';
require_once __DIR__ . '/../../GUIType.php';

class SFTypePassword extends SFGUIType
{
  public static function prepareInsertData($section, $field, $data) {
    return md5($data[$field['alias']]);
  }
}

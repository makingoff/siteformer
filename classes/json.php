<?php if (!defined('ROOT')) die('You can\'t just open this file, dude');

function parseJSON($str) {
  return prepareJSONResponse(json_decode($str, true));
}

function prepareJSONResponse($obj) {
  if (gettype($obj) === 'array') {
    foreach ($obj as $key => $value) {
      $obj[$key] = prepareJSONResponse($value);
    }
  } elseif (gettype($obj) === 'string') {
    if (preg_match('/^(true|false)$/', $obj)) {
      return $obj === 'true' ? true : false;
    }

    if (preg_match('/^\d+$/', $obj)) {
      return (int)$obj;
    }

    if (preg_match('/^\d+\.\d+/', $obj)) {
      return (float)$obj;
    }
  }

  return $obj;
}
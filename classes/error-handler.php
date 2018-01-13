<?php

function echoError($message) {
  if (APPLICATION_ENV !== 'production') {
    SFResponse::error(500, $message);
  } else {
    SFResponse::error(500, 'Произошла некоторая ошибка');
  }
}

function shutdownHandler() {
  $error = error_get_last();

  switch ($error['type']) {
    case E_ERROR:
    case E_CORE_ERROR:
    case E_COMPILE_ERROR:
    case E_USER_ERROR:
    case E_RECOVERABLE_ERROR:
    case E_CORE_WARNING:
    case E_COMPILE_WARNING:
    case E_PARSE:
      echoError($error['message'] . ' at ' . $error['file'] . ':' . $error['line']);
  }
}

function errorHandler($severity, $msg, $file, $line) {
  if (error_reporting() & $severity) {
    echoError($msg . ' at ' . $file . ':' . $line);
  }
}

function exceptionHandler($exception) {
  echoError([
    'message' => $exception->getMessage() . ' at ' . $exception->getFile() . ':' . $exception->getLine(),
    'trace' => $exception->getTrace()
  ]);
}
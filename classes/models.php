<?php if (!defined('ROOT')) die('You can\'t just open this file, dude');

  require_once ENGINE.'classes/text.php';

  class SFModels
  {
    private static $paths = [];

    public static function factory($model)
    {
      $path = explode('/', $model);
      require_once ACTIONS . $model . '.php';
      $model = $path[count($path) - 1];
      $className = 'SF' . SFText::camelCasefy($model, true);
      return new $className;
    }

    public static function registerPath ($path) {
      self::$paths[] = SFPath::prepareDir($path);
    }

    public static function get ($model, $params) {
      if ($className = self::getClassNameByModelName($model)) {
        return call_user_func([$className, 'get'], $params);
      }

      return false;
    }

    public static function post ($model, $params) {
      if ($className = self::getClassNameByModelName($model)) {
        return call_user_func([$className, 'post'], $params);
      }

      return false;
    }

    private static function getClassNameByModelName($model) {
      $path = explode('/', $model);
      $finded = false;

      foreach (self::$paths as $pathItem) {
        if (file_exists($pathItem . $model . '.php')) {
          require_once $pathItem . $model . '.php';

          $model = $path[count($path) - 1];
          return 'SF' . SFText::camelCasefy($model, true);
        }
      }

      return false;
    }
  }

?>

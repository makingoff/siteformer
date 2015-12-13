<?php

  class JsStringifier
  {
    private static $resultString = '';
    private static $arrIndex = 0;
    private static $TOP_BANNER = '(function (factory)
    {
      if (typeof module !== \'undefined\' && typeof module.exports !== \'undefined\') {
        module.exports = factory();
      }
      else if (typeof define !== \'undefined\' && typeof define.amd !== \'undefined\') {
        define(\'first-try\', [], factory());
      }
      else {
        window.';
    private static $TOP_BANNER_TWO = ' = factory();
      }
    })(function ()
    {
      var _hasProp = Object.prototype.hasOwnProperty;
      var _crEl = function (node)
      {
        return {type: \'node\', name: node, attrs: [], childs: []};
      };
      var _crTN = function (node)
      {
        return {type: \'text\', text: node};
      };
      function create()
      {
        if (arguments.length === 1) {
          var rootNodes = [];
          arguments[0](rootNodes);
          if (rootNodes.length) {
            for (indexAttr in rootNodes) {
              if (_hasProp.call(rootNodes, indexAttr)) {
                if (typeof rootNodes[indexAttr] === \'string\' || typeof rootNodes[indexAttr] === \'boolean\' || typeof rootNodes[indexAttr] === \'number\') {
                  rootNodes[indexAttr] = _crTN(rootNodes[indexAttr]);
                }
              }
            }
          }
          return rootNodes;
        }
        else if (arguments.length === 3) {
          var nodes = [];
          var parentNode;
          var indexNode;
          var node;
          var indexAttr;
          var attr;
          var attrs = arguments[1];
          arguments[2](nodes);
          parentNode = _crEl(arguments[0]);
          if (attrs.length) {
            for (indexAttr in attrs) {
              if (_hasProp.call(attrs, indexAttr)) {
                attr = attrs[indexAttr];
                parentNode.attrs.push({
                  name: attr[0],
                  value: attr[1]
                });
              }
            }
          }
          for (indexNode in nodes) {
            if (_hasProp.call(nodes, indexNode)) {
              node = nodes[indexNode];
              if (typeof node === \'string\' || typeof node === \'boolean\' || typeof node === \'number\') {
                parentNode.childs.push(_crTN(node));
              }
              else {
                parentNode.childs.push(node);
              }
            }
          }
          return parentNode;
        }
      }
      return function (obj)
      {
        return create(function (childs)
        {with (obj) {';
    private static $BOTTOM_BANNER = '}})};
    });';

    private static function getProperFilename($filename)
    {
      $filename = basename($filename);
      $filename_ = array();
      $currentname = '';
      for ($i = 0; $i < strlen($filename); $i++) {
        if (preg_match('/[^a-zA-Z0-9]/', $filename[$i])) {
          $filename_[] = $currentname;
          $currentname = '';
        }
        else {
          $currentname .= $filename[$i];
        }
      }
      if (!empty($currentname)) {
        $filename_[] = $currentname;
      }
      $currentname = '';
      for ($i = 1; $i < count($filename_); $i++) {
        $filename_[$i] = strtoupper($filename_[$i][0]) . substr($filename_[$i], 1);
      }
      return implode('', $filename_);
    }

    public static function stringify($tree, $filename)
    {
      self::$resultString = '';
      $filename = self::getProperFilename($filename);
      if ($tree !== false) self::recString($tree);
      return self::$TOP_BANNER . $filename . self::$TOP_BANNER_TWO . self::$resultString . self::$BOTTOM_BANNER;
    }

    private static function handleIf($exprs)
    {
      $values[] = 'if ( ';
      foreach ($exprs as $index => $expr) {
        if (!$index) continue;
        $values[] = self::handleRecursiveExpression($expr);
      }
      $values[] = ') {' . "\n";
      return implode('', $values);
    }

    private static function handleElseIf($exprs)
    {
      $values[] = '} else if ( ' . "\n";
      foreach ($exprs as $index => $expr) {
        if (!$index) continue;
        $values[] = self::handleRecursiveExpression($expr);
      }
      $values[] = ') {' . "\n";
      return implode('', $values);
    }

    private static function handleElse($exprs)
    {
      return '} else {' . "\n";
    }

    private static function handleEndif($exprs)
    {
      return '}' . "\n";
    }

    private static function getFirstElementFromConcat($expr)
    {
      if (get_class($expr) === 'MathConcat') {
        $elements = $expr->elements();
        return $elements[0];
      }
      return $expr;
    }

    private static function handleFor($exprs)
    {
      $arr = '';
      if (isset($exprs[3])) {
        $arr = 'var arr' . self::$arrIndex . ' = ' . self::handleRecursiveExpression($exprs[3]) . '; ';
      }
      else {
        $indexes = self::getFirstElementFromConcat($exprs[2])->indexes();
        if (count($indexes) && get_class($indexes[0]) === 'MathIndexRange') {
          $indexes = $indexes[0];
          $arr = 'var arr' . self::$arrIndex . ' = MakeArray(' . self::handleRecursiveExpression($indexes->leftPart()) .
            ', ' .
            self::handleRecursiveExpression($indexes->rightPart()) .
            ');' . "\n";
        }
      }
      if (self::getFirstElementFromConcat($exprs[2])->name() === 'revertin') {
        $arr .= 'arr' . self::$arrIndex . ' = arr' . self::$arrIndex . '.reverse();' . "\n";
      }
      $values = $arr;
      $values .= 'for (';
      $items = array();
      $value = '';
      if (get_class(self::getFirstElementFromConcat($exprs[1])) === 'LogicList') {
        $items = self::getFirstElementFromConcat($exprs[1])->getItems();
        $value = self::handleRecursiveExpression($items[0]);
      }
      else {
        $value = self::handleRecursiveExpression($exprs[1]);
      }
      $values .= $value;
      $values .= ' in arr' . self::$arrIndex . ') if (_hasProp.call(arr' . self::$arrIndex . ', ' . $value . ')) {' . "\n";
      if (get_class(self::getFirstElementFromConcat($exprs[1])) === 'LogicList') {
        $values .= self::handleRecursiveExpression($items[1]) . ' = arr' . self::$arrIndex . '[' . self::handleRecursiveExpression($items[0]) . '];' . "\n";
      }
      else {
        $values .= self::handleRecursiveExpression($exprs[1]) . ' = arr' . self::$arrIndex . '[' . self::handleRecursiveExpression($exprs[1]) . '];' . "\n";
      }
      self::$arrIndex++;
      return $values;
    }

    private static function handleEndfor($exprs)
    {
      return '}';
    }

    private static function handleRecursiveExpressions($exprs)
    {
      $values = array();
      if (gettype($exprs) === 'array') {
        foreach ($exprs as $expr) {
          $values[] = self::handleRecursiveExpression($expr);
        }
      }
      else if (gettype($exprs) === 'object') {
        return self::handleRecursiveExpression($exprs);
      }
      else {
        return $exprs;
      }
      return implode('', $values);
    }

    private static function handleRecursiveExpression($expr)
    {
      if (gettype($expr) !== 'object')  {
        if (gettype($expr) === 'boolean' || is_numeric($expr)) {
          return $expr;
        }
        return '"' . $expr . '"';
      }
      switch (get_class($expr)) {
        case 'MathVar':
          return self::handleMathVar($expr);
        case 'LogicNotEqual':
          return self::handleLogicNotEqual($expr);
        case 'MathFunction':
          return self::handleMathFunction($expr);
        case 'MathPlus':
          return self::handleMathPlus($expr);
        case 'MathMinus':
          return self::handleMathMinus($expr);
        case 'MathUMinus':
          return self::handleMathUMinus($expr);
        case 'MathDevide':
          return self::handleMathDevide($expr);
        case 'MathMultiply':
          return self::handleMathMultiply($expr);
        case 'MathNumber':
          return self::handleMathNumber($expr);
        case 'MathBrackets':
          return self::handleMathBrackets($expr);
        case 'LogicLTEqual':
          return self::handleLogicLTEqual($expr);
        case 'LogicGTEqual':
          return self::handleLogicLTEqual($expr);
        case 'LogicLT':
          return self::handleLogicLT($expr);
        case 'LogicGT':
          return self::handleLogicGT($expr);
        case 'LogicAnd':
          return self::handleLogicAnd($expr);
        case 'LogicOr':
          return self::handleLogicOr($expr);
        case 'LogicEqual':
          return self::handleLogicEqual($expr);
        case 'LogicNotEqual':
          return self::handleLogicEqual($expr);
        case 'MathConcat':
          return self::handleMathConcat($expr);
        case 'LogicAssigment':
          return self::handleLogicAssigment($expr);
        case 'LogicNot':
          return self::handleLogicNot($expr);
      }
    }

    private static function handleLogicNot($expr)
    {
      return '!(' . self::handleRecursiveExpression($expr->expr()) . ')';
    }

    private static function handleLogicAnd($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' && ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleLogicOr($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' || ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleMathConcat($expr)
    {
      $values = array();
      $elements = $expr->elements();
      foreach ($elements as $element) {
        $values[] = self::handleRecursiveExpression($element);
      }
      return implode(' + ', $values);
    }

    private static function handleMathMultiply($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' * ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleMathDevide($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' / ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleMathUMinus($expr)
    {
      return '-' . self::handleRecursiveExpression($expr->expr());
    }

    private static function handleMathMinus($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' - ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function LogicNotEqual($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' != ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleLogicEqual($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' == ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleLogicGT($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' > ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleLogicLT($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' < ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function LogicGTEqual($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' >= ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleLogicLTEqual($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' <= ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleMathBrackets($expr)
    {
      return '(' . self::handleRecursiveExpression($expr->expr()) . ')';
    }

    private static function handleMathNumber($expr)
    {
      return $expr->value();
    }

    private static function handleMathPlus($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' + ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleMathFunction($expr)
    {
      $values = $expr->name()->name() . '(';
      $params = $expr->params();
      $parameters = array();
      foreach ($params as $param) {
        $parameters[] = self::handleRecursiveExpression($param);
      }
      if (count($parameters)) {
        $values .= implode(', ', $parameters);
      }
      return $values . ')';
    }

    private static function handleLogicNotEqual($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' != ' .
        self::handleRecursiveExpression($expr->rightPart());
    }

    private static function handleMathVar($expr)
    {
      $isCheckVariable = $expr->isCheck();
      $values = '';
      $variableName = $expr->name();
      if ($isCheckVariable) {
        $values .= ' typeof ' . $variableName . ' !== \'undefined\' ? ';
      }
      $indexes = $expr->indexes();
      if (count($indexes)) {
        $values .= self::handleMathVarIndex($variableName, $indexes);
      }
      else {
        $values .= $variableName;
      }
      if ($isCheckVariable) {
        $values .= ' : \'\' ';
      }
      return $values;
    }

    private static function handleMathVarIndex($variableName, $indexes)
    {
      $values = '';
      if (count($indexes)) {
        $index = array_shift($indexes);

        $name = $index->name();
        $isCheck = $index->isCheck();

        $variable = '';
        if (is_numeric($name)) {
          $variable = '[' . $name . ']';
        }
        else if (gettype($name) === 'string') {
          $variable = '[\'' . $name . '\']';
        }
        else if (gettype($name) === 'object') {
          $variable = '[';
          $variable .= self::handleRecursiveExpression($name);
          $variable .= ']';
        }

        if ($isCheck) {
          return '( typeof ' . $variableName . $variable . ' != \'undefined\' ? ' . self::handleMathVarIndex($variableName . $variable, $indexes) . ' : \'\')';
        }
        else {
          return self::handleMathVarIndex($variableName . $variable, $indexes);
        }
      }
      else {
        return $variableName;
      }
      return $values;
    }

    private static function expandLogicExpressions($element)
    {
      $exprs = $element->exprs();
      if (get_class($element) === 'LogicNode') {
        return self::handleLogicExpressions($exprs);
      }
      else if (get_class($element) === 'LogicNodeEcho') {
        return self::handleRecursiveExpressions($exprs->exprs());
      }
      return '';
    }

    private static function handleLogicAssigment($expr)
    {
      return self::handleRecursiveExpression($expr->leftPart()) .
        ' = ' .
        self::handleRecursiveExpressions($expr->rightPart()) . ';';
    }

    private static function handleLogicExpressions($exprs)
    {
      $exprs = $exprs->exprs();
      if (get_class($exprs[0]) === 'MathConcat') {
        $elements = $exprs[0]->elements();
        if (count($elements) === 1) {
          switch ($elements[0]->name()) {
            case 'if':
              return self::handleIf($exprs);
            case 'else':
              return self::handleElse($exprs);
            case 'elseif':
              return self::handleElseIf($exprs);
            case 'endif':
              return self::handleEndif($exprs);
            case 'for':
              return self::handleFor($exprs);
            case 'endfor':
              return self::handleEndfor($exprs);
          }
        }
      }
      else if (get_class($exprs[0]) === 'LogicAssigment') {
        return self::handleRecursiveExpressions($exprs);
      }
    }

    private static function prepareAttribute($attribute)
    {
      if (get_class($attribute) == 'Attribute') {
        $values = $attribute->values();
        $value = array('(function () {' . "\n" . 'var attr = \'\';' . "\n");
        if (gettype($values) == 'array') {
          foreach ($values as $val) {
            if (gettype($val) == 'object' && (get_class($val) == 'LogicNode')) {
              $value[] = self::handleLogicNode($val);
            }
            elseif (gettype($val) == 'object' && (get_class($val) == 'LogicNodeEcho')) {
              $value[] = 'attr += ' . self::handleLogicNode($val) . ';' . "\n";
            }
            else {
              $value[] = 'attr += \'' . $val .  '\';' . "\n";
            }
          }
        }
        return implode('', $value) .
          'attrs.push([\'' . $attribute->name() . '\', attr]);' . "\n" .
          '})();' . "\n";
      }
      elseif (get_class($attribute) == 'LogicNode') {
        return self::handleLogicNode($attribute);
      }
      return '';
    }

    private static function prepareTag($element)
    {
      if ($element->type() == 'open') {
        self::$resultString .= '(function () {' . "\n" . 'var attrs = [];' . "\n";
        $attributes = $element->attributes();
        foreach ($attributes as $attribute) {
          self::$resultString .= self::prepareAttribute($attribute);
        }
        self::$resultString .= 'childs.push(create(\'' . $element->name() . '\', attrs, function (childs) {' . "\n";
        if (in_array($element->name(), array('hr', 'br', 'base', 'col', 'embed', 'img', 'area', 'source', 'track', 'input'))) {
          self::$resultString .= '}));' . "\n" . '})();' . "\n";
        }
      }
      else {
        self::$resultString .= '}));' . "\n" . '})();' . "\n";
      }
    }

    private static function prepareTagComment($element)
    {
    }

    private static function prepareTextNode($element)
    {
      $text = trim($element->text());
      if (strlen($text)) {
        self::$resultString .= 'childs.push(\'' . $element->text() . '\');' . "\n";
      }
    }

    private static function handleLogicNode($element)
    {
      if (get_class($element) === 'LogicNodeEcho') {
        $modifiers = $element->modifiers();
        if (count($modifiers)) {
          return self::handleLogicNodeModifiers($element, $modifiers);
        }
      }
      return self::expandLogicExpressions($element);
    }

    private static function handleLogicNodeModifiers($element, $modifiers)
    {
      if (count($modifiers)) {
        $modify = array_shift($modifiers);
        $params = $modify->params();
        if (gettype($params) === 'array' && count($params)) {
          $attribs = array();
          foreach ($params as $param) {
            $attribs[] = self::handleRecursiveExpression($param);
          }
          return $modify->name() . '(' . self::handleLogicNodeModifiers($element, $modifiers) . ' ,' . implode(', ', $attribs) . ')';
        }
        return $modify->name() . '(' . self::handleLogicNodeModifiers($element, $modifiers) . ')';
      }
      return self::handleRecursiveExpressions($element->exprs()->exprs());
    }

    private static function prepareLogicNode($sourceElement)
    {
      self::$resultString .= self::handleLogicNode($sourceElement);
    }

    private static function prepareLogicEchoNode($sourceElement)
    {
      self::$resultString .= 'childs.push(' . self::handleLogicNode($sourceElement) . ')' . "\n";
    }

    private static function recString($tree)
    {
      if ($tree === false) {
        $childs = array();
      }
      else {
        $childs = $tree->getChilds();
      }
      foreach ($childs as $element) {
        $sourceElement = $element->element();
        switch (get_class($sourceElement)) {
          case 'Tag':
            self::prepareTag($sourceElement);
            break;
          case 'TagComment':
            self::prepareTagComment($sourceElement);
            break;
          case 'TextNode':
            self::prepareTextNode($sourceElement);
            break;
          case 'LogicNode':
            self::prepareLogicNode($sourceElement);
            break;
          case 'LogicNodeEcho':
            self::prepareLogicEchoNode($sourceElement);
            break;
        }
        self::recString($element);
      }
    }
  }

<?php if (!defined('ROOT')) die('You can\'t just open this file, dude');

  function arrMap($arr, $cb)
  {
    foreach ($arr as $index => $value) {
      $arr[$index] = $cb($value, $index);
    }
    return $arr;
  }

  function arrReduce($arr, $cb, $default = 0)
  {
    $result = $default;
    foreach ($arr as $index => $value) {
      $result = $cb($result, $value, $index);
    }
    return $result;
  }

  function arrFilter($arr, $cb)
  {
    $result = array();
    foreach ($arr as $item) {
      if ($cb($item)) {
        $result[] = $item;
      }
    }
    return $result;
  }

  function arrSort($arr, $cb)
  {
    $prevIndex = false;
    $t = true;
    while ($t) {
      $t = false;
      foreach ($arr as $index => $item) {
        if ($prevIndex === false) {
          $prevIndex = $index;
          continue;
        }
        if ($cb($item, $arr[$prevIndex])) {
          $arr[$index] = $arr[$prevIndex];
          $arr[$prevIndex] = $item;
          $t = true;
          $prevIndex = false;
          break;
        }
        $prevIndex = $index;
      }
    }
    return $arr;
  }

  function arrDifference($source, $dest)
  {
    $res = array();
    $indexA = 0;
    $indexB = 0;
    $buffer = array();
    for ($indexA = 0; $indexA < count($source); $indexA++) {
      if (!isset($dest[$indexB])) {
        // it seems that size array of source is bigger than size array of dest
        // so mark current source item as delete
        if (count($buffer)) {
          $findedIndexB = findSourceIndexInBuffer($buffer['indexesB'], $dest, $source[$indexA]);
          if ($findedIndexB !== false) {
            // echo $findedIndexB."←\n";
            $indexB = $buffer['indexesB'][$findedIndexB];
            // print_r($source[$findedIndexB]);
            putBufferElementsInRes($buffer, $res, -$findedIndexB - 1, $source);
            putElementInResWithMark($res, $dest[$indexB], 'skip');
          }
          else {
            $buffer['indexesA'][] = $indexA;
          }
        }
        else {
          putElementInResWithMark($res, $source[$indexA], 'delete');
        }
      }
      else {
        // elements are not equal
        if (!isEqualArrays($source[$indexA], $dest[$indexB])) {
          // and buffer is empty
          if (!count($buffer)) {
            // create buffer with index and element of dest
            $buffer = array(
              'indexesA' => array($indexA),
              'indexesB' => array($indexB),
              'elements' => array($dest[$indexB])
            );
          }
          // buffer is not empty
          else {
            // if element of dest is equal already skiped element of source
            // find index and mark the number of elements like edit
            // and mark other elements like add
            $findedIndexA = findSourceIndexInBuffer($buffer['indexesA'], $source, $dest[$indexB]);
            $findedIndexB = findSourceIndexInBuffer($buffer['indexesB'], $dest, $source[$indexA]);
            if ($findedIndexA !== false) {
              $indexA = $buffer['indexesA'][$findedIndexA];
              putBufferElementsInRes($buffer, $res, $findedIndexA, $source);
              putElementInResWithMark($res, $dest[$indexB], 'skip');
            }
            else if ($findedIndexB !== false) {
              $indexB = $buffer['indexesB'][$findedIndexB];
              $indexA = $indexA;
              putBufferElementsInRes($buffer, $res, -$findedIndexB - 1, $source);
              putElementInResWithMark($res, $dest[$indexB], 'skip');
            }
            // if index not found
            // add dest element to buffer element
            else {
              $buffer['indexesA'][] = $indexA;
              $buffer['indexesB'][] = $indexB;
              $buffer['elements'][] = $dest[$indexB];
            }
          }
        }
        // elements are equal
        // check buffer and do something with it
        else {
          // buffer is not empty and equal elements gets
          // mark buffer elements as edit
          if (count($buffer)) {
            putBufferElementsInRes($buffer, $res, count($buffer['indexesA']), $source);
          }
          putElementInResWithMark($res, $dest[$indexB], 'skip');
        }
      }
      $indexB++;
      if ($indexA === count($source) - 1) {
        // elements of source are fetched
        // but elements of dest still exists
        // because size array of dest is bigger than size array of source
        $findedIndexA = false;
        if ($indexB < count($dest)) {
          // create empty buffer
          if (!count($buffer)) {
            $buffer = array(
              'indexesA' => array(),
              'elements' => array()
            );
          }
          // append rest of elements of dest to buffer
          for (; $indexB < count($dest); $indexB++) {
            $findedIndexA = findSourceIndexInBuffer($buffer['indexesA'], $source, $dest[$indexB]);
            if ($findedIndexA !== false) {
              break;
            }
            else {
              $buffer['elements'][] = $dest[$indexB];
            }
          }
        }
        // append buffer elements to res
        // if not found equal element at sources
        if (count($buffer)) {
          // if equal element found in sources
          if ($findedIndexA !== false) {
            // do this loop one more time
            $indexA = $buffer['indexesA'][$findedIndexA] - 1;
            putBufferElementsInRes($buffer, $res, $findedIndexA, $source);
          }
          else {
            putBufferElementsInRes($buffer, $res, count($buffer['indexesB']), $source);
          }
        }
      }
    }
    return $res;
  }

  function putElementInResWithMark(& $res, $element, $mark, $origin = false)
  {
    $element = array(
      'mark' => $mark,
      'element' => $element
    );
    if ($origin !== false) {
      $element['origin'] = $origin;
    }
    $res[] = $element;
  }

  function putBufferElementsInRes(& $buffer, & $res, $index, $source)
  {
    if ($index < 0) {
      $index = -$index - 1;
      for ($i = 0; $i < $index; $i++) {
        putElementInResWithMark($res, $buffer['elements'][$i], 'edit', $source[$buffer['indexesB'][$i]]);
      }
      for ($i = $index; $i < count($buffer['indexesA']); $i++) {
        putElementInResWithMark($res, $source[$buffer['indexesA'][$i]], 'delete');
      }
    }
    else {
      for ($i = 0; $i < $index; $i++) {
        putElementInResWithMark($res, $buffer['elements'][$i], 'edit', $source[$buffer['indexesA'][$i]]);
      }
      for ($i = $index; $i < count($buffer['elements']); $i++) {
        putElementInResWithMark($res, $buffer['elements'][$i], 'add');
      }
      for ($i = count($buffer['elements']); $i < count($buffer['indexesA']); $i++) {
        putElementInResWithMark($res, $source[$buffer['indexesA'][$i]], 'delete');
      }
    }
    $buffer = array();
  }

  function isEqualArrays($sourceA, $sourceB)
  {
    // each fields of source array and check them at dest array
    // if it not exists or not equals then return false
    foreach ($sourceA as $key => $value) {
      if (!isset($sourceB[$key])) return false;
      elseif ($value !== $sourceB[$key]) return false;
    }
    // do the same thing with dest array
    // if dest field not exists in source array
    // or not empty then return false
    foreach ($sourceB as $key => $value) {
      if (!isset($sourceA[$key])) return false;
      elseif ($value !== $sourceA[$key]) return false;
    }
    // if everything fine return true
    // arrays are equals
    return true;
  }

  function findSourceIndexInBuffer($indexes, $elements, $srcElement)
  {
    for ($i = 0; $i < count($indexes); $i++) {
      if (isEqualArrays($elements[$indexes[$i]], $srcElement)) {
        return $i;
      }
    }
    return false;
  }

?>

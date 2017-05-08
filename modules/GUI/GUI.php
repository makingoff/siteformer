<?php if (!defined('ROOT')) die('You can\'t just open this file, dude');

require_once $modulePath . 'GUIGetItemList.php';
require_once $modulePath . 'GUIGetItem.php';

class SFGUI
{
  private static $sections = [];

  public static function init($params) {
    $dependencies = ['SFRouter', 'SFTemplater'];
    arrMap($dependencies, function ($dependence) {
      if (!class_exists($dependence)) die('Need module Router before '. $dependence);
    });

    self::checkTables();

    // append types handlers
    $dir = opendir(MODULES . 'GUI/types');

    while($filename = readdir($dir)) {
      $dirname = MODULES . 'GUI/types/' . $filename;
      $isDir = is_dir($dirname);
      $isFileExists = file_exists($dirname . '/' . $filename . '.php');
      if ($filename != '.' && $filename != '..' && $isDir && $isFileExists) {
        require_once MODULES . 'GUI/types/' . $filename . '/' . $filename . '.php';

        $className = self::getClassNameByType($filename);

        if (method_exists($className, 'prepareDatabase')) {
          $className::prepareDatabase();
        }
      }
    }

    SFModels::registerPath(MODULES . 'GUI/models');

    SFRouter::addRule('/cms/', MODULES . 'GUI/sections/main/index');
    SFRouter::addRule('/cms/configs/', [
      'data' => [
        'page-title' => 'gui-scalar?value=Настройки',
        'section' => 'gui-scalar?value=configs',
        'sections' => 'gui-sections',
        'modules' => 'gui-modules'
      ],
      'template' => 'sections/configs/index.tmplt'
    ]);
    SFRouter::addRule('/cms/configs/add/', [
      'data' => [
        'types' => 'gui-types',
        'sections' => 'gui-sections',
        'modules' => 'gui-modules',
        'fields' => 'gui-fields?section=new',
        'section' => 'gui-scalar?value=section',
        'title' => 'gui-scalar?value=',
        'page-title' => 'gui-scalar?value=Добавить раздел',
        'alias' => 'gui-scalar?value=',
        'module' => 'gui-scalar?value=default'
      ],
      'template' => 'sections/configs/add.tmplt'
    ]);
    SFRouter::addRule('/cms/configs/{section}/', [
      'data' => [
        'title' => 'gui-sections?section={section}&field=title',
        'page-title' => 'gui-scalar?value=Редактировать раздел «{title}»',
        'id' => 'gui-sections?section={section}&field=id',
        'alias' => 'gui-sections?section={section}&field=alias',
        'module' => 'gui-sections?section={section}&field=module',
        'fields' => 'gui-fields?section={section}',
        'section' => 'gui-scalar?value=configs',
        'types' => 'gui-types',
        'sections' => 'gui-sections',
        'modules' => 'gui-modules'
      ],
      'template' => 'sections/configs/add.tmplt'
    ]);
    SFRouter::addRule('/cms/types/{type}/{handle}/', MODULES . 'GUI/sections/main/type');
    SFRouter::addRule('/cms/{section}/', [
      'data' => [
        'title' => 'gui-sections?section={section}&field=title',
        'page-title' => 'gui-scalar?value=Редактировать раздел «{title}»',
        'fields' => 'gui-fields?section={section}',
        'user-fields' => 'gui-fields?section={section}&usersonly',
        'section' => 'gui-scalar?value={section}',
        'sections' => 'gui-sections',
        'data' => 'gui-item-list?section={section}&status=public,draft',
        'pages' => 'gui-pages?section={section}&status=public,draft',
        'page' => 'gui-scalar?value={page}'
      ],
      'template' => 'sections/item/index.tmplt'
    ]);
    SFRouter::addRule('/cms/{section}/page/{page}/', [
      'data' => [
        'title' => 'gui-sections?section={section}&field=title',
        'page-title' => 'gui-scalar?value=Редактировать раздел «{title}»',
        'fields' => 'gui-fields?section={section}',
        'user-fields' => 'gui-fields?section={section}&usersonly',
        'section' => 'gui-scalar?value={section}',
        'sections' => 'gui-sections',
        'data' => 'gui-item-list?section={section}&page={page}&status=public,draft',
        'pages' => 'gui-pages?section={section}&status=public,draft',
        'page' => 'gui-scalar?value={page}'
      ],
      'template' => 'sections/item/index.tmplt'
    ]);
    SFRouter::addRule('/cms/{section}/page/{page}/limit/{limit}/', [
      'data' => [
        'title' => 'gui-sections?section={section}&field=title',
        'page-title' => 'gui-scalar?value=Редактировать раздел «{title}»',
        'fields' => 'gui-fields?section={section}',
        'user-fields' => 'gui-fields?section={section}&usersonly',
        'section' => 'gui-scalar?value={section}',
        'sections' => 'gui-sections',
        'data' => 'gui-item-list?section={section}&page={page}&limit={limit}&status=public,draft',
        'pages' => 'gui-pages?section={section}&limit={limit}&status=public,draft',
        'page' => 'gui-scalar?value={page}'
      ],
      'template' => 'sections/item/index.tmplt'
    ]);
    SFRouter::addRule('/cms/{section}/add/', [
      'data' => [
        'sections' => 'gui-sections',
        'fields' => 'gui-fields?section={section}',
        'section' => 'gui-sections?section={section}&field=alias',
        'section-name' => 'gui-sections?section={section}&field=title',
        'page-title' => 'gui-scalar?value=Добавить запись в «{section-name}»',
        'data' => 'gui-section-data?section={section}'
      ],
      'template' => 'sections/item/add.tmplt'
    ]);
    SFRouter::addRule('/cms/{section}/action_save/', MODULES . 'GUI/sections/item/action_save');
    SFRouter::addRule('/cms/{section}/action_delete/', MODULES . 'GUI/sections/item/action_delete');
    SFRouter::addRule('/cms/{section}/{id}/', [
      'data' => [
        'sections' => 'gui-sections',
        'fields' => 'gui-fields?section={section}',
        'section' => 'gui-sections?section={section}&field=alias',
        'section-name' => 'gui-sections?section={section}&field=title',
        'page-title' => 'gui-scalar?value=Добавить запись в «{section-name}»',
        'data' => 'gui-section-data?section={section}&id={id}'
      ],
      'template' => 'sections/item/add.tmplt'
    ]);

    define('GUI_COMPILE_TEMPLATES', ENGINE . 'modules/GUI/dist/');

    if (SFURI::getFirstUri() === 'cms') {
      SFTemplater::setCompilesPath(GUI_COMPILE_TEMPLATES);
    }
  }

  // Get array of sections
  // It could be news or events or galleries
  public static function getSections() {
    $res = SFORM::select()
      ->from('sections')
      ->join('section_fields')
      ->on('section_fields.section', SFORM::field('sections.id'))
      ->order('sections.id desc')
      ->where('sections.enable', true)
      ->exec();

    if (count($res)) {
      $res = arrMap($res, function ($item) {
        $item['fields'] = self::prepareSectionFields($item['section_fields']);
        unset($item['section_fields']);

        return $item;
      });
    }

    return $res;
  }

  // Get section by alias
  public static function getSection($alias, $force = false) {
    if (isset(self::$sections[$alias]) && !$force) {
      return self::$sections[$alias];
    }

    $res = SFORM::select()
      ->from('sections')
      ->join('section_fields')
      ->on('section_fields.section', SFORM::field('sections.id'))
      ->where('sections.alias', $alias)
      ->andWhere('sections.enable', true)
      ->exec();

    if (count($res)) {
      $res = $res[0];
      $res['fields'] = self::prepareSectionFields($res['section_fields']);
      unset($res['section_fields']);

      self::$sections[$alias] = $res;
      self::$sections[$res['id']] = $res;

      return $res;
    }

    return false;
  }

  // Get section by id
  public static function getSectionById($id) {
    $res = SFORM::select()
      ->from('sections')
      ->join('section_fields')
      ->on('section_fields.section', SFORM::field('sections.id'))
      ->where('id', $id)
      ->andWhere('sections.enable', true)
      ->exec();

    if (count($res)) {
      $res = $res[0];
      $res['fields'] = self::prepareSectionFields($res['section_fields']);
      unset($res['section_fields']);

      self::$sections[$id] = $res;
      self::$sections[$res['alias']] = $res;

      return $res;
    }

    return false;
  }

  public static function getNewFields () {
    $types = self::getTypes();
    list($index, $firstType) = each($types);
    reset($types);

    foreach ($types as $type) {
      if ($type['type'] === 'string') {
        $firstType = $type;
      }
    }

    $fields = [
      [
        'title' => '',
        'alias' => '',
        'type' => $firstType['type'],
        'settings' => $firstType['defaultSettings'],
        'position' => 0,
        'required' => false
      ]
    ];

    return $fields;
  }

  // Add section
  public static function addSection($data) {
    $modules = SFGUI::getModules();
    $modules[] = 'default';

    $types = arrMap(SFGUI::getTypes(), function ($type) use (& $types) {
      return $type['type'];
    });

    SFResponse::showContent();

    $data = SFValidate::value([
      'title' => [
        'required' => true,
        'unique' => function ($value) {
          $res = SFORM::select()
            ->from('sections')
            ->where('title', $value)
            ->andWhere('enable', true);

          return !$res->length();
        }
      ],
      'alias' => [
        'required' => true,
        'valid' => '/^[a-zA-Z\-_]+$/i',
        'unique' => function ($value) {
          $res = SFORM::select()
            ->from('sections')
            ->where('alias', $value)
            ->andWhere('enable', true);

          return !$res->length();
        }
      ],
      'module' => [
        'required' => true,
        'values' => $modules
      ],
      'fields' => [
        'minlength' => 1,
        'collection' => [
          'id' => [
            'required' => false,
            'default' => 0,
            'valid' => '/^\d+$/'
          ],
          'title' => [
            'required' => true,
            'unique' => true
          ],
          'alias' => [
            'required' => true,
            'unique' => true,
            'valid' => '/^[a-zA-Z0-9\-_]+$/i'
          ],
          'type' => [
            'values' => $types
          ],
          'required' => [],
          'settings' => [
            'type' => 'array',
            'modify' => function ($settings) {
              return json_encode($settings);
            }
          ],
          'position' => [
            'valid' => '/^\d+$/'
          ]
        ]
      ]
    ], $data);

    self::validateSettingsOfData($data);

    $defaultField = [
      'name' => '',
      'type' => '',
      'null' => false,
      'autoincrement' => false,
      'default' => NULL
    ];
    $tableFields = [
      [
        'name' => 'id',
        'type' => 'INT(11) UNSIGNED',
        'null' => false,
        'autoincrement' => true,
        'default' => false
      ],
      [
        'name' => 'status',
        'type' => 'ENUM(\'public\', \'draft\', \'deleted\')',
        'null' => 'NOT NULL',
        'default' => 'draft'
      ]
    ];

    foreach ($data['fields'] as $field) {
      $fieldType = array_merge($defaultField, self::getSqlFieldType($field));
      $fieldType['name'] = $field['alias'];
      $tableFields[] = $fieldType;
    }

    $data['table'] = self::generateTableNameByAlias($data['alias']);
    $table = SFORM::create($data['table']);

    foreach ($tableFields as $field) {
      $table->addField($field);
    }

    $table->addKey('id', 'primary key');
    $table->exec();

    $idSection = SFORM::insert('sections')
      ->values([
        'title' => $data['title'],
        'alias' => $data['alias'],
        'module' => $data['module'],
        'table' => $data['table']
      ])
      ->exec();

    arrMap($data['fields'], function ($field) use ($idSection) {
      SFORM::insert('section_fields')
        ->values([
          'section' => $idSection,
          'title' => $field['title'],
          'alias' => $field['alias'],
          'type' => $field['type'],
          'settings' => $field['settings'],
          'position' => $field['position']
        ])
        ->exec();
    });

    return $idSection;
  }

  // Save section
  public static function saveSection($id, $data) {
    $modules = SFGUI::getModules();
    $modules[] = 'default';

    $types = arrMap(SFGUI::getTypes(), function ($type) {
      return $type['type'];
    });

    $data = SFValidate::value([
      'title' => [
        'required' => true,
        'unique' => function ($value) use ($id) {
          $res = SFORM::select()
            ->from('sections')
            ->where('title', $value)
            ->andWhere('enable', true);

          if ($id !== false) {
            $res = $res->andWhere('id', '!=', $id);
          }

          return !$res->length();
        }
      ],
      'alias' => [
        'required' => true,
        'valid' => '/^[a-zA-Z\-_]+$/i',
        'unique' => function ($value) use ($id) {
          $res = SFORM::select()
            ->from('sections')
            ->where('alias', $value)
            ->andWhere('enable', true);

          if ($id !== false) {
            $res = $res->andWhere('id', '!=', $id);
          }

          return !$res->length();
        }
      ],
      'module' => [
        'required' => true,
        'values' => $modules
      ],
      'fields' => [
        'minlength' => 1,
        'collection' => [
          'id' => [
            'valid' => '/^\d+$/',
            'default' => 0
          ],
          'title' => [
            'required' => true,
            'unique' => true
          ],
          'alias' => [
            'required' => true,
            'unique' => true,
            'valid' => '/^[a-zA-Z0-9\-_]+$/i'
          ],
          'type' => [
            'values' => $types
          ],
          'required' => [],
          'settings' => [
            'type' => 'array',
            'modify' => function ($settings) {
              return json_encode($settings);
            }
          ],
          'position' => [
            'valid' => '/^\d+$/',
            'unique' => true
          ]
        ]
      ]
    ], $data);

    $defaultField = [
      'name' => '',
      'type' => '',
      'null' => false,
      'autoincrement' => false,
      'default' => NULL
    ];

    $source = self::getSectionById($id);
    self::validateSettingsOfData($data);

    // prepare source fields and new data fields for get diff
    $source['fields'] = arrMap(arrSort($source['fields'], function ($a, $b) {
      return $a['id'] < $b['id'];
    }), function ($field) {
      $field['position'] = (int)$field['position'];

      return $field;
    });

    $index = 0;

    $data['fields'] = arrMap(arrSort($data['fields'], function ($a, $b) {
      return $a['position'] < $b['position'];
    }), function ($field) use (& $index) {
      $field['position'] = $index++;

      return $field;
    });

    $data['fields'] = arrSort($data['fields'], function ($a, $b) {
      return $a['id'] < $b['id'] && strlen($a['id']) && strlen($b['id']);
    });

    $dataFields = $data['fields'];
    $sourceFields = $source['fields'];

    $sourceFields = arrMap($sourceFields, function ($field) {
      unset($field['section']);
      unset($field['title']);
      $field['settings'] = json_encode($field['settings']);
      $field['position'] = (int)$field['position'];

      return $field;
    });

    $dataFields = arrMap($dataFields, function ($field) {
      unset($field['title']);

      return $field;
    });

    // get diff
    $arrDiff = arrDifference($sourceFields, $dataFields);

    foreach ($arrDiff as $field) {
      switch ($field['mark']) {
        case 'delete':
          SFORM::alter($source['table'])
            ->dropField($field['element']['alias'])
            ->exec();

          break;
      }
    }

    foreach ($arrDiff as $field) {
      switch ($field['mark']) {
        case 'edit':
          $fieldType = array_merge($defaultField, self::getSqlFieldType($field['element']));
          $fieldType['name'] = $field['element']['alias'];
          SFORM::alter($source['table'])
            ->changeField($field['origin']['alias'], $fieldType)
            ->exec();

          break;
      }
    }

    foreach ($arrDiff as $field) {
      switch ($field['mark']) {
        case 'add':
          $fieldType = array_merge($defaultField, self::getSqlFieldType($field['element']));
          $fieldType['name'] = $field['element']['alias'];
          SFORM::alter($source['table'])
            ->addField($fieldType)
            ->exec();

          break;
      }
    }

    SFORM::update('sections')
      ->values([
        'title' => $data['title'],
        'module' => $data['module']
      ])
      ->id($id)
      ->exec();

    $sourceFields = arrMap($source['fields'], function ($item) {
      $item['settings'] = json_encode($item['settings']);
      unset($item['section']);

      return $item;
    });

    $fields = arrDifference($sourceFields, $data['fields']);

    foreach ($fields as $field) {
      switch($field['mark']) {
        case 'add':
          SFORM::insert('section_fields')
            ->values([
              'section' => $id,
              'title' => $field['element']['title'],
              'alias' => $field['element']['alias'],
              'type' => $field['element']['type'],
              'required' => $field['element']['required'],
              'settings' => $field['element']['settings'],
              'position' => $field['element']['position']
            ])
            ->exec();

          break;
        case 'delete':
          SFORM::delete('section_fields')
            ->id($field['element']['id'])
            ->exec();

          break;
        case 'edit':
          SFORM::update('section_fields')
            ->values([
              'title' => $field['element']['title'],
              'alias' => $field['element']['alias'],
              'type' => $field['element']['type'],
              'required' => $field['element']['required'],
              'settings' => $field['element']['settings'],
              'position' => $field['element']['position']
            ])
            ->id($field['origin']['id'])
            ->exec();

          break;
        case 'skip':
          $fieldType = self::getSqlFieldType($field['element']);

          break;
      }
    }

    $section = self::getSectionById($id);
    SFResponse::set('fields', $section['fields']);
  }

  // Get array of modules
  // Module is view-type: table or masonry or etc.
  public static function getModules() {
    if (file_exists(MODULES . 'GUI/modules')) {
      $dir = opendir(MODULES . 'GUI/modules');
      $handlers = [];

      while ($subdir = readdir($dir)) {
        if (is_dir(MODULES . 'GUI/modules/' . $subdir) && !in_array($subdir, ['.', '..'])) {
          $handlers[] = $subdir;
        }
      }

      return $handlers;
    }

    return [];
  }

  // Get types for section structure
  public static function getTypes() {
    if (file_exists(MODULES . 'GUI/types')) {
      $dir = opendir(MODULES . 'GUI/types');
      $types = [];

      while ($subdir = readdir($dir)) {
        if (is_dir(MODULES . 'GUI/types/' . $subdir) && !in_array($subdir, ['.', '..'])) {
          if (file_exists(MODULES . 'GUI/types/' . $subdir . '/configs.json')) {
            $type = parseJSON(file_get_contents(MODULES . 'GUI/types/' . $subdir . '/configs.json'));
            $type['type'] = $subdir;
            $types[] = $type;
          }
        }
      }

      return $types;
    }

    return [];
  }

  public static function getClassNameByType($type) {
    return 'SFType' . SFText::camelCasefy($type, true);
  }

  public static function removeSection($id) {
    $request = SFORM::update('sections')
      ->values(array(
        'enable' => false
      ))
      ->where('id', $id)
      ->exec();
  }

  public static function getItemList($section) {
    return new SFGUIGetItemList($section);
  }

  public static function getItem($section) {
    return new SFGUIGetItem($section);
  }

  private static function stringifyToArray($data, $indent = 1) {
    if (gettype($data) === 'array') {
      $i = 0;
      $isAssoc = false;
      $items = [];

      foreach ($data as $index => $item) {
        if ($index !== $i) {
          $isAssoc = true;
          break;
        }
        $i++;
      }

      foreach ($data as $index => $item) {
        $items[] = self::getTabsByIndent($indent) . ($isAssoc ? self::getIndexString($index) . ' => ' : '') . self::stringifyToArray($item, $indent + 1);
      }

      return 'array(' . N . implode(',' . N, $items) . N . self::getTabsByIndent($indent - 1) . ')';
    } elseif (gettype($data) === 'integer') {
      return $data;
    } elseif (gettype($data) === 'boolean') {
      return ($data ? 'true' : 'false');
    } elseif (gettype($data) === 'string') {
      return '\'' . $data . '\'';
    }

    return '';
  }

  private static function getIndexString($index) {
    return (is_numeric($index) ? $index : '\'' . $index . '\'');
  }

  private static function getTabsByIndent($indent) {
    $tabs = '';

    for ($i = 0; $i < $indent * 2; $i++) {
      $tabs .= ' ';
    }

    return $tabs;
  }

  private static function getSqlFieldType($field) {
    $className = self::getClassNameByType($field['type']);

    if (class_exists($className)) {
      return $className::getSqlField(parseJSON($field['settings']));
    }

    return false;
  }

  private static function prepareSectionFields($fields) {
    return arrSort(arrMap($fields, function($field) {
      $field['settings'] = parseJSON($field['settings']);

      return $field;
    }), function ($a, $b) {
      return $a['position'] < $b['position'];
    });
  }

  private static function validateSettingsOfData(& $data) {
    foreach ($data['fields'] as $index => $field) {
      $className = self::getClassNameByType($field['type']);

      if (class_exists($className)) {
        try {
          $settings = parseJSON($field['settings']);
          $data['fields'][$index]['settings'] = json_encode($className::validateSettings($settings, $data['fields'], $field['alias']));
        } catch (ValidateException $e) {
          $message = $e->getOriginMessage();

          throw new ValidateException([
            'code' => $message['code'],
            'index' => array_merge(['fields', $index, 'settings'], $message['index']),
            'source' => $message['source']
          ]);
        }
      }
    }
  }

  private static function generateTableNameByAlias($alias) {
    $tableList = SFORMDatabase::getTables();
    $tableName = $alias;
    $i = 1;

    while (in_array($tableName, $tableList)) {
      $tableName = $alias . ($i++);
    }

    return $tableName;
  }

  private static function checkTables() {
    if (!SFORM::exists('section_fields')) {
      SFORM::create('section_fields')
        ->addField([
          'name' => 'id',
          'type' => 'INT(11) UNSIGNED',
          'autoincrement' => true,
          'null' => false
        ])
        ->addField([
          'name' => 'section',
          'type' => 'INT(11) UNSIGNED',
          'null' => true,
          'default' => NULL
        ])
        ->addField([
          'name' => 'title',
          'type' => 'VARCHAR(60)',
          'null' => false
        ])
        ->addField([
          'name' => 'alias',
          'type' => 'VARCHAR(60)',
          'null' => false
        ])
        ->addField([
          'name' => 'type',
          'type' => 'VARCHAR(20)',
          'null' => false
        ])
        ->addField([
          'name' => 'settings',
          'type' => 'TEXT'
        ])
        ->addField([
          'name' => 'position',
          'type' => 'INT(3)',
          'null' => false,
          'default' => 0
        ])
        ->addKey('id', 'primary key')
        ->addKey('position')
        ->addKey('section')
        ->addKey('alias')
        ->exec();
    }

    if (!SFORM::exists('section_fields_users')) {
      SFORM::create('section_fields_users')
        ->addField([
          'name' => 'user',
          'type' => 'INT(11) UNSIGNED',
          'autoincrement' => true,
          'null' => false
        ])
        ->addField([
          'name' => 'section',
          'type' => 'INT(11) UNSIGNED',
          'null' => false
        ])
        ->addField([
          'name' => 'field',
          'type' => 'INT(11) UNSIGNED',
          'null' => false
        ])
        ->addKey(['user', 'section', 'field'], 'primary key')
        ->addKey(['user', 'section'])
        ->exec();
    }
  }
}

<?php

namespace Engine\Modules\ERM\Types;

use Engine\Modules\ERM\ERMType;
use Engine\Modules\ORM\ORM;

class TypeTags extends ERMType {
	public static $name = 'Теги';
	public static $type = 'tags';
	public static $requiredable = true;
	public static $settings = [];

	public static function prepareDatabase() {
		if (!ORM::exists('sys_type_tags')) {
			ORM::create('sys_type_tags')
			->addField([
				'name' => 'id',
				'type' => 'INT(11) UNSIGNED',
				'autoincrement' => true,
				'null' => false
			])
			->addField([
				'name' => 'collection',
				'type' => 'INT(11) UNSIGNED',
				'null' => 'NULL',
				'default' => NULL
			])
			->addField([
				'name' => 'field',
				'type' => 'VARCHAR(100)',
				'null' => 'NULL',
				'default' => NULL
			])
			->addField([
				'name' => 'tag',
				'type' => 'VARCHAR(200)'
			])
			->addKey('id', 'primary key')
			->addKey(['collection', 'field'])
			->addKey(['collection', 'field', 'tag'])
			->exec();
		}

		if (!ORM::exists('sys_type_tags_records')) {
			ORM::create('sys_type_tags_records')
			->addField([
				'name' => 'record',
				'type' => 'INT(11) UNSIGNED',
				'null' => false
			])
			->addField([
				'name' => 'tag',
				'type' => 'INT(11) UNSIGNED',
				'null' => false
			])
			->addKey(['record', 'tag'], 'primary key')
			->exec();
		}
	}

	public static function getDefaultData($settings) {
		return '';
	}

	public static function prepareInsertData($collection, $field, $data) {
		$tags = $data[$field['alias']];

		if (gettype($tags) === 'string') {
			$tags = self::getArrTagsFromString($data[$field['alias']]);
		}

		$existsTags = arrMap(self::getTagRecords($field['collection'], $field['alias'], $tags), function ($row) {
			return $row['tag'];
		});

		foreach ($tags as $index => $tag) {
			if (!in_array($tag, $existsTags)) {
				ORM::insert('sys_type_tags')
				->values([
					'collection' => $field['collection'],
					'field' => $field['alias'],
					'tag' => $tag
				])
				->exec();
			}
		}

		return implode(', ', $tags);
	}

	public static function prepareUpdateData($collection, $field, $currentData, $data) {
		$tags = $data[$field['alias']];

		if (gettype($tags) === 'string') {
			$tags = self::getArrTagsFromString($data[$field['alias']]);
		}

		$existsTags = arrMap(self::getTagRecords($field['collection'], $field['alias'], $tags), function ($row) {
			return $row['tag'];
		});

		foreach ($tags as $index => $tag) {
			if (!in_array($tag, $existsTags)) {
				ORM::insert('sys_type_tags')
				->values([
					'collection' => $field['collection'],
					'field' => $field['alias'],
					'tag' => $tag
				])
				->exec();
			}
		}

		return implode(', ', $tags);
	}

	public static function postPrepareInsertData($collection, $field, $record, $data) {
		$tags = $data[$field['alias']];

		if (gettype($tags) === 'string') {
			$tags = self::getArrTagsFromString($data[$field['alias']]);
		}

		$ids = arrMap(self::getTagRecords($field['collection'], $field['alias'], $tags), function ($row) {
			return $row['id'];
		});

		ORM::delete('sys_type_tags_records')->where('record', $record['id'])->exec();

		foreach ($ids as $id) {
			ORM::insert('sys_type_tags_records')
			->values([
				'record' => $record['id'],
				'tag' => $id
			])
			->exec();
		}
	}

	public static function postPrepareUpdateData($collection, $field, $record, $data) {
		$tags = $data[$field['alias']];

		if (gettype($tags) === 'string') {
			$tags = self::getArrTagsFromString($data[$field['alias']]);
		}

		$ids = arrMap(self::getTagRecords($field['collection'], $field['alias'], $tags), function ($row) {
			return $row['id'];
		});

		ORM::delete('sys_type_tags_records')->where('record', $record['id'])->exec();

		foreach ($ids as $id) {
			ORM::insert('sys_type_tags_records')
			->values([
				'record' => $record['id'],
				'tag' => $id
			])
			->exec();
		}
	}

	public static function postProcessData($collection, $field, $data) {
		$data[$field['alias']] = explode(', ', $data[$field['alias']]);

		return $data;
	}

	public static function whereExpression($collection, $field, $value, $params = false) {
		if (gettype($value) === 'string') {
			$value = [$value];
		}

		$insertions = [];
		$options = [
			'collection' => $collection['id'],
			'field' => 'id',
			'raw_field' => $field
		];

		foreach ($value as $index => $tag) {
			$insertions[] = ':field IN (
				SELECT `sys_type_tags_records`.`record` FROM `sys_type_tags_records`
				INNER JOIN `sys_type_tags` ON `sys_type_tags`.`id` = `sys_type_tags_records`.`tag`
				WHERE `sys_type_tags`.`collection` = :collection AND `sys_type_tags`.`field` = ":raw_field" AND (
				`sys_type_tags`.`tag` = ":value' . $index . '"))';
			$options['value' . $index] = $tag;
		}

		$joinStr = ' AND ';

		if ($params !== false) {
			if ($params === 'any') {
				$joinStr = ' OR ';
			}
		}

		return ORM::generateValue(join($joinStr, $insertions), $options);
	}

	private static function getArrTagsFromString($tags) {
		return arrMap(explode(',', $tags), function ($tag) {
			return trim($tag);
		});
	}

	private static function getTagRecords($collection, $field, $tags) {
		$result = ORM::select()
		->from('sys_type_tags')
		->where('collection', $collection)
		->andWhere('field', $field)
		->andOpenWhere();

		foreach ($tags as $index => $tag) {
			if (!$index) {
				$result = $result->where('tag', $tag);
			} else {
				$result = $result->orWhere('tag', $tag);
			}
		}

		return $result->closeWhere()
		->exec();
	}
}

<?php

namespace Engine\Classes;

class Text {
	public static function camelCasefy($str, $allChars = false) {
		$separate = ['-', '_'];

		foreach ($separate as $sepword) {
			$res = explode($sepword, $str);

			foreach ($res as $index => $word) {
				if ($allChars || $index) {
					$res[$index] = strtoupper(substr($word, 0, 1)) . substr($word, 1);
				} else {
					$res[$index] = $word;
				}
			}

			$str = implode('', $res);
		}

		return $str;
	}

	public static function subs($text, $from, $len = false) {
		if ($len === false) {
			$len = self::strl($text) - $from;
		}

		return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s', '$1', $text);
	}

	public static function strl($text) {
		return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $text, $dummy);
	}

	public static function hsc($text) {
		return htmlspecialchars(trim(magic_quotes_gpc ? stripslashes($text) : $text));
	}

	public static function db_html($text) { // в базу с фильтрацией html-тэгов
		return str_replace(["\\r", "\\n"], ["\r", "\n"], @mysql_escape_string(htmlspecialchars(get_magic_quotes_gpc() ? stripslashes($text) : $text)));
	}

	public static function db($text) {// в базу без фильтрации html-тэгов
		return str_replace(["\\r", "\\n"], ["\r", "\n"], @mysql_escape_string(get_magic_quotes_gpc() ? stripslashes($text) : $text));
	}

	public static function page_html($text) {// на страницу с фильтрацией html-тэгов
		return htmlspecialchars(get_magic_quotes_gpc() ? stripslashes($text) : $text);
	}

	public static function page($text) {// на страницу без фильтрации html-тэгов
		return (get_magic_quotes_gpc() ? stripslashes($text) : $text);
	}

	public static function inset($text) {
		return @mysql_escape_string(functext::hsc($text));
	}

	public static function sc($text) {
		return @mysql_escape_string(magic_quotes_gpc ? stripslashes($text) : $text);
	}

	public static function inp($text) {
		return htmlspecialchars(magic_quotes_gpc ? stripslashes($text) : $text);
	}

	public static function un_sc($text) {
		return magic_quotes_gpc ? stripslashes($text) : $text;
	}

	public static function unhtmlentities($string) {
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);

		return strtr($string, $trans_tbl);
	}

	public static function nohtml($string) {
		return strip_tags($string);
	}


	public static function short($text, $lenght) {
		if (self::strl($text) > $lenght) {
			return self::subs($text, 0, $lenght)."…";
		}

		return $text;
	}

	public static function translite($text) {
		$replaces = [
			'а' => 'a',
			'б' => 'b',
			'в' => 'v',
			'г' => 'g',
			'д' => 'd',
			'е' => 'e',
			'ё' => 'yo',
			'ж' => 'zh',
			'з' => 'z',
			'и' => 'i',
			'й' => 'y',
			'к' => 'k',
			'л' => 'l',
			'м' => 'm',
			'н' => 'n',
			'о' => 'o',
			'п' => 'p',
			'р' => 'r',
			'с' => 's',
			'т' => 't',
			'у' => 'u',
			'ф' => 'f',
			'х' => 'h',
			'ц' => 's',
			'ч' => 'ch',
			'ш' => 'sh',
			'щ' => 'csh',
			'ъ' => '',
			'ы' => 'i',
			'ь' => '',
			'э' => 'e',
			'ю' => 'yu',
			'я' => 'ya',
			'А' => 'A',
			'Б' => 'B',
			'В' => 'V',
			'Г' => 'G',
			'Д' => 'D',
			'Е' => 'E',
			'Ё' => 'Yo',
			'Ж' => 'Zh',
			'З' => 'Z',
			'И' => 'I',
			'Й' => 'Y',
			'К' => 'K',
			'Л' => 'L',
			'М' => 'M',
			'Н' => 'N',
			'О' => 'O',
			'П' => 'P',
			'Р' => 'R',
			'С' => 'S',
			'Т' => 'T',
			'У' => 'I',
			'Ф' => 'F',
			'Х' => 'H',
			'Ц' => 'S',
			'Ч' => 'Ch',
			'Ш' => 'Sh',
			'Щ' => 'Sch',
			'Ъ' => '',
			'Ы' => 'I',
			'Ь' => '',
			'Э' => 'E',
			'Ю' => 'Yu',
			'Я' => 'Ya',
			' ' => '-'
		];

		return str_replace('ĭ', 'i', strtr($text, $replaces));
	}

	public static function removeSpecialCharacters($text) {
		return preg_replace('/[^\p{L}0-9\-_\s]/iu', '', $text);
	}

	public static function getTag($text) {
		return strtolower(self::removeSpecialCharacters(self::translite($text)));
	}

	public static function ends($num, $params) {
		$end = substr($num, strlen($num) - 2);

		if ($end >= 10 && $end <= 20) {
			return $params[2];
		}

		$end = substr($num, strlen($num) - 1);

		if ($end > 1 && $end < 5) {
			return $params[1];
		}

		if ($end == 1) {
			return $params[0];
		}

		return $params[2];
	}

	public static function upFirstChar($string) {
		$first_char = self::subs($string, 0, 1);
		$last_chars = self::subs($string, 1, self::strl($string));
		$from_char = [
			'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
		];
		$to_char = [
			'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
		];

		$first_char = str_replace($from_char, $to_char, $first_char);

		return $first_char . $last_chars;
	}

	public static function wysiwyg($text) {
		$text = '<p>'.str_replace(array("\r\n\r\n", "\n\n"), array('</p><p>', '</p><p>'), $text).'</p>';

		return $text;
	}

	public static function unwysiwyg($text) {
		$text = str_replace('</p><p>', "\n\n", $text);

		if (substr($text, 0, 3) == '<p>') {
			$text = substr($text, 3);
		}

		if (substr($text, -4) == '</p>') {
			$text = substr($text, 0, strlen($text) -4);
		}

		return $text;
	}
}

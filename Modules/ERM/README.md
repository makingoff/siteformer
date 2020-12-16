# Модуль ERM

Модуль — абстрация над ORM. Вместо прямой работы с таблицами происходит работа с коллекциями. Коллекция состоит из полей сложных типов данных. Это нужно для того, чтобы можно было передать массив тегов для записи, а внутренние механизмы сами записали данные в нужные таблицы нужные данные. Для выборки записей делается то же самое — можно передать массив тегов, а на уровне ORM внутренние механизмы сами сделают нужные JOIN-ы и получат записи.

## Создание коллекции

```
ERM::addCollection([
  'alias' => 'places', // идентификатор колекции, дальнейшная работа будет через него
  'title' => 'Места', // название коллекции на человеческом языке
  'fields' => [] // набор полей
]);
```

### Описание поля

```
[
  'alias' => 'title', // идентификатор поля
  'title' => 'Заголовок', // название поля на человеческом языке
  'type' => 'string', // тип данных
  'required' => true, // маркер обязательного для заполнения
  'settings' => [] // опциональное поле, содержание нужно брать из конкретного типа
]
```

## Создание записи

Коллекции кроме полей, которые задаются при создании, содержат ещё поля для айди пользователя, который создал запись и последний отредактировал её, дату создания и дату последнего редактирования и статус записи.

### Статусы записи

`public` — запись опубликована
`draft` — черновик
`deleted` — запись удалена

### Пример создания записи

```
ERM::createItem([
  'status' => 'public', // статус записи
  'user' => 3, // айди пользователя-создателя записи
  'data' => [ // данные
    'title' => 'Hello'
  ]
]);
```

## Обновление записи

```
ERM::updateItem([
  'id' => 1, // айди редактируемой записи
  'status' => 'draft', // статус записи
  'user' => 4, // айди пользователя, который обновляет запись
  'data' => [ // данные
    'title' => 'Hello, World'
  ]
]);
```

## Выборка записей

Можно выбрать из коллекции одну запись или массив.

### Одна запись

```
$result = ERM::getItem('places')->exec(); // `places` — идентификатор поллекции
```

### Несколько записей

```
$result = ERM::getItemList('places')->exec(); // `places` — идентификатор поллекции
```

Между getItem и exec можно добавлять where-выражения, для поиска нужных записей:

```
$result = ERM::getItemList('places')
  ->where('tags', 'all', ['пиццерия', 'суши'])
  ->exec();
```

Where-выражение принимает три параметра: идентификатор поля, параметры сравнения и значение поля. Параметры сравнения — необязательны. Если их не задавать, значит будет работать параметр сравнения по умолчанию. У разных типов данных он разный. И вообще параметры сравнения могут быть разными и зависят от типа данных.
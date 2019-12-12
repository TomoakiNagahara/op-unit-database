The onepiece-framework Database Unit.
===

## Usage

### Instancate

```php
$db = $app->Unit('Database');
```

### Connection

```php
//  Configuration.
$config = [
    'prod'     => 'mysql',
    'host'     => 'localhost',
    'port'     => '3306',
    'user'     => 'testcase',
    'password' => 'password',
    'database' => 'testcase',
];

//  Connect to database by configuration.
if(!$io = $db->Connect($config) ){
    throw new Exception('Connect to database was failed.');
}
```

### SQL

```php
/**
 * You can execute SQL statement directly.
 * This is not recommended.
 * Because, SQL injection occurs.
 */
$records = $db->SQL('SELECT * FROM table_name');

/**
 * Result of record may return int 0 or empty array.
 * If this fails, this returns false.
 */
if( $records === false ){
    throw new Exception('Select was failed.');
}
```

### Select

```php
//  Config
$config = [
    'table' => 'table_name',
    'field' => 'id, nickname, timestamp, YEAR(timestamp) as year',
    'limit' =>  10,
    'order' => 'id, year desc',
    'offset'=>  10,
    'where'[] = 'id > 0',
];

//  Fetch records.
$records = $db->Select($query);
```

### Insert

```php
//  Config
$config = [
    'table' => 'table_name',
    'set'[] =  "nickname = $nickname",
];

//  Fetch records.
$records = $db->Insert($query);
```

### Update

```php
//  Config
$config = [
    'table' => 'table_name',
    'limit' =>  1,
    'where'[] = "id = $id";
    'set'[] =  "nickname = $nickname",
];

//  Fetch records.
$records = $db->Update($query);
```

### Delete

```php
//  Config
$config = [
    'table' => 'table_name',
    'limit' =>  1,
    'where'[] = "id = $id";
];

//  Fetch records.
$records = $db->Delete($query);
```

Laravel Kendo UI DataSource
===========================

Server side Kendo UI DataSource implementation for Laravel

Example
```php
$kd = KendoDataSource::make(
	Input::all(),
	[
		'address' => 'string',
		'suburb' => 'string',
		'phone' => 'string',
		'created_at' => 'date',
		'fully_registered' => 'boolean',
	]
);

$query = User::newQuery();
$count = $kd->execute($query);
return Response::json(['data' => $query->get()->toArray(), 'total' => $count]);
```

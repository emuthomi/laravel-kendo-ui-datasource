Laravel Kendo UI DataSource
===========================

Server side Kendo UI DataSource implementation for Laravel

### Installation

- [Laravel Kendo UI DataSource on Packagist](https://packagist.org/packages/meowcakes/laravel-kendo-ui-datasource)
- [Laravel Kendo UI DataSource on GitHub](https://github.com/meowcakes/laravel-kendo-ui-datasource)

To get the latest version simply require it in your `composer.json` file.

~~~
"meowcakes/laravel-kendo-ui-datasource": "dev-master"
~~~

You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~
'aliases' => array(

    'KendoDataSource' => 'Meowcakes\LaravelKendoUiDatasource\Facade'

)
~~~

### Example

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

<?php

class DataSourceTest extends PHPUnit_Framework_TestCase
{

	protected function getBuilder()
	{
		$grammar = new Illuminate\Database\Query\Grammars\Grammar;
		$processor = Mockery::mock('Illuminate\Database\Query\Processors\Processor');
		return new Illuminate\Database\Query\Builder(Mockery::mock('Illuminate\Database\ConnectionInterface'), $grammar, $processor);
	}

	protected function getApp()
	{
		$config = Mockery::mock('Illuminate\Config\Repository');
		$config->shouldReceive('get')->once()->with('laravel-kendo-ui-datasource::sortKey')->andReturn('sort');
		$config->shouldReceive('get')->once()->with('laravel-kendo-ui-datasource::filterKey')->andReturn('filter');

		$app = Mockery::mock('Illuminate\Foundation\Application');
		$app->shouldReceive('offsetGet')->times(2)->with('config')->andReturn($config);
		return $app;
	}

	public function testDataSource()
	{
		$query = $this->getBuilder()->select('*')->from('foo');

		$input = [
			'sort' => [
				[
					'field' => 'bar',
					'dir' => 'desc'
				],
			],
			'filter' => [
				'logic' => 'and',
				'filters' => [
					[
						'field' => 'baz',
						'operator' => 'eq',
						'value' => '123',
					],
					[
						'field' => 'baz',
						'operator' => 'neq',
						'value' => '321',
					],
					[
						'logic' => 'or',
						'filters' => [
							[
								'field' => 'bar',
								'operator' => 'contains',
								'value' => 'abc',
							],
							[
								'field' => 'bar',
								'operator' => 'doesnotcontain',
								'value' => 'cba',
							],
						],
					]
				],
			]
		];

		$columns = ['bar' => 'string', 'baz' => 'number'];

		$datasource = new Meowcakes\LaravelKendoUiDatasource\DataSource($this->getApp(), $input, $columns);
		$datasource->execute($query);

		echo $query->toSql() . PHP_EOL;
		$this->assertTrue(true);
	}

}

<?php

class DataSourceTest extends PHPUnit_Framework_TestCase
{

	protected function getBuilder()
	{
		return Mockery::mock('Illuminate\Database\Query\Builder');
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

	public function mockWhereWithLogic($query, $logic)
	{
		$query->shouldReceive('where')->once()->with(Mockery::on(function($x)
		{
			return is_callable($x);
		}), null, null, $logic)->andReturn($query);
	}

	public function testDataSource()
	{
		$query = $this->getBuilder();
		$query->shouldReceive('orderBy')->once()->with('bar', 'desc')->andReturn($query);
		$this->mockWhereWithLogic($query, 'and');
		$query->shouldReceive('where')->once()->with('baz', '=', '123', 'and')->andReturn($query);
		$query->shouldReceive('where')->once()->with('baz', '!=', '321', 'and')->andReturn($query);
		$this->mockWhereWithLogic($query, 'and');
		$query->shouldReceive('where')->once()->with('bar', 'like', '%abc%', 'or')->andReturn($query);
		$query->shouldReceive('where')->once()->with('bar', 'not like', '%cba%', 'or')->andReturn($query);
		$query->shouldReceive('count')->once()->andReturn(3);

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
		$total = $datasource->execute($query);
		$this->assertEquals(3, $total);
	}

}

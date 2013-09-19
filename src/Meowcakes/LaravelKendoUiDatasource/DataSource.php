<?php namespace Meowcakes\LaravelKendoUiDatasource;

use \Illuminate\Foundation\Application;

class DataSource
{

	protected $app;
	protected $input;
	protected $columns;
	protected $sortKey;
	protected $filterKey;
	protected $stringOps = [
		'eq' => 'like',
		'neq' => 'not like',
		'doesnotcontain' => 'not like',
		'contains' => 'like',
		'startswith' => 'like',
		'endswith' => 'like'
	];
	protected $numberOps = [
		'eq' => '=',
		'gt' => '>',
		'gte' => '>=',
		'lt' => '<',
		'lte' => '<=',
		'neq' => '!='
	];

	public function __construct(Application $app, array $input, array $columns)
	{
		$this->app = $app;
		$this->input = $input;
		$this->columns = $columns;
		$this->sortKey = $this->app->offsetGet('config')->get('laravel-kendo-ui-datasource::sortKey');
		$this->filterKey = $this->app->offsetGet('config')->get('laravel-kendo-ui-datasource::filterKey');
	}

	private function sort($query, $d)
	{
		if( ! is_array($d))
			$this->app->abort(400);

		foreach($d as $f)
		{
			if( ! is_array($f))
				$this->app->abort(400);

			if( ! isset($this->columns[$f['field']]))
				$this->app->abort(400);

			if( ! isset($f['dir']) or ! in_array($f['dir'], ['asc', 'desc'], true))
				$this->app->abort(400);

			$query->orderBy($f['field'], $f['dir']);
		}
	}

	private function filterField($query, $d, $logic)
	{
		if( ! isset($d['field']) or ! isset($this->columns[$d['field']]))
			$this->app->abort(400);

		if($this->columns[$d['field']] === 'string')
		{
			if( ! isset($d['operator']) or ! isset($this->stringOps[$d['operator']]))
				$this->app->abort(400);

			if( ! isset($d['value']) or ! is_string($d['value']))
				$this->app->abort(400);

			$value = $d['value'];
			if($d['operator'] === 'contains' or $d['operator'] === 'doesnotcontain')
				$value = "%$value%";
			else if($d['operator'] === 'startswith')
				$value = "$value%";
			else if($d['operator'] === 'endswith')
				$value = "%$value";

			$query->where($d['field'], $d['operator'], $value, $logic);
		}
		else if($this->columns[$d['field']] === 'number')
		{
			if( ! isset($d['operator']))
				$this->app->abort(400);

			if( ! isset($d['value']) or ! is_numeric($d['value']))
				$this->app->abort(400);

			$query->where($d['field'], $d['value'] === 'true' ? '!=' : '=', 0, $logic);
		}
		else if($this->columns[$d['field']] === 'boolean')
		{
			if( ! isset($d['operator']) or ! isset($this->numberOps[$d['operator']]))
				$this->app->abort(400);

			if( ! isset($d['value']))
				$this->app->abort(400);

			$query->where($d['field'], $d['operator'], $d['value'] === 'true' ? 1 : 0, $logic);			
		}
		else if($this->columns[$d['field']] === 'date')
		{
			if( ! isset($d['operator']) or ! isset($this->numberOps[$d['operator']]))
				$this->app->abort(400);

			try {
				$value = new DateTime($d['value']);
			}
			catch(Exception $e)
			{
				$this->app->abort(400);
			}

			$query->where($d['field'], $d['operator'], $value, $logic);
		}
		else {
			$this->app->abort(500);
		}
	}

	private function filter($query, $d)
	{
		$filter_r = function($query, $d, $depth, $logic) use(&$filter_r)
		{
			if($depth >= 32)
				$this->app->abort(400);

			if( ! is_array($d))
				$this->app->abort(400);

			if(isset($d['filters']) and is_array($d['filters']))
			{
				if( ! isset($d['logic']) or ! in_array($d['logic'], ['and', 'or'], true))
					$this->app->abort(400);

				$query->where(function($query) use ($d, $depth, $filter_r)
				{
					foreach($d['filters'] as $f)
						$filter_r($query, $f, $depth + 1, $d['logic']);
				}, null, null, $logic);
			}
			else {
				$this->filterField($query, $d, $logic);
			}
		};

		$filter_r($query, $d, 0, 'and');
	}

	public function execute($query)
	{
		if(isset($this->input[$this->sortKey]))
			$this->sort($query, $this->input[$this->sortKey]);

		if(isset($this->input[$this->filterKey]))
			$this->filter($query, $this->input[$this->filterKey]);
	}

}

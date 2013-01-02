<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright © 2011–2013 Spadefoot Team.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * This class provides a way to access the scheme for a PostgreSQL database.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2013-01-01
 *
 * @abstract
 */
abstract class Base_DB_PostgreSQL_Schema extends DB_Schema {

	/**
	 * This function returns a result set that contains an array of all fields in
	 * the specified database table/view.
	 *
	 * @access public
	 * @override
	 * @param string $table                 the table/view to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 an array of fields within the specified
	 *                                      table
	 *
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 */
	public function fields($table, $like = '') {
		/*
		$this->_connection or $this->connect();

		$sql = 'SELECT column_name, column_default, is_nullable, data_type, character_maximum_length, numeric_precision, numeric_scale, datetime_precision'
			.' FROM information_schema.columns'
			.' WHERE table_schema = '.$this->quote($this->schema()).' AND table_name = '.$this->quote($table);

		if (is_string($like))
		{
			$sql .= ' AND column_name LIKE '.$this->quote($like);
		}

		$sql .= ' ORDER BY ordinal_position';

		$result = array();

		foreach ($this->query(Database::SELECT, $sql, FALSE) as $column)
		{
			$column = array_merge($this->datatype($column['data_type']), $column);

			$column['is_nullable'] = ($column['is_nullable'] === 'YES');

			$result[$column['column_name']] = $column;
		}

		return $result;
		*/
	}

	/**
	 * This function returns a result set of indexes for the specified table.
	 *
	 * @access public
	 * @override
	 * @param string $table                 the table to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of indexes for the specified
	 *                                      table
	 *
	 * @see http://stackoverflow.com/questions/2204058/show-which-columns-an-index-is-on-in-postgresql
	 * @see http://code.activestate.com/recipes/576557/
	 */
	public function indexes($table, $like = '') {
		/*
		$sql = "SELECT
			t.relname AS table_name,
			i.relname AS index_name,
			a.attname AS column_name
		FROM
			pg_class t,
			pg_class i,
			pg_index ix,
			pg_attribute a
		WHERE
			t.oid = ix.indrelid
			AND i.oid = ix.indexrelid
			AND a.attrelid = t.oid
			AND a.attnum = ANY(ix.indkey)
			AND t.relkind = 'r'
			AND t.relname LIKE 'test%'
		ORDER BY
			t.relname,
			i.relname;";

		$connection = DB_Connection_Pool::instance()->get_connection($this->source);
		$results = $connection->query($sql);

		return $results;
		*/
	}

	/**
	 * This function returns a result set of database tables.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table.                                     |
	 * | type          | string        | The type of table.              .                          |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @override
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of database tables
	 *
	 * @see http://www.alberton.info/postgresql_meta_info.html
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 * @see http://www.polak.ro/postgresql-select-tables-names.html
	 */
	public function tables($like = '') {
		$builder = DB_SQL::select($this->source)
			->column('table_schema', 'schema')
			->column('table_name', 'table')
			->column(DB_SQL::expr("'BASE'"), 'type')
			->from('information_schema.tables')
			->where('table_type', DB_SQL_Operator::_EQUAL_TO_, 'BASE TABLE')
			->where('table_schema', DB_SQL_Operator::_NOT_IN_, array('pg_catalog', 'information_schema'))
			->order_by(DB_SQL::expr('UPPER("table_schema")'))
			->order_by(DB_SQL::expr('UPPER("table_name")'));

		if ( ! empty($like)) {
			$builder->where('table_name', DB_SQL_Operator::_LIKE_, $like);
		}

		return $builder->query();
	}

	/**
	 * This function returns a result set of triggers for the specified table.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table to which the trigger is defined on.  |
	 * | trigger       | string        | The name of the trigger.                                   |
	 * | event         | string        | 'INSERT', 'DELETE', or 'UPDATE'                            |
	 * | timing        | string        | 'BEFORE', 'AFTER', or 'INSTEAD OF'                         |
	 * | per           | string        | 'ROW', 'STATEMENT', or 'EVENT'                             |
	 * | action        | string        | The action that will be triggered                          |
	 * | seq_index     | integer       | The sequence index of the trigger.                         |
	 * | created       | date/time     | The date/time of when the trigger was created.             |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @override
	 * @param string $table                 the table to evaluated
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of triggers for the specified
	 *                                      table
	 *
	 * @see http://www.postgresql.org/docs/8.1/static/infoschema-triggers.html
	 */
	public function triggers($table, $like = '') {
		$builder = DB_SQL::select($this->source)
			->column('event_object_schema', 'schema')
			->column('event_object_table', 'table')
			->column('trigger_name', 'trigger')
			->column('event_manipulation', 'event')
			->column('condition_timing', 'timing')
			->column('action_orientation', 'per')
			->column('action_statement', 'action')
			->column('action_order', 'seq_index')
			->column(DB_SQL::expr('NULL'), 'created')
			->from('information_schema.triggers')
			->where('event_object_schema', DB_SQL_Operator::_NOT_IN_, array('pg_catalog', 'information_schema'))
			->where('event_object_table', '!~', '^pg_')3
			->where(DB_SQL::expr('UPPER("event_object_table")'), DB_SQL_Operator::_EQUAL_TO_, $table)
			->order_by(DB_SQL::expr('UPPER("event_object_schema")'))
			->order_by(DB_SQL::expr('UPPER("event_object_table")'))
			->order_by(DB_SQL::expr('UPPER("trigger_name")'))
			->order_by('action_order');

		if ( ! empty($like)) {
			$builder->where('trigger_name', DB_SQL_Operator::_LIKE_, $like);
		}

		return $builder->query();
	}

	/**
	 * This function returns a result set of database views.
	 *
	 * +---------------+---------------+------------------------------------------------------------+
	 * | field         | data type     | description                                                |
	 * +---------------+---------------+------------------------------------------------------------+
	 * | schema        | string        | The name of the schema that contains the table.            |
	 * | table         | string        | The name of the table.                                     |
	 * | type          | string        | The type of table.              .                          |
	 * +---------------+---------------+------------------------------------------------------------+
	 *
	 * @access public
	 * @override
	 * @param string $like                  a like constraint on the query
	 * @return DB_ResultSet                 a result set of database views
	 *
	 * @see http://www.alberton.info/postgresql_meta_info.html
	 * @see http://www.linuxscrew.com/2009/07/03/postgresql-show-tables-show-databases-show-columns/
	 * @see http://www.polak.ro/postgresql-select-tables-names.html
	 */
	public function views($like = '') {
		$builder = DB_SQL::select($this->source)
			->column('table_schema', 'schema')
			->column('table_name', 'table')
			->column(DB_SQL::expr("'VIEW'"), 'type')
			->from('information_schema.tables')
			->where('table_type', DB_SQL_Operator::_EQUAL_TO_, 'VIEW')
			->where('table_schema', DB_SQL_Operator::_NOT_IN_, array('pg_catalog', 'information_schema'))
			->where('table_name', '!~', '^pg_')
			->order_by(DB_SQL::expr('UPPER("table_schema")'))
			->order_by(DB_SQL::expr('UPPER("table_name")'));

		if ( ! empty($like)) {
			$builder->where('table_name', DB_SQL_Operator::_LIKE_, $like);
		}

		return $builder->query();
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * This function returns an associated array which describes the properties
	 * for the specified SQL data type.
	 *
	 * @access protected
	 * @override
	 * @param string $type                  the SQL data type
	 * @return array                        an associated array which describes the properties
	 *                                      for the specified data type
	 */
	protected function data_type($type) {
		/*
		static $types = array(
			// PostgreSQL >= 7.4
			'box'       => array('type' => 'string'),
			'bytea'     => array('type' => 'string', 'binary' => TRUE),
			'cidr'      => array('type' => 'string'),
			'circle'    => array('type' => 'string'),
			'inet'      => array('type' => 'string'),
			'int2'      => array('type' => 'int', 'min' => '-32768', 'max' => '32767'),
			'int4'      => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
			'int8'      => array('type' => 'int', 'min' => '-9223372036854775808', 'max' => '9223372036854775807'),
			'line'      => array('type' => 'string'),
			'lseg'      => array('type' => 'string'),
			'macaddr'   => array('type' => 'string'),
			'money'     => array('type' => 'float', 'exact' => TRUE, 'min' => '-92233720368547758.08', 'max' => '92233720368547758.07'),
			'path'      => array('type' => 'string'),
			'polygon'   => array('type' => 'string'),
			'point'     => array('type' => 'string'),
			'text'      => array('type' => 'string'),

			// PostgreSQL >= 8.3
			'tsquery'   => array('type' => 'string'),
			'tsvector'  => array('type' => 'string'),
			'uuid'      => array('type' => 'string'),
			'xml'       => array('type' => 'string'),
		);

		if (isset($types[$type])) {
			return $types[$type];
		}

		return parent::data_type($type);
		*/
	}

}

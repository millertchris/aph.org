<?php
/**
 * Query
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Query' ) ) {

	/**
	 * Class Query.
	 */
	class SFL_Query {

		/**
		 * Table.
		 *
		 * @var Object
		 */
		protected $table = '';

		/**
		 * Database object.
		 *
		 * @var Object
		 */
		protected $database = null;

		/**
		 * Type
		 *
		 * @var Integer
		 */
		protected $type = 0;

		/**
		 * Target.
		 *
		 * @var String
		 */
		protected $target = null;

		/**
		 * Joins.
		 *
		 * @var Array
		 */
		protected $joins = array();

		/**
		 * Where
		 *
		 * @var Array
		 */
		protected $where = array();

		/**
		 * GROUP BY.
		 *
		 * @var Array
		 */
		protected $group_by = array();

		/**
		 *  HAVING.
		 *
		 * @var Array
		 */
		protected $having = array();

		/**
		 * Number of rows returned by the SELECT.
		 *
		 * @var Integer
		 */
		protected $limit = 0;

		/**
		 * Specify the offset of the first row to return.
		 *
		 * @var Integer
		 */
		protected $offset = 0;

		/**
		 *  Order By.
		 *
		 * @var String
		 */
		protected $order_by = null;

		/**
		 * Order.
		 *
		 * @var String
		 */
		protected $order = 'ASC';

		/**
		 * Index By.
		 *
		 * @var String
		 */
		protected $index_by = null;

		/**
		 * Alias.
		 *
		 * @var String
		 */
		protected $alias;

		/**
		 * Constructor.
		 *
		 * @since 1.0
		 */
		public function __construct( $table, $alias = 't' ) {
			if ( ! $this->database ) {
				global $wpdb;

				$this->database = $wpdb;
			}

			$this->table    = $table;
			$this->alias    = $alias;
			$this->target   = "`{$alias}`.*";
			$this->order_by = "`{$alias}`.`id`";
		}

		/**
		 * Set query type to SELECT and specify fields to be selected.
		 *
		 * @since 1.0
		 */
		public function select( $target = null ) {
			$this->type   = 0;
			$this->target = null !== $target ? $target : "`{$this->alias}`.*";

			return $this;
		}

		/**
		 * Left join another table.
		 *
		 * @since 1.0
		 * @param Object $table Database Table
		 * @param String $alias Alias name.
		 * @param String $on on.
		 */
		public function leftJoin( $table, $alias, $on ) {

			$this->joins[ $alias ] = array(
				'table' => $table,
				'on'    => $on,
				'type'  => 'LEFT',
			);

			return $this;
		}

		/**
		 * Left join no table.
		 *
		 * @since 1.0
		 * @param Object $table Database Table
		 * @param String $alias Alias name.
		 * @param String $on on.
		 */
		public function tableJoin( $table, $alias, $on ) {
			$this->joins[ $alias ] = array(
				'table' => $table,
				'on'    => $on,
				'type'  => 'LEFT',
			);

			return $this;
		}

		/**
		 * Inner join another table.
		 *
		 * @since 1.0
		 * @param Object $table Database Table
		 * @param String $alias Alias name.
		 * @param String $on on.
		 */
		public function innerJoin( $table, $alias, $on ) {

			$this->joins[ $alias ] = array(
				'table' => $table,
				'on'    => $on,
				'type'  => 'INNER',
			);

			return $this;
		}

		/**
		 * Set the maximum number of results to return at once.
		 *
		 * @since 1.0
		 * @param Integer $limit Limit
		 */
		public function limit( $limit ) {
			$this->limit = (int) $limit;

			return $this;
		}

		/**
		 * Set the offset to use when calculating results.
		 *
		 * @since 1.0
		 * @param Integer $offset Offset
		 */
		public function offset( $offset ) {
			$this->offset = (int) $offset;

			return $this;
		}

		/**
		 * Set the column we should sort by.
		 *
		 * @since 1.0
		 * @param String $order_by Order By
		 */
		public function orderBy( $order_by ) {
			$this->order_by = $order_by;

			return $this;
		}

		/**
		 * Set the order we should sort by.
		 *
		 * @since 1.0
		 * @param String $order Order
		 */
		public function order( $order ) {
			$this->order = $order;

			return $this;
		}

		/**
		 * Add a `=` clause to the search query.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $value Table value.
		 * @param String $glue Condition.
		 */
		public function where( $column, $value, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'where',
				'column' => $column,
				'value'  => $value,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Add a `!=` clause to the search query.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $value Table value.
		 * @param String $glue Condition.
		 */
		public function whereNot( $column, $value, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'not',
				'column' => $column,
				'value'  => $value,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Add a `LIKE` clause to the search query.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $value Table value.
		 * @param String $glue Condition.
		 */
		public function whereLike( $column, $value, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'like',
				'column' => $column,
				'value'  => $value,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Add a `NOT LIKE` clause to the search query.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $value Table value.
		 * @param String $glue Condition.
		 */
		public function whereNotLike( $column, $value, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'not_like',
				'column' => $column,
				'value'  => $value,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Add a `<` clause to the search query.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $value Table value.
		 * @param String $glue Condition.
		 */
		public function whereLt( $column, $value, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'lt',
				'column' => $column,
				'value'  => $value,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Add a `<=` clause to the search query.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $value Table value.
		 * @param String $glue Condition.
		 */
		public function whereLte( $column, $value, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'lte',
				'column' => $column,
				'value'  => $value,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Add a `>` clause to the search query.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $value Table value.
		 * @param String $glue Condition.
		 */
		public function whereGt( $column, $value, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'gt',
				'column' => $column,
				'value'  => $value,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Add a `>=` clause to the search query.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $value Table value.
		 * @param String $glue Condition.
		 */
		public function whereGte( $column, $value, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'gte',
				'column' => $column,
				'value'  => $value,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Add an `IN` clause to the search query.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $value Table value.
		 * @param String $glue Condition.
		 */
		public function whereIn( $column, array $in, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'in',
				'column' => $column,
				'value'  => $in,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Add a `NOT IN` clause to the search query.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $value Table value.
		 * @param String $glue Condition.
		 */
		public function whereNotIn( $column, array $not_in, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'not_in',
				'column' => $column,
				'value'  => $not_in,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Add an OR statement to the where clause.
		 *
		 * @since 1.0
		 * @param Array  $where Table Where.
		 * @param String $glue Condition.
		 */
		public function whereAny( array $where, $glue = 'AND' ) {
			$this->where[] = array(
				'type'  => 'any',
				'where' => $where,
				'glue'  => $glue,
			);

			return $this;
		}

		/**
		 * Add an AND statement to the where clause.
		 *
		 * @since 1.0
		 * @param Array  $where Table Where.
		 * @param String $glue Condition.
		 */
		public function whereAll( array $where, $glue = 'AND' ) {
			$this->where[] = array(
				'type'  => 'all',
				'where' => $where,
				'glue'  => $glue,
			);

			return $this;
		}

		/**
		 * Add a BETWEEN statement to the where clause.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 * @param String $start Start.
		 * @param String $end End.
		 * @param String $glue Condition.
		 */
		public function whereBetween( $column, $start, $end, $glue = 'AND' ) {
			$this->where[] = array(
				'type'   => 'between',
				'column' => $column,
				'start'  => $start,
				'end'    => $end,
				'glue'   => $glue,
			);

			return $this;
		}

		/**
		 * Set the group by.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 */
		public function groupBy( $column ) {
			$this->group_by[] = $column;

			return $this;
		}

		/**
		 * Add raw having statement.
		 *
		 * @since 1.0
		 * @param String $statement Statement.
		 * @param Array  $values Values.
		 * @param String $glue Condition.
		 */
		public function havingRaw( $statement, array $values, $glue = 'AND' ) {
			$this->having[] = array(
				'type'      => 'raw_having',
				'statement' => $statement,
				'values'    => $values,
				'glue'      => $glue,
			);

			return $this;
		}

		/**
		 * Set a column that will be used as index for resulting array.
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 */
		public function indexBy( $column ) {
			$this->index_by = $column;

			return $this;
		}

		/**
		 * Runs the same query as find, but with no limit and don't retrieve the
		 * results, just the total items found.
		 *
		 * @since 1.0
		 * @return Integer.
		 */
		public function count() {

			return (int) $this->database->get_var( $this->composeQuery( true ) );
		}

		/**
		 * Returns the specified column
		 *
		 * @since 1.0
		 * @param String $column Table Column.
		 */
		public function fetchCol( $column ) {

			$this->select( $column );

			return $this->database->get_col( $this->composeQuery() );
		}

		/**
		 * Execute query and hydrate result as array.
		 *
		 * @since 1.0
		 * @return Array
		 */
		public function fetchArray() {
			// Query.
			$query = $this->composeQuery( false );

			return $this->database->get_results( $query, ARRAY_A );
		}

		/**
		 * Execute query and hydrate result as object.
		 *
		 * @since 1.0
		 * @return Object
		 */
		public function fetchObject() {
			// Query.
			$query = $this->composeQuery( false );

			return $this->database->get_results( $query, OBJECT );
		}

		/**
		 * Execute query and fetch one result as array.
		 *
		 * @since 1.0
		 * @return Array
		 */
		public function fetchRow() {

			return $this->database->get_row( $this->composeQuery( false ), ARRAY_A );
		}

		/**
		 * Compose the actual SQL query from all of our filters and options.
		 *
		 * @since 1.0
		 * @param Boolean $only_count Count.
		 */
		public function composeQuery( $only_count = false ) {
			$table  = $this->table;
			$join   = '';
			$where  = '';
			$group  = '';
			$having = '';
			$order  = '';
			$limit  = '';
			$offset = '';
			$values = array();

			// Join.
			foreach ( $this->joins as $alias => $t ) {
				$join .= " {$t[ 'type' ]} JOIN `{$t[ 'table' ]}` AS `{$alias}` ON {$t[ 'on' ]}";
			}

			// Where.
			foreach ( $this->where as $q ) {
				// where.
				if ( 'where' == $q['type'] ) {
					$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} ";
					if ( null === $q['value'] ) {
						$where .= 'IS NULL';
					} else {
						$where .= "= '{$q[ 'value' ]}'";
					}
				} elseif ( 'not' == $q['type'] ) { // where_not.
					$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} ";
					if ( null === $q['value'] ) {
						$where .= 'IS NOT NULL';
					} else {
						$where .= "!= '{$q[ 'value' ]}'";
					}
				} elseif ( 'like' == $q['type'] ) { // where_like.
					if ( sfl_check_is_array( $q['column'] ) ) {
						$where .= " {$q[ 'glue' ]} (";
						foreach ( $q['column'] as $column => $value ) {
							$where .= " ({$column} LIKE '{$value}') {$q[ 'value' ]}";
						}
						$where = substr( $where, 0, - 3 ) . ')';
					} else {
						$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} LIKE '{$q[ 'value' ]}'";
					}
				} elseif ( 'not_like' == $q['type'] ) { // where_not_like.
					if ( sfl_check_is_array( $q['column'] ) ) {
						$where .= " {$q[ 'glue' ]} (";
						foreach ( $q['column'] as $column => $value ) {
							$where .= " ({$column} NOT LIKE '{$value}') {$q[ 'value' ]}";
						}
						$where = substr( $where, 0, - 3 ) . ')';
					} else {
						$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} NOT LIKE '{$q[ 'value' ]}'";
					}
				} elseif ( 'lt' == $q['type'] ) { // where_lt.
					if ( sfl_check_is_array( $q['column'] ) ) {
						$where .= " {$q[ 'glue' ]} (";
						foreach ( $q['column'] as $column => $value ) {
							$where .= " ({$column} < '{$value}') {$q[ 'value' ]}";
						}
						$where = substr( $where, 0, - 3 ) . ')';
					} else {
						$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} < '{$q[ 'value' ]}'";
					}
				} elseif ( 'lte' == $q['type'] ) { // where_lte.
					if ( sfl_check_is_array( $q['column'] ) ) {
						$where .= " {$q[ 'glue' ]} (";
						foreach ( $q['column'] as $column => $value ) {
							$where .= " ({$column} <= '{$value}') {$q[ 'value' ]}";
						}
						$where = substr( $where, 0, - 3 ) . ')';
					} else {
						$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} <= '{$q[ 'value' ]}'";
					}
				} elseif ( 'gt' == $q['type'] ) { // where_gt.
					if ( sfl_check_is_array( $q['column'] ) ) {
						$where .= " {$q[ 'glue' ]} (";
						foreach ( $q['column'] as $column => $value ) {
							$where .= " ({$column} > '{$value}') {$q[ 'value' ]}";
						}
						$where = substr( $where, 0, - 3 ) . ')';
					} else {
						$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} > '{$q[ 'value' ]}'";
					}
				} elseif ( 'gte' == $q['type'] ) { // where_gte.
					if ( sfl_check_is_array( $q['column'] ) ) {
						$where .= " {$q[ 'glue' ]} (";
						foreach ( $q['column'] as $column => $value ) {
							$where .= " ({$column} >= '{$value}') {$q[ 'value' ]}";
						}
						$where = substr( $where, 0, - 3 ) . ')';
					} else {
						$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} >= '{$q[ 'value' ]}'";
					}
				} elseif ( 'in' == $q['type'] ) { // where_in.
					$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} IN (";

					if ( empty( $q['value'] ) ) {
						$where .= 'NULL';
					} else {
						foreach ( $q['value'] as $value ) {
							$where .= "'{$value}',";
						}
						$where = substr( $where, 0, - 1 );
					}

					$where .= ')';
				} elseif ( 'not_in' == $q['type'] ) { // where_not_in.
					if ( ! empty( $q['value'] ) ) {
						$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} NOT IN (";

						foreach ( $q['value'] as $value ) {
							$where .= "{$value},";
						}

						$where = substr( $where, 0, - 1 ) . ')';
					}
				} elseif ( 'any' == $q['type'] ) { // where_any.
					$where .= " {$q[ 'glue' ]} (";

					foreach ( $q['where'] as $column => $value ) {
						$where .= "{$column} = {$value} OR ";
					}

					$where = substr( $where, 0, - 4 ) . ')';
				} elseif ( 'all' == $q['type'] ) { // where_all.
					$where .= " {$q[ 'glue' ]} (";

					foreach ( $q['where'] as $column => $value ) {
						$where .= "{$column} = {$value} AND ";
					}

					$where = substr( $where, 0, - 5 ) . ')';
				} elseif ( 'between' == $q['type'] ) { // between.
					$where .= " {$q[ 'glue' ]} {$q[ 'column' ]} BETWEEN '{$q[ 'start' ]}' AND '{$q[ 'end' ]}'";
				}
			}

			// Finish where clause.
			if ( '' != $where ) {
				$where = ' WHERE ' . substr( $where, strpos( $where, ' ', 1 ) + 1 );
			}

			// Group.
			if ( ! empty( $this->group_by ) ) {
				$group = ' GROUP BY ' . implode( ',', $this->group_by );
			}

			// Having.
			foreach ( $this->having as $q ) {
				// raw_having.
				if ( 'raw_having' == $q['type'] ) {
					$having .= " {$q[ 'glue' ]} ({$q[ 'statement' ]})";
					foreach ( $q['values'] as $value ) {
						$values[] = $value;
					}
				}
			}

			// Finish having clause.
			if ( ! empty( $having ) ) {
				$having = ' HAVING ' . substr( $having, strpos( $having, ' ', 1 ) + 1 );
			}

			// Order.
			$order = ' ORDER BY ' . $this->order_by . ' ' . $this->order;

			// Limit.
			if ( $this->limit > 0 ) {
				$limit = ' LIMIT ' . $this->limit;
			}

			// Offset.
			if ( $this->offset > 0 ) {
				$offset = ' OFFSET ' . $this->offset;
			}

			// Query.
			if ( $only_count ) {
				return "SELECT COUNT(*) FROM `{$table}` AS `{$this->alias}`{$join}{$where}{$group}{$having}";
			}

			return "SELECT {$this->target} FROM `{$table}` AS `{$this->alias}`{$join}{$where}{$group}{$having}{$order}{$limit}{$offset}";
		}
	}

}

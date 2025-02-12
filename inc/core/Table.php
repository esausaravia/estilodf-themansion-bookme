<?php
namespace Bookme\Inc\Core;

/**
 * Class Table
 */
abstract class Table
{
    /**
     * Table field id
     * @var   int
     */
    protected $id;

    /**
     * Reference to global database object.
     * @var \wpdb
     */
    protected static $wpdb;

    /**
     * Name of table in database without WordPress prefix.
     * Must be defined in the child class.
     * @static
     * @var string
     */
    protected static $table;

    /**
     * Schema of table fields in database.
     * Must be defined in the child class as
     * array(
     *     '[FIELD_NAME]' => array(
     *         'format'  => '[FORMAT]',
     *         'default' => '[DEFAULT_VALUE]',
     *     )
     * )
     * @static
     * @var array
     */
    protected static $schema;

    /**
     * Array of cached tables indexed by class_name & id.
     * @var array
     */
    protected static $cache = array();

    /**
     * Name of table in database with WordPress prefix.
     * @var string
     */
    private $table_name = null;

    /**
     * Values loaded from the database.
     * @var boolean
     */
    private $loaded_values = null;

    /**
     * Constructor
     * @param array $fields
     */
    public function __construct( $fields = array() )
    {
        if ( self::$wpdb === null ) {
            /** @var \wpdb $wpdb */
            global $wpdb;

            self::$wpdb = $wpdb;
        }

        $this->table_name = static::get_table_name();

        $this->set_fields( $fields );
    }

    /**
     * Load table from database by ID.
     *
     * @param integer $id
     * @return boolean
     */
    public function load( $id )
    {
        return $this->load_by( array( 'id' => $id ) );
    }

    /**
     * Load table from database by field values.
     *
     * @param array $fields
     * @return bool
     */
    public function load_by(array $fields )
    {
        // Prepare WHERE clause.
        $where = array();
        $values = array();
        foreach ( $fields as $field => $value ) {
            if ( $value === null ) {
                $where[] = sprintf( '`%s` IS NULL', $field );
            } else {
                $where[] = sprintf( '`%s` = %s', $field, static::$schema[ $field ]['format'] );
                $values[] = $value;
            }
        }

        $query = sprintf(
            'SELECT * FROM `%s` WHERE %s LIMIT 1',
            $this->table_name,
            implode( ' AND ', $where )
        );

        $row = self::$wpdb->get_row(
            empty ( $values ) ? $query : self::$wpdb->prepare( $query, $values )
        );

        if ( $row ) {
            $this->set_fields( $row );
            $this->loaded_values = $this->get_fields();
        } else {
            $this->loaded_values = null;
        }

        return $this->is_loaded();
    }

    /**
     * Check whether the table was loaded from the database or not.
     *
     * @return bool
     */
    public function is_loaded()
    {
        return $this->loaded_values !== null;
    }

    /**
     * Set values to fields.
     * The method can be used to update only some fields.
     *
     * @param array|\stdClass $data
     * @param bool $overwrite_loaded_values
     * @return $this
     */
    public function set_fields($data, $overwrite_loaded_values = false )
    {
        if ( $data = (array) $data ) {
            foreach ( static::$schema as $field => $meta ) {
                if ( array_key_exists( $field, $data ) ) {
                    $this->{$field} = $data[ $field ];
                }
            }
        }

        // This parameter is used by System::bind_data_with_table()
        if ( $overwrite_loaded_values ) {
            $this->loaded_values = $this->get_fields();
        }

        return $this;
    }

    /**
     * Get values of fields as array.
     *
     * @return array
     */
    public function get_fields()
    {
        $data = array();
        foreach ( static::$schema as $field => $format ) {
            $data[ $field ] = $this->{$field};
        }
        return $data;
    }

    /**
     * Find table by id possibly using cache.
     *
     * @param $id
     * @param bool|true $use_cache
     * @return static|false
     */
    public static function find( $id, $use_cache = true )
    {
        $called_class = get_called_class();

        if ( $use_cache && isset ( Table::$cache[ $called_class ][ $id ] ) ) {
            return Table::$cache[ $called_class ][ $id ];
        }

        /** @var static $table */
        $table = new $called_class();
        if ( $table->load_by( array( 'id' => $id ) ) ) {
            if ( $use_cache ) {
                Table::$cache[ $called_class ][ $id ] = $table;
            }

            return $table;
        }

        return false;
    }

    /**
     * Save table to database.
     *
     * @return int|false
     */
    public function save()
    {
        // Prepare query data.
        $set    = array();
        $values = array();
        foreach ( static::$schema as $field => $data ) {
            if ( $field == 'id' ) {
                continue;
            }
            $value = $this->{$field};
            if ( $value === null ) {
                $set[]    = sprintf( '`%s` = NULL', $field );
            } else {
                $set[] = sprintf( '`%s` = %s', $field, static::$schema[ $field ]['format'] );
                $values[] = $value;
            }
        }
        // Run query.
        if ( $this->get_id() ) {
            $res = self::$wpdb->query( self::$wpdb->prepare(
                sprintf(
                    'UPDATE `%s` SET %s WHERE `id` = %d',
                    $this->table_name,
                    implode( ', ', $set ),
                    $this->get_id()
                ),
                $values
            ) );
        } else {
            $res = self::$wpdb->query( self::$wpdb->prepare(
                sprintf(
                    'INSERT INTO `%s` SET %s',
                    $this->table_name,
                    implode( ', ', $set )
                ),
                $values
            ) );
            if ( $res ) {
                $this->set_id( self::$wpdb->insert_id );
            }
        }

        if ( $res ) {
            // Update loaded values.
            $this->loaded_values = $this->get_fields();
        }

        return $res;
    }

    /**
     * Delete table from database.
     *
     * @return bool|false|int
     */
    public function delete()
    {
        if ( $this->get_id() ) {
            // Delete from cache.
            unset( Table::$cache[ get_called_class() ][ $this->get_id() ] );
            return self::$wpdb->delete( $this->table_name, array( 'id' => $this->get_id() ), array( '%d' ) );
        }

        return false;
    }

    /**
     * Get modified fields with initial values.
     *
     * @return array
     */
    public function get_modified()
    {
        return array_diff_assoc( $this->loaded_values ?: array(), $this->get_fields() );
    }

    /**
     * Get table name.
     *
     * @static
     * @return string
     */
    public static function get_table_name()
    {
        global $wpdb;

        return $wpdb->prefix . static::$table;
    }

    /**
     * Get schema.
     *
     * @static
     * @return array
     */
    public static function get_schema()
    {
        return static::$schema;
    }

    /** @return int */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Sets id
     *
     * @param int $id
     * @return $this
     */
    public function set_id($id )
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get table foreign key constraints
     *
     * @static
     * @return array
     */
    public static function get_constraints()
    {
        $constraints = array();
        foreach ( static::$schema as $field_name => $options ) {
            if ( array_key_exists( 'reference', $options ) ) {
                $ref_table = $options['reference']['table'];
                if ( isset ( $options['reference']['namespace'] ) ) {
                    $ref_table = $options['reference']['namespace'] . '\\' . $ref_table;
                } else {
                    $called_class = get_called_class();
                    $ref_table = substr( $called_class, 0, strrpos( $called_class, '\\' ) ) . '\\' . $ref_table;
                }
                $constraints[] = array(
                    'column_name'            => $field_name,
                    'referenced_table_name'  => call_user_func( array( $ref_table, 'get_table_name' ) ),
                    'referenced_column_name' => isset ( $options['reference']['field'] ) ? $options['reference']['field'] : 'id',
                );
            }
        }

        return $constraints;
    }
}
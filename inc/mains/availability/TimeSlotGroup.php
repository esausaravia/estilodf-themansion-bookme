<?php
namespace Bookme\Inc\Mains\Availability;

/**
 * Class TimeSlotGroup
 */
class TimeSlotGroup implements \IteratorAggregate
{
    /** @var  TimeSlot[] */
    protected $time_slots = array();

    /**
     * Create collection from array of time_slots.
     *
     * @param array $time_slots
     * @return static
     */
    public static function from_array(array $time_slots )
    {
        $new_collection = new static();
        $new_collection->time_slots = $time_slots;

        return $new_collection;
    }

    /**
     * Tells whether collection is empty.
     *
     * @return bool
     */
    public function is_empty()
    {
        return empty ( $this->time_slots );
    }

    /**
     * Tells whether collection is not empty.
     *
     * @return bool
     */
    public function is_not_empty()
    {
        return ! empty ( $this->time_slots );
    }

    /**
     * Push time_slot to collection.
     *
     * @param TimeSlot $time_slot
     * @return static
     */
    public function push( TimeSlot $time_slot  )
    {
        $this->time_slots[] = $time_slot;

        return $this;
    }

    /**
     * Put time_slot with the given key into the collection.
     *
     * @param mixed $key
     * @param TimeSlot $time_slot
     * @return static
     */
    public function put( $key, TimeSlot $time_slot  )
    {
        $this->time_slots[ $key ] = $time_slot;

        return $this;
    }

    /**
     * Determines if a given key exists in the collection.
     *
     * @param mixed $key
     * @return bool
     */
    public function has( $key )
    {
        return isset ( $this->time_slots[ $key ] );
    }

    /**
     * Get time_slot at a given key.
     *
     * @param mixed $key
     * @return TimeSlot|false
     */
    public function get( $key )
    {
        return $this->has( $key ) ? $this->time_slots[ $key ] : false;
    }

    /**
     * Get all time_slots.
     *
     * @return TimeSlot[]
     */
    public function all()
    {
        return $this->time_slots;
    }

    /**
     * Sort collection by keys.
     *
     * @return static
     */
    public function ksort()
    {
        ksort( $this->time_slots );

        return $this;
    }

    /**
     * Computes the intersection between collection and given time_slot.
     *
     * @param TimeSlot $time_slot
     * @return static
     */
    public function intersect( TimeSlot $time_slot )
    {
        $new_collection = new static();

        foreach ( $this->time_slots as $r1 ) {
            $r2 = $r1->intersect( $time_slot );
            if ( $r2 ) {
                $new_collection->push( $r2 );
            }
        }

        return $new_collection;
    }

    /**
     * Computes the subtraction between collection and given time_slot.
     *
     * @param TimeSlot $time_slot
     * @param self $removed
     * @return static
     */
    public function subtract( TimeSlot $time_slot, self &$removed = null )
    {
        $new_collection = new static();

        $removed = new static();

        foreach ( $this->time_slots as $r ) {
            $new_collection = $new_collection->merge( $r->subtract( $time_slot, $removed_time_slot ) );
            if ( $removed_time_slot ) {
                $removed->push( $removed_time_slot );
            }
        }

        return $new_collection;
    }

    /**
     * Computes the result by merging two collections.
     *
     * @param self $collection
     * @return static
     */
    public function merge( self $collection )
    {
        return static::from_array( array_merge( $this->time_slots, $collection->all() ) );
    }

    /**
     * Computes the union of two collections.
     *
     * @param self $collection
     * @return static
     */
    public function union( self $collection )
    {
        return static::from_array( $this->time_slots + $collection->all() );
    }

    /**
     * Computes new collection after applying filter to each item.
     *
     * @param callable $callback
     * @return static
     */
    public function filter( $callback )
    {
        return static::from_array( array_filter( $this->time_slots, $callback ) );
    }

    /**
     * Computes new collection by applying the callback to each item.
     *
     * @param callable $callback
     * @return static
     */
    public function map( $callback )
    {
        return static::from_array( array_map( $callback, $this->time_slots ) );
    }

    /**
     * @inheritdoc
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator( $this->time_slots );
    }
}
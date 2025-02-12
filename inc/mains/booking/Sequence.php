<?php
namespace Bookme\Inc\Mains\Booking;

/**
 * Class Sequence
 */
class Sequence
{
    /* @var SequenceItem[] */
    private $items = array();

    /**
     * Add Sequence item.
     *
     * @param SequenceItem $item
     */
    public function add( SequenceItem $item )
    {
        $this->items[] = $item;
    }

    /**
     * Drop all Sequence items.
     */
    public function clear()
    {
        $this->items = array();
    }

    /**
     * Get sequence items.
     *
     * @return SequenceItem[]
     */
    public function get_items()
    {
        return $this->items;
    }

    /**
     * Get items data as array.
     *
     * @return array
     */
    public function get_items_data()
    {
        $data = array();
        foreach ( $this->items as $key => $item ) {
            $data[ $key ] = $item->get_data();
        }

        return $data;
    }

    /**
     * Set items data from array.
     *
     * @param array $data
     */
    public function set_items_data(array $data )
    {
        foreach ( $data as $key => $item_data ) {
            $item = new SequenceItem();
            $item->set_data( $item_data );
            $this->items[ $key ] = $item;
        }
    }

}
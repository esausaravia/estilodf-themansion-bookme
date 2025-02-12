<?php

namespace Bookme\Inc\Mains\Tables;

use Bookme\Inc;

/**
 * Class SentNotification
 */
class SentNotification extends Inc\Core\Table
{
    /** @var  int */
    protected $ref_id;
    /** @var  int */
    protected $notification_id;
    /** @var  string */
    protected $created;

    protected static $table = 'bm_sent_notifications';

    protected static $schema = array(
        'id' => array('format' => '%d'),
        'ref_id' => array('format' => '%d'),
        'notification_id' => array('format' => '%d', 'reference' => array('table' => 'Notification')),
        'created' => array('format' => '%s'),
    );

    /**
     * Get ref_id
     *
     * @return int
     */
    public function get_ref_id()
    {
        return $this->ref_id;
    }

    /**
     * Set ref_id
     *
     * @param int $ref_id
     * @return $this
     */
    public function set_ref_id($ref_id)
    {
        $this->ref_id = $ref_id;

        return $this;
    }

    /**
     * Get notification id
     *
     * @return int
     */
    public function get_notification_id()
    {
        return $this->notification_id;
    }

    /**
     * Set notification id
     *
     * @param int $notification_id
     * @return $this
     */
    public function set_notification_id($notification_id)
    {
        $this->notification_id = $notification_id;

        return $this;
    }

    /**
     * Get created
     *
     * @return string
     */
    public function get_created()
    {
        return $this->created;
    }

    /**
     * Set created
     *
     * @param string $created
     * @return $this
     */
    public function set_created($created)
    {
        $this->created = $created;

        return $this;
    }

}
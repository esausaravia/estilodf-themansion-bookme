<?php
namespace Bookme\Inc\Mains;


use Bookme\Inc\Mains\Availability\DataHolders\Booking;

/**
 * Class Google
 */
class Google
{
    const EVENTS_PER_REQUEST = 250;

    /** @var \Google_Client */
    private $client;

    /** @var \Google_Service_Calendar */
    private $service;

    /** @var \Google_Service_Calendar_CalendarListEntry */
    private $calendar;

    /** @var \Google_Service_Calendar_Event */
    private $event;

    /** @var Tables\Employee */
    private $staff;

    private $errors = array();

    public function __construct()
    {
        include_once Plugin::get_directory() . '/inc/external/google/autoload.php';

        $this->client = new \Google_Client();
        $this->client->setClientId( get_option( 'bookme_gc_client_id' ) );
        $this->client->setClientSecret( get_option( 'bookme_gc_client_secret' ) );
    }

    /**
     * Load Google and Calendar Service data by Staff
     *
     * @param Tables\Employee $staff
     * @return bool
     */
    public function load_by_staff(Tables\Employee $staff )
    {
        $this->staff = $staff;
        if ($staff->get_google_data() ) {
            try {
                $this->client->setAccessToken( $staff->get_google_data() );
                if ( $this->client->isAccessTokenExpired() ) {
                    $this->client->refreshToken( $this->client->getRefreshToken() );
                    $staff->set_google_data( $this->client->getAccessToken() );
                    $staff->save();
                }

                $this->service = new \Google_Service_Calendar( $this->client );

                return true;
            } catch ( \Exception $e ) {
                $this->errors[] = 'Google Calendar: ' . $e->getMessage();
            }
        }

        return false;
    }

    /**
     * Load Google and Calendar Service data by Staff ID
     *
     * @param int $staff_id
     * @return bool
     */
    public function load_by_staff_id($staff_id )
    {
        $staff = Tables\Employee::find( $staff_id );

        return $this->load_by_staff( $staff );
    }

    /**
     * Create Event and return id
     *
     * @param Tables\Booking $booking
     * @return mixed
     */
    public function create_event(Tables\Booking $booking )
    {
        try {
            if ( in_array( $this->get_calendar_access(), array( 'writer', 'owner' ) ) ) {
                $this->event = new \Google_Service_Calendar_Event();

                $this->handle_event_data( $booking );

                /** @var \Google_Service_Calendar_Event $createdEvent */
                $createdEvent = $this->service->events->insert( $this->get_calendar_id(), $this->event );

                return $createdEvent->getId();
            }
        } catch ( \Exception $e ) {
            $this->errors[] = $e->getMessage();
        }

        return false;
    }

    /**
     * Update event
     *
     * @param Tables\Booking $booking
     * @return bool
     */
    public function update_event(Tables\Booking $booking )
    {
        try {
            if ( in_array( $this->get_calendar_access(), array( 'writer', 'owner' ) ) ) {
                $this->event = $this->service->events->get( $this->get_calendar_id(), $booking->get_google_event_id() );

                $this->handle_event_data( $booking );

                $this->service->events->update( $this->get_calendar_id(), $this->event->getId(), $this->event );

                return true;
            }
        } catch ( \Exception $e ) {
            $this->errors[] = $e->getMessage();
        }

        return false;
    }

    /**
     * Get list of Google Calendars.
     *
     * @return array
     */
    public function get_calendar_list()
    {
        $result = array();
        try {
            $calendarList = $this->service->calendarList->listCalendarList();
            while ( true ) {
                /** @var \Google_Service_Calendar_CalendarListEntry $calendarListEntry */
                foreach ( $calendarList->getItems() as $calendarListEntry ) {
                    if ( in_array( $calendarListEntry->getAccessRole(), array( 'writer', 'owner' ) ) ) {
                        $result[ $calendarListEntry->getId() ] = array(
                            'primary' => $calendarListEntry->getPrimary(),
                            'summary' => $calendarListEntry->getSummary(),
                        );
                    }
                }
                $pageToken = $calendarList->getNextPageToken();
                if ( $pageToken ) {
                    $optParams    = array( 'pageToken' => $pageToken );
                    $calendarList = $this->service->calendarList->listCalendarList( $optParams );
                } else {
                    break;
                }
            }
        } catch ( \Exception $e ) {
            Functions\Session::set( 'employee_google_auth_error', json_encode( $e->getMessage() ) );
        }

        return $result;
    }

    /**
     * Returns a collection of Google calendar events
     *
     * @param \DateTime $start_date
     * @return array|false
     */
    public function get_calendar_events(\DateTime $start_date )
    {
        $result = array();

        try {
            $calendar_access = $this->get_calendar_access();
            $limit_events    = get_option( 'bookme_gc_limit_events' );

            $timeMin = $start_date->format( \DateTime::RFC3339 );

            $events = $this->service->events->listEvents( $this->get_calendar_id(), array(
                'singleEvents' => true,
                'orderBy'      => 'startTime',
                'timeMin'      => $timeMin,
                'maxResults'   => $limit_events ?: self::EVENTS_PER_REQUEST,
            ) );

            while ( true ) {
                foreach ( $events->getItems() as $event ) {
                    //print_r($event);
                    /** @var \Google_Service_Calendar_Event $event */
                    // transparency = 'opaque'      - The event blocks time on the calendar.
                    //              = 'transparent' - The event does not block time on the calendar.
                    if ( $event->getStatus() !== 'cancelled' && ( $event->getTransparency() === null || $event->getTransparency() === 'opaque' ) ) {

                        // Skip events created by Bookme in non freeBusyReader calendar.
                        if ( $calendar_access != 'freeBusyReader' ) {
                            $ext_properties = $event->getExtendedProperties();
                            if ( $ext_properties !== null ) {
                                $private = $ext_properties->private;
                                if ( $private !== null && array_key_exists( 'service_id', $private ) ) {
                                    continue;
                                }
                            }
                        }

                        // Get start/end dates of event and transform them into WP timezone (Google doesn't transform whole day events into our timezone).
                        $event_start = $event->getStart();
                        $event_end   = $event->getEnd();

                        if ( $event_start->dateTime == null ) {
                            // All day event.
                            $event_start_date = new \DateTime( $event_start->date, new \DateTimeZone( $this->get_calendar_timezone() ) );
                            $event_end_date = new \DateTime( $event_end->date, new \DateTimeZone( $this->get_calendar_timezone() ) );
                        } else {
                            // Regular event.
                            $event_start_date = new \DateTime( $event_start->dateTime );
                            $event_end_date = new \DateTime( $event_end->dateTime );
                        }

                        // Convert to WP time zone.
                        $event_start_date = date_timestamp_set( date_create( Functions\System::get_wp_time_zone() ), $event_start_date->getTimestamp() );
                        $event_end_date   = date_timestamp_set( date_create( Functions\System::get_wp_time_zone() ), $event_end_date->getTimestamp() );

                        $result[] = new Booking(
                            0,
                            1,
                            $event_start_date->format( 'Y-m-d H:i:s' ),
                            $event_end_date->format( 'Y-m-d H:i:s' ),
                            0,
                            0,
                            true
                        );
                    }
                }

                if ( ! $limit_events && $events->getNextPageToken() ) {
                    $events = $this->service->events->listEvents( $this->get_calendar_id(), array(
                        'singleEvents' => true,
                        'orderBy'      => 'startTime',
                        'timeMin'      => $timeMin,
                        'pageToken'    => $events->getNextPageToken()
                    ) );
                } else {
                    break;
                }
            }

            return $result;
        } catch ( \Exception $e ) {
            $this->errors[] = $e->getMessage();
        }

        return false;
    }

    /**
     * @param $code
     * @return bool
     */
    public function auth_code_handler($code )
    {
        $this->client->setRedirectUri( self::generate_redirect_uri() );

        try {
            $this->client->authenticate( $code );

            return true;
        } catch ( \Exception $e ) {
            $this->errors[] = $e->getMessage();
        }

        return false;
    }

    /**
     * @return string
     */
    public function get_access_token()
    {
        return $this->client->getAccessToken();
    }

    /**
     * Revoke Google Calendar token.
     */
    public function revoke_token()
    {
        try {
            $this->client->revokeToken( $this->staff->get_google_data() );
        } catch ( \Exception $e ) {
            $this->errors[] = $e->getMessage();
        }
        $this->staff
            ->set_google_data( null )
            ->set_google_calendar_id( null )
            ->save();
    }

    /**
     * @param $staff_id
     * @return string
     */
    public function create_auth_url($staff_id )
    {
        $this->client->setRedirectUri( self::generate_redirect_uri() );
        $this->client->addScope( 'https://www.googleapis.com/auth/calendar' );
        $this->client->setState( strtr( base64_encode( $staff_id ), '+/=', '-_,' ) );
        $this->client->setApprovalPrompt( 'force' );
        $this->client->setAccessType( 'offline' );

        return $this->client->createAuthUrl();
    }

    /**
     * Delete event by id
     *
     * @param $event_id
     * @return bool
     */
    public function delete( $event_id )
    {
        try {
            if ( in_array( $this->get_calendar_access(), array( 'writer', 'owner' ) ) ) {
                $this->service->events->delete( $this->get_calendar_id(), $event_id );

                return true;
            }
        } catch ( \Exception $e ) {
            $this->errors[] = $e->getMessage();
        }

        return false;
    }

    /**
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     * @param Tables\Booking $booking
     */
    private function handle_event_data(Tables\Booking $booking )
    {
        $start_datetime = new \Google_Service_Calendar_EventDateTime();
        $start_datetime->setDateTime(
            Functions\Date::from_string( $booking->get_start_date() )->format( \DateTime::RFC3339 )
        );

        $end_datetime = new \Google_Service_Calendar_EventDateTime();
        $end_datetime->setDateTime(
            Functions\Date::from_string( $booking->get_end_date() )->format( \DateTime::RFC3339 )
        );

        $service = Tables\Service::find( $booking->get_service_id() );
        $description  = esc_html__( 'Service', 'bookme' ) . ': ' . $service->get_title() . PHP_EOL;
        $client_names = array();
        foreach ($booking->get_customer_bookings() as $ca ) {
            $description .= sprintf(
                "%s: %s\n%s: %s\n%s: %s\n",
                esc_html__( 'Name',  'bookme' ), $ca->customer->get_full_name(),
                esc_html__( 'Email', 'bookme' ), $ca->customer->get_email(),
                esc_html__( 'Phone', 'bookme' ), $ca->customer->get_phone()
            );
            $description .= $ca->get_formatted_custom_fields( 'text' ) . PHP_EOL;
            $client_names[] = $ca->customer->get_full_name();
        }

        if($url = $booking->get_online_meeting_host_url()) {
            $description .= sprintf(
                "%s: %s\n",
                esc_html__( 'Online Meeting',  'bookme' ), $url
            );
        }

        if($password = $booking->get_online_meeting_password()) {
            $description .= sprintf(
                "%s: %s\n",
                esc_html__( 'Meeting Password',  'bookme' ), $password
            );
        }

        $staff = Tables\Employee::find( $booking->get_staff_id() );
        $category = Tables\Category::find($service->get_category_id() );

        $title = strtr( get_option( 'bookme_gc_event_title', '{service_name}' ), array(
            '{service_name}' => $service->get_translated_title(),
            '{category_name}' => $category->get_name(),
            '{customer_name}' => implode( ', ', $client_names ),
            '{employee_name}'   => $staff->get_full_name()
        ) );

        $this->event->setStart( $start_datetime );
        $this->event->setEnd( $end_datetime );
        $this->event->setSummary( $title );
        $this->event->setDescription( $description );

        $extended_property = new \Google_Service_Calendar_EventExtendedProperties();
        $extended_property->setPrivate( array(
            'customers'      => json_encode( array_map( function( $ca ) { return $ca->customer->get_id(); }, $booking->get_customer_bookings() ) ),
            'service_id'     => $service->get_id(),
            'appointment_id' => $booking->get_id(),
        ) );
        $this->event->setExtendedProperties( $extended_property );
    }

    /**
     * @return string
     */
    private function get_calendar_id()
    {
        return $this->staff->get_google_calendar_id() ?: 'primary';
    }

    /**
     * @return string [freeBusyReader, reader, writer, owner]
     */
    private function get_calendar_access()
    {
        if ( $this->calendar === null ) {
            $this->calendar = $this->service->calendarList->get( $this->get_calendar_id() );
        }

        return $this->calendar->getAccessRole();
    }

    /**
     * @return mixed
     */
    private function get_calendar_timezone()
    {
        if ( $this->calendar === null ) {
            $this->calendar = $this->service->calendarList->get( $this->get_calendar_id() );
        }

        return $this->calendar->getTimeZone();
    }

    /**
     * Validate calendar
     *
     * @param null $calendar_id (send this parameter on unsaved form)
     * @return bool
     */
    public function validate_calendar($calendar_id = null )
    {
        if ( !$this->service ) {
            return false;
        }

        try {
            $this->service->calendarList->get( $calendar_id ?: $this->get_calendar_id() );

            return true;
        } catch ( \Exception $e ) {
            $this->errors[] = $e->getMessage();
        }

        return false;
    }

    /**
     * @return string
     */
    public static function generate_redirect_uri()
    {
        return admin_url( 'admin.php?page=' . \Bookme\App\Admin\Employees::page_slug );
    }

}
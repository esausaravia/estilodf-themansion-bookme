<?php
namespace Bookme\Inc\Mains\Functions;

use Bookme\Inc;

/**
 * Class Validator
 */
class Validator
{
    private $errors = array();

    /**
     * Get errors.
     *
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     * Validate name.
     *
     * @param string $field
     * @param string $name
     */
    public function validate_name($field, $name )
    {
        if ( $name != '' ) {
            $max_length = 255;
            if ( preg_match_all( '/./su', $name ) > $max_length ) {
                $this->errors[ $field ] = sprintf(
                    esc_html__( '"%s" is too long (%d characters max).', 'bookme' ),
                    $name,
                    $max_length
                );
            }
        } else {
            switch ( $field ) {
                case 'full_name' :
                    $this->errors[ $field ] = esc_html__( 'Name is required', 'bookme' );;
                    break;
                case 'first_name' :
                    $this->errors[ $field ] = esc_html__( 'First name is required', 'bookme' );;
                    break;
                case 'last_name' :
                    $this->errors[ $field ] = esc_html__( 'Last name is required', 'bookme' );;
                    break;
            }
        }
    }

    /**
     * Validate email.
     *
     * @param string $field
     * @param array $data
     */
    public function validate_email($field, $data )
    {
        if ( $data['email'] != '' ) {
            if ( ! is_email( $data['email'] ) ) {
                $this->errors[ $field ] = esc_html__( 'Invalid email', 'bookme' );
            }
            // Check email for uniqueness when a new WP account is going to be created.
            if ( get_option( 'bookme_customer_create_account', 0 ) && ! get_current_user_id() ) {
                $customer = new Inc\Mains\Tables\Customer();
                // Try to find customer by phone or email.
                $customer->load_by(
                    System::phone_required()
                        ? array( 'phone' => $data['phone'] )
                        : array( 'email' => $data['email'] )
                );
                if ( ( ! $customer->is_loaded() || ! $customer->get_wp_user_id() ) && email_exists( $data['email'] ) ) {
                    $this->errors[ $field ] = esc_html__( 'This email is already in use', 'bookme' );
                }
            }
        } else {
            $this->errors[ $field ] = esc_html__( 'Email is required', 'bookme' );
        }
    }

    /**
     * Validate phone.
     *
     * @param string $field
     * @param string $phone
     * @param bool $required
     */
    public function validate_phone($field, $phone, $required = false )
    {
        if ( $phone == '' && $required ) {
            $this->errors[ $field ] = esc_html__( 'Phone is required', 'bookme' );;
        }
    }

    /**
     * Validate number.
     *
     * @param string $field
     * @param mixed $number
     * @param bool $required
     */
    public function validate_number($field, $number, $required = false )
    {
        if ( $number != '' ) {
            if ( ! is_numeric( $number ) ) {
                $this->errors[ $field ] = esc_html__( 'Invalid data sent', 'bookme' );
            }
        } elseif ( $required ) {
            $this->errors[ $field ] = esc_html__( 'Required', 'bookme' );
        }
    }

    /**
     * Validate date.
     *
     * @param string $field
     * @param string $date
     * @param bool $required
     */
    public function validate_date($field, $date, $required = false )
    {
        if ( $date != '' ) {
            if ( date_create( $date ) === false ) {
                $this->errors[ $field ] = esc_html__( 'Invalid date sent', 'bookme' );
            }
        } elseif ( $required ) {
            $this->errors[ $field ] = esc_html__( 'Required', 'bookme' );
        }
    }

    /**
     * Validate time.
     *
     * @param string $field
     * @param string $time
     * @param bool $required
     */
    public function validate_time($field, $time, $required = false )
    {
        if ( $time != '' ) {
            if ( ! preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
                $this->errors[ $field ] = esc_html__( 'Invalid time sent', 'bookme' );
            }
        } elseif ( $required ) {
            $this->errors[ $field ] = esc_html__( 'Required', 'bookme' );
        }
    }

    /**
     * Validate custom fields.
     *
     * @param string $value
     * @param int $form_id
     * @param int $cart_key
     */
    public function validate_custom_fields($value, $form_id, $cart_key )
    {
        $decoded_value = json_decode( $value );
        $fields = array();
        foreach ( json_decode( get_option( 'bookme_custom_fields' ) ) as $field ) {
            $fields[ $field->id ] = $field;
        }

        foreach ( $decoded_value as $field ) {
            if ( isset( $fields[ $field->id ] ) ) {
                if ( $fields[ $field->id ]->required && empty ( $field->value ) && $field->value != '0' ) {
                    $this->errors['custom_fields'][ $cart_key ][ $field->id ] = esc_html__( 'Required', 'bookme' );
                }
            }
        }
    }

    /**
     * Validate google recaptcha
     *
     * @param string $value
     */
    public function validate_recaptcha($value)
    {
        if(get_option('bookme_captcha_enabled') && get_option('bookme_captcha_site_key')) {
            $secret = get_option('bookme_captcha_secret_key');
            //get verify response data
            $verifyResponse = wp_remote_fopen('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $value);
            $responseData = json_decode($verifyResponse);
            if(!$responseData->success){
                $this->errors['g_recaptcha_response'] = esc_html__( 'reCaptcha response is invalid.', 'bookme' );
            }
        }
    }

    /**
     * Validate customer
     *
     * @param array $data
     * @param Inc\Mains\Booking\UserData $bookingData
     */
    public function validate_customer($data, Inc\Mains\Booking\UserData $bookingData )
    {
        if ( empty ( $this->errors ) ) {
            $user_id  = get_current_user_id();
            $customer = new Inc\Mains\Tables\Customer();
            if ( $user_id > 0 ) {
                // find customer by WP user ID.
                $customer->load_by( array( 'wp_user_id' => $user_id ) );
            }
            if ( ! $customer->is_loaded() ) {
                // find customer by phone if phone is required
                $identifier = System::phone_required() ? 'phone' : 'email';
                $customer->load_by( array( $identifier => $data[ $identifier ] ) );
                if ( ! $customer->is_loaded() ) {
                    // find customer by email if phone is not required
                    $identifier = System::phone_required() ? 'email' : 'phone';
                    $customer->load_by( array( 'phone' => '', 'email' => '', $identifier => $data[ $identifier ] ) );
                }

                if ( get_option('bookme_customer_allow_duplicate') ) {
                    if ( System::show_first_last_name() ) {
                        $customer_data = array(
                            'first_name' => $data['first_name'],
                            'last_name'  => $data['last_name'],
                        );
                    } else {
                        $customer_data = array( 'full_name' => $data['full_name'] );
                    }
                    if ( $data['email'] != '' ) {
                        $customer_data['email'] = $data['email'];
                    }
                    if ( $data['phone'] != '' ) {
                        $customer_data['phone'] = $data['phone'];
                    }
                    $customer->load_by( $customer_data );

                } elseif ( ! isset ( $data['force_update_customer'] ) && $customer->is_loaded() ) {
                    // Find difference between new and existing data.
                    $diff   = array();
                    $fields = array(
                        'phone'     => esc_html__('Phone','bookme'),
                        'email'     => esc_html__('Email','bookme')
                    );
                    $current = $customer->get_fields();
                    if ( System::show_first_last_name() ) {
                        $fields['first_name'] = esc_html__('First Name', 'bookme');
                        $fields['last_name']  = esc_html__('Last Name', 'bookme');
                    } else {
                        $fields['full_name'] = esc_html__('Name', 'bookme');
                    }
                    foreach ( $fields as $field => $name ) {
                        if (
                            $data[ $field ] != '' &&
                            $current[ $field ] != '' &&
                            $data[ $field ] != $current[ $field ]
                        ) {
                            $diff[] = $name;
                        }
                    }

                    if ( ! empty ( $diff ) ) {
                        $this->errors['customer'] = sprintf(
                            __( 'This %s(%s) is already assigned to another user with different %s.<br/>Click "Update" if you want to update your data, or "Cancel" to edit the entered data.', 'bookme' ),
                            $fields[ $identifier ],
                            $data[ $identifier ],
                            implode( ', ', $diff )
                        );
                    }
                }
            }
            if ( $customer->is_loaded() ) {
                // Check bookings limit
                foreach ($bookingData->cart->get_items() as $item ) {
                    $service          = $item->get_service();
                    $first_visit_time = $bookingData->get( 'slots' );
                    if ( $service->check_bookings_limit_reached( $customer->get_id(), $first_visit_time[0][2] ) ) {
                        $this->errors['bookings_limit'] = true;
                        break;
                    }
                }
            }
        }
    }

    /**
     * Validate cart
     *
     * @param array $cart
     * @param int $form_id
     */
    public function validate_cart($cart, $form_id )
    {
        foreach ( $cart as $cart_key => $cart_parameters ) {
            foreach ( $cart_parameters as $parameter => $value ) {
                switch ( $parameter ) {
                    case 'custom_fields':
                        $this->validate_custom_fields( $value, $form_id, $cart_key );
                        break;
                }
            }
        }
    }
}
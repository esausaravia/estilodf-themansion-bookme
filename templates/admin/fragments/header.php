<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>

<?php if(get_option('bookme_stripe_notice', false)){ ?>
    <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php printf( __( '<strong>Bookme:</strong> Stripe payment method has been upgraded to support <a href="%s" target="_blank">SCA</a>. You must update your Stripe settings to use the payment method.', 'bookme' ), 'https://stripe.com/en-se/guides/strong-customer-authentication' ); ?><br><?php printf( __( 'Check the payment settings <a href="%s">here</a>.', 'bookme' ), \Bookme\Inc\Mains\Functions\System::esc_admin_url(\Bookme\App\Admin\Settings::page_slug, array( 'tab' => 'payments' )) ); ?>
    </div>
<?php
    delete_option('bookme_stripe_notice');
} ?>

<!-- Page Header Start-->
<div class="page-main-header">
    <div class="main-header-right">
        <div class="main-header-left text-center">
            <div class="logo-wrapper"><a href="#"><img src="<?php echo BOOKME_URL.'assets/admin/images/logo.png'; ?>" alt=""></a></div>
        </div>
        <div class="mobile-sidebar">
            <div class="media-body text-right switch-sm">
                <label class="switch ml-3"><i class="font-primary icon-feather-align-center" id="sidebar-toggle"></i></label>
            </div>
        </div>
        <div class="nav-right col pull-right right-menu">
            <ul class="nav-menus">
                <li><a class="text-dark" href="#" onclick="toggleFullScreen()" title="<?php esc_html_e('Full Screen','bookme') ?>" data-tippy-placement="top"><i class="icon-feather-maximize"></i></a></li>
                <li><button class="btn btn-default" type="button" style="cursor: default"><?php echo esc_html__('Version','bookme').' '.BOOKME_VERSION ?></button></li>
            </ul>
        </div>
    </div>
</div>
<!-- Page Header Ends -->
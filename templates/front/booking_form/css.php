<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access
$primary_color = get_option('bookme_primary_color', '#6B76FF');
$secondary_color = get_option('bookme_secondary_color', '#fff');
?>
<style>
    .bookme-form-error,
    .bookme-steps > li,
    .bookme-steps > li.bookme-steps-is-active:before,
    .bookme-modal a {
        color: <?php echo $primary_color ?> !important;
    }

    .bookme-booking-form .bookme-error,
    .bookme-steps > li:before,
    .bookme-steps > li.bookme-steps-is-active:before {
        border-color: <?php echo $primary_color ?> !important;
    }

    .bookme-button,
    .bookme-booking-form .bookme-box-loader .bookme-loader:after,
    .bookme-booking-form .bookme-calendar.bookme-loader:after,
    .bookme-steps > li:before,
    .bookme-steps > li:after{
        background-color: <?php echo $primary_color ?> !important;
    }

    .clndr .clndr-controls,
    .clndr .clndr-table tr .day:hover,
    .clndr .clndr-table tr .day.selected.event,
    .clndr .clndr-table tr .next-month:hover,
    .clndr .clndr-table tr .day.selected {
        background-color: <?php echo $primary_color ?>;
    }

    .bookme-button,
    .bookme-steps > li:before{
        color: <?php echo $secondary_color ?> !important;
    }

    .clndr .clndr-controls .clndr-month,
    .clndr .clndr-controls .clndr-control-button span,
    .clndr .clndr-table tr .day:hover,
    .clndr .clndr-table tr .day.selected.event,
    .clndr .clndr-table tr .day.selected,
    .clndr .clndr-table tr .day .selected .event .day-contents {
        color: <?php echo $secondary_color ?>;
    }

    .bookme-booking-form .bookme-loader:after {
        background-color: <?php echo $secondary_color ?> !important;
    }
    
    .bookme-steps > li.bookme-steps-is-active:before {
        background-color: transparent !important;
    }
</style>

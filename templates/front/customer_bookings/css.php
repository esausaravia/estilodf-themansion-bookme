<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access
$primary_color = get_option('bookme_primary_color', '#6B76FF');
$secondary_color = get_option('bookme_secondary_color', '#fff');
?>
<style>
    .bookme-customer-bookings {
        overflow: auto;
    }

    .bookme-customer-bookings h2 {
        margin-bottom: 20px;
        padding-bottom: 5px;
        border-bottom: 1px solid <?php echo $primary_color; ?>;
        width: fit-content;
        font-size: 22px;
    }

    .bookme-customer-bookings table {
        border-spacing: 1px;
        border-collapse: collapse;
        background: #fff;
        border-radius: 4px;
        overflow: hidden;
        width: 100%;
        position: relative;
        margin: 0 auto 20px;
    }

    .bookme-customer-bookings table thead tr {
        background: <?php echo $primary_color; ?>;
    }

    .bookme-customer-bookings table thead th {
        font-size: 18px;
        color: <?php echo $secondary_color; ?>;
        line-height: 1.2;
        font-weight: unset;
        vertical-align: middle;
        padding: 10px;
        text-align: center;
        border: none;
    }

    .bookme-customer-bookings table tbody tr {
        font-size: 15px;
        color: gray;
        line-height: 1.2;
        font-weight: unset;
        transition: 0.3s ease;
    }

    .bookme-customer-bookings table td {
        border: none;
        vertical-align: middle;
        padding: 10px;
        text-align: center;
    }

    .bookme-customer-bookings .bookme-no-bookings {
        text-align: center;
        height: 50px;
    }

    .bookme-customer-bookings table tbody tr:nth-child(even) {
        background-color: #f5f5f5;
    }

    .bookme-customer-bookings table tbody tr:hover {
        color: #555;
        background-color: #f5f5f5;
        cursor: pointer;
    }

    .bookme-customer-bookings table td .bookme-button {
        padding: 5px 10px !important;
        width: auto;
        min-width: 0;
        font-size: 12px !important;
        font-family: inherit;
    }
</style>

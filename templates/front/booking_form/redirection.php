<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="1;url=<?php echo esc_attr( $url ) ?>">
    <script type="text/javascript">
        window.location.href = <?php echo json_encode( $url ) ?>;
    </script>
    <title><?php esc_html_e( 'Page Redirection', 'bookme' ) ?></title>
</head>
<body>
<?php printf( __( 'If you are not redirected automatically, <a href="%s">Click here</a>.', 'bookme' ), esc_attr( $url ) ) ?>
</body>
</html>
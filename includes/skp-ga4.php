<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Direct access se bachav

function skp_add_ga4() {
    ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script src="https://www.googletagmanager.com/gtag/js?id=G-WTCN4EQ8VC"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        window.gtag = gtag;
        gtag('js', new Date());
        gtag('config', 'G-WTCN4EQ8VC');
    </script>
    <?php
}
add_action('wp_head', 'skp_add_ga4');


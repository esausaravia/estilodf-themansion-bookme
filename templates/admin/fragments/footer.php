<?php
defined('ABSPATH') or die('No script kiddies please!'); // No direct access ?>
<!-- footer start-->
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 footer-copyright">
                <p class="mb-0"><?php printf(__('Copyright &copy; %d <a href="%s" target="_blank">Bylancer</a>. All rights reserved.','bookme'), date('Y'), esc_url('https://bylancer.com')) ?></p>
            </div>
            <div class="col-md-6">
                <p class="float-right mb-0"><?php printf(__('Hand-crafted & made with %s','bookme'), '<i class="icon-feather-heart"></i>') ?></p>
            </div>
        </div>
    </div>
</footer>
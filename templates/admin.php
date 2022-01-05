<h1>WN Cocktails Settings</h1>
<?php settings_errors(); ?>
<form method="post" action="options.php">
    <?php settings_fields('wn-ckt-settings-group'); ?>
    <?php do_settings_sections('wn_cocktails'); ?>
    <?php submit_button(); ?>
</form>
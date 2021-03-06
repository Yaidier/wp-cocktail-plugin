<?php
/**
 * @package Wn Cocktails
 */

$letters = range('a', 'z');
$load_images = get_option( 'wn_ckt_load_images' ) ? 'yes' : 'no';


?>


<div class="wn_cocktails_wrapper" display-iimages="<?php echo $load_images; ?>">

    <div class="wn_ckt_letters_container">
        <?php foreach($letters as $letter) { ?>
            <div class="wn_ckt_letter_box">
                <a href=""><?php echo $letter ?></a>
            </div>
        <?php } ?>
    </div>

    <div class="wn_ckt_drinks_list">
        <!-- Content Generated by Ajax Call -->
        <div class="wn_ckt_error_message">Ooops! Something went wrong...</div>
    </div>


</div>
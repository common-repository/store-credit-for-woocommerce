<p class="text-center"><?php echo $top_desc; ?></p>
<p class="text-center"><h1 class="text-center">[code]</h1></p>
<?php 
    if(!empty($read_more_url) && !empty($read_more) ){
        ?>
<p class="text-center"><a href="<?php echo esc_url($read_more_url); ?>" class="button" ><?php echo esc_html($read_more); ?></a></p>   
        <?php
    }
?>
<?php if(!empty($expiry_date)) : ?>
<p class="text-center"><?php echo $expiry_msg; ?></p>
<?php endif; ?>

<p class="text-center"><?php echo $bottom_desc; ?></p>
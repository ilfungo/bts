<?php function oboxfb_upgrade_license_options(){ ?>
   <li class="admin-block-item">
        <div class="admin-description">
            <h4><?php _e("License Key","ocmx"); ?></h4>
        </div>
        <div class="admin-content">
            <div>
                <input name="oboxfb_license_key" id="oboxfb_license_key" value="<?php echo get_option("oboxfb_hashkey"); ?>" type="text">
                <input name="" id="oboxfb_license_button" value="Validate Key &amp; Update" class="button-primary" type="button">
            </div>
            <p><?php _e("Enter your License Key from Obox or your Purchase Code from Theme Forest. This is only for <em>updates</em>, it is not required to use Obox Social Commerce.","ocmx"); ?></p>
        </div>
	</li>
    <li id="result" class="admin-block-item no_display">
        <div class="admin-description">
            <h4><?php _e("Progress","ocmx"); ?></h4>
        </div>
        <div class="admin-content">
        	<ul class="form-options contained-forms"></ul>
        </div>
    </li>
<?php } 
add_action("oboxfb_upgrade_license_options", "oboxfb_upgrade_license_options"); ?>

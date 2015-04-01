<!-- Quality Service Section ---->
<div class="container">
	<?php $current_options=get_option('quality_options'); ?>

	<div class="row">
		<div class="col-md-4 col-sm-6 qua-service-area">
			<?php if($current_options['service_one_icon']) { ?>
			<div class="hexagon-box">
                    <a href="/?page_id=1423"><img src="<?php echo $current_options['service_one_icon']; ?>"></i></a>
			</div>
			<?php } ?>
			<?php if($current_options['service_one_title']) { ?>
			<h2><?php echo $current_options['service_one_title']; ?></h2>
			<?php } ?>
			<?php if($current_options['service_one_text']) { ?>
			<p><?php echo $current_options['service_one_text'];?></p>
			<?php } ?>			
		</div>
		<div class="col-md-4 col-sm-6 qua-service-area">
			<?php if($current_options['service_two_icon']) { ?>
			<div class="hexagon-box">
                    <a href="/?page_id=9"><img src="<?php echo $current_options['service_two_icon']; ?>"></a>
            </div>
			<?php } ?>
			<?php if($current_options['service_two_title']) { ?>
			<h2><a href="/?page_id=2">Login<?php //echo $current_options['service_two_title']; ?></a></h2>
			<?php } ?>
			<?php if($current_options['service_two_text']) { ?>
			<p><?php echo $current_options['service_two_text'];?></p>
			<?php } ?>			
		</div>
		<div class="col-md-4 col-sm-6 qua-service-area">
			<?php if($current_options['service_three_icon']) { ?>
			<div class="hexagon-box">
                    <a href="/?page_id=1417"><img src="<?php echo $current_options['service_three_icon']; ?>"></i></a>
            </div>
			<?php } ?>
			<?php if($current_options['service_three_title']) { ?>
			<h2><?php echo $current_options['service_three_title']; ?></h2>
			<?php } ?>
			<?php if($current_options['service_three_text']) { ?>
			<p><?php echo $current_options['service_three_text'];?></p>
			<?php } ?>
		</div>

	</div>
</div>
<!-- /Quality Service Section ---->
<div class="wrap">
    <?php require dirname(__FILE__).'/admin_header.php'; ?>

	<h2 class="nav-tab-wrapper">
	<?php
	$tabs = array(
		'main_config' => __('Main Configuration', 'wpfts_lang'),
		'indexing_engine_settings' => __('Indexing Engine Settings', 'wpfts_lang'),
		'sandbox_page' => __('Sandbox Area', 'wpfts_lang'),
	);
	$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'main_config';
	foreach ($tabs as $tab_key => $tab_caption) {
		$active = ($current_tab == $tab_key) ? " nav-tab-active" : "";
		echo '<a class="nav-tab'.$active.'" href="?page=wpfts-options&amp;tab='.$tab_key.'">'.$tab_caption.'</a>';
	}
	?>
	</h2>
	<?php
	if ($current_tab == 'main_config') {
	?>
    <form method="post" id="wpftsi_form">
		<?php wp_nonce_field( 'wpfts_options', 'wpfts_options-nonce' ); ?>
        <div id="poststuff">

			<div id="post-body" class="metabox-holder columns-2">
				
				<!-- Main Content -->
			    <div id="postbox-container-1" class="postbox-container">
		            <?php do_meta_boxes('wpfts-options', 'side', array()); ?>
	            </div>

			    <div id="postbox-container-2" class="postbox-container">
		            <?php do_meta_boxes('wpfts-options', 'normal1', array()); ?>
	            </div>

			    <div>
		            <button type="button" class="button-primary wpfts_submit" name="update_options"><?php echo __('Save Changes', 'wpfts_lang'); ?></button>
	            </div>
			</div>
        </div><!--#poststuff-->
    </form>
	<?php
	} elseif ($current_tab == 'indexing_engine_settings') {
	?>
    <form method="post" id="wpftsi_form2">
		<?php wp_nonce_field( 'wpfts_options', 'wpfts_options-nonce' ); ?>
        <div id="poststuff">

			<div id="post-body" class="metabox-holder columns-2">
				
				<!-- Main Content -->
			    <div id="postbox-container-1" class="postbox-container">
		            <?php do_meta_boxes('wpfts-options', 'side', array()); ?>
	            </div>

			    <div id="postbox-container-2" class="postbox-container">
		            <?php do_meta_boxes('wpfts-options', 'normal2', array()); ?>
	            </div>

			    <div>
		            <button type="button" class="button-primary wpfts_submit2" name="update_options" data-confirm="<?php echo htmlspecialchars(__('Changing of Indexing Engine Settings will automatically rebuild the search index. This operation could take some time. Are you sure?', 'wpfts_lang')); ?>"><?php echo __('Save Changes and Rebuild Index', 'wpfts_lang'); ?></button>
	            </div>
			</div>
        </div><!--#poststuff-->
    </form>
	<?php
	} elseif ($current_tab == 'sandbox_page') {
	?>
    <form method="post" id="wpftsi_form3">
		<?php wp_nonce_field( 'wpfts_options', 'wpfts_options-nonce' ); ?>
        <div id="poststuff">

			<div id="post-body" class="metabox-holder columns-2">
				
				<!-- Main Content -->
			    <div id="postbox-container-1" class="postbox-container">
		            <?php do_meta_boxes('wpfts-options', 'side', array()); ?>
	            </div>

			    <div id="postbox-container-2" class="postbox-container">
		            <?php do_meta_boxes('wpfts-options', 'normal3', array()); ?>
	            </div>

			</div>
        </div><!--#poststuff-->
    </form>
	<?php
	} else {
		//
	}
	?>
</div>


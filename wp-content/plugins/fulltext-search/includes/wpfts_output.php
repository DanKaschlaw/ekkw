<?php

require_once dirname(__FILE__).'/wpfts_htmltools.php';

class WPFTS_Output {

	public function status_box($post, $status = false, $is_return = false) {
		
		global $wpfts_core;
		
		if ($status) {
			$status = $wpfts_core->get_status();
		}
		
		ob_start();
		?>
		<p class="wpfts_status">
			<?php
			if (!$status['enabled']) {
				?><span class="wpfts_status_bullet wpfts_red">&#9679;</span>&nbsp;<b><?php echo __('Disabled', 'wpfts_lang'); ?></b><?php
			} else {
				if ($status['index_ready']) {
					?><span class="wpfts_status_bullet wpfts_green">&#9679;</span>&nbsp;<b><?php echo __('Active', 'wpfts_lang'); ?></b><?php
				} else {
					?><span class="wpfts_status_bullet wpfts_yellow">&#9679;</span>&nbsp;<b><?php echo __('Awaiting', 'wpfts_lang'); ?></b><?php
				}
			}
			?>
		</p>
		<p><?php echo __('In Index', 'wpfts_lang'); ?>: <b><span id="wpfts_st_inindex"><?php echo $status['n_inindex']; ?></span></b> <?php echo __('posts', 'wpfts_lang'); ?></p>
		<?php 
		if ($status['autoreindex']) {
			if ($status['n_pending'] > 0) {
				
				$percent = (0.0 + intval($status['n_actual'])) * 100 / (intval($status['n_inindex']) + intval($status['n_pending']));
		?>
		<div class="wpfts_indexer_info">
			<p><span class="wpfts_indexing_status_bullet wpfts_yellow">&#9679;</span>&nbsp;<?php echo __('Indexing is in progress', 'wpfts_lang'); ?></p>
			<span><?php echo __('Actual', 'wpfts_lang'); ?>: <b><span id="wpfts_st_actual"><?php echo $status['n_actual']; ?></span></b> <?php echo __('posts', 'wpfts_lang'); ?></span><br>
			<span><?php echo __('Pending', 'wpfts_lang'); ?>: <b><span id="wpfts_st_pending"><?php echo $status['n_pending']; ?></span></b> <?php echo __('posts', 'wpfts_lang'); ?></span>
			<div id="wpfts_st_1">
				<p class="wpfts_st_percent"><img src="<?php echo $wpfts_core->root_url; ?>/style/waiting16.gif" alt="" title="<?php echo __('Indexing is in progress', 'wpfts_lang'); ?>">&nbsp;<span><?php echo sprintf('%.2f', $percent).'%'; ?></span></p>
				<p><?php echo __('Est. time left: ', 'wpfts_lang'); ?><span class="wpfts_st_esttime"><?php echo $status['est_time']; ?></span></p>
			</div>
		</div>
		<p style="color: #00a000;"><i><?php echo __('In the process of indexing your site might run slower. Please do not worry and wait until index finishing.', 'wpfts_lang'); ?></i></p>
		<?php
			} else {
				?><p><span class="wpfts_indexing_status_bullet wpfts_green">&#9679;</span>&nbsp;<?php echo __('Index is OK', 'wpfts_lang'); ?></p><?php
			}
		} else {
			?><p><span class="wpfts_indexing_status_bullet wpfts_red">&#9679;</span>&nbsp;<?php echo __('Indexing is disabled', 'wpfts_lang'); ?></p><?php
		}
		
		$output = ob_get_clean();
		
		if ($is_return) {
			return $output;
		} else {
			echo $output;
		}
	}

	public function useful_box($post) {
		
		global $wpfts_core;
		
		echo '<p><a href="'.$wpfts_core->_wpfts_domain.'" target="_blank">'.__('WPFTS Home', 'wpfts_lang').'</a></p>';
		echo '<p><a href="'.$wpfts_core->_wpfts_domain.$wpfts_core->_documentation_link.'" target="_blank">'.__('WPFTS Documentation', 'wpfts_lang').'</a></p>';
	}

	public function control_box($post) {

		global $wpfts_core;
		
		$enabled = intval($wpfts_core->get_option('enabled'));
		$autoreindex = intval($wpfts_core->get_option('autoreindex'));
		
		?>
		<table class="form-table wpfts_formtable">
			<tr>
				<th><?php echo __('Enable FullText Search', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_enabled', 1, __('Enabled', 'wpfts_lang'), $enabled);
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(If not enabled, regular integrated "not indexed" WordPress search will be used)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Auto-index', 'wpfts_lang'); ?></th>
				<td>
					<div>
						<?php
						echo WPFTS_Htmltools::makeLabelledCheckbox('wpfts_autoreindex', 1, __('Enabled', 'wpfts_lang'), $autoreindex);
						?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Normally, WP FullText Search will autoindex any new post or post changes even if you disabled previous option. Disabling this option will completely stop all plugin functions. However, you probably have to do a full index rebuild, if you activate the plugin again.)', 'wpfts_lang'); ?></i><br>
						<?php echo __('<strong>NOTE</strong>: Disabling this option is NOT recommended!', 'wpfts_lang'); ?></p>
				</td>
			</tr>
			<tr>
				<th></th>
				<td>
					<div>
						<button type="button" class="button wpfts_btn_rebuild" name="wpfts_btn_rebuild" data-confirm="<?php echo htmlspecialchars(__('The action will rebuild the search index, which could take some time. Are you sure?', 'wpfts_lang')); ?>"><?php echo __('Rebuild Index', 'wpfts_lang'); ?></button>
						<div class="wpfts_show_resetting"><img src="<?php echo $wpfts_core->root_url; ?>/style/waiting16.gif" alt="">&nbsp;<?php echo __('Resetting', 'wpfts_lang'); ?></div>
					</div>
				</td>
				<td>
					<p><i><?php echo sprintf(__('(Use this button when you need to completely rebuild search index database, for example, when you changed custom <b>wpfts_index_post</b> filter function. Remember that this operation could take a long time. Please refer for <a href="%s" target="_blank">documentation</a> for more information.)', 'wpfts_lang'), $wpfts_core->_wpfts_domain.$wpfts_core->_documentation_link); ?></i></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function relevance_box($post) {

		global $wpfts_core;
		
		$deflogic = $wpfts_core->get_option('deflogic');

		$deflogic_data = array(
			0 => 'AND',
			1 => 'OR',
		);
		
		$cluster_weights = $wpfts_core->get_option('cluster_weights');
		
		?>
		<table class="form-table wpfts_formtable">
			<tr>
				<th><?php echo __('Default Search Logic', 'wpfts_lang'); ?></th>
				<td>
					<div class="wpfts_search_logic_group">
					<?php
						echo WPFTS_Htmltools::makeRadioGroup('wpfts_deflogic', $deflogic_data, $deflogic, array());
					?>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(This option tells the search engine whether all query words should contain in the found post (AND) or any of these words (OR).)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Cluster Weights', 'wpfts_lang'); ?></th>
				<td colspan="2">
					<div class="wpfts_scroller" style="margin-top: 0px;">
					<?php
						
						$cluster_types = $wpfts_core->get_cluster_types();

						foreach ($cluster_types as $d) {
							$name = 'eclustertype_' . $d;
							$w = isset($cluster_weights[$d]) ? floatval($cluster_weights[$d]) : 0.5;
							
							echo '<label for="'.$name.'_id"><span>'.htmlspecialchars($d).'</span>&nbsp;'.WPFTS_Htmltools::makeText($w, array('name' => $name, 'class' => 'wpfts_short_input')).'</label>';
						}
					?>
					</div>
					<p><i><?php echo __('(`Cluster` is a part of post (either title, content or even specific part which you can define using <b>wpfts_index_post</b> filter). You can assign some relevance weight to each of them)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>

		</table>
		<?php
	}

	public function indexing_box($post) {

		global $wpfts_core;
		
		$minlen = intval($wpfts_core->get_option('minlen'));
		$maxrepeat = intval($wpfts_core->get_option('maxrepeat'));
		$stopwords = $wpfts_core->get_option('stopwords');
		$epostype = $wpfts_core->get_option('epostype');
		
		?>
		<table class="form-table wpfts_formtable">
			<tr>
				<th><?php echo __('Minimum Word Length', 'wpfts_lang'); ?></th>
				<td>
					<div>
					<?php
						echo WPFTS_Htmltools::makeText($minlen, array('name' => 'wpfts_minlen', 'class' => 'wpfts_short_input'));
					?>
						&nbsp;<span><?php echo __('characters', 'wpfts_lang'); ?></span>
					</div>
				</td>
				<td>
					<p><i><?php echo sprintf(__('(Consider any word shorter than specified number of characters as a <a data-hint="%1s" href="%2s" target="_blank">stop word</a>.)', 'wpfts_lang'), htmlspecialchars(__('<b>Stop Word</b> is a word which is not indexing and can not be used to search for.', 'wpfts_lang')), $wpfts_core->_wpfts_domain.$wpfts_core->_documentation_link.'#stop_word'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Maximum Word Frequency', 'wpfts_lang'); ?></th>
				<td>
					<div>
					<?php
						echo WPFTS_Htmltools::makeText($maxrepeat, array('name' => 'wpfts_maxrepeat', 'class' => 'wpfts_short_input'));
					?>
						&nbsp;<span>%</span>
					</div>
				</td>
				<td>
					<p><i><?php echo __('(Consider a word that is found in the specified or more amount of documents as a stop word.)', 'wpfts_lang'); ?></i></p>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Stop Words', 'wpfts_lang'); ?></th>
				<td colspan="2">
					<p><?php echo __('A comma-separated list of custom stop words', 'wpfts_lang'); ?></p>
					<div>
					<?php
						echo WPFTS_Htmltools::makeTextarea(
								$stopwords, array('name' => 'wpfts_stopwords', 'class' => 'wpfts_long_textarea', 'placeholder' => __('the, a, an, ...etc', 'wpfts_lang'))
						);
					?>
					</div>
				</td>
			</tr>
			<?php
			/*
			<tr>
				<th><?php echo __('Exclude Post Types', 'wpfts_lang'); ?></th>
				<td colspan="2">
					<p>Check post types which will be excluded from index</p>
					<div class="wpfts_scroller">
					<?php
						
						$post_types = $wpfts_core->get_post_types();

						foreach ($post_types as $k => $d) {
							$name = 'epostype_' . $k;
							echo WPFTS_Htmltools::makeLabelledCheckbox($name, 1, $d . ' (' . $k . ')', (isset($epostype[$k]) && ($epostype[$k])) ? 1 : 0);
						}
					?>
					</div>
					<p><i>(To search for posts of selected post types built-in WordPress algorithm will be used.)</i></p>
				</td>
			</tr>
			*/
			?>
		</table>
		<?php
	}

	public function sandbox_box($post) {

		global $wpfts_core, $wpdb;
		
		$testpostid = $wpfts_core->get_option('testpostid');
		$testquery = $wpfts_core->get_option('testquery');
		$tq_disable = $wpfts_core->get_option('tq_disable');
		$tq_nocache = $wpfts_core->get_option('tq_nocache');
		$tq_post_status = $wpfts_core->get_option('tq_post_status');
		$tq_post_type = $wpfts_core->get_option('tq_post_type');
		
		$post_statuses = array(
			'any' => __('* (Any)', 'wpfts_lang'),
			'publish' => __('publish (Published)', 'wpfts_lang'),
			'future' => __('future (Future)', 'wpfts_lang'),
			'draft' => __('draft (Draft)', 'wpfts_lang'),
			'pending' => __('pending (Pending)', 'wpfts_lang'),
			'private' => __('private (Private)', 'wpfts_lang'),
			'trash' => __('trash (Trash)', 'wpfts_lang'),
			'auto-draft' => __('auto-draft (Auto-Draft)', 'wpfts_lang'),
			'inherit' => __('inherit (Inherit)', 'wpfts_lang'),
		);
		
		$q = 'select distinct post_type from `'.$wpdb->posts.'` order by post_type asc';
		$res = $wpdb->get_results($q, ARRAY_A);
		
		$post_types = array('any' => __('* (Any)', 'wpfts_lang'));
		foreach ($res as $d) {
			$post_types[$d['post_type']] = $d['post_type'];
		}
		
		?>
		<table class="form-table wpfts_formtable">
			<tr>
				<th>
					<?php echo __('Pre-indexing Filter Tester', 'wpfts_lang'); ?>
				</th>
				<td>
					<p><?php echo __('You can test your own <b>wpfts_index_post</b> filter here. Just enter an ID of a post you want and press Test Filter button.', 'wpfts_lang'); ?></p>
					<div>
						<p><?php echo __('Post ID:', 'wpfts_lang'); ?>
						<?php
							echo WPFTS_Htmltools::makeText($testpostid, array('name' => 'wpfts_testpostid', 'class' => 'wpfts_middle_input'));
						?>
						<?php
							echo WPFTS_Htmltools::makeButton(__('Test Filter', 'wpfts_lang'), array('id' => 'wpfts_testbutton', 'type' => 'button', 'class' => 'button'));
						?></p>
					</div>
				</td>
			</tr>
			<tr>
				<th>
					<?php echo __('Search Tester', 'wpfts_lang'); ?>
				</th>
				<td>
					<p><?php echo __('You can test search with any query here. Standard wordpress <b>WP_Query</b> object with WPFTS features will be used.', 'wpfts_lang'); ?></p>
					<div>
						<p><?php echo __('Query:', 'wpfts_lang'); ?> 
						<?php
							echo WPFTS_Htmltools::makeText($testquery, array('name' => 'wpfts_testquery', 'class' => 'wpfts_middle_input'));
						?>
						<?php
							echo WPFTS_Htmltools::makeButton(__('Test Search', 'wpfts_lang'), array('id' => 'wpfts_testquerybutton', 'type' => 'button', 'class' => 'button'));
						?></p>
					</div>
					
					<div>
						<p><b><?php echo __('Additional Options:', 'wpfts_lang'); ?></b></p>
						<p>
						<span style="margin-right: 20px;"><?php
							echo WPFTS_Htmltools::makeCheckbox($tq_disable, array('id' => 'wpfts_tq_disable', 'name' => 'wpfts_tq_disable', 'class' => 'wpfts_middle_input', 'value' => 1), __('Disable WPFTS', 'wpfts_lang'));
						?></span>
						<span style="margin-right: 20px;"><?php
							echo WPFTS_Htmltools::makeCheckbox($tq_nocache, array('id' => 'wpfts_tq_nocache', 'name' => 'wpfts_tq_nocache', 'class' => 'wpfts_middle_input', 'value' => 1), __('Disable SQL cache', 'wpfts_lang'));
						?></span>
						</p>
						<p>
							<span style="margin-right: 20px;"><?php
								echo __('Post Type:', 'wpfts_lang').'&nbsp;'; 
								echo WPFTS_Htmltools::makeSelect($post_types, $tq_post_type, array('id' => 'wpfts_tq_post_type', 'name' => 'wpfts_tq_post_type', 'class' => 'wpfts_middle_input'));
							?></span>
							<span style="margin-right: 20px;"><?php
								echo __('Post Status:', 'wpfts_lang').'&nbsp;';
								echo WPFTS_Htmltools::makeSelect($post_statuses, $tq_post_status, array('id' => 'wpfts_tq_post_status', 'name' => 'wpfts_tq_post_status', 'class' => 'wpfts_middle_input'));
							?></span>
						</p>
					</div>
					
				</td>
			</tr>
			<tr>
				<td colspan="2" id="wpfts_sandbox_output">
					
				</td>
			</tr>

		</table>
		<?php
	}
}

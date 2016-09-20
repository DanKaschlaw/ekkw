<?php

require_once dirname(__FILE__).'/wpfts_index.php';
require_once dirname(__FILE__).'/wpfts_jx.php';
require_once dirname(__FILE__).'/wpfts_htmltools.php';
require_once dirname(__FILE__).'/wpfts_output.php';

class WPFTS_Core {
	
	protected $_index = null;
	
	protected $_pid = false;
	
	public $_wpfts_domain = 'https://fulltextsearch.org';
	public $_documentation_link = '/documentation';
	
	public $root_url;
	
	public $index_error = '';
	
	public function __construct() {
		
		$this->_index = new WPFTS_Index();
		
		$this->root_url = dirname(plugins_url('', __FILE__));
		$am = $this->get_option('admin_message');
		if ($am) {
			add_action('admin_notices', function() use ($am){
				echo $am;
			});
			$this->set_option('admin_message', '');
		}
	}
	
	function queue_admin_notice($text, $type = 'error') {
		
		ob_start();
		?>
	    <div class="<?php echo $type; ?>"><p><?php echo $text; ?></p></div>
		<?php
		$this->set_option('admin_message', ob_get_clean());
	}

	public function activate_plugin() {
		
		if ((isset($_GET['action'])) && ($_GET['action'] == 'error_scrape')) {
			// Showing error
			echo __('Error: ', 'wpfts_lang').$this->get_option('activation_error');
			//$this->set_option('activation_error', '');
			
		} else {

			// Check db
			$this->_index->clearLog();
			$ch = $this->_index->check_db_tables();
			if (count($ch) > 0) {
				// Something wrong, reinitialize DB
				$result = $this->_index->create_db_tables();

				if ($result) {
					// Reset dbactive flag
					$this->set_option('index_ready', 0);
					
					// Start reindex
					$this->rebuild_index(time());
					
					// Show successful message
					$this->queue_admin_notice(__('<b style="color: red;">Congratulations!</b> Wordpress FullText Search plugin was just installed and activated successfully!<br>In order to complete activation, we need to create an index of your existing WP post data. To run this process, just go to <a href="/wp-admin/options-general.php?page=wpfts-options">WPFTS Settings Page</a>', 'wpfts_lang'), 'update-nag');
					
				} else {
					// Can not create DB tables
					$this->set_option('activation_error', __('WPFTS plugin can not create some DB tables.', 'wpfts_lang') . ': ' . $this->_index->getLog());
					trigger_error('WPFTS Activation Error', E_USER_ERROR);
				}
			}
		}
	}
	
	public function deactivate_plugin() {
		//
	}
	
	public function getPid() {
		if (!$this->_pid) {
			$this->_pid = sha1(time().uniqid());
		}
		
		return $this->_pid;
	}
	
	public function get_post_types() {
		
		$post_types = get_post_types('', 'objects');

		$z = array();
		foreach ($post_types as $k => $d) {
			$z[$k] = isset($d->labels->singular_name) ? $d->labels->singular_name : $k;
		}

		return $z;
	}
	
	public function get_cluster_types() {
		
		return $this->_index->getClusters();
	}

	protected function default_options() {
		return array(
			'enabled' => 1,
			'autoreindex' => 1,
			'index_ready' => 0,
			'deflogic' => 0, // AND
			'minlen' => 3,
			'maxrepeat' => 80, // 80%
			'stopwords' => '',
			'epostype' => '',
			'cluster_weights' => serialize(array(
				'post_title' => 0.8,
				'post_content' => 0.5,
			)),
			'testpostid' => '',
			'testquery' => '',
			'tq_disable' => 0,
			'tq_nocache' => 1,
			'tq_post_status' => 'any',
			'tq_post_type' => 'any',
			'rebuild_time' => 0,
			'process_time' => '0|',
			'ping_period' => 30,
			'est_time' => '00:00:00',
			'activation_error' => '',
			'admin_message' => '',
		);
	}
	
	public function get_option($optname) {

		$defaults = $this->default_options();

		$v = get_option('wpfts_' . $optname, isset($defaults[$optname]) ? $defaults[$optname] : false);

		switch ($optname) {
			case 'epostype':
				$v = (strlen($v) > 0) ? @unserialize($v) : array();
				break;
			case 'cluster_weights':
				$v = (strlen($v) > 0) ? @unserialize($v) : array();
				break;
		}

		return $v;
	}

	public function set_option($optname, $value) {
		
		$defaults = $this->default_options();
		
		if (isset($defaults[$optname])) {
			// Allowed option
			$v = $value;
			switch ($optname) {
				case 'epostype':
				case 'cluster_weights':
					$v = serialize($value);
					break;
			}
			
			$option_name = 'wpfts_'.$optname;
			if (get_option($option_name, false) !== false) {
				update_option($option_name, $v);
			} else {
				add_option($option_name, $v, '', 'no');
			}			
			return true;
		} else {
			// Not allowed option
			return false;
		}
	}
	
	public function checkAndSyncWPPosts() {
		return $this->_index->checkAndSyncWPPosts($this->get_option('rebuild_time'));
	}
	
	public function get_status() {
		
		$st = $this->_index->get_status();
		$st['est_time'] = $this->get_option('est_time');
		$st['enabled'] = $this->get_option('enabled');
		$st['index_ready'] = $this->get_option('index_ready');
		$st['autoreindex'] = $this->get_option('autoreindex');
		
		return $st;
	}
	
	public function rebuild_index($time = false) {
		if (!$time) {
			$time = time();
		}
		
		$this->set_option('rebuild_time', $time);
		
		return $this->checkAndSyncWPPosts();
	}
	
	public function indexerProcessState($process_id) {
		$time = time();
		$process_time = explode('|', $this->get_option('process_time'));
		$ping_period = intval($this->get_option('ping_period'));
		
		if ($process_id != $process_time[1]) {
			if ($process_time[0] + $ping_period * 4 > $time) {
				return 2;	// Other pid indexing goes
			} else {
				return 0;	// Free
			}
		} else {
			if ($process_time[0] + $ping_period * 2 > $time) {
				return 1;	// Our pid indexing goes
			} else {
				return 0;	// Free
			}
		}
		
		// Unreachable
		return 0;
	}
	
	public function sql_select($search, &$wpq) {
		return $this->_index->sql_select($search, $wpq);
	}
	
	/**
	 * Insert, Update or Delete index record for specified post
	 * 
	 * @param int $post_id Post ID
	 * @return boolean Success or not
	 */
	public function reindex_post($post_id) {
		$post = get_post($post_id);
		if ($post) {
			// Insert or update index record
			$chunks = array(
				'post_title' => $post->post_title,
				'post_content' => $post->post_content,
			);
			$chunks2 = apply_filters('wpfts_index_post', $chunks, $post);
			
			$modt = $post->post_modified;
			$time = time();
			$build_time = $this->get_option('rebuild_time');
			$insert_id = $this->_index->updateIndexRecordForPost($post_id, $modt, $build_time, $time, 0);
			
			$this->_index->clearLog();
			$res = $this->_index->reindex($insert_id, $chunks2);
			$this->index_error = (!$res) ? 'Indexing error: '.$this->_index->getLog() : '';
			
			return $res;
		} else {
			// Check if index record exists and delete it
			$this->_index->removeIndexRecordForPost($post_id);
			return true;
		}
	}
	
	public function ajax_rebuild_step() {
		
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			
			$process_id = $data['pid'];
			$status = $this->get_status();
			
			$st = $this->indexerProcessState($process_id);			
			if ($st != 2) {
				// Allow to start indexer session
				// Set up lock
				$this->set_option('process_time', time() . '|' . $process_id);

				$build_time = $this->get_option('rebuild_time');

				$maxtime = 10;
				$start_ts = microtime(true);
				
				ignore_user_abort(true);
			
				$n = 0;
				while (microtime(true) - $start_ts < $maxtime) {
//$zt0 = microtime(true);
					$ids = $this->_index->getRecordsToRebuild(1000);
//$zt1 = microtime(true) - $zt0;
//$jx->console(count($ids).' '.$zt1);
					foreach ($ids as $item) {
						
						if (!(microtime(true) - $start_ts < $maxtime)) {
							break;
						}
						
						// Rebuild this record
						if ($item['tsrc'] == 'wp_posts') {

							// Check if locked and lock if not locked
							if ($this->_index->lockUnlockedRecord($item['id'])) {

								// Record is locked, lets index it now
								$post = get_post($item['tid']);
								$modt = $post->post_modified;
								$chunks = array(
									'post_title' => $post->post_title,
									'post_content' => $post->post_content,
								);
								$chunks2 = apply_filters('wpfts_index_post', $chunks, $post);
								
								$this->_index->clearLog();
								$res = $this->_index->reindex($item['id'], $chunks2);
								if (!$res) {
									$jx->console('Indexing error: '.$this->_index->getLog());
								}

								//$jx->console($this->_index->timelog);
								
								// Store some statistic
								$time = time();
								$this->_index->updateRecordData($item['id'], array(
									'tdt' => $modt,
									'build_time' => $build_time,
									'update_dt' => date('Y-m-d H:i:s', $time),
									'force_rebuild' => 0,
								));

								$this->_index->unlockRecord($item['id']);
							}
						}
						
						$n ++;
					}
					
					if ($n < 1) {
						break;
					}

				}

				$finish_ts = microtime(true);
				
				$jx->variable('code', 0);
				
				$status = $this->get_status();
				
				$est_seconds = $n > 0 ? intval((($finish_ts - $start_ts) * $status['n_pending']) / $n) : 0;
				
				$est_h = intval($est_seconds / 3600);
				$est_m = intval(($est_seconds - $est_h * 3600) / 60);
				$est_s = ($est_seconds - $est_h * 3600) % 60;
				$est_str = sprintf('%02d:%02d:%02d', $est_h, $est_m, $est_s);
				
				$this->set_option('est_time', $est_str);
				
				$status['est_time'] = $est_str;
				
				if ($status['n_pending'] > 0) {
					if (($finish_ts - $start_ts) < ($maxtime / 2)) {
						// Just a delay
						$this->set_option('process_time', '0|' . $process_id);
						$jx->variable('result', 10);
					} else {
						// There is something to index
						// Remove lock
						$this->set_option('process_time', '0|' . $process_id);
						$jx->variable('result', 5);	// Continue indexing
					}
				} else {
					// Nothing to index
					// Remove lock
					$this->set_option('process_time', '0|0');
					$jx->variable('result', 0);	// Indexing stopped, ping
					$this->set_option('index_ready', 1);
					$jx->variable('delay', 0);
				}
				
				$out = new WPFTS_Output();
				$jx->variable('status', $out->status_box(1, $status, true));
				
				$jx->console(sprintf(__('%s posts has been rebuilt', 'wpfts_lang'), $n));
			} else {
				// Unable to index
				$jx->variable('code', 1);
			}
			
		}
		
		echo $jx->getJSON();
		wp_die();
	}
	
	public function ajax_ping() {
		
		$t0 = microtime(true);
		
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			
			$time = time();
			
			$process_id = $data['pid'];
			$status = $this->get_status();
			
			$st = $this->indexerProcessState($process_id);
			
			$jx->variable('code', 0);
			
			$out = new WPFTS_Output();
			$jx->variable('status', $out->status_box(1, $status, true));
			switch ($st) {
				case 2:
				case 1:
					// Other pid is indexing
					$jx->variable('result', 10);	// Just wait, ping
					break;
				case 0:
				default:
					// Indexer is free now, lets check what to do
					if ($status['n_pending'] > 0) {
						// There is something to index
						$jx->variable('result', 5);	// Start to index
					} else {
						// Nothing to index
						$jx->variable('result', 0);	// Indexing stopped, ping
					}
			}
			
			$jx->console('pong! '.(microtime(true) - $t0).' s');
		}
		
		echo $jx->getJSON();
		wp_die();
	}
	
	/**
	 * Save Main Configuration & Relevance Settings
	 */
	public function ajax_submit_settings() {
		
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			
			// Check nonce
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				//$jx->console($data);

				$e = array();

				$cluster_weights = $this->get_option('cluster_weights');
				
				foreach ($data as $k => $d) {
					if (preg_match('~^eclustertype_(.+)$~', $k, $m)) {
						$clname = $m[1];
						$clvalue = floatval($d);
						if ((is_numeric($d)) && ($clvalue >= 0) && ($clvalue <= 1.0)) {
							$cluster_weights[$clname] = $clvalue;
						} else {
							$e[] = array($k, sprintf(__('The weight value of cluster "%s" should be numeric value from 0.0 to 1.0', 'wpfts_lang'), $clname));
						}
					} elseif (preg_match('~^wpfts_(.+)$~', $k, $m)) {
						$key = $m[1];
						switch ($key) {
							case 'enabled':
							case 'autoreindex':
								$v = ($d) ? 1 : 0;
								$this->set_option($key, $v);
								break;
							case 'deflogic':
								$v = ($d) ? 1 : 0;
								$this->set_option($key, $v);
								break;
							default:
						}
					} else {
						// Not valid input name
					}
				}

				if (count($e) > 0) {

					$z = array();
					foreach ($e as $dd) {
						$z[] = '* ' . $dd[1];
					}
					$txt = __('There are errors', 'wpfts_lang') . ":\n\n" . implode("\n", $z);

					$jx->alert($txt);
				} else {

					// Validation passed!
					$this->set_option('cluster_weights', $cluster_weights);

					$jx->reload();
				}

			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		
		echo $jx->getJSON();
		wp_die();
	}
	
	/**
	 * Save Indexing Engine Settings
	 */
	public function ajax_submit_settings2() {
		
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			
			// Check nonce
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				//$jx->console($data);

				$e = array();

				$epostype = $this->get_option('epostype');
				$post_types = $this->get_post_types();
				
				foreach ($data as $k => $d) {
					if (preg_match('~^epostype_(.+)$~', $k, $m)) {
						$psname = $m[1];
						$psvalue = intval($d);
						if (isset($post_types[$psname])) {
							$epostype[$psname] = $psvalue ? 1 : 0;
						}
					} elseif (preg_match('~^wpfts_(.+)$~', $k, $m)) {
						$key = $m[1];
						switch ($key) {
							case 'maxrepeat':
								$v = intval($d);
								if (is_numeric($d) && ($v >= 0) && ($v <= 100)) {
									$this->set_option($key, $v);
								} else {
									$e[] = array($key, __('Maximum Word Frequency should be an integer number from 0 to 100', 'wpfts_lang'));
								}
								break;
							case 'minlen':
								$v = intval($d);
								if (is_numeric($d) && ($v >= 0) && ($v <= 50)) {
									$this->set_option($key, $v);
								} else {
									$e[] = array($key, __('Minimum Word Length should be an integer number from 0 to 50', 'wpfts_lang'));
								}
								break;
							case 'stopwords':
								$v = trim($d);
								$this->set_option($key, $v);
								break;
							default:
						}
					} else {
						// Not valid input name
					}
				}

				if (count($e) > 0) {

					$z = array();
					foreach ($e as $dd) {
						$z[] = '* ' . $dd[1];
					}
					$txt = __('There are errors', 'wpfts_lang') . ":\n\n" . implode("\n", $z);

					$jx->alert($txt);
				} else {

					// Validation passed!
					$this->set_option('epostype', $epostype);
					
					$this->rebuild_index(time());

					$jx->reload();
				}

			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		
		echo $jx->getJSON();
		wp_die();
	}
	
	public function ajax_submit_testpost() {
		
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				//$jx->console($data);
				
				$postid = trim($data['wpfts_testpostid']);

				$e = array();
				
				if (strlen($postid) < 1) {
					$e[] = array('testpostid', __('Please specify post ID', 'wpfts_lang'));
				} else {
					if (!is_numeric($postid)) {
						$e[] = array('testpostid', __('Please specify a number', 'wpfts_lang'));
					}
				}
				
				if (count($e) > 0) {
					$z = array();
					foreach ($e as $dd) {
						$z[] = '* ' . $dd[1];
					}
					$txt = __('There are errors', 'wpfts_lang') . ":\n\n" . implode("\n", $z);

					$jx->alert($txt);
				} else {
					
					$o_title = sprintf(__('Results of Pre-indexing Filter Tester for Post ID = %s', 'wpfts_lang'), $postid);
					
					// Looking for post ID
					$p = get_post(intval($postid));
					
					if ($p) {
						// Processing filter
						$index = array(
							'post_title' => $p->post_title,
							'post_content' => $p->post_content,
						);

						$index = apply_filters('wpfts_index_post', $index, $p);
						
						if (is_array($index)) {
							
							ob_start();
							?>
							<table class="wpfts_testoutput">
							<tr>
								<th><?php echo __('Token', 'wpfts_lang'); ?></th>
								<th><?php echo __('Content', 'wpfts_lang'); ?></th>
							</tr>
							<?php
							$o_result = ob_get_clean();
							
							foreach ($index as $k => $d) {
								$o_result .= '<tr>';
								$o_result .= '<td><b>'.htmlspecialchars($k).'</b></td>';
								$o_result .= '<td>'.htmlspecialchars($d).'</td>';
								$o_result .= '</tr>';
							}
							
							ob_start();
							?>
							</table>
							<?php
							$o_result .= ob_get_clean();
							
						} else {
							// Wrong filter result
							$o_result = '<p>'.sprintf(__('Filter result is not array. Please read <a href="%s" target="_blank">documentation</a> to fix this error.', 'wpfts_lang'), $this->_wpfts_domain.$this->_documentation_link).'</p>';
						}
						
					} else {
						// Post not found
						$o_result = '<p>'.__('The post with specified ID is not found.', 'wpfts_lang').'</p>';
					}
					
					$this->set_option('testpostid', $postid);
					
					$output = '<hr>';
					$output .= '<h4>'.htmlspecialchars($o_title).'</h4>';
					$output .= $o_result;
					
					$jx->variable('code', 0);
					$jx->variable('text', $output);
				}
				
			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		echo $jx->getJSON();
		wp_die();
	}
	
	public function ajax_submit_testsearch() {
		
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				//$jx->console($data);
				
				$query = trim($data['wpfts_testquery']);
				$tq_disable = $data['wpfts_tq_disable'];
				$tq_nocache = $data['wpfts_tq_nocache'];
				$tq_post_type = $data['wpfts_tq_post_type'];
				$tq_post_status = $data['wpfts_tq_post_status'];

				$current_page = max(1, isset($data['wpfts_tq_current_page']) ? intval($data['wpfts_tq_current_page']) : 0);
				$n_perpage = isset($data['wpfts_tq_n_perpage']) ? intval($data['wpfts_tq_n_perpage']) : 25;
				
				
				$e = array();
				
				if (strlen($query) < 1) {
					$e[] = array('testquery', __('Please specify search query', 'wpfts_lang'));
				}
				
				if (count($e) > 0) {
					$z = array();
					foreach ($e as $dd) {
						$z[] = '* ' . $dd[1];
					}
					$txt = __('There are errors', 'wpfts_lang') . ":\n\n" . implode("\n", $z);

					$jx->alert($txt);
				} else {
					
					$o_title = sprintf(__('Results of search for query = "%s"', 'wpfts_lang'), $query);
					
					$t0 = microtime(true);
					
					$wpq = new WP_Query(array(
						//'fields' => 'ids',
						'fields' => '*',
						's' => $query,
						'post_status' => 'any',
						//'nopaging' => true,
						'wpfts_disable' => $tq_disable ? 1 : 0,
						'wpfts_nocache' => $tq_nocache ? 1 : 0,
						'posts_per_page' => $n_perpage,
						'paged' => $current_page,
						'post_status' => $tq_post_status,
						'post_type' => $tq_post_type,
					));
					
					$t1 = microtime(true) - $t0;
					
					if (isset($GLOBALS['posts_clauses'])) {
						$jx->console($GLOBALS['posts_clauses']);
					}
					
					//$num = $wpq->have_posts() ? count($wpq->posts) : 0;
					$num = $wpq->found_posts;
					
					$o_result = '<p><i>'.sprintf(__('Time spent: <b>%.3f</b> sec', 'wpfts_lang'), $t1).'</i><br>';
					$o_result .= '</p>';
					
					global $post;
					
					$a = array();
					$n = ($current_page - 1) * $n_perpage + 1;
					while ( $wpq->have_posts() ) {
						$wpq->the_post();
						
						$relev = isset($post->relev) ? $post->relev : 0;
						$post = get_post($post->ID);
						setup_postdata($post);
						
						$tn = '';
						$post_tn = get_post_thumbnail_id($post->ID);
						if ($post_tn) {
							$large_image_url = wp_get_attachment_image_src($post_tn, 'thumbnail');
							if ( ! empty( $large_image_url[0] ) ) {
								$tn = '<img src="'.esc_url($large_image_url[0]).'" alt="" class="wpfts_table_img">';
							}
						}
						
						ob_start();
						the_excerpt();
						$exc = ob_get_clean();
						
						$a[] = array(
							'n' => $n ++,
							'ID' => $post->ID,
							'post_type' => $post->post_type,
							'post_title' => $post->post_title,
							'post_status' => $post->post_status,
							'tn' => $tn,
							'exc' => $exc,
							'relevance' => sprintf('%.2f', $relev * 100).'%',
						);
					}
					wp_reset_postdata();
					
					if (count($a) > 0) {
						
						$o_result .= '<p>'.$this->sandboxPaginator($current_page, $num, $n_perpage).'</p>';
						
						ob_start();
						?>
						<table class="wpfts_testoutput" style="width: 100%;">
						<tr>
							<th style="width: 10%;"><?php echo __('#', 'wpfts_lang'); ?></th>
							<th style="width: 10%;"><?php echo __('ID', 'wpfts_lang'); ?></th>
							<th style="width: 10%;"><?php echo __('Type', 'wpfts_lang'); ?></th>
							<th style="width: 10%;"><?php echo __('Status', 'wpfts_lang'); ?></th>
							<th style="width: 50%;"><?php echo __('Title, Thumbnail, Excerpt', 'wpfts_lang'); ?></th>
							<th style="width: 10%;"><?php echo __('Relevance', 'wpfts_lang'); ?></th>
						</tr>
						<?php
						$o_result .= ob_get_clean();
							
						foreach ($a as $d) {
							
							$content = '<div class="wpfts_tq_content"><div class="cont1">'.$d['tn'].'</div><div class="cont2"><b>'.htmlspecialchars($d['post_title']).'</b><br>'.$d['exc'].'</div></div>';
							
							$o_result .= '<tr>';
							$o_result .= '<td>'.$d['n'].'</td>';
							$o_result .= '<td><a href="/?p='.$d['ID'].'">'.$d['ID'].'</a></td>';
							$o_result .= '<td>'.$d['post_type'].'</td>';
							$o_result .= '<td>'.$d['post_status'].'</td>';
							$o_result .= '<td>'.$content.'</td>';
							$o_result .= '<td>'.$d['relevance'].'</td>';
							$o_result .= '</tr>';
						}
							
						ob_start();
						?>
						</table>
						<?php
						$o_result .= ob_get_clean();
						
						$o_result .= '<p>'.$this->sandboxPaginator($current_page, $num, $n_perpage).'</p>';
					} else {
						$o_result .= '<p><i>'.sprintf(__('Found: <b>%d</b> posts', 'wpfts_lang'), $num).'</i></p>';
					}
					
					$this->set_option('testquery', $query);
					$this->set_option('tq_disable', $tq_disable);
					$this->set_option('tq_post_type', $tq_post_type);
					$this->set_option('tq_post_status', $tq_post_status);
					
					$output = '<hr>';
					$output .= '<h4>'.htmlspecialchars($o_title).'</h4>';
					$output .= $o_result;
					
					$jx->variable('code', 0);
					$jx->variable('text', $output);
				}
				
			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		echo $jx->getJSON();
		wp_die();
	}
	
	public function ajax_submit_rebuild() {
		
		$jx = new WPFTS_jxResponse();
		
		if (($data = $jx->getData()) !== false) {
			if (wp_verify_nonce($data['wpfts_options-nonce'], 'wpfts_options')) {
				
				$this->set_option('index_ready', 0);
				$this->_index->create_db_tables();
				$this->rebuild_index(time());

				$jx->reload();
				
			} else {
				$jx->alert(__('The form is outdated. Please refresh the page and try again.', 'wpfts_lang'));
			}
		}
		echo $jx->getJSON();
		wp_die();
	}
	
	public function sandboxPaginator($current_page, $total_items, $n_perpage) {
		
		$a_nn = array(
			10 => 10,
			25 => 25,
			50 => 50,
			100 => 100,
			250 => 250,
			500 => 500,
		);
		$sel_perpage = '<span class="wpfts_tq_perpage">'.WPFTS_HtmlTools::makeSelect($a_nn, $n_perpage, array('id' => 'wpfts_tq_n_perpage')).'&nbsp;'.__('posts per page', 'wpfts_lang').'</span>';
		
		$maxpage = ceil($total_items / $n_perpage);
		
		$a_pages = array();
		for ($i = 1; $i <= $maxpage; $i ++) {
			$a_pages[$i] = (($i - 1) * $n_perpage + 1).' - '.min($total_items, $i * $n_perpage);
		}
		
		$pager = '<span class="wpfts_tq_pager">';
		$pager .= '<button class="wpfts_tq_prevpage"'.(($current_page > 1) ? '' : ' disabled="disabled"').' type="button">&lt;&lt;</button>';
		$pager .= sprintf(__('Shown <span>%1s</span> from <b>%2s</b>', 'wpfts_lang'), WPFTS_HtmlTools::makeSelect($a_pages, $current_page, array('id' => 'wpfts_tq_current_page')), $total_items);
		$pager .= '<button class="wpfts_tq_nextpage"'.(($current_page < $maxpage) ? '' : ' disabled="disabled"').' type="button">&gt;&gt;</button>';
		$pager .= '</span>';
		
		return '<div class="wpfts_tq_pagerline">'.$pager.$sel_perpage.'<div style="clear: both;"></div></div>';
	}
	
	public function set_hooks() {
		
		add_action('pre_get_posts', array($this, 'index_pre_get_posts'), 10);
		add_filter('posts_search', array($this, 'index_sql_select'), 10, 2);
		add_filter('posts_join', array($this, 'index_sql_joins'), 10, 2);
		add_filter('posts_search_orderby', array($this, 'index_sql_orderby'), 10, 2);
		add_filter('the_posts', array($this, 'index_the_posts'), 10, 2);
		add_filter('posts_clauses', array($this, 'index_posts_clauses'), 10, 2);
		add_filter('posts_fields', array($this, 'index_posts_fields'), 10, 2);
		add_filter('posts_distinct', array($this, 'index_posts_distinct'), 10, 2);
/*
		add_action('posts_selection', function($s) {

			$wpud = wp_upload_dir();

			$log_fn = $wpud['path'] . '/wpfts_log.txt';

			file_put_contents($log_fn, date('Y-m-d H:i:s') . ' *** POSTS_SELECTION *******************' . "\n", FILE_APPEND);
			file_put_contents($log_fn, print_r($s, true) . "\n", FILE_APPEND);
			file_put_contents($log_fn, 'END POSTS_SELECTION %%%%%%%%%%%%%%%%%%%%' . "\n\n", FILE_APPEND);
		}, 1);
 */
	}

	function index_posts_clauses($clauses, &$wpq) {
		
		if ((!isset($GLOBALS['posts_clauses'])) || (!is_array($GLOBALS['posts_clauses']))) {
			$GLOBALS['posts_clauses'] = array();
		}
		$GLOBALS['posts_clauses'][] = $clauses;
		
		return $clauses;
	}
	
	function index_posts_fields($fields, &$wpq) {

		if (intval($this->get_option('enabled'))) {
			return $this->_index->sql_posts_fields($fields, $wpq);
		}
		return $fields;
	}
	
	function index_sql_joins($join, &$wpq) {
		if (intval($this->get_option('enabled'))) {
			$cluster_weights = $this->get_option('cluster_weights');
			return $this->_index->sql_joins($join, $wpq, $cluster_weights);
		}
		return $join;
	}
	
	function index_sql_select($search, &$wpq) {
		
		$wpud = wp_upload_dir();

		$log_fn = $wpud['path'] . '/wpfts_log.txt';

		$q = $wpq->query_vars;
/*
		file_put_contents($log_fn, date('Y-m-d H:i:s') . ' *** POSTS_SEARCH *******************' . "\n", FILE_APPEND);
		file_put_contents($log_fn, print_r($search, true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'S: ' . print_r($q['s'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'SENTENCE: ' . print_r($q['sentence'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'SEARCH_TERMS_COUNT: ' . print_r($q['search_terms_count'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'SEARCH_TERMS: ' . print_r($q['search_terms'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'EXACT: ' . print_r($q['exact'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'SEARCH_ORDERBY_TITLE: ' . print_r($q['search_orderby_title'], true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'END POSTS_SEARCH ///////////////' . "\n\n", FILE_APPEND);
*/		
/*
			if (isset($wpfts_core) && (intval($wpfts_core->get_option('enabled')))) {
				return $wpfts_core->sql_select($search, $wpq);
			}
*/
		if (intval($this->get_option('enabled'))) {
			return $this->_index->sql_select($search, $wpq);
		}
		return $search;
	}
	
	function index_sql_orderby($orderby, $wpq) {

		$wpud = wp_upload_dir();

		$log_fn = $wpud['path'] . '/wpfts_log.txt';
/*
		file_put_contents($log_fn, date('Y-m-d H:i:s') . ' *** POSTS_SEARCH_ORDERBY *******************' . "\n", FILE_APPEND);
		file_put_contents($log_fn, print_r($orderby, true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'END POSTS_SEARCH_ORDERBY ##############' . "\n\n", FILE_APPEND);
*/
		if (intval($this->get_option('enabled'))) {
			return $this->_index->sql_orderby($orderby, $wpq);
		}
		return $orderby;
	}
	
	function index_pre_get_posts(&$wpq) {

		$wpud = wp_upload_dir();

		$log_fn = $wpud['path'] . '/wpfts_log.txt';
/*
		file_put_contents($log_fn, date('Y-m-d H:i:s') . ' *** PRE_GET_POSTS *************************' . "\n", FILE_APPEND);
		file_put_contents($log_fn, print_r($wpq->query_vars, true) . "\n", FILE_APPEND);
		file_put_contents($log_fn, 'END PRE_GET_POSTS ****************************' . "\n\n", FILE_APPEND);
*/		
		if (intval($this->get_option('enabled'))) {
			$cluster_weights = $this->get_option('cluster_weights');
			return $this->_index->sql_pre_posts($wpq, $cluster_weights);
		}
	}
	
	function index_the_posts($posts, &$wpq) {
		if (intval($this->get_option('enabled'))) {
			return $this->_index->sql_the_posts($posts, $wpq);
		}
		return $posts;
	}
	
	function index_posts_distinct($distinct, &$wpq) {
		if (intval($this->get_option('enabled'))) {
			return $this->_index->sql_posts_distinct($distinct, $wpq);
		}
		return $distinct;
	}
}
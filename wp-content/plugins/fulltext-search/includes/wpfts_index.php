<?php

class WPFTS_Index {

	public $prefix = 'wpftsi_';
	public $max_word_length = 32;
	public $error = '';
	public $lock_time = 300;	// 5 min

	protected $stops = array();
	
	protected $log = array();
	
	public $timelog = array();
	
	protected $_islock = true;
	
	function __construct() {
		//
	}

	protected function load_stops() {
		
		global $wpdb;
		
		$q = 'select `word` from `'.$this->prefix.'stops`';
		$res = $wpdb->get_result($q, ARRAY_A);
		
		$z = array();
		foreach ($res as $d) {
			$z[mb_strtolower($d['word'])] = 1;
		}
		$this->stops = $z;
	}
	
	protected function log($message) {
		$this->log[] = $message;
	}
	
	public function clearLog() {
		$this->log = array();
	}
	
	public function getLog() {
		return implode("\n", $this->log);
	}
	
	public function check_db_tables() {
		
		global $wpdb;
		
		$sch = $this->getDbScheme();
		
		// Check all tables
		$wrongs = array();
		foreach ($sch as $k => $d) {
			$q = 'SHOW TABLES LIKE "'.$this->prefix.$k.'"';
			$res = $wpdb->get_results($q, ARRAY_A);
			if (count($res) > 0) {
				// Table exists
				
				// Check columns
				$q = 'show columns from `'.$this->prefix.$k.'`';
				$res = $wpdb->get_results($q, ARRAY_A);
				
				if (count($d['cols']) === count($res)) {
					
					foreach ($res as $dd) {
						if (isset($d['cols'][$dd['Field']])) {
							
							$cl = $d['cols'][$dd['Field']];
							if ($cl[0] != $dd['Type']) {
								$wrongs[$k] = 'ntype:'.$dd['Field'];
								break;
							}
							if ($cl[1] != $dd['Null']) {
								$wrongs[$k] = 'nnull:'.$dd['Field'];
								break;
							}
							/*
							Key
							Default
							Extra
							*/	
							
						} else {
							$wrongs[$k] = 'ncol:'.$dd['Field'];
							break;
						}
					}
					
				} else {
					$wrongs[$k] = 'nrows';
				}
				
			} else {
				$wrongs[$k] = 'nexist';
			}
		}
		
		return $wrongs;
	}
	
	public function create_db_tables() {
		
		global $wpdb;
		
		$success = true;
		
		$sch = $this->getDbScheme();

		foreach ($sch as $k => $d) {
			
			$q = 'drop table if exists `'.$this->prefix.$k.'`';
			$wpdb->query($q);
			
			$wpdb->query($d['create2']);
			if ($wpdb->last_error) {
				$this->log('Can\'t create table "'.$this->prefix.$k.'": '.$wpdb->last_error);
				$success = false;
			}
		}
		
		return $success;
	}

	public function getDbScheme() {
		$dbscheme = array(
			'docs' => array(
				'cols' => array(
					// name => type, isnull, keys, default, extra
					'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
					'index_id' => array('int(10) unsigned', 'NO', 'MUL'),
					'token' => array('varchar(255)', 'NO', 'MUL'),
					'n' => array('int(10) unsigned', 'NO'),
				),
				'index' => array(
					'PRIMARY' => array(0, 'id'),
					'token' => array(1, 'token'),
					'index_id' => array(1, 'index_id'),
				),
				'create' => "CREATE TABLE `wpftsi_docs` (
								`id` int(10) unsigned NOT NULL auto_increment,
								`index_id` int(10) unsigned NOT NULL,
								`token` varchar(255) NOT NULL,
								`n` int(10) unsigned NOT NULL,
								PRIMARY KEY  (`id`),
								KEY `token` (`token`),
								KEY `index_id` USING BTREE (`index_id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8",
			),
			'index' => array(
				'cols' => array(
					'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
					'tid' => array('bigint(10) unsigned', 'NO', 'MUL'),		
					'tsrc' => array('varchar(255)', 'NO', 'MUL'),		
					'tdt' => array('datetime', 'NO', '', '0000-00-00 00:00:00'),
					'build_time' => array('int(11)', 'NO', 'MUL', '0'),	
					'update_dt' => array('datetime', 'NO', '', '0000-00-00 00:00:00'),
					'force_rebuild' => array('tinyint(4)', 'NO', 'MUL', '0'),
					'locked_dt' => array('datetime', 'NO', 'MUL', '0000-00-00 00:00:00'),
				),
				'index' => array(
					'PRIMARY' => array(0, 'id'),
					'tid_tsrc_unique' => array(0, 'tid,tsrc'),
					'tid' => array(1, 'tid'),
					'build_time' => array(1, 'build_time'),
					'force_rebuild' => array(1, 'force_rebuild'),
					'locked_dt' => array(1, 'locked_dt'),
					'tsrc' => array(1, 'tsrc'),
				),
				'create' => "CREATE TABLE `wpftsi_index` (
								`id` int(10) unsigned NOT NULL auto_increment,
								`tid` bigint(10) unsigned NOT NULL,
								`tsrc` varchar(255) NOT NULL,
								`tdt` datetime NOT NULL default '0000-00-00 00:00:00',
								`build_time` int(11) NOT NULL default '0',
								`update_dt` datetime NOT NULL default '0000-00-00 00:00:00',
								`force_rebuild` tinyint(4) NOT NULL default '0',
								`locked_dt` datetime NOT NULL default '0000-00-00 00:00:00',
								PRIMARY KEY  (`id`),
								UNIQUE KEY `tid_tsrc_unique` USING BTREE (`tid`,`tsrc`),
								KEY `tid` (`tid`),
								KEY `build_time` (`build_time`),
								KEY `force_rebuild` (`force_rebuild`),
								KEY `locked_dt` (`locked_dt`),
								KEY `tsrc` USING HASH (`tsrc`)
							) ENGINE=MyISAM AUTO_INCREMENT=114874 DEFAULT CHARSET=utf8",
			),
			'stops' => array(
				'cols' => array(
					'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
					'word' => array('varchar(32)', 'NO', 'UNI'),
				),
				'index' => array(
					'PRIMARY' => array(0, 'id'),
					'word' => array(0, 'word'),
				),
				'create' => 'CREATE TABLE `wpftsi_stops` (
								`id` int(10) unsigned NOT NULL auto_increment,
								`word` varchar(32) character set utf8 collate utf8_bin NOT NULL,
								PRIMARY KEY  (`id`),
								UNIQUE KEY `word` (`word`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8',
			),
			'vectors' => array(
				'cols' => array(
					'wid' => array('int(10) unsigned', 'NO', 'PRI'),
					'did' => array('int(10) unsigned', 'NO', 'PRI'),
					'f' => array('float(10,4)', 'NO', ''),
				),
				'index' => array(
					'wid_did' => array(0, 'wid,did'),
					'wid' => array(1, 'wid'),
					'did' => array(1, 'did'),
				),
				'create' => 'CREATE TABLE `wpftsi_vectors` (
								`wid` int(10) unsigned NOT NULL,
								`did` int(10) unsigned NOT NULL,
								`f` float(10,4) NOT NULL,
								UNIQUE KEY `wid` (`wid`,`did`),
								KEY `wid_2` (`wid`),
								KEY `did` (`did`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8',
			),
			'words' => array(
				'cols' => array(
					'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
					'word' => array('varchar(32)', 'NO', 'UNI'),
				),
				'index' => array(
					'PRIMARY' => array(0, 'id'),
					'word' => array(0, 'word'),
				),
				'create' => 'CREATE TABLE `wpftsi_words` (
								`id` int(10) unsigned NOT NULL auto_increment,
								`word` varchar(32) character set utf8 collate utf8_bin NOT NULL,
								PRIMARY KEY  (`id`),
								UNIQUE KEY `word` (`word`)
							) ENGINE=MyISAM AUTO_INCREMENT=173320 DEFAULT CHARSET=utf8',
			),
		);
		
		// Make Mysql Db creation queries
		foreach ($dbscheme as $k => $d) {
			
			$s = 'CREATE TABLE `'.$this->prefix.$k.'` ('."\n";
			
			$cs = array();
			$ai = false;
			foreach ($d['cols'] as $kk => $dd) {
				$ss = '`'.$kk.'` '.$dd[0].' '.($dd[1] == 'NO' ? 'NOT NULL' : 'NULL');
				if (isset($dd[3])) {
					$ss .= ' default \''.$dd[3].'\'';
				}
				if ((isset($dd[4])) && ($dd[4] == 'auto_increment')) {
					$ss .= ' auto_increment';
					$ai = true;
				}
				$cs[] = $ss;
			}
			
			$iz = array();
			foreach ($d['index'] as $kk => $dd) {
				$ss = '';
				if ($kk == 'PRIMARY') {
					$ss = 'PRIMARY KEY';
				} else {
					if ($dd[0] == 0) {
						$ss = 'UNIQUE KEY `'.$kk.'`';
					} else {
						$ss = 'KEY `'.$kk.'`';
					}
				}
				$ws = explode(',', $dd[1]);
				$zz = array();
				foreach ($ws as $z) {
					$zz[] = '`'.$z.'`';
				}
				$ss .= ' ('.implode(',', $zz).')';
				
				$iz[] = $ss;
			}
			
			$s .= implode(",\n", $cs);
			
			if (count($iz) > 0) {
				$s .= ",\n".implode(",\n", $iz);
			}
			
			$s .= "\n".') ENGINE=MyISAM'.($ai ? ' AUTO_INCREMENT=1' : '').' DEFAULT CHARSET=utf8';
			
			$dbscheme[$k]['create2'] = $s;
		}
		
		return $dbscheme;
	}
	
	protected function split_to_words($str) {
		preg_match_all("~([\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w][\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w'\-]*[\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w]+|[\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w]+)~u", $str, $matches);
		return apply_filters('wpfts_split_to_words', $matches[1], $str);
	}

	public function reindex($index_id, $chunks) {
		
		global $wpdb;
		
		if (!is_array($chunks)) {
			$this->log('Reindex: wrong chunks format');
			return false;
		}
		foreach ($chunks as $k => $d) {
			$q = 'select id from `'.$this->prefix.'docs` where `index_id` = "'.addslashes($index_id).'" and `token` = "'.addslashes($k).'"';
			$res = $wpdb->get_results($q, ARRAY_A);
			
			if (!isset($res[0]['id'])) {
				// Insert token record
				$wpdb->insert($this->prefix.'docs', array(
					'index_id' => $index_id,
					'token' => $k,
					'n' => 0,
				));
				
				$doc_id = $wpdb->insert_id;
				
			} else {
				$doc_id = $res[0]['id'];
			}
			
			$r2 = $this->add(array($doc_id => $d));
			if (!$r2) {
				return false;
			}
		}
		return true;
	}
	
	public function add($docs = array()) {
		
		global $wpdb;
		
		// Validate
		if (!is_array($docs)) {
			$this->log('Add document: parameter should be an array');
			return false;
		}
		
		if (count($docs) < 1) {
			// Nothing to do
			return true;
		}
		
		foreach ($docs as $id => $doc) {
			if (!is_numeric($id)) {
				$this->log('Add document: bad index "'.$id.'" given.');
				return false;
			} else {
				$a_ids[] = $id;
			}
		}
		
		$pfx = $this->prefix;

		$wordlist = array();
		$doclist = array();
		foreach ($docs as $id => $doc) {
			
			if (!isset($doc) || (mb_strlen($doc) < 1)) {
				continue;
			}
			
			$words = $this->split_to_words($doc);
			$num_of_words = count($words);
			$doclist[$id] = $num_of_words;

			// Remove 1-char words, stop words and too long words
			$w2 = array();
			while (list($k, $v) = each($words)) {
				$len = mb_strlen($v);
				$lv = mb_strtolower($v);
				if (($len > 1) && ($len <= $this->max_word_length) && (!isset($this->stops[$lv]))) {
					if (!isset($w2[$lv])) {
						$w2[$lv] = 1;
					} else {
						$w2[$lv] ++;
					}
				}
			}
			
			foreach ($w2 as $k => $v) {
				$wordlist[] = array($k, $id, $v / $num_of_words);
			}
			
			$wpdb->update($pfx.'docs', array('n' => $num_of_words), array('id' => $id));
		}
		
		// Insert words to `words` table
		$wordlist_ch = array_chunk($wordlist, 1000);
		foreach ($wordlist_ch as $d) {
			$z = array();
			foreach ($d as $dd) {
				$z[] = '("'.addslashes($dd[0]).'")';
			}
			$q = 'insert ignore into `'.$pfx.'words` (`word`) values '.implode(',', $z);
			$wpdb->query($q);
			
			if ($wpdb->last_error) {
				$this->log('Add document: can not add words. Error: '.$wpdb->last_error);
				$wpdb->query('unlock tables');
				return false;
			}
		}

		// lock the tables in case some other process remove a certain word
		// between step 0 and 1 and 2 and 3
		if ($this->_islock) {
			$q = 'lock tables `'.$pfx.'vectors` write, `'.$pfx.'words` write';
			$wpdb->query($q);

			if ($wpdb->last_error) {
				// Disable locking
				$this->_islock = false;
				$wpdb->query('unlock tables');
				//$this->log('Add document: Error when locking tables: '.$wpdb->last_error);
				//return false;
			}
		}

		// Remove old vectors
		$q = 'delete from `'.$pfx.'vectors` where `did` in ('.implode(',', $a_ids).')';
		$wpdb->query($q);
		
		if ($wpdb->last_error) {
			$this->log('Add document: Error when removing old vectors: '.$wpdb->last_error);
			if ($this->_islock) {
				$wpdb->query('unlock tables');
			}
			return false;
		}

		// Insert new vectors
		foreach ($wordlist as $d) {
			
			$q = 'insert ignore into `'.$pfx.'vectors` (`wid`,`did`,`f`)
					select 
						id,
						'.$d[1].',
						'.$d[2].'
					from `'.$pfx.'words`
					where `word` = "'.addslashes($d[0]).'"
				';
			$wpdb->query($q);
			
			if ($wpdb->last_error) {
				$this->log('Add vectors: can not add vector. Error: '.$wpdb->last_error);
				if ($this->_islock) {
					$wpdb->query('unlock tables');
				}
				return false;
			}
		}
		
		if ($this->_islock) {
			$wpdb->query('unlock tables');
		}
		return true;
	}

	function is_stop_word($word) {
		return isset($this->stoplist[mb_strtolower($word)]);
	}

	function parse_search_terms($a) {
		
		$z = array();
		foreach ($a as $d) {
			$z[] = mb_strtolower(trim($d), 'utf-8');
		}
		
		return $z;
	}
	
	function sql_parts(&$wpq, $cw, $issearch, $nocache) {
		
		global $wpdb;
		
		$q = &$wpq->query_vars;
		
		//$txnc = $nocache ? ' SQL_NO_CACHE' : '';
		$txnc = '';
		$join = '';
		$fields = '';
		$matches = array();
		if ((!empty( $q['s'])) && ($issearch)) {
			
			$qs = stripslashes($q['s']);
			if ( empty( $_GET['s'] ) && $wpq->is_main_query() ) {
				$qs = urldecode( $qs );
			}

			$qs = str_replace( array( "\r", "\n" ), '', $qs );
			$q['search_terms_count'] = 1;
			if ( ! empty( $q['sentence'] ) ) {
				$q['search_terms'] = array( $qs );
			} else {
				if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $qs, $matches ) ) {
					$q['search_terms_count'] = count( $matches[0] );
					$q['search_terms'] = $this->parse_search_terms( $matches[0] );
					// if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence
					if ( empty( $q['search_terms'] ) || count( $q['search_terms'] ) > 9 ) {
						$q['search_terms'] = array( $qs );
					}
				} else {
					$q['search_terms'] = array( $qs );
				}
			}
			// Decode terms
			$minchars = 3;
			$ts = array();
			foreach ($q['search_terms'] as $t) {
				$f = !empty( $q['exact'] ) ? 1 : 0;
				if (mb_substr($t, 0, 1, 'utf-8') == '"') {
					$t2 = explode(' ', trim($t, '"'));
					$f = 1;
				} else {
					$t2 = explode(' ', trim($t));
				}
				if (is_array($t2)) {
					foreach ($t2 as $tt) {
						if (mb_strlen(trim($tt), 'utf-8') >= $minchars) {
							if ($f) {
								$ts[] = array(1, trim($tt));
							} else {
								$ts[] = array(0, trim($tt));
							}
						}
					}
				}
			}
			$q['search_terms'] = $ts;
			$q['search_terms_count'] = count($ts);

			$j = '';
			if ($q['search_terms_count'] > 0) {
			
				$q['search_orderby_title'] = array();
				$i = 1;
				foreach ( $q['search_terms'] as $term ) {
				
					if ($i > 1) {
						$j .= ' inner join ';
					}
					if (!$term[0]) {
						// Like
						$j .= '(
								select '.$txnc.'
									ds1.id,
									ds1.index_id,
									ds1.token,
									v1.f
								from `wpftsi_words` w1	
								left join `wpftsi_vectors` v1
									on v1.wid = w1.id
								left join `wpftsi_docs` ds1
									on v1.did = ds1.id
								where
									(w1.`word` like "%'.$wpdb->esc_like( $term[1] ).'%")
								) t'.$i;
					} else {
						// Exact equality
						$j .= '(
								select '.$txnc.'
									ds1.id,
									ds1.index_id,
									ds1.token,
									v1.f
								from `wpftsi_words` w1	
								left join `wpftsi_vectors` v1
									on v1.wid = w1.id
								left join `wpftsi_docs` ds1
									on v1.did = ds1.id
								where
									(w1.`word` = "'.$wpdb->esc_like( $term[1] ).'")
								) t'.$i;
					}
					if ($i > 1) {
						$j .= ' on t'.$i.'.id = t1.id';
					}
				
					$i ++;
				}

				$j .= ' group by t1.index_id
						) t3
							on t3.index_id = fi.id
						) wpftsi_t
							on wpftsi_t.tid = '.$wpdb->posts.'.ID';
		
				$fields = ', wpftsi_t.relev ';
			}

			$i --;
			if ($i < 2) {
				$relev = 't1.f';
			} else {
				$sum = array();
				for ($ii = 1; $ii <= $i; $ii ++) {
					$sum[] = 't'.$ii.'.f';
				}
				$relev = '('.implode('+', $sum).') / '.count($sum);
			}
			if (count($cw) > 1) {
				$x = array();
				foreach ($cw as $k => $d) {
					//if(t1.token = "post_title", 100, 50)
					$x[] = ' when "'.$k.'" then '.floatval($d);
				}
				$rcv = ' (case t1.token '.implode('', $x).' else 1 end)';
			} else {
				$rcv = 1;
			}
			
			$jhdr = ' left join (
						select '.$txnc.'
							fi.tid,
							t3.relev
						from `wpftsi_index` fi
						inner join (
							select
								t1.index_id, 
								sum('.$relev.' * '.$rcv.') relev
							from ';
			
			$join .= $jhdr.$j;
			
		} else {
			$issearch = 0;
		}
		
		$parts = array(
				'token' => md5(time().'|'.uniqid('session')),
				'issearch' => $issearch,
				'nocache' => $nocache,
				'join' => $join,
				'select' => ' and (not isnull(wpftsi_t.tid))',
				'orderby' => ' (wpftsi_t.relev) desc',
				'fields' => $fields,
				'sql_no_cache' => $nocache ? ' SQL_NO_CACHE' : '',
			);
		
		return $parts;
	}
	
	function sql_joins($join, &$wpq, $cw) {
		
		if ((isset($wpq->wpftsi_session['token'])) && ($wpq->wpftsi_session['issearch'])) {
			return $join.$wpq->wpftsi_session['join'];
		}
		return $join;
	}
	
	/**
	 * Constructing SQL search part
	 * 
	 * @param string $search Search SQL from WP
	 * @param WP_Query $wpq WP query object
	 */
	function sql_select($search, &$wpq) {
		
		if ((isset($wpq->wpftsi_session['token'])) && ($wpq->wpftsi_session['issearch'])) {
			$search = $wpq->wpftsi_session['select'];
		}
		
		return $search;
	}
	
	function sql_orderby($orderby, &$wpq) {
		
		if ((isset($wpq->wpftsi_session['token'])) && ($wpq->wpftsi_session['issearch'])) {
			$orderby = $wpq->wpftsi_session['orderby'];
		}
		
		return $orderby;
	}
	
	function sql_pre_posts(&$wpq, $cw) {
		
		if ((!isset($wpq->wpftsi_session['token'])) || (!$wpq->wpftsi_session['token'])) {
			
			$disable = (isset($wpq->query_vars['wpfts_disable']) && ($wpq->query_vars['wpfts_disable'])) ? 1 : 0;
			$nocache = (isset($wpq->query_vars['wpfts_nocache']) && ($wpq->query_vars['wpfts_nocache'])) ? 1 : 0;
			
			// Calculate data
			$sql_parts = $this->sql_parts($wpq, $cw, $disable ? 0 : 1, $nocache);
			$sql_parts['token'] = md5(time().'|'.uniqid('session'));
			$wpq->wpftsi_session = $sql_parts;
		}
	}
	
	function sql_posts_fields($fields, &$wpq) {

		if ((isset($wpq->wpftsi_session['token'])) && ($wpq->wpftsi_session['issearch'])) {
			return $fields.$wpq->wpftsi_session['fields'];
		}
		
		return $fields;
	}
	
	function sql_posts_distinct($distinct, &$wpq) {

		if ((isset($wpq->wpftsi_session['token'])) /*&& ($wpq->wpftsi_session['issearch'])*/) {
			return str_replace('SQL_NO_CACHE', '', $distinct).$wpq->wpftsi_session['sql_no_cache'];
		}
		
		return $distinct;
	}
	
	function sql_the_posts($posts, &$wpq) {

		if (isset($wpq->wpftsi_session)) {
			$wpq->wpftsi_session = null;
		}
		return $posts;
	}
	
	function getRecordsToRebuild($n_max = 1) {
		
		global $wpdb;
		
		$idx = $this->prefix;
		
		$time = time();
		$time2 = date('Y-m-d H:i:s', $time - $this->lock_time);
		
		$q = 'select 
					id, tid, tsrc 
			from `'.$idx.'index` 
			where 
				((force_rebuild != 0) or (build_time = 0)) and 
				((locked_dt = "0000-00-00 00:00:00") or (locked_dt < "'.$time2.'"))
			order by build_time asc, id asc 
			limit '.intval($n_max).'';
		$r = $wpdb->get_results($q, ARRAY_A);
		
		return $r;
	}
	
	function checkAndSyncWPPosts($current_build_time) {
		
		global $wpdb;
		
		$idx = $this->prefix;
		
		// Step 1. Mark index rows contains old posts and posts with wrong date of post or build time.
		$q = 'update `'.$idx.'index` wi
				left join `wp_posts` p
					on p.ID = wi.tid
				set 
					wi.force_rebuild = if(p.ID is null, 2, if ((wi.build_time = "'.addslashes($current_build_time).'") and (wi.tdt = p.post_modified), 0, 1))
				where 
					(wi.tsrc = "wp_posts") and (wi.force_rebuild = 0)';
		$wpdb->query($q);
		
		// Step 2. Find and add new posts // @todo need to be optimized!
		$q = 'insert ignore into `'.$idx.'index` 
				(`tid`, `tsrc`, `tdt`, `build_time`, `update_dt`, `force_rebuild`, `locked_dt`) 
				select 
					p.ID tid,
					"wp_posts" tsrc,
					"0000-00-00 00:00:00" tdt,
					0 build_time,
					"0000-00-00 00:00:00" update_dt,
					1 force_rebuild,
					"0000-00-00 00:00:00" locked_dt
				from `wp_posts` p';
		$wpdb->query($q);
		
		// Step 3. What else?
	}
	
	function get_status() {
		
		global $wpdb;
		
		$idx = $this->prefix;
		
		$q = 'select 
				sum(if (build_time != 0, 1, 0)) n_inindex, 
				sum(if ((force_rebuild = 0) and (build_time != 0), 1, 0)) n_actual,
				sum(if ((force_rebuild != 0) or (build_time = 0), 1, 0)) n_pending
			from `'.$idx.'index` 
			where tsrc = "wp_posts"';
		$res = $wpdb->get_results($q, ARRAY_A);
		
		$ret = array();
		if (isset($res[0]['n_inindex'])) {
			$ret = array(
				'n_inindex' => $res[0]['n_inindex'],
				'n_actual' => $res[0]['n_actual'],
				'n_pending' => $res[0]['n_pending'],
			);
		} else {
			$ret = array(
				'n_inindex' => 0,
				'n_actual' => 0,
				'n_pending' => 0,
			);
		}
		
		return $ret;
	}
	
	function getClusters() {
		
		global $wpdb;
		
		$idx = $this->prefix;
		
		$z = array('post_title' => 1, 'post_content' => 1);
		
		$q = 'select distinct `token` from `'.$idx.'docs` limit 100';
		$res = $wpdb->get_results($q, ARRAY_A);
		
		$z = array();
		foreach ($res as $d) {
			if (!isset($z[$d['token']])) {
				$z[$d['token']] = 1;
			}
		}
		
		return array_keys($z);
	}

	function lockUnlockedRecord($id) {
		
		global $wpdb;
		
		$idx = $this->prefix;
		
		$time = time();
		$time2 = date('Y-m-d H:i:s', $time - $this->lock_time);
		$new_time = date('Y-m-d H:i:s', $time);
		
		$q = 'select id, if((locked_dt = "0000-00-00 00:00:00") or (locked_dt < "'.$time2.'"), 0, 1) islocked from `'.$idx.'index` where id = "'.addslashes($id).'"';
		$res = $wpdb->get_results($q, ARRAY_A);
		
		if (isset($res[0])) {
			if ($res[0]['islocked']) {
				// Already locked
				return false;
			} else {
				// Lock it
				$wpdb->update($idx.'index', array('locked_dt' => $new_time), array('id' => $id));
				return true;
			}
		} else {
			// Record not found
			return false;
		}
		
	}
	
	function unlockRecord($id) {
		
		global $wpdb;
		
		$idx = $this->prefix;
		
		$wpdb->update($idx.'index', array('locked_dt' => '0000-00-00 00:00:00'), array('id' => $id));
	}
	
	function updateRecordData($id, $data = array()) {
		
		global $wpdb;
		
		$idx = $this->prefix;
		
		$a = array();
		foreach ($data as $k => $d) {
			if (in_array($k, array('tdt', 'build_time', 'update_dt', 'force_rebuild', 'locked_dt'))) {
				$a[$k] = $d;
			}
		}
		$wpdb->update($idx.'index', $a, array('id' => $id));
	}
	
	function insertRecordData($data = array()) {
		
		global $wpdb;
		
		$idx = $this->prefix;
		
		$a = array();
		foreach ($data as $k => $d) {
			if (in_array($k, array('tdt', 'build_time', 'update_dt', 'force_rebuild', 'locked_dt', 'tid', 'tsrc'))) {
				$a[$k] = $d;
			}
		}
		$wpdb->insert($idx.'index', $a);
		
		return $wpdb->insert_id;
	}
	
	function updateIndexRecordForPost($post_id, $modt, $build_time, $time = false, $force_rebuild = 0) {
		
		global $wpdb;
		
		if ($time === false) {
			$time = time();
		}
		
		$q = 'select * from `'.$this->prefix.'index` where (`tid` = "'.$post_id.'") and (`tsrc` = "wp_posts")';
		$res = $wpdb->get_results($q, ARRAY_A);
		
		if (isset($res[0])) {
			// Update existing record
			$this->updateRecordData(
					$res[0]['id'], 
					array(
						'tdt' => $modt,
						'build_time' => $build_time,
						'update_dt' => date('Y-m-d H:i:s', $time),
						'force_rebuild' => $force_rebuild,
						'locked_dt' => '0000-00-00 00:00:00',
					)
			);
			
			return $res[0]['id'];
		} else {
			// Insert new record
			$insert_id = $this->insertRecordData(
					array(
						'tid' => $post_id,
						'tsrc' => 'wp_posts',
						'tdt' => $modt,
						'build_time' => $build_time,
						'update_dt' => date('Y-m-d H:i:s', $time),
						'force_rebuild' => $force_rebuild,
						'locked_dt' => '0000-00-00 00:00:00',
					)
			);
			
			return $insert_id;
		}
	}
	
	function getColumn($a, $col) {
		$r = array();
		foreach ($a as $d) {
			if (isset($d[$col])) {
				$r[] = $d[$col];
			}
		}
		return $r;
	}
	
	function removeIndexRecordForPost($post_id) {
		
		global $wpdb;
		
		$idx = $this->prefix;
		
		$q = 'select id from `'.$idx.'index` where (tid = "'.addslashes($post_id).'") and (tsrc = "wp_posts")';
		$res_index = $wpdb->get_results($q, ARRAY_A);
		
		if (isset($res_index[0])) {
			$q = 'select id from `'.$idx.'docs` where index_id in ('.implode(',', $this->getColumn($res_index, 'id')).')';
			$res_docs = $wpdb->get_results($q, ARRAY_A);
			
			if (isset($res_docs[0])) {
				$q = 'delete from `'.$idx.'vectors` where did in ('.implode(',', $this->getColumn($res_docs, 'id')).')';
				$wpdb->query($q);
				
				$q = 'delete from `'.$idx.'docs` where index_id in ('.implode(',', $this->getColumn($res_index, 'id')).')';
				$wpdb->query($q);
			}
			
			$q = 'delete from `'.$idx.'index` where (tid = "'.addslashes($post_id).'") and (tsrc = "wp_posts")';
			$wpdb->query($q);
		}
		
		return true;
	}
}

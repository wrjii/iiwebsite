<?php

/*
=====================================================
 ExpressionEngine - by EllisLab
-----------------------------------------------------
 http://expressionengine.com/
-----------------------------------------------------
 Copyright (c) 2003 - 2009 EllisLab, Inc.
=====================================================
 THIS IS COPYRIGHTED SOFTWARE
 PLEASE READ THE LICENSE AGREEMENT
 http://expressionengine.com/docs/license.html
=====================================================
 File: mod.member_subscriptions.php
=====================================================

*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Member_subscriptions extends Member {


    /** ----------------------------------
    /**  Member_settings Profile Constructor
    /** ----------------------------------*/

	function Member_subscriptions()
	{
	}
	/* END */
	
	
    /** ----------------------------------------
    /**  Subscriptions Edit Form
    /** ----------------------------------------*/
	
	function edit_subscriptions()
	{
		global $IN, $LANG, $FNS, $DB, $LOC, $PREFS, $SESS, $REGX;
		
        // Set some base values
        
        $blog_subscriptions		= FALSE;
        $galery_subscriptions	= FALSE;
        $forum_subscriptions	= FALSE;
        $result_ids				= array();
        $result_data			= array();
        $pageurl 				= $this->_member_path('edit_subscriptions');
        $perpage				= 50;
        $total_count			= 0;
        $rownum  				= $this->cur_id; 
        $page_links				= '';
		
		/** ----------------------------------------
		/**  Set update path
		/** ----------------------------------------*/

		$swap['path:update_subscriptions'] = $this->_member_path('update_subscriptions');
		
		
		/** ----------------------------------------
		/**  Fetch Weblog Comment Subscriptions
		/** ----------------------------------------*/
		
		$query = $DB->query("SELECT DISTINCT(entry_id)  FROM exp_comments WHERE email = '".$SESS->userdata['email']."' AND notify = 'y' ORDER BY comment_date DESC");

		if ($query->num_rows > 0)
		{
			$blog_subscriptions	= TRUE;
			
			$temp_ids = array();
					
			foreach ($query->result as $row)
			{
				$temp_ids[] = $row['entry_id'];
			}
			
			// and now grab the most recent activity for each subscription for ordering later
			$query = $DB->query("SELECT entry_id, recent_comment_date FROM exp_weblog_titles WHERE entry_id IN (".implode(',', $temp_ids).")");
			
			if ($query->num_rows > 0)
			{
				foreach ($query->result as $row)
				{
					$result_ids[$row['recent_comment_date'].'b'] = $row['entry_id'];
				}
			}
		}
		
		/** ----------------------------------------
		/**  Fetch Gallery Comment Subscriptions
		/** ----------------------------------------*/

		// Since the gallery module might not be installed we'll test for it first.
						
		if ($DB->table_exists('exp_gallery_comments'))
		{
			$query = $DB->query("SELECT DISTINCT(entry_id) FROM exp_gallery_comments WHERE email = '".$DB->escape_str($SESS->userdata['email'])."' AND notify = 'y' ORDER BY comment_date DESC");
		
			if ($query->num_rows > 0)
			{
				$galery_subscriptions = TRUE;
				
				$temp_ids = array();
				
				foreach ($query->result as $row)
				{
					$temp_ids[] = $row['entry_id'];
				}
				
				// and now grab the most recent activity for each subscription for ordering later
				$query = $DB->query("SELECT entry_id, recent_comment_date FROM exp_gallery_entries WHERE entry_id IN (".implode(',', $temp_ids).")");

				if ($query->num_rows > 0)
				{
					foreach ($query->result as $row)
					{
						$result_ids[$row['recent_comment_date'].'g'] = $row['entry_id'];
					}
				}
			}
		}

		/** ----------------------------------------
		/**  Fetch Forum Topic Subscriptions
		/** ----------------------------------------*/

		// Since the forum module might not be installed we'll test for it first.
						
		if ($DB->table_exists('exp_forum_subscriptions'))
		{
			$query = $DB->query("SELECT topic_id FROM exp_forum_subscriptions WHERE member_id = '".$DB->escape_str($SESS->userdata('member_id'))."' ORDER BY subscription_date DESC");
		
			if ($query->num_rows > 0)
			{
				$forum_subscriptions = TRUE;
				
				$temp_ids = array();
				
				foreach ($query->result as $row)
				{
					$temp_ids[] = $row['topic_id'];
				}
				
				// and now grab the most recent activity for each subscription for ordering later
				$query = $DB->query("SELECT topic_id, last_post_date FROM exp_forum_topics WHERE topic_id IN (".implode(',', $temp_ids).")");

				if ($query->num_rows > 0)
				{
					foreach ($query->result as $row)
					{
						$result_ids[$row['last_post_date'].'f'] = $row['topic_id'];
					}
				}				
			}
		}
		
		
        /** ------------------------------------
		/**  No results?  Bah, how boring...
		/** ------------------------------------*/
		
		if (count($result_ids) == 0)
		{
			$swap['subscription_results'] = $this->_var_swap($this->_load_element('no_subscriptions_message'), array('lang:no_subscriptions'=> $LANG->line('no_subscriptions')));
											
			return $this->_var_swap($this->_load_element('subscriptions_form'), $swap);
		}

		// Sort the array for newest activity first
		// we'll end up doing this twice, one to determine what entries
		// belong on the page for pagination, then again for the data queries
		krsort($result_ids);
	
        /** ---------------------------------
        /**  Do we need pagination?
        /** ---------------------------------*/
        
        $total_rows = count($result_ids);
        
        if ($rownum != '')
        	$rownum = substr($rownum, 1);

		$rownum = ($rownum == '' || ($perpage > 1 AND $rownum == 1)) ? 0 : $rownum;
		
		if ($rownum > $total_rows)
		{
			$rownum = 0;
		}
					
		$t_current_page = floor(($rownum / $perpage) + 1);
		$total_pages	= intval(floor($total_rows / $perpage));
		
		if ($total_rows % $perpage) 
			$total_pages++;
		
		if ($total_rows > $perpage)
		{
			if ( ! class_exists('Paginate'))
			{
				require PATH_CORE.'core.paginate'.EXT;
			}
			
			$PGR = new Paginate();
				
			$PGR->first_url 	= $pageurl;
			$PGR->path			= $pageurl;
			$PGR->prefix		= 'R';
			$PGR->total_count 	= $total_rows;
			$PGR->per_page		= $perpage;
			$PGR->cur_page		= $rownum;
			$PGR->qstr_var      = 'rownum';
			
			$page_links	= $PGR->show_links();
			
			$result_ids = array_slice($result_ids, $rownum, $perpage);
		}
		else
		{
			$result_ids = array_slice($result_ids, 0, $perpage);	
		}


        /** ---------------------------------
        /**  Fetch Weblog Titles
        /** ---------------------------------*/

		if ($blog_subscriptions	== TRUE)
		{
			$sql = "SELECT
					exp_weblog_titles.title, exp_weblog_titles.url_title, exp_weblog_titles.weblog_id, exp_weblog_titles.entry_id,  exp_weblog_titles.recent_comment_date,
					exp_weblogs.comment_url, exp_weblogs.blog_url
					FROM exp_weblog_titles
					LEFT JOIN exp_weblogs ON exp_weblog_titles.weblog_id = exp_weblogs.weblog_id 
					WHERE entry_id IN (";
		
			$idx = '';
		
			foreach ($result_ids as $key => $val)
			{			
				if (substr($key, strlen($key)-1) == 'b')
				{
					$idx .= $val.",";
				}
			}
		
			$idx = substr($idx, 0, -1);
			
			if ($idx != '')
			{
				$query = $DB->query($sql.$idx.') ');
	
				if ($query->num_rows > 0)
				{
					foreach ($query->result as $row)
					{																
						$result_data[$row['recent_comment_date']] = array(
												'path'	=> $FNS->remove_double_slashes($REGX->prep_query_string(($row['comment_url'] != '') ? $row['comment_url'] : $row['blog_url']).'/'.$row['url_title'].'/'),
												'title'	=> str_replace(array('<', '>', '{', '}', '\'', '"', '?'), array('&lt;', '&gt;', '&#123;', '&#125;', '&#146;', '&quot;', '&#63;'), $row['title']),
												'id'	=> 'b'.$row['entry_id'],
												'type'	=> $LANG->line('comment')
												);
					}
				}
			}
		}

        /** ---------------------------------
        /**  Fetch Gallery Titles
        /** ---------------------------------*/

		if ($galery_subscriptions == TRUE)
		{
			$sql = "SELECT
					exp_gallery_entries.title, exp_gallery_entries.entry_id, exp_gallery_entries.gallery_id, exp_gallery_entries.recent_comment_date,
					exp_galleries.gallery_comment_url
					FROM exp_gallery_entries
					LEFT JOIN exp_galleries ON exp_gallery_entries.gallery_id = exp_galleries.gallery_id 
					WHERE entry_id IN (";
					
			$idx = '';
		
			foreach ($result_ids as $key => $val)
			{			
				if (substr($key, strlen($key)-1) == 'g')
				{
					$idx .= $val.",";
				}
			}
		
			$idx = substr($idx, 0, -1);
			
			if ($idx != '')
			{
				$query = $DB->query($sql.$idx.') ');
	
				if ($query->num_rows > 0)
				{
					foreach ($query->result as $row)
					{																
						$result_data[$row['recent_comment_date']] = array(
												'path'	=> $FNS->remove_double_slashes($REGX->prep_query_string($row['gallery_comment_url'] ).'/'.$row['entry_id'].'/'),
												'title'	=> str_replace(array('<', '>', '{', '}', '\'', '"', '?'), array('&lt;', '&gt;', '&#123;', '&#125;', '&#146;', '&quot;', '&#63;'), $row['title']),
												'id'	=> 'g'.$row['entry_id'],
												'type'	=> $LANG->line('mbr_image_gallery')
												);
					}
				}
			}
		}


        /** ---------------------------------
        /**  Fetch Forum Topics
        /** ---------------------------------*/

		if ($forum_subscriptions == TRUE)
		{
			$sql = "SELECT title, topic_id, board_forum_url, last_post_date FROM exp_forum_topics, exp_forum_boards
					WHERE exp_forum_topics.board_id = exp_forum_boards.board_id
					AND topic_id IN (";
					
			$idx = '';
		
			foreach ($result_ids as $key => $val)
			{			
				if (substr($key, strlen($key)-1) == 'f')
				{
					$idx .= $val.",";
				}
			}
		
			$idx = substr($idx, 0, -1);
			
			if ($idx != '')
			{
				$query = $DB->query($sql.$idx.') ');
	
				if ($query->num_rows > 0)
				{
					foreach ($query->result as $row)
					{																
						$result_data[$row['last_post_date']] = array(
												'path'	=> $FNS->remove_double_slashes($REGX->prep_query_string($row['board_forum_url'] ).'/viewthread/'.$row['topic_id'].'/'),
												'title'	=> str_replace(array('<', '>', '{', '}', '\'', '"', '?'), array('&lt;', '&gt;', '&#123;', '&#125;', '&#146;', '&quot;', '&#63;'), $row['title']),
												'id'	=> 'f'.$row['topic_id'],
												'type'	=> $LANG->line('mbr_forum_post')
												);
					}
				}
			}
		}
		
		// sort the data results
		krsort($result_data);
	
		// Build the result table...

		$out = $this->_var_swap($this->_load_element('subscription_result_heading'),
								array(
										'lang:title'        =>	$LANG->line('title'),
										'lang:type'         =>	$LANG->line('type'),
										'lang:unsubscribe'  =>	$LANG->line('unsubscribe')
									 )
							);
		
		$i = 0;
		foreach ($result_data as $val)
		{
			$rowtemp = $this->_load_element('subscription_result_rows');
						
			$rowtemp = str_replace('{class}',   ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo', $rowtemp);
		   
			$rowtemp = str_replace('{path}',    $val['path'],	$rowtemp);
			$rowtemp = str_replace('{title}',   $val['title'],	$rowtemp);
			$rowtemp = str_replace('{id}',      $val['id'],		$rowtemp);
			$rowtemp = str_replace('{type}',    $val['type'],	$rowtemp);

			$out .= $rowtemp;
		}
		
		$out .= $this->_var_swap($this->_load_element('subscription_pagination'), 
								 array('pagination' => $page_links, 
								 	   'lang:unsubscribe' => $LANG->line('unsubscribe'),
								 	   'class' => ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo'));

	
		$swap['subscription_results'] = $out;
				
		return $this->_var_swap(
									$this->_load_element('subscriptions_form'), $swap
								);
	}
	/* END */
	
	
	
    /** ----------------------------------------
    /**  Update Subscriptions
    /** ----------------------------------------*/
	
	function update_subscriptions()
	{
		global $FNS, $IN, $SESS, $DB, $LANG;
		
        if ( ! $IN->GBL('toggle', 'POST'))
        {
			$FNS->redirect($this->_member_path('edit_subscriptions'));
			exit;    
        }
                
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'toggle') AND ! is_array($val))
            {            
            	switch (substr($val, 0, 1))
            	{
            		case "b"	: $DB->query("UPDATE exp_comments SET notify = 'n' WHERE entry_id = '".substr($val, 1)."' AND email = '".$DB->escape_str($SESS->userdata('email'))."'");
            			break;
            		case "g"	: $DB->query("UPDATE exp_gallery_comments SET notify = 'n' WHERE entry_id = '".substr($val, 1)."' AND email = '".$DB->escape_str($SESS->userdata('email'))."'");
            			break;
					case "f"	: $DB->query("DELETE FROM exp_forum_subscriptions WHERE topic_id = '".substr($val, 1)."' AND member_id = '{$SESS->userdata['member_id']}'");
            			break;
            	}
            }        
        }
				
        /** -------------------------------------
        /**  Success message
        /** -------------------------------------*/
	
		return $this->_var_swap($this->_load_element('success'),
								array(
										'lang:heading'		=>	$LANG->line('subscriptions'),
										'lang:message'		=>	$LANG->line('subscriptions_removed')
									 )
								);
	}
	/* END */
	
}
// END CLASS
?>
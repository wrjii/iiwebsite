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
 File: mcp.moblog.php
-----------------------------------------------------
 Purpose: Moblog class - CP
=====================================================
*/


if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Moblog_CP {

    var $version 			= '2.0';
    var $blog_array   		= array();
    var $cat_array    		= array();
    var $status_array 		= array();
    var $field_array  		= array();
    var $author_array 		= array();
    var $default_template 	= '';
    
    var $gallery_cats		= array();
    var $gallery_array		= array();
    var $gallery_authors	= array();
    
    var $default_gallery_cat	= '';
    var $default_weblog_cat		= '';


    /** -------------------------
    /**  Constructor
    /** -------------------------*/
    
    function Moblog_CP( $switch = TRUE )
    {
        global $IN, $DB;
        
		/** -------------------------------
		/**  Is the module installed?
		/** -------------------------------*/
        
        $query = $DB->query("SELECT COUNT(*) AS count FROM exp_modules WHERE module_name = 'Moblog'");
        
        if ($query->row['count'] == 0)
        {
        	return;
        }
                
        
        /** ----------------------------------
        /**  Update Fields
        /** ----------------------------------*/
        
        if ($DB->table_exists('exp_moblogs'))
        {
        	$existing_fields = array();
        	
        	$new_fields = array('moblog_type'				=> "`moblog_type` varchar(10) NOT NULL default '' AFTER `moblog_time_interval`",
        						'moblog_gallery_id'			=> "`moblog_gallery_id` int(6) unsigned NOT NULL default '0' AFTER `moblog_type`",
        						'moblog_gallery_category'	=> "`moblog_gallery_category` int(10) unsigned NOT NULL default '0' AFTER `moblog_gallery_id`", 
								'moblog_gallery_status'		=> "`moblog_gallery_status` varchar(50) NOT NULL default '' AFTER `moblog_gallery_category`",
								'moblog_gallery_comments'	=> "`moblog_gallery_comments` varchar(10) NOT NULL default 'y' AFTER `moblog_gallery_status`",
								'moblog_gallery_author'		=> "`moblog_gallery_author` int(10) unsigned NOT NULL default '1' AFTER `moblog_gallery_comments`",
								'moblog_ping_servers'		=> "`moblog_ping_servers` varchar(50) NOT NULL default ''",
								'moblog_allow_overrides'	=> "`moblog_allow_overrides` char(1) NOT NULL default 'y'",
								'moblog_sticky_entry'		=> "`moblog_sticky_entry` char(1) NOT NULL default 'n'");        	
        	
        	$query = $DB->query("SHOW COLUMNS FROM exp_moblogs");
        	
        	foreach($query->result as $row)
        	{
        		$existing_fields[] = $row['Field'];
        	}
        	
        	foreach($new_fields as $field => $alter)
        	{
        		if ( ! in_array($field, $existing_fields))
        		{
        			$DB->query("ALTER table exp_moblogs ADD COLUMN {$alter}");
        		}
        	}        	
        }
        
        $this->default_template = <<<EOT
{text}

{images}
<img src="{file}" width="{width}" height="{height}" alt="pic" />
{/images}

{files match="audio|files|movie"}
<a href="{file}">Download File</a>
{/files}
EOT;
        
        if ($switch)
        {
            switch($IN->GBL('P'))
            {
                case 'view'   			:  $this->view_moblogs();
                    break;	
                case 'create'	  		:  $this->create_moblog();
                    break;
                case 'delete_confirm'  	 :  $this->delete_confirm();
                    break;
                case 'delete'   			:  $this->delete_moblogs();
                    break;
                case 'modify'   			:  $this->create_moblog();
                    break;
                case 'update' 			:  $this->update_moblog();
                    break;
                case 'check'   			:  $this->check_moblog();
                    break;
                default       			:  $this->moblog_home();
                    break;
            }
        }
    }
    /* END */
    

    /** -------------------------
    /**  Moblog Home Page
    /** -------------------------*/
    
    function moblog_home($message='')
    {
        global $IN, $DSP, $LANG, $FNS, $DB, $LOC, $PREFS;
                
        if ( ! $rownum = $IN->GBL('rownum', 'GP'))
        {        
            $rownum = 0;
        }
        
        $perpage = 100;

		$qm = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';
                        
                        
        $DSP->title 	= $LANG->line('moblog');
        $DSP->crumb 	= $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=moblog', $LANG->line('moblog')).$DSP->crumb_item($LANG->line('moblog_view'));  
		
		$DSP->right_crumb($LANG->line('create_moblog'), BASE.AMP.'C=modules'.AMP.'M=moblog'.AMP.'P=create');

        $DSP->body .= $DSP->qdiv('tableHeading', $LANG->line('moblog')); 
        
       // $DSP->body .= '<div class="galleryNavOn">Whoo haaaadddd</div>';


        $sql = "SELECT count(*) AS count FROM exp_moblogs";
    			
    		if (USER_BLOG !== FALSE)
		{
			$sql .= " WHERE exp_moblogs.user_blog_id = '".UB_BLOG_ID."'";
		}
		else
		{
			$sql .= " WHERE exp_moblogs.is_user_blog = 'n'";
		}
		
		$query = $DB->query($sql);

        if ($query->row['count'] == 0)
        {
			$DSP->body .= $DSP->div('box');
            $DSP->body .= $DSP->qdiv('itemWrapper', '<b>'.$LANG->line('no_moblogs').'</b>');      
			$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=moblog'.AMP.'P=create', $LANG->line('create_moblog'))); 
			$DSP->body .= $DSP->div_c();    
            return;
        }  
        
        if ($message != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $message);
        }
        
        $total = $query->row['count'];
                
        $sql = "SELECT moblog_full_name, moblog_id, moblog_type, moblog_enabled FROM exp_moblogs";
    			
    	if (USER_BLOG !== FALSE)
		{
			$sql .= " WHERE exp_moblogs.user_blog_id = '".UB_BLOG_ID."'";
		}
		else
		{
			$sql .= " WHERE exp_moblogs.is_user_blog = 'n'";
		}
        		
        $sql .= " ORDER BY moblog_full_name asc LIMIT $rownum, $perpage";
        					
        $query = $DB->query($sql);
        
        if ($query->num_rows == 0)
        {
            $DSP->body .= $DSP->qdiv('itemWrapper', $LANG->line('no_moblogs'));      

            return;
        }  

        $DSP->body	.=	$DSP->toggle();
                
        $DSP->body	.=	$DSP->form_open(array('action' => 'C=modules'.AMP.'M=moblog'.AMP.'P=delete_confirm', 'name' => 'target', 'id' => 'target'));
    
        $DSP->body	.=	$DSP->table('tableBorder', '0', '0', '100%').
						$DSP->tr().
						$DSP->table_qcell('tableHeadingAlt', 
											array(
													$LANG->line('moblog_view'),
													$LANG->line('moblog_type'),
													$LANG->line('check_moblog'),
													$LANG->line('moblog_prefs'),
													$DSP->input_checkbox('toggleflag', '', '', " onclick=\"toggle(this);\"").NBS.$LANG->line('delete').NBS.NBS
												 )
											).
						$DSP->tr_c();
		
		$i = 0;

		foreach ($query->result as $row)
		{				
			$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
                      
            $DSP->body .= $DSP->tr();
            
            if ($row['moblog_enabled'] == 'n')
            {
            	$check_link = $LANG->line('check_moblog');
            }
            else
            {
            	$check_link = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=moblog'.AMP.'P=check'.AMP.'moblog_id='.$row['moblog_id'],$LANG->line('check_moblog'));
            }
            
            $DSP->body .= $DSP->table_qcell($style, '<b>'.$row['moblog_full_name'].'</b>', '30%');
            $DSP->body .= $DSP->table_qcell($style, ucfirst($row['moblog_type']), '20%');
            $DSP->body .= $DSP->table_qcell($style, $check_link, '20%');
            $DSP->body .= $DSP->table_qcell($style, $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=moblog'.AMP.'P=modify'.AMP.'id='.$row['moblog_id'],$LANG->line('moblog_prefs')), '20%');
            $DSP->body .= $DSP->table_qcell($style, $DSP->input_checkbox('toggle[]', $row['moblog_id']), '10%');
											
			$DSP->body .= $DSP->tr_c();
		}
		
		$DSP->body	.=	$DSP->table_c(); 
		        
		$pager = $DSP->pager(
                            BASE.AMP.'C=modules'.AMP.'M=moblog',
                            $total,
                            $perpage,
                            $rownum,
                            'rownum'
                          );

		$DSP->body  .= $DSP->table('', '0', '0', '100%');
		$DSP->body	.= $DSP->tr();
		$DSP->body	.= $DSP->table_qcell('default', $pager, '30%'); 
		$DSP->body	.= $DSP->table_qcell('default', '', '20%'); 
		$DSP->body	.= $DSP->table_qcell('default', '', '20%'); 
		$DSP->body	.= $DSP->table_qcell('default', '', '20%'); 
		$DSP->body	.= $DSP->table_qcell('default', $DSP->input_submit($LANG->line('delete')), '10%');             
		$DSP->body	.= $DSP->tr_c();

        $DSP->body	.=	$DSP->table_c(); 
        
        $DSP->body	.=	$DSP->form_close();     
     
    }
    /* END */
    

	/** -------------------------
    /**  Create Moblog
    /** -------------------------*/
    
    function create_moblog()
    {
		global $DSP, $DB, $LANG, $IN, $SESS, $PREFS;
		
		$id		= ( ! $IN->GBL('id', 'GET')) ? '' : $IN->GBL('id', 'GET');
		$basis	= ( ! $IN->GBL('basis', 'POST'))  ? '' : $_POST['basis'];
		
		$sql = "SELECT count(*) AS count FROM exp_moblogs";
				
		if (USER_BLOG !== FALSE)
		{
			$sql .= " WHERE exp_moblogs.user_blog_id = '".UB_BLOG_ID."'";
		}
		else
		{
			$sql .= " WHERE exp_moblogs.is_user_blog = 'n'";
		}
		
		$query = $DB->query($sql);   	
		
		$DSP->title			= $LANG->line('moblog');
        $DSP->crumb			= $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=moblog', $LANG->line('moblog'));
        $DSP->body_props	= ' onload="switch_type();"';
        
        if ($id != '')
        {
       		$DSP->crumb .= $DSP->crumb_item($LANG->line('edit_moblog'));  
       		$r			 = $DSP->qdiv('tableHeading', $LANG->line('edit_moblog')); 
       	}
       	else
       	{
       		$DSP->crumb .= $DSP->crumb_item($LANG->line('create_moblog'));
       		$r			 = $DSP->qdiv('tableHeading', $LANG->line('create_moblog')); 
       	}
       	
		/** -------------------------------
		/**  Base new moblog on existing one?
		/** -------------------------------*/
		
		if ($basis == '' && $query->row['count'] > 0 && $id == '') 
		{    		
			$r .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=moblog'.AMP.'P=create', 'name' => 'moblog_basis', 'id' => 'moblog_basis'));             
                  
            // moblog pull-down menu
            
            $r .= $DSP->div('box');
            
            $r .= $DSP->qdiv('itemWrapper', $LANG->line('moblog_basis'));
            
            $r .= $DSP->input_select_header('basis');
            
            $r .= $DSP->input_select_option('none', $LANG->line('none'));
			
			/** -----------------------------
			/**  Find Moblogs - LOOK! It checks for USER_BLOG!!!
			/** -----------------------------*/
			
    			$sql = "SELECT moblog_id, moblog_full_name
    					FROM exp_moblogs";
    			
    			if (USER_BLOG !== FALSE)
			{
				$sql .= " WHERE exp_moblogs.user_blog_id = '".UB_BLOG_ID."'";
			}
			else
			{
				$sql .= " WHERE exp_moblogs.is_user_blog = 'n'";
			}
			
			$query = $DB->query($sql);
    
            foreach ($query->result as $row)
            {
            		$r .= $DSP->input_select_option($row['moblog_id'], $row['moblog_full_name']);
            }
    
            $r .= $DSP->input_select_footer();    
            
            $r .= $DSP->div_c();
            
            $r .= $DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line('submit'), 'submit'));
            $DSP->body .= $r;
            return;
    	}
    	
    	
    	/** ---------------------------
    	/**  Fetch Weblogs
    	/** ---------------------------*/
    	
    	$weblog_array = array();
    	
    	$sql = "SELECT weblog_id, blog_title, site_label FROM exp_weblogs, exp_sites ";
				
		if (USER_BLOG !== FALSE)
		{
			$sql .= " WHERE exp_weblogs.weblog_id = '".UB_BLOG_ID."'";
		}
		else
		{
			$sql .= " WHERE exp_weblogs.is_user_blog = 'n'";
		}
		
		$sql .= " AND exp_weblogs.site_id = exp_sites.site_id ";
		
		if ($PREFS->ini('multiple_sites_enabled') !== 'y')
		{
			$sql .= "AND exp_weblogs.site_id = '1' ";
		}
		        
        $weblog_array['null'] = $LANG->line('weblog_id');
        
        $result = $DB->query($sql);

        if ($result->num_rows > 0)
        {            
            foreach ($result->result as $rez)
            {
                $weblog_array[$rez['weblog_id']] = ($PREFS->ini('multiple_sites_enabled') === 'y') ? $rez['site_label'].NBS.'-'.NBS.$rez['blog_title'] : $rez['blog_title'];
            }
        }
		
		/** ---------------------------
		/**  Fetch Galleries
		/** ---------------------------*/
    	        	
        if ($DB->table_exists('exp_galleries'))
        {
    		$sql = "SELECT gallery_id, gallery_full_name FROM exp_galleries ";
				
			if (USER_BLOG !== FALSE)
			{
				$sql .= " WHERE exp_galleries.user_blog_id = '".UB_BLOG_ID."'";
			}
			else
			{
				$sql .= " WHERE exp_galleries.is_user_blog = 'n'";
			}
			
	        $this->gallery_array['null'] = $LANG->line('gallery_id');
        
        	$result = $DB->query($sql);

			if ($result->num_rows > 0)
			{            
				foreach ($result->result as $rez)
				{
					$this->gallery_array[$rez['gallery_id']] = $rez['gallery_full_name'];
				}
			}
		}
		else
		{
			$this->gallery_array['null'] = $LANG->line('gallery_id');
		}
		
		/** ----------------------------- 
        /**  Assignable Gallery Authors
        /** -----------------------------*/
   		/*
   		$sql = "SELECT DISTINCT exp_members.member_id, exp_members.username, exp_members.screen_name
   				FROM exp_members, exp_module_member_groups, exp_modules
				WHERE exp_members.group_id = 1
				OR
				(exp_members.group_id = exp_module_member_groups.group_id
				AND 
				exp_modules.module_id = exp_module_member_groups.module_id
				AND
				exp_modules.module_name = 'Gallery')";
				
		$query = $DB->query($sql);
        
        if ($query->num_rows > 0)
        {
        	foreach ($query->result as $row)
       		{
       			$author = ($row['screen_name'] == '') ? $row['username'] : $row['screen_name'];
       		
       			$this->gallery_authors[]  = array($row['member_id'], $author);
       		}
   		}
   		*/
   		
   		$this->gallery_authors[$SESS->userdata['member_id']] = ($SESS->userdata['screen_name'] == '') ? $SESS->userdata['username'] : $SESS->userdata['screen_name'];
        
        
		/** ---------------------------
		/**  Fetch Upload Directories
		/** ---------------------------*/
		
		$upload_array = array('0' => $LANG->line('none'));
		
		if ($SESS->userdata['group_id'] == 1)
        {            
            $query = $DB->query("SELECT id, name FROM exp_upload_prefs WHERE is_user_blog = 'n' ORDER BY name");
        }
        else
        {         	
            $sql = "SELECT id, name FROM exp_upload_prefs ";
        
			if (USER_BLOG === FALSE) 
			{
				$query = $DB->query("SELECT upload_id FROM exp_upload_no_access WHERE member_group = '".$SESS->userdata['group_id']."'");
					  
				$idx = array();
				
				if ($query->num_rows > 0)
				{
					foreach ($query->result as $row)
					{	
						$idx[] = $row['upload_id'];
					}
				}
			
				$sql .= " WHERE is_user_blog = 'n' ";
				
				if (count($idx) > 0)
				{	
					foreach ($idx as $val)
					{
						$sql .= " AND id != '".$val."' ";
					}
				}
			}
			else
			{
				$sql .= " WHERE weblog_id = '".UB_BLOG_ID."' ";		
			}
        
        	$query = $DB->query($sql);
        }
        
        foreach ($query->result as $row)
        {
            $upload_array[$row['id']] = $row['name'];
        }    	
        
		/** ---------------------------
		/**  Options Matrix - Whoa.
		/** ---------------------------*/
		
		$change_js = 'onchange="changemenu(this.selectedIndex);"';
		
		$form_data = array(
						'moblog_full_name'			=> '',
						'moblog_short_name'			=> '',						
						'moblog_time_interval'		=> '15',
						'moblog_enabled'			=> array('r', array('y' => 'yes', 'n' => 'no'),'y'),
						'moblog_file_archive'		=> array('r', array('y' => 'yes', 'n' => 'no'),'n'),
						'moblog_type'				=> array('r', array('weblog' => 'weblog', 'gallery' => 'gallery'), 'weblog', ' onclick="switch_type();"'),
						
						'table2a'					=> array('sf','new_table','moblog_entry_settings'),
						'weblog_id'					=> array('s', $weblog_array,$change_js),
						'cat_id[]'					=> array('ms', array('none'=> $LANG->line('none'))),
						'field_id'					=> array('s', array('none'=> $LANG->line('none'))),
						'status'					=> array('s', array('none'=> $LANG->line('none'), 'open' => "open", 'closed' => "closed")),
						'author_id'					=> array('s', array('none'=> $LANG->line('none'),
																		$SESS->userdata['member_id'] => ($SESS->userdata['screen_name'] == '') ? $SESS->userdata['username'] : $SESS->userdata['screen_name'])),
						
						'moblog_sticky_entry'		=> array('r', array('y' => 'yes', 'n' => 'no'),'n'),
						'moblog_allow_overrides'	=> array('r', array('y' => 'yes', 'n' => 'no'),'y'),
						'moblog_upload_directory'	=> array('s', $upload_array),
						'moblog_template'			=> array('t', $this->default_template),
						
						'table2b'					=> array('sf','new_table','moblog_gallery_settings'),
						'gallery_id'				=> array('s', $this->gallery_array,$change_js),
						'gallery_cat'				=> array('s', array('null'=> $LANG->line('choose_category'))),	
						'gallery_status'			=> array('s', array('open' => "open", 'closed' => "closed"),'open'),
						'gallery_comments'			=> array('c', array('y'=> "yes"),'y'),	
						'gallery_author'			=> array('s', $this->gallery_authors),
						
						'table3'					=> array('sf','new_table','moblog_email_settings'),
						'moblog_email_type'			=> array('s', array('pop3' => 'pop3'),'pop3'),
						'moblog_email_address'		=> '',
						'moblog_email_server'		=> '',
						'moblog_email_login'		=> '',
						'moblog_email_password'		=> '',
						'moblog_subject_prefix'		=> 'moblog:',
						'moblog_auth_required'		=> array('r', array('y' => 'yes', 'n' => 'no'),'n'),
						'moblog_auth_delete'		=> array('r', array('y' => 'yes', 'n' => 'no'),'n'),
						'moblog_valid_from'			=> array('t', ''),
						'moblog_ignore_text'		=> array('t', ''),
						
						'table4'					=> array('sf','new_table','moblog_image_settings'),
						'moblog_image_width'		=> '0',
						'moblog_image_height'		=> '0',
						'moblog_resize_image'		=> array('r', array('y' => 'yes', 'n' => 'no'),'n'),
						'moblog_resize_width'		=> '0',
						'moblog_resize_height'		=> '0',
						'moblog_create_thumbnail'	=> array('r', array('y' => 'yes', 'n' => 'no'),'n'),
						'moblog_thumbnail_width'	=> '0',
						'moblog_thumbnail_height'	=> '0'
						);
					
        				

		/** -----------------------------
		/**  Secondary lines of text
		/** -----------------------------*/
		
		// This array lets us define sub-text that appears below any given preference defenition	
			
		$subtext = array(	
							'moblog_email_login'		=> array('data_encrypted'),
							'moblog_email_password'		=> array('data_encrypted'),
							'moblog_short_name' 		=> array('no_spaces'),
							'moblog_image_width' 		=> array('set_to_zero'),
							'moblog_image_height' 		=> array('set_to_zero'),
							'moblog_email_server'		=> array('server_example'),
							'moblog_time_interval'		=> array('interval_subtext','moblog_time_interval_subtext'),
							'moblog_ignore_text'		=> array('ignore_text_subtext'),
							'moblog_valid_from'			=> array('valid_from_subtext'),
							'moblog_file_archive'		=> array('file_archive_subtext'),
							'weblog_id'					=> array('weblog_id_subtext'),
							'moblog_auth_required'		=> array('moblog_auth_subtext'),
							'moblog_auth_delete'		=> array('moblog_auth_delete_subtext'),
							'moblog_subject_prefix'		=> array('subject_prefix_subtext'),
							'moblog_allow_overrides'	=> array('moblog_allow_overrides_subtext')
						);
						
		/** -----------------------------
		/**  Magic Javascript
		/** -----------------------------*/

		$r .= $this->filtering_menus('moblog_create');
		$r .= $this->switch_js('moblog_create');

		/** -----------------------------
		/**  Data
		/** -----------------------------*/
		
		$data = array(	'moblog_upload_directory' 	=> '1',
						'author_id'  				=> $SESS->userdata['member_id']);
		
		if (($basis != '' && $basis != 'none') || ($id != '' && is_numeric($id)))
		{
			$moblog_id = ($basis != '') ? $basis : $id;  
    		
    		$sql = "SELECT * FROM exp_moblogs
    				WHERE moblog_id = '{$moblog_id}'";
    			
    		if (USER_BLOG !== FALSE)
			{
				$sql .= " AND exp_moblogs.user_blog_id = '".UB_BLOG_ID."'";
			}
			else
			{
				$sql .= " AND exp_moblogs.is_user_blog = 'n'";
			}
    		
    		$query = $DB->query($sql);  
    		
    		
    		// Upload Directory Double-Check
    		
    		if (!isset($upload_array[$query->row['moblog_upload_directory']]))
    		{
				$results = $DB->query("SELECT name FROM exp_upload_prefs WHERE id='".$query->row['moblog_upload_directory']."'");
				
				if ($results->num_rows > 0)
				{
					$upload_array[$query->row['moblog_upload_directory']] = $results->row['name'];
					$form_data['moblog_upload_directory'] = array('s', $upload_array);
				}
    		}
    		
    		$data = array(
    					'moblog_short_name'			=> ($basis != '') ? $query->row['moblog_short_name'].'_copy' : $query->row['moblog_short_name'],
						'moblog_full_name'			=> ($basis != '') ? $query->row['moblog_full_name'].' - copy' : $query->row['moblog_full_name'],
						'weblog_id'					=> $query->row['moblog_weblog_id'],
						'cat_id[]'					=> explode('|',$query->row['moblog_categories']),
						'field_id'					=> $query->row['moblog_field_id'],
						'status'					=> $query->row['moblog_status'],
						'author_id'					=> $query->row['moblog_author_id'],
						'moblog_auth_required'		=> $query->row['moblog_auth_required'],
						'moblog_auth_delete'		=> $query->row['moblog_auth_delete'],
						'moblog_upload_directory'	=> $query->row['moblog_upload_directory'],
						'moblog_image_width'		=> $query->row['moblog_image_width'],
						'moblog_image_height'		=> $query->row['moblog_image_height'],
						'moblog_resize_image'		=> $query->row['moblog_resize_image'],
						'moblog_resize_width'		=> $query->row['moblog_resize_width'],
						'moblog_resize_height'		=> $query->row['moblog_resize_height'],
						'moblog_create_thumbnail'	=> $query->row['moblog_create_thumbnail'],
						'moblog_thumbnail_width'	=> $query->row['moblog_thumbnail_width'],
						'moblog_thumbnail_height'	=> $query->row['moblog_thumbnail_height'],
						'moblog_email_type'			=> $query->row['moblog_email_type'],
						'moblog_email_address'		=> base64_decode($query->row['moblog_email_address']),
						'moblog_email_server'		=> $query->row['moblog_email_server'],
						'moblog_email_login'		=> base64_decode($query->row['moblog_email_login']),
						'moblog_email_password'		=> base64_decode($query->row['moblog_email_password']),
						'moblog_subject_prefix'		=> $query->row['moblog_subject_prefix'],
						'moblog_valid_from'			=> str_replace('|',"\n",$query->row['moblog_valid_from']),
						'moblog_ignore_text'		=> $query->row['moblog_ignore_text'],
						'moblog_template'			=> $query->row['moblog_template'],
						'moblog_time_interval'		=> $query->row['moblog_time_interval'],
						'moblog_enabled'			=> $query->row['moblog_enabled'],
						'moblog_file_archive'		=> $query->row['moblog_file_archive'],
						'moblog_type'				=> ( ! isset($query->row['moblog_type']) OR $query->row['moblog_type'] == '') ? 'weblog' : $query->row['moblog_type'],
						'gallery_id'				=> ( ! isset($query->row['moblog_gallery_id']) OR $query->row['moblog_gallery_id'] == '') ? '1' : $query->row['moblog_gallery_id'],
						'gallery_cat'				=> ( ! isset($query->row['moblog_gallery_category']) OR $query->row['moblog_gallery_category'] == '') ? '' : $query->row['moblog_gallery_category'],
						'gallery_status'			=> ( ! isset($query->row['moblog_gallery_status']) OR $query->row['moblog_gallery_status'] == '') ? 'open' : $query->row['moblog_gallery_status'],
						'gallery_comments'			=> ( ! isset($query->row['moblog_gallery_comments']) OR $query->row['moblog_gallery_comments'] == '') ? 'y' : $query->row['moblog_gallery_comments'],
						'gallery_author'			=> ( ! isset($query->row['moblog_gallery_author']) OR $query->row['moblog_gallery_author'] == '') ? $SESS->userdata['member_id'] : $query->row['moblog_gallery_author'],
						
						'moblog_allow_overrides'	=> ( ! isset($query->row['moblog_allow_overrides']) OR $query->row['moblog_allow_overrides'] == '') ? 'y' : $query->row['moblog_allow_overrides'],
						'moblog_sticky_entry'		=> ( ! isset($query->row['moblog_sticky_entry']) OR $query->row['moblog_sticky_entry'] == '') ? 'n' : $query->row['moblog_sticky_entry']
						);
						
				
						
				/** ------------------------------
				/**  Modify Form Creation Data
				/** ------------------------------*/
				
				if (isset($query->row['moblog_gallery_id']) OR (isset($query->row['moblog_gallery_id']) && $query->row['moblog_gallery_id'] == '0'))
				{
					$new_array = array('null' =>  $LANG->line('none'));
					
					foreach($this->gallery_cats as $val)
					{
						if (is_array($val) && $val['0'] == $query->row['moblog_gallery_id'])
						{
							$new_array[$val['1']] = (str_replace(" ","&nbsp;",$val['2']));
						}
					}
					
					$form_data['gallery_cat'] = array('s', $new_array);
				}
				
				if ($query->row['moblog_weblog_id'] != 0)
				{
				
    				foreach($this->cat_array as $key => $val)
					{
						if (is_array($val) AND ! in_array($val['0'], explode('|', $this->blog_array[$query->row['moblog_weblog_id']]['1'])))
						{
							unset($this->cat_array[$key]);
						}
					}
					
					if (count($this->cat_array > 0))
					{
						$new_array = array('all'=> $LANG->line('all'));
					}
					
					$new_array = array('none'=> $LANG->line('none'));					
					$i=0;
					
            		foreach ($this->cat_array as $ckey => $cat)
            		{
		            	if ($ckey-1 < 0 OR ! isset($this->cat_array[$ckey-1]))
    		        	{
        		    		$new_array['NULL_'.$i] = '-------';
            			}
            			
            			$new_array[$cat['1']] = (str_replace("!-!","&nbsp;",$cat['2']));

		            	if (isset($this->cat_array[$ckey+1]) && $this->cat_array[$ckey+1]['0'] != $cat['0'])
    		        	{
        		    		$new_array['NULL_'.$i] = '-------';
            			}
            			$i++;
					}
					
					$form_data['cat_id[]'] = array('ms', $new_array);
					$new_array = array('none'=> $LANG->line('none'), 'open' => "open", 'closed' => "closed" );
										
    				foreach($this->status_array as $val)
					{
						if (is_array($val) && $val['0'] == $this->blog_array[$query->row['moblog_weblog_id']]['2'])
						{
							$new_array[$val['1']] = $val['1'];
						}
					}
					
					if (!in_array($query->row['moblog_status'],$new_array))
					{
						$new_array[$query->row['moblog_status']] = $query->row['moblog_status'];
					}
						
					$form_data['status'] = array('s', $new_array);				
					$new_array = array('none'=> $LANG->line('none'));
					
    				foreach($this->field_array as $val)
					{
						if (is_array($val) && $val['0'] == $this->blog_array[$query->row['moblog_weblog_id']]['3'])
						{
							$new_array[$val['1']] = $val['2'];
						}
					}
					
					$form_data['field_id'] = array('s', $new_array);				
					$new_array = array('none'=> $LANG->line('none'));
					
    				foreach($this->author_array as $val)
					{
						if (is_array($val) && $val['0'] == $query->row['moblog_weblog_id'])
						{
							$new_array[$val['1']] = $val['2'];
						}
					}
					
					$form_data['author_id'] = array('s', $new_array);	
				}		
    		}
    		
    	/** -----------------------------
    	/**  Create the form
    	/** -----------------------------*/
    	
    	$r .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=moblog'.AMP.'P=update', 'name' => 'moblog_create', 'id' => 'moblog_create'));
    	
    	if ($id != '' && is_numeric($id))
    	{
    		$r .= $DSP->input_hidden('id',$id);
    	}
    	
		$r .= $this->create_form($form_data,$data,$subtext);
		
		$button = ($id != '' && is_numeric($id)) ? 'update' : 'submit';
		
		if ($ping_servers = $this->fetch_ping_servers(( ! isset($query->row['moblog_ping_servers'], $button) ? '' : $query->row['moblog_ping_servers'])))
		{
			$r .= $DSP->div('', '', 'table5');

			$r .= $DSP->table('tableBorder', '0', '', '100%');
			$r .= $DSP->tr();
			$r .= $DSP->td('tableHeadingAlt', '', '2');
			$r .= $LANG->line('ping_servers');
			$r .= $DSP->td_c();
			$r .= $DSP->tr_c();
			
			$r .= $DSP->tr();
			$r .= $DSP->td('tableCellOne', '50%', '', '', 'top');
			$r .= $DSP->div('defaultBold');
			$r .= $LANG->line('ping_sites', 'ping');
			$r .= $DSP->div_c();
			$r .= $DSP->td_c();
			$r .= $DSP->td('tableCellOne', '50%', '');
			
			$r .= $ping_servers;
			
			$r .= $DSP->td_c();
			$r .= $DSP->tr_c();	
			
			$r .= $DSP->table_c();	
			
			$r .= $DSP->div_c();
		}
		
        $r .= $DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line($button), 'submit'));
		    
    	$DSP->body .= $r;    	
    
    }
    /* END */
    
    
    /** -----------------------------
    /**  Create Form Automagically
    /** -----------------------------*/
    
    function create_form($fdata, $data, $subtext)
    {
    	global $DSP, $LANG;				

		$r  =	$DSP->div('', '', 'table1');
		
		$r .=	$DSP->table('tableBorder', '0', '', '100%');
		$r .=	$DSP->tr();
		$r .=   $DSP->td('tableHeadingAlt', '', '2');
		$r .=   $LANG->line('moblog_general_settings');
		$r .=   $DSP->td_c();
		$r .=	$DSP->tr_c();
		
		$i = 0;
		
		/** -----------------------------
		/**  Blast through the array
		/** -----------------------------*/
				
		foreach ($fdata as $key => $val)				
		{		
			$style = ($i % 2) ? 'tableCellOne' : 'tableCellTwo'; $i++;
			
			$default_data = (is_array($val)) ? '' : $val;
			
			$data[$key] = ( ! isset($data[$key])) ? $default_data : $data[$key];
			
			if (!is_array($val) || $val['0'] != 'sf')
			{
				$r .=	$DSP->tr();
				
				// If the form type is a textarea, we'll align the text at the top, otherwise, we'll center it
			
				if (is_array($val) && ($val['0'] == 't' OR $val['0'] == 'ms' OR ($val['0'] == 'c' && sizeof($val['1']) > 1)))
				{
					$r .= $DSP->td($style, '50%', '', '', 'top');
				}
				else
				{
					$r .= $DSP->td($style, '50%', '');
				}
				
				/** -----------------------------
				/**  Preference heading
				/** -----------------------------*/
			
				$r .= $DSP->div('defaultBold');
						
				$label = ( ! is_array($val)) ? $key : '';
				
				// Fix for array form variables like cat_id[]
				// Such names to do no work well with the
				// translation utility sadly.
				
				if (($LANG->line($key) === false || $LANG->line($key) == '') && strpos($key, '[]') !== false)
				{
					$tkey = str_replace('[]','',$key);
					$r .= $LANG->line($tkey, $label);
				}
				else
				{
					$r .= $LANG->line($key, $label);
				}
	
				$r .= $DSP->div_c();
			
			
				/** -----------------------------
				/**  Preference sub-heading
				/** -----------------------------*/
			
				if (isset($subtext[$key]))
				{
					foreach ($subtext[$key] as $sub)
					{
						$r .= $DSP->qdiv('subtext', $LANG->line($sub));
					}
				}
			
				$r .= $DSP->td_c();
			
				/** -----------------------------
				/**  Preference value
				/** -----------------------------*/
				
				$r .= $DSP->td($style, '50%', '');
			}
			
				if (is_array($val))
				{
					/** -----------------------------
					/**  Drop-down menus
					/** -----------------------------*/
								
					if ($val['0'] == 's' || $val['0'] == 'ms')
					{
						$multi = ($val['0'] == 'ms') ? "class='multiselect' style='width:100%;' size='8' multiple='multiple'" : "class='select'";
					
						if (isset($val['2']))
						{
							$r .= "<select name='{$key}' $multi ".$val['2'].">\n";
						}
						else
						{
							$r .= "<select name='{$key}' $multi >\n";
						}
						
						foreach ($val['1'] as $k => $v)
						{
							if (substr($k, 0, 5) == 'NULL_')
							{
								$r .= $DSP->input_select_option('', $v);
								continue;
							}

							if ($val['0'] == 's' || ! is_array($data[$key]))
							{
								$selected = ($k == $data[$key]) ? 1 : '';
							}
							elseif(is_array($data[$key]))
							{
								$selected = (in_array($k,$data[$key])) ? 1 : '';								
							}
						
							$name = ($LANG->line($v) == false OR $key == 'weblog_id') ? $v : $LANG->line($v);
						
							$r .= $DSP->input_select_option($k, $name, $selected);
						}
						
						$r .= $DSP->input_select_footer();
						
					} 
					elseif ($val['0'] == 'r')
					{
						/** -----------------------------
						/**  Radio buttons
						/** -----------------------------*/
						
						$data[$key] = ($data[$key] == '') ? stripslashes("{$val['2']}") : stripslashes("{$data[$key]}");
					
						foreach ($val['1'] as $k => $v)
						{
							$selected = ($k == $data[$key]) ? 1 : '';
						
							$r .= $LANG->line($v).$DSP->nbs();
							$r .= $DSP->input_radio($key, $k, $selected, ( ! isset($val['3'])) ? '' : $val['3']).$DSP->nbs(3);
						}					
					}
					elseif ($val['0'] == 'c')
					{
						/** -----------------------------
						/**  Checkboxes
						/** -----------------------------*/
						
						$data[$key] = ($data[$key] == '') ? stripslashes("{$val['2']}") : stripslashes("{$data[$key]}");
					
						foreach ($val['1'] as $k => $v)
						{
							$selected = ($k == $data[$key]) ? 1 : '';
							
							if (sizeof($val['1']) == 1)
							{
								$r .= $DSP->input_checkbox($key, $k, $selected);
							}
							else
							{
								$r .= $DSP->qdiv('publishPad', $DSP->input_checkbox($key, $k, $selected).' '.$LANG->line($v));
							}							
						}					
					}
					elseif ($val['0'] == 't')
					{
						/** -----------------------------
						/**  Textarea fileds
						/** -----------------------------*/
						
						// The "kill_pipes" index instructs us to 
						// turn pipes into newlines
						
						$data[$key] = ($data[$key] == '') ? stripslashes("{$val['1']}") : stripslashes("{$data[$key]}");
						
						if (isset($val['2']['kill_pipes']) AND $val['2']['kill_pipes'] === TRUE)
						{
							$text	= '';
							
							foreach (explode('|', $data[$key]) as $exp)
							{
								$text .= $exp.NL;
							}
						}
						else
						{
							$text = $data[$key];
						}
												
						$rows = (isset($val['2']['rows'])) ? $val['2']['rows'] : '15';
						
						$r .= $DSP->input_textarea($key, $text, $rows);
						
					}
					elseif ($val['0'] == 'f' || $val['0'] == 'sf')
					{
						switch($val['1'])
						{
							case 'new_table' :  
							
								$i = 0;
								// Close current tables
								$r .= $DSP->table_c();	
								
								$r .= $DSP->div_c();
								
								// Open new table
								$r .= $DSP->div('', '', $key);
																
								$r .= $DSP->table('tableBorder', '0', '', '100%');
								$r .= $DSP->tr();
								$r .= $DSP->td('tableHeadingAlt', '', '2');
								$r .= $LANG->line($val['2']);
								$r .= $DSP->td_c();
								$r .= $DSP->tr_c();	
						
							break;
						}
					}
				}
				else
				{
					/** -----------------------------
					/**  Text input fields
					/** -----------------------------*/
				
					$r .= $DSP->input_text($key, stripslashes($data[$key]), '20', '120', 'input', '100%');
				}
				
			$r .= $DSP->td_c();
			$r .= $DSP->tr_c();
		}
				
		$r .= $DSP->table_c();
		$r .= $DSP->div_c();
		
		return $r;
    }
    /* END */
    
    
    
	/** -----------------------------------------------------------
    /**  JavaScript filtering code
    /** -----------------------------------------------------------*/
    // This function writes some JavaScript functions that
    // are used to switch the various pull-down menus in the
    // CREATE page
    //-----------------------------------------------------------

    function filtering_menus($form_name)
    { 
        global $DSP, $LANG, $SESS, $FNS, $DB, $PREFS, $REGX;
     
        // In order to build our filtering options we need to gather 
        // all the weblogs, categories and custom statuses
        
        /** ----------------------------- 
        /**  Allowed Weblogs
        /** -----------------------------*/
        
		$allowed_blogs = $FNS->fetch_assigned_weblogs(TRUE);

		if (count($allowed_blogs) > 0)
		{
			// Fetch weblog titles
			
			$sql = "SELECT blog_title, weblog_id, cat_group, status_group, field_group FROM exp_weblogs";
					
			if ( ! $DSP->allowed_group('can_edit_other_entries') || $SESS->userdata['weblog_id'] != 0)
			{
				$sql .= " WHERE weblog_id IN (";
			
				foreach ($allowed_blogs as $val)
				{
					$sql .= "'".$val."',"; 
				}
				
				$sql = substr($sql, 0, -1).')';
			}
			else
			{
				$sql .= " WHERE is_user_blog = 'n'";
			}
			
			$sql .= " ORDER BY blog_title";
			
			$query = $DB->query($sql);
					
			foreach ($query->result as $row)
			{
				$this->blog_array[$row['weblog_id']] = array(str_replace('"','',$row['blog_title']), $row['cat_group'], $row['status_group'], $row['field_group']);
			}        
        }
        
		/** ----------------------------- 
		/**  Category Tree
		/** -----------------------------*/
		
		$sql = "SELECT exp_categories.group_id, exp_categories.parent_id, exp_categories.cat_id, exp_categories.cat_name 
				FROM exp_categories, exp_category_groups
				WHERE exp_category_groups.group_id = exp_categories.group_id";
		
		if ($SESS->userdata['weblog_id'] != 0)
		{
			$sql .= " AND exp_categories.group_id = '".$query->row['cat_id']."'";
		}
		else
		{
			$sql .= " AND exp_category_groups.is_user_blog = 'n'";
		}
		
		$sql .= " ORDER BY group_id, parent_id, cat_name";
		
		$query = $DB->query($sql);
					
		if ($query->num_rows > 0)
		{
			foreach ($query->result as $row)
			{			
				$categories[] = array($row['group_id'], $row['cat_id'], $REGX->entities_to_ascii($row['cat_name']), $row['parent_id']);
			}

			foreach($categories as $key => $val)
			{
				if (0 == $val['3']) 
				{
					$this->cat_array[] = array($val['0'], $val['1'], $val['2']);
					$this->category_subtree($val['1'], $categories, $depth=1);
				}
			}
		} 

        /** ----------------------------- 
        /**  Entry Statuses
        /** -----------------------------*/
            
        $query = $DB->query("SELECT group_id, status FROM exp_statuses ORDER BY status_order");
        
        if ($query->num_rows > 0)
        {
        	foreach ($query->result as $row)
        	{
            	$this->status_array[]  = array($row['group_id'], $row['status']);
        	}
        }
        
        /** ----------------------------- 
        /**  Custom Weblog Fields
        /** -----------------------------*/
        
        /* -------------------------------------
		/*  Hidden Configuration Variable
		/*  - moblog_allow_nontextareas => Removes the textarea only restriction
		/*	for custom fields in the moblog module (y/n)
		/* -------------------------------------*/
        
        $xsql = ($PREFS->ini('moblog_allow_nontextareas') == 'y') ? "" : " WHERE exp_weblog_fields.field_type = 'textarea' ";
        
        $query = $DB->query("SELECT group_id, field_label, field_id 
        					 FROM exp_weblog_fields 
        					 {$xsql} ORDER BY field_label");
        
        if ($query->num_rows > 0)
        {
        	foreach ($query->result as $row)
        	{
            	$this->field_array[]  = array($row['group_id'], $row['field_id'], str_replace('"','',$row['field_label']));
        	}
		}
		
		/** ----------------------------- 
        /**  SuperAdmins
        /** -----------------------------*/
        
        $sql = "SELECT exp_members.member_id, exp_members.username, exp_members.screen_name 
				FROM exp_members
				WHERE exp_members.group_id = '1'"; 

        $query = $DB->query($sql);
        
        foreach ($query->result as $row)
       	{
       		$author = ($row['screen_name'] == '') ? $row['username'] : $row['screen_name'];
       		
       		foreach($this->blog_array as $key => $value)
       		{
       			$this->author_array[]  = array($key, $row['member_id'], str_replace('"','',$author));
       		}
       	}
		
		/** ----------------------------- 
        /**  Assignable Weblog Authors
        /** -----------------------------*/
        
		$sql = "SELECT exp_members.member_id, exp_weblogs.weblog_id, exp_members.group_id, exp_members.username, exp_members.screen_name 
				FROM exp_weblogs, exp_members, exp_weblog_member_groups 
				WHERE (exp_weblog_member_groups.weblog_id = exp_weblogs.weblog_id OR exp_weblog_member_groups.weblog_id IS NULL) 
				AND exp_members.group_id = exp_weblog_member_groups.group_id"; 

        $query = $DB->query($sql);
        
        if ($query->num_rows > 0)
        {
        	foreach ($query->result as $row)
       		{
       			$author = ($row['screen_name'] == '') ? $row['username'] : $row['screen_name'];
       		
       			$this->author_array[]  = array($row['weblog_id'], $row['member_id'], str_replace('"','',$author));
       		}
   		}
   		
   		
   		/** ----------------------------- 
        /**  Gallery Categories
        /** -----------------------------*/
        
        if ($DB->table_exists('exp_galleries'))
        {
        	$this->gallery_category_tree();
        }
        
        //print_r($this->gallery_cats);
        
        // Build the JavaScript needed for the dynamic pull-down menus
        // We'll use output buffering since we'll need to return it
        // and we break in and out of php
        
        ob_start();
                
?>

<script type="text/javascript">
<!--

var firstcategory = 0;
var firststatus = 0;
var firstfield = 0;
var firstauthor = 0;
var firstgcat = 0;

function changemenu(index)
{ 

  var categories   = new Array();
  var statuses     = new Array();
  var fields       = new Array();
  var authors      = new Array();
  var gallery_cats = new Array();
  
  var i = firstcategory;
  var j = firststatus;
  var k = firstfield;
  var l = firstauthor;
  var m = firstgcat;
  
  var blogs   = 'null';
  var gallery = 'null';
  
  if (document.<?php echo $form_name; ?>.moblog_type[1].checked)
  {
     gallery = document.<?php echo $form_name; ?>.gallery_id.options[index].value;
  }
  else
  {
     blogs   = document.<?php echo $form_name; ?>.weblog_id.options[index].value;
  }
  
   with(document.<?php echo $form_name; ?>.elements['gallery_id'])
   {
       <?php
                        
        foreach ($this->gallery_array as $key => $val)
        {
        	$any = 0;        
        ?>
        
        if (gallery == "<?php echo $key ?>")
        {<?php
         
            if (count($this->gallery_cats) > 0)
            {            
                foreach ($this->gallery_cats as $k => $v)
                {
                	//$v['2'] = str_replace('&nbsp;',' ',$v['2']);
                    if ($v['0'] == $key)
                    {
                    	$any++;
                    	echo "\n";                    
            // Note: this kludgy indentation is so that the JavaScript will look nice when it's renedered on the page        
            ?>
			gallery_cats[m] = new Option("<?php echo $v['2'];?>", "<?php echo $v['1'];?>"); m++; <?php
                    }
                }
            }
            
        if ($any == '0')
        { 
        	echo "\n";
        ?>
			gallery_cats[m] = new Option("<?php echo $LANG->line('none'); ?>", "0"); m++;
        <?php
        } ?>
		}
        <?php
        }?>
	}
  
    with(document.<?php echo $form_name; ?>.elements['cat_id[]'])
    {
        if (blogs == "null")
        {    
            categories[i] = new Option("<?php echo $LANG->line('none'); ?>", "none"); i++;
    
            statuses[j] = new Option("<?php echo $LANG->line('none'); ?>", "none"); j++;
            
            fields[k] = new Option("<?php echo $LANG->line('none'); ?>", "none"); k++;
            
            authors[l] = new Option("<?php echo $LANG->line('none'); ?>", "none"); l++;
        }
        
       <?php
                        
        foreach ($this->blog_array as $key => $val)
        {
        
        ?>
        
        if (blogs == "<?php echo $key ?>")
        {<?php
         
            if (count($this->cat_array) > 0)
            {
            	$last_group = 0;
            
                foreach ($this->cat_array as $k => $v)
                {
                    if (in_array($v['0'], explode('|', $val['1'])))
                    {
                    	if (! isset($set))
                    	{
            				echo 'categories[i] = new Option("'.$LANG->line('all').'", ""); i++;'; 
                            echo 'categories[i] = new Option("'.$LANG->line('none').'", "none"); i++;'."\n";              				
            				$set = 'y';
            			}

                    	if ($last_group == 0 OR $last_group != $v['0'])
                    	{?>
            categories[i] = new Option("-------", ""); i++; <?php echo "\n";
            				$last_group = $v['0'];
                    	}
             
         
            // Note: this kludgy indentation is so that the JavaScript will look nice when it's renedered on the page        
            ?>
            categories[i] = new Option("<?php echo addslashes($v['2']);?>", "<?php echo $v['1'];?>"); i++; <?php echo "\n";
                    }
                }
                if ( ! isset($set))
                {
                 echo 'categories[i] = new Option("'.$LANG->line('none').'", "none"); i++;'."\n"; 
                }
				unset($set);                

			}
			
              
            ?>
            
            statuses[j] = new Option("<?php echo $LANG->line('none'); ?>", "none"); j++; <?php
            if (count($this->status_array) > 0)
            {
                foreach ($this->status_array as $k => $v)
                {
                    if ($v['0'] == $val['2'])
                    {
                    
					$status_name = ($v['1'] == 'closed' OR $v['1'] == 'open') ?  $LANG->line($v['1']) : $v['1'];
            ?> 
            statuses[j] = new Option("<?php echo $status_name; ?>", "<?php echo $v['1']; ?>"); j++; <?php
                    }
                }
            }
            else
            {
            ?>
            statuses[j] = new Option("<?php echo $LANG->line('open'); ?>", "open"); j++; 
            statuses[j] = new Option("<?php echo $LANG->line('closed'); ?>", "closed"); j++; 
            <?php
            }
             
            ?>             
            
            fields[k] = new Option("<?php echo $LANG->line('none'); ?>", "none"); k++; <?php echo "\n";
         
            if (count($this->field_array) > 0)
            {
                foreach ($this->field_array as $k => $v)
                {
                    if ($v['0'] == $val['3'])
                    {
                    
            // Note: this kludgy indentation is so that the JavaScript will look nice when it's renedered on the page        
            ?>
            fields[k] = new Option("<?php echo $v['2'];?>", "<?php echo $v['1'];?>"); k++; <?php echo "\n";
                    }
                }
                echo "\n";
            }                    
             
            ?>             
            
            authors[l] = new Option("<?php echo $LANG->line('none'); ?>", "none"); l++; <?php echo "\n";              
            
         
            if (count($this->author_array) > 0)
            {
            	$inserted_authors = array();
            	
                foreach ($this->author_array as $k => $v)
                {
                    if ($v['0'] == $key && ! in_array($v['1'],$inserted_authors))
                    {
                    	$inserted_authors[] = $v['1'];
                                        
            // Note: this kludgy indentation is so that the JavaScript will look nice when it's renedered on the page        
            ?>
            authors[l] = new Option("<?php echo $v['2'];?>", "<?php echo $v['1'];?>"); l++; <?php echo "\n";
                    }
                }
            }
              
            ?>

        } // END if blogs
            
        <?php
         
        } // END OUTER FOREACH
         
        ?> 
        
        spaceString = eval("/!-!/g");
        
        with (document.<?php echo $form_name; ?>.elements['gallery_cat'])
        {
            for (m = length-1; m >= firstgcat; m--)
                options[m] = null;
            
            for (m = firstgcat; m < gallery_cats.length; m++)
            {
                options[m] = gallery_cats[m];
                options[m].text = options[m].text.replace(spaceString, String.fromCharCode(160));
            }
            
            options[0].selected = true;
        }
        
        with (document.<?php echo $form_name; ?>.elements['cat_id[]'])
        {
            for (i = length-1; i >= firstcategory; i--)
                options[i] = null;
            
            for (i = firstcategory; i < categories.length; i++)
            {
                options[i] = categories[i];
                options[i].text = options[i].text.replace(spaceString, String.fromCharCode(160));
            }
            
            options[0].selected = true;
        }
        
        with (document.<?php echo $form_name; ?>.field_id)
        {
            for (i = length-1; i >= firstfield; i--)
                options[i] = null;
            
            for (i = firstfield; i < fields.length; i++)
                options[i] = fields[i];
            
            options[0].selected = true;
        }
        
        with (document.<?php echo $form_name; ?>.status)
        {
            for (i = length-1; i >= firststatus; i--)
                options[i] = null;
            
            for (i = firststatus;i < statuses.length; i++)
                options[i] = statuses[i];
            
            options[0].selected = true;
        }
        
        with (document.<?php echo $form_name; ?>.author_id)
        {
            for (i = length-1; i >= firstauthor; i--)
                options[i] = null;
            
            for (i = firstauthor;i < authors.length; i++)
            {
                options[i] = authors[i];
                if (options[i].value == <?php echo $SESS->userdata['member_id']; ?>)
                {
                	options[i].selected = true;          
                }
            }
        }
    }
}

//--></script>
        
<?php
                
        $javascript = ob_get_contents();
        
        ob_end_clean();
        
        return $javascript;
     
    }
    /* END */
    
    
    
    
	/** -----------------------------------------------------------
    /**  JavaScript Switch code
    /** -----------------------------------------------------------*/
    // Changed the Preference Options Depending on
    // if this is for a Weblog or Gallery
    //-----------------------------------------------------------

    function switch_js($form_name)
    { 
        
        ob_start();
                
?>

<script type="text/javascript">
<!--


function switch_type()
{ 
	if (document.<?php echo $form_name; ?>.moblog_type[1].checked)
	{
		document.getElementById('table2a').style.display = "none";
		document.getElementById('table2b').style.display = "block";
		document.getElementById('table4').style.display = "none";
		document.getElementById('table5').style.display = "none";
	}
	else
	{
		document.getElementById('table2a').style.display = "block";
		document.getElementById('table2b').style.display = "none";
		document.getElementById('table4').style.display = "block";
		document.getElementById('table5').style.display = "block";
	}
}

//--></script>
        
<?php
                
        $javascript = ob_get_contents();
        
        ob_end_clean();
        
        return $javascript;
     
    }
    /* END */
    
    
    /** --------------------------------
    /**  Category Sub-tree
    /** --------------------------------*/
	function category_subtree($cat_id, $categories, $depth)
    {
        global $DSP, $IN, $DB, $REGX, $LANG;

        $spcr = '!-!';
                  
        $indent = $spcr.$spcr.$spcr.$spcr;
    
        if ($depth == 1)	
        {
            $depth = 4;
        }
        else 
        {	                            
            $indent = str_repeat($spcr, $depth).$indent;
            
            $depth = $depth + 4;
        }
        
        $sel = '';
            
        foreach ($categories as $key => $val) 
        {
            if ($cat_id == $val['3']) 
            {
                $pre = ($depth > 2) ? $spcr : '';
                
              	$this->cat_array[] = array($val['0'], $val['1'], $pre.$indent.$spcr.$val['2']);
                                
                $this->category_subtree($val['1'], $categories, $depth);
            }
        }
    }
    /* END */
    
    
    
     /** ------------------------------
    /**  Gallery Category tree
    /** ------------------------------*/

    function gallery_category_tree($sort_order = 'a')
    {  
        global $DB;
    
        // Fetch category groups
                
		$sql = "SELECT cat_name, cat_id, parent_id, gallery_id FROM exp_gallery_categories 
				ORDER BY gallery_id, parent_id, cat_name";
                             
        $query = $DB->query($sql);
              
        if ($query->num_rows == 0)
        {
            return false;
        }     
        
        // Assign the query result to a multi-dimensional array
                    
        foreach($query->result as $row)
        {        
            $cat_array[$row['cat_id']]  = array($row['gallery_id'], $row['cat_name'], $row['parent_id']);
        }
                
        // Build our output...
                 
        foreach($cat_array as $key => $val) 
        {        
            if (0 == $val['2']) 
            {
				$this->gallery_cats[] = array($val['0'], $key, $val['1']);				
					
				$this->gallery_cat_subtree($key, $cat_array, $depth=0);
            }
        } 
    }
    /* END */
    
    
    
    /** --------------------------------------
    /**  Gallery Category sub-tree
    /** --------------------------------------*/
        
    function gallery_cat_subtree($cat_id, $cat_array, $depth)
    {
    	$spcr = '!-!';
                  
        $indent = $spcr.$spcr.$spcr.$spcr;
        
		if ($depth == 0)	
        {
            $depth = 1;
        }
        else 
        {	                            
            $indent = str_repeat($spcr, $depth+1).$indent;
            
            $depth = $depth + 4;
        }
                
        foreach ($cat_array as $key => $val) 
        {				
            if ($cat_id == $val['2']) 
            {
                $pre = ($depth > 2) ? "&nbsp;" : '';				
										
                $this->gallery_cats[] = array($val['0'], $key, $pre.$indent.$spcr.$val['1']);
        
				$this->gallery_cat_subtree($key, $cat_array, $depth);    
            }
        }
    }
    /* END */
    


    /** -------------------------
    /**  View Moblog
    /** -------------------------*/
    
    function view_moblogs()
    {
        global $IN, $DSP, $LANG, $FNS, $DB, $LOC, $PREFS;
                
        if ( ! $rownum = $IN->GBL('rownum', 'GP'))
        {        
            $rownum = 0;
        }
        
        $perpage = 100;

		$qm = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';
                        
        $DSP->title = $LANG->line('moblog');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=moblog', $LANG->line('moblog'));
        $DSP->crumb .= $DSP->crumb_item($LANG->line('view_moblogs'));    

        $DSP->body = $DSP->heading($LANG->line('view_moblogs'));            
        
        
        
        
        
        
        
        
        
        $sql = "SELECT count(*) AS count FROM exp_moblogs";
    			
    		if (USER_BLOG !== FALSE)
		{
			$sql .= " WHERE exp_moblogs.user_blog_id = '".UB_BLOG_ID."'";
		}
		else
		{
			$sql .= " WHERE exp_moblogs.is_user_blog = 'n'";
		}
		
		$query = $DB->query($sql);

        if ($query->row['count'] == 0)
        {
            $DSP->body .= $DSP->qdiv('itemWrapper', $LANG->line('no_moblogs'));      

            return;
        }  
        
        
        
        
        
        $total = $query->row['count'];
        
        $DSP->body .= $DSP->qdiv('itemWrapper', $LANG->line('total_moblogs').NBS.NBS.$total);
        
        /** -----------------------------
        /**  Find Moblogs - LOOK! It checks for USER_BLOG!!!
        /** -----------------------------*/
        
        $sql = "SELECT moblog_full_name, moblog_id FROM exp_moblogs";
    			
    		if (USER_BLOG !== FALSE)
		{
			$sql .= " WHERE exp_moblogs.user_blog_id = '".UB_BLOG_ID."'";
		}
		else
		{
			$sql .= " WHERE exp_moblogs.is_user_blog = 'n'";
		}
        		
        $sql .= " ORDER BY moblog_full_name asc LIMIT $rownum, $perpage";
        					
        $query = $DB->query($sql);
        
        if ($query->num_rows == 0)
        {
            $DSP->body .= $DSP->qdiv('itemWrapper', $LANG->line('no_moblogs'));      

            return;
        }  

        $DSP->body	.=	$DSP->toggle();
                
        $DSP->body	.=	$DSP->form_open(array('action' => 'C=modules'.AMP.'M=moblog'.AMP.'P=delete_confirm', 'name' => 'target', 'id' => 'target'));
    
        $DSP->body	.=	$DSP->table('tableBorder', '0', '0', '100%').
						$DSP->tr().
						$DSP->table_qcell('tableHeading', 
											array(
													$LANG->line('moblog_view'),
													$LANG->line('moblog_modify'),
													$DSP->input_checkbox('toggleflag', '', '', " onclick=\"toggle(this);\"").NBS.$LANG->line('delete').NBS.NBS
												 )
											).
						$DSP->tr_c();
		
		$i = 0;

		foreach ($query->result as $row)
		{				
			$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
                      
            $DSP->body .= $DSP->tr();
            
            $DSP->body .= $DSP->table_qcell($style, '<b>'.$row['moblog_full_name'].'</b>', '50%');
            $DSP->body .= $DSP->table_qcell($style, $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=moblog'.AMP.'P=modify'.AMP.'id='.$row['moblog_id'],$LANG->line('moblog_modify')), '30%');
            $DSP->body .= $DSP->table_qcell($style, $DSP->input_checkbox('toggle[]', $row['moblog_id']), '20%');
											
			$DSP->body .= $DSP->tr_c();
		}
		        
		$pager = $DSP->pager(
                            BASE.AMP.'C=modules'.AMP.'M=moblog'.AMP.'P=view_moblogs',
                            $total,
                            $perpage,
                            $rownum,
                            'rownum'
                          );
		
		
		$style = 'itemWrapper';

		$DSP->body	.= $DSP->tr();
		$DSP->body	.= $DSP->table_qcell($style, $pager);             
		$DSP->body	.= $DSP->table_qcell($style, NBS);             
		$DSP->body	.= $DSP->table_qcell($style, $DSP->input_submit($LANG->line('delete')));             
		$DSP->body	.= $DSP->tr_c();

        $DSP->body	.=	$DSP->table_c(); 
        $DSP->body	.=	$DSP->form_close();     
     
    }
    /* END */
    


    /** -------------------------
    /**  Update Moblog
    /** -------------------------*/
    
    function update_moblog()
    {
		global $IN, $DB, $DSP, $LANG, $OUT, $REGX;
    	
		$required = array(	'moblog_full_name', 
							'moblog_short_name',
							'moblog_auth_required',
							'moblog_auth_delete',
							'moblog_email_type',
							'moblog_email_address',
							'moblog_email_server',
							'moblog_email_login',
							'moblog_email_password',
							'moblog_time_interval',
							'moblog_enabled');
							
		if (isset($_POST['moblog_type']) && $_POST['moblog_type'] == 'gallery')
		{
			$additional_required = array('gallery_id',
										  'gallery_cat',
										  'gallery_status',
										  'gallery_author');
		}
		else
		{
			$additional_required = array('moblog_upload_directory',
										  'moblog_resize_image',
										  'moblog_create_thumbnail');    	
		}
		
		$required = array_merge($required, $additional_required);
		
		foreach($required as $value)
		{
			if ( ! isset($_POST[$value]) OR $_POST[$value] == '' OR $_POST[$value] == 'null' )
			{
				$message = str_replace('%e',$LANG->line($value), $LANG->line('moblog_missing_field'));
				return $OUT->show_user_error('submission', array($message));
			}
		}
    	
    	// Short name check 
   		if (preg_match('/[^a-z0-9\-\_]/i', $_POST['moblog_short_name']))
        {
            return $OUT->show_user_error('submission', array($LANG->line('invalid_short_name')));
        }
        
        /** -------------------------------
        /**  Gallery Error Checking
        /** -------------------------------*/
        
        if (isset($_POST['moblog_type']) && $_POST['moblog_type'] == 'gallery')
        {
        	if ($_POST['gallery_id'] == 'null')
        	{
        		return $OUT->show_user_error('submission', array($LANG->line('choose_gallery')));
        	}
        	elseif( ! isset($_POST['gallery_cat']) OR $_POST['gallery_cat'] == 'none')
        	{
        		return $OUT->show_user_error('submission', array($LANG->line('choose_gallery_category')));
        	}
        }
        
        
        /** ------------------------------
        /**  Duplicate Name check
        /** ------------------------------*/
        
       	$id_addition = ( ! isset($_POST['id'])) ? '' : " AND moblog_id != '".$DB->escape_str($_POST['id'])."'";
       	
       	/** ------------------------------
       	/**  Short Name Check - Zzzzz...
       	/** ------------------------------*/
       	
       	$sql = "SELECT count(*) as count FROM exp_moblogs WHERE moblog_short_name = '".$DB->escape_str($_POST['moblog_short_name'])."' {$id_addition}";
        
        if (USER_BLOG !== FALSE)
		{
			$sql .= " AND exp_moblogs.user_blog_id = '".UB_BLOG_ID."'";
		}
		else
		{
			$sql .= " AND exp_moblogs.is_user_blog = 'n'";
		}
		
		$query = $DB->query($sql);
        
        if ($query->row['count'] > 0)
        {
            return $DSP->error_message($LANG->line('moblog_taken_short_name'));
        }
        
        /** -----------------------------
        /**  Full Name Check
        /** -----------------------------*/
        
        $sql = "SELECT count(*) as count FROM exp_moblogs WHERE moblog_full_name = '".$DB->escape_str($_POST['moblog_full_name'])."' {$id_addition}";
       	
       	if (USER_BLOG !== FALSE)
		{
			$sql .= " AND exp_moblogs.user_blog_id = '".UB_BLOG_ID."'";
		}
		else
		{
			$sql .= " AND exp_moblogs.is_user_blog = 'n'";
		}
		
		$query = $DB->query($sql); 
		
       	if ($query->row['count'] > 0)
        {
            return $DSP->error_message($LANG->line('moblog_taken_name'));
        }
        
        // In case the select none/all and any others.
        if (isset($_POST['cat_id']) && sizeof($_POST['cat_id']) > 1 && (in_array('all',$_POST['cat_id']) || in_array('none',$_POST['cat_id'])))
        {
        	if (in_array('all',$_POST['cat_id']))
        	{
        		$_POST['cat_id'] = array('all');
        	}
        	else
        	{
        		$_POST['cat_id'] = array('none');
        	}
        }     
        
        /** -------------------------
        /**  Validate from emails
        /** -------------------------*/
        
        if ( ! isset($_POST['moblog_valid_from']))
        {
        	$from_values = '';
        }
        else
        {
        	$from_emails = array();
        	$input  = trim($_POST['moblog_valid_from']);
    		$input  = preg_replace("/[,|\|]/", "", $input);
    		$input  = preg_replace("/[\r\n|\r|\n]/", " ", $input);
    		$input  = preg_replace("/\t+/", " ", $input);
    		$input  = preg_replace("/\s+/", " ", $input);
    		$emails = explode(" ", $input);
    		
    		foreach($emails as $addr)
			{
				if ($REGX->valid_email($addr))
				{
					$from_emails[] = $addr;
				}			
			}
			
			if (sizeof($from_emails) > 0)
			{	
				$from_values = implode('|',$from_emails);
			}
			else
			{
				$from_values = '';
			}
        }
        
        $post_data = array(
						'moblog_full_name'			=> $_POST['moblog_full_name'],
						'moblog_short_name'			=> $_POST['moblog_short_name'],
						'moblog_weblog_id'			=> ( ! isset($_POST['weblog_id']) || $_POST['weblog_id'] == 'null') ? 'none' : $_POST['weblog_id'],
						'moblog_categories'			=> ( ! isset($_POST['cat_id'])) ? 'none' : implode('|',$_POST['cat_id']),
						'moblog_field_id'			=> ( ! isset($_POST['field_id'])) ? 'none' : $_POST['field_id'],
						'moblog_status'				=> ( ! isset($_POST['status'])) ? 'none' : $_POST['status'],
						'moblog_author_id'			=> ( ! isset($_POST['author_id'])) ? 'none' : $_POST['author_id'],
						'moblog_auth_required'		=> $_POST['moblog_auth_required'],
						'moblog_auth_delete'		=> $_POST['moblog_auth_delete'],
						'moblog_upload_directory'	=> $_POST['moblog_upload_directory'],
						'moblog_image_width'		=> ( ! isset($_POST['moblog_image_width'])) ? '0' : $_POST['moblog_image_width'],
						'moblog_image_height'		=> ( ! isset($_POST['moblog_image_height'])) ? '0' : $_POST['moblog_image_height'],
						'moblog_resize_image'		=> $_POST['moblog_resize_image'],
						'moblog_resize_width'		=> ( ! isset($_POST['moblog_resize_width'])) ? '0' : $_POST['moblog_resize_width'],
						'moblog_resize_height'		=> ( ! isset($_POST['moblog_resize_height'])) ? '0' : $_POST['moblog_resize_height'],
						'moblog_create_thumbnail'	=> $_POST['moblog_create_thumbnail'],
						'moblog_thumbnail_width'	=> ( ! isset($_POST['moblog_thumbnail_width'])) ? '0' : $_POST['moblog_thumbnail_width'],
						'moblog_thumbnail_height'	=> ( ! isset($_POST['moblog_thumbnail_height'])) ? '0' : $_POST['moblog_thumbnail_height'],
						'moblog_email_type'			=> $_POST['moblog_email_type'],
						'moblog_email_address'		=> base64_encode($_POST['moblog_email_address']),
						'moblog_email_server'		=> $_POST['moblog_email_server'],
						'moblog_email_login'		=> base64_encode($_POST['moblog_email_login']),
						'moblog_email_password'		=> base64_encode($_POST['moblog_email_password']),
						'moblog_subject_prefix'		=> ( ! isset($_POST['moblog_subject_prefix'])) ? '' : $_POST['moblog_subject_prefix'],
						'moblog_valid_from'			=> $from_values,
						'moblog_ignore_text'		=> ( ! isset($_POST['moblog_ignore_text'])) ? '' : $_POST['moblog_ignore_text'],
						'moblog_template'			=> ( ! isset($_POST['moblog_template'])) ? '' : $_POST['moblog_template'],
						'moblog_time_interval'		=> $_POST['moblog_time_interval'],
						'moblog_enabled'			=> $_POST['moblog_enabled'],
						'moblog_file_archive'		=> $_POST['moblog_file_archive'],
						
						'moblog_type'				=> ( ! isset($_POST['moblog_type'])) ? 'weblog' : $_POST['moblog_type'],
						'moblog_gallery_id'			=> ( ! isset($_POST['gallery_id'])) ? '' : $_POST['gallery_id'],
						'moblog_gallery_category'	=> ( ! isset($_POST['gallery_cat'])) ? '' : $_POST['gallery_cat'],
						'moblog_gallery_status'		=> ( ! isset($_POST['gallery_status'])) ? '' : $_POST['gallery_status'],
						'moblog_gallery_comments'	=> ( ! isset($_POST['gallery_comments'])) ? 'n' : 'y',
						'moblog_gallery_author'		=> ( ! isset($_POST['gallery_author'])) ? '1' : $_POST['gallery_author'],
						'moblog_ping_servers'		=> ( ! isset($_POST['ping'])) ? '' : implode('|',$_POST['ping']),
						
						'moblog_allow_overrides'	=> ( ! isset($_POST['moblog_allow_overrides'])) ? 'y' : $_POST['moblog_allow_overrides'],
						'moblog_sticky_entry'		=> ( ! isset($_POST['moblog_sticky_entry'])) ? 'n' : $_POST['moblog_sticky_entry']
						);						
						
		if (USER_BLOG !== FALSE)
		{
			$post_data['is_user_blog'] = 'y';
			$post_data['user_blog_id'] = UB_BLOG_ID;
		}
		else
		{
			$post_data['is_user_blog'] = 'n';
			$post_data['user_blog_id'] = 0;
		}
			
						
		if ( ! isset($_POST['id']))
		{
			$sql = $DB->insert_string('exp_moblogs', $post_data);
			$DB->query($sql);
			$message = $LANG->line('moblog_created');
		}
		else
		{
			$sql = $DB->update_string('exp_moblogs', $post_data, "moblog_id = '".$DB->escape_str($_POST['id'])."'");
			$DB->query($sql);
			$message = $LANG->line('moblog_updated');
		}
		
		return $this->moblog_home($DSP->qdiv('success', $message));
    }
    /* END */
    
    
    
    /** -------------------------------------------
    /**  Delete Confirm
    /** -------------------------------------------*/

    function delete_confirm()
    { 
        global $IN, $DSP, $LANG;
        
        if ( ! $IN->GBL('toggle', 'POST'))
        {
            return $this->view_moblogs();
        }
        
        $DSP->title = $LANG->line('moblog');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=moblog', $LANG->line('moblog'));
		$DSP->crumb .= $DSP->crumb_item($LANG->line('delete'));

        $DSP->body	.=	$DSP->form_open(array('action' => 'C=modules'.AMP.'M=moblog'.AMP.'P=delete'));
        
        $i = 0;
        
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'toggle') AND ! is_array($val))
            {
                $DSP->body	.=	$DSP->input_hidden('delete[]', $val);
                
                $i++;
            }        
        }
        
		$DSP->body .= $DSP->heading($DSP->qspan('alert', $LANG->line('moblog_delete_confirm')));
		$DSP->body .= $DSP->div('box');
		$DSP->body .= $DSP->qdiv('defaultBold', $LANG->line('moblog_delete_question'));
		$DSP->body .= $DSP->qdiv('alert', BR.$LANG->line('action_can_not_be_undone'));
		$DSP->body .= $DSP->qdiv('', BR.$DSP->input_submit($LANG->line('delete')));
		$DSP->body .= $DSP->qdiv('alert',$DSP->div_c());
		$DSP->body .= $DSP->div_c();
		$DSP->body .= $DSP->form_close();
    }
    /* END */
    
    
    
    /** -------------------------------------------
    /**  Delete Moblogs
    /** -------------------------------------------*/

    function delete_moblogs()
    { 
        global $IN, $DSP, $LANG, $SESS, $DB;
        
        
        if ( ! $IN->GBL('delete', 'POST'))
        {
            return $this->view_moblogs();
        }

        $ids = array();
                
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'delete') AND ! is_array($val))
            {
                $ids[] = "moblog_id = '".$val."'";
            }        
        }
        
        $IDS = implode(" OR ", $ids);
        
        $DB->query("DELETE FROM exp_moblogs WHERE ".$IDS);
    
        $message = (count($ids) == 1) ? $LANG->line('moblog_deleted') : $LANG->line('moblogs_deleted');

        return $this->moblog_home($DSP->qdiv('success', $message));
    }
    /* END */
    
    
	/** -------------------------
    /**  Check Moblog
    /** -------------------------*/
    
	function check_moblog()
	{
		global $DSP, $DB, $LANG, $IN, $OUT;
    	
		$DSP->title = $LANG->line('moblog');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=moblog', $LANG->line('moblog'));
        $DSP->crumb .= $DSP->crumb_item($LANG->line('check_moblog'));  
       	
       	$r = $DSP->heading($LANG->line('check_moblog')); 
    	
		if (! $id = $IN->GBL('moblog_id', 'GET'))
		{
			return false;
		}
		
		$sql = "SELECT * FROM exp_moblogs
				WHERE moblog_enabled = 'y' AND moblog_id = '{$id}'";
    			
		if (USER_BLOG !== FALSE)
		{
			$sql .= " AND exp_moblogs.user_blog_id = '".UB_BLOG_ID."'";
		}
		else
		{
			$sql .= " AND exp_moblogs.is_user_blog = 'n'";
		}
		
        $query = $DB->query($sql);
        
        if ($query->num_rows == 0)
        {
        	return $OUT->show_user_error('submission', array($LANG->line('invalid_moblog')));
        }
    	
		if ( ! class_exists('Moblog'))
        {
            require PATH_MOD.'moblog/mod.moblog'.EXT;
        }
        
        $MP = new Moblog();
        $MP->moblog_array = $query->row;
        
        $success = 'n';
        if ($MP->moblog_array['moblog_email_type'] == 'imap')
        {
			if ( ! $MP->check_imap_moblog())
			{
				$display = $MP->message_array;
			}
			else
			{
				$message  = $DSP->qdiv('success', $LANG->line('moblog_successful_check'));
				$message .= $DSP->div('box'); 
				$message .= $DSP->table('', '0', '0', '100%');
				$message .= $DSP->tr();
				$message .= $DSP->table_qcell('none', $LANG->line('emails_done').NBS.NBS.$MP->emails_done, '30%');
				$message .= $DSP->table_qcell('none', $LANG->line('entries_added').NBS.NBS.$MP->entries_added, '25%');
				$message .= $DSP->table_qcell('none', $LANG->line('attachments_uploaded').NBS.NBS.$MP->uploads, '25%');
				$message .= $DSP->table_qcell('none', $LANG->line('pings_sent').NBS.NBS.$MP->pings_sent, '20%');
				$message .= $DSP->tr_c();
				$message .= $DSP->table_c();
				$message .= $DSP->div_c();
				
				if (sizeof($MP->message_array) > 0)
				{
					$message .= $DSP->qdiv('box', $DSP->qdiv('alert', $MP->errors()));
				}
				
				return $this->moblog_home($message);
			}
		}
		else
		{
			if ( ! $MP->check_pop_moblog())
			{
        		$display = $MP->message_array;
			}
			else
			{
			
				$message  = $DSP->qdiv('success', $LANG->line('moblog_successful_check'));
				$message .= $DSP->div('box'); 
				$message .= $DSP->table('', '0', '0', '100%');
				$message .= $DSP->tr();
				$message .= $DSP->table_qcell('none', $LANG->line('emails_done').NBS.NBS.$MP->emails_done, '30%');
				$message .= $DSP->table_qcell('none', $LANG->line('entries_added').NBS.NBS.$MP->entries_added, '25%');
				$message .= $DSP->table_qcell('none', $LANG->line('attachments_uploaded').NBS.NBS.$MP->uploads, '25%');
				$message .= $DSP->table_qcell('none', $LANG->line('pings_sent').NBS.NBS.$MP->pings_sent, '20%');
				$message .= $DSP->tr_c();
				$message .= $DSP->table_c();
				$message .= $DSP->div_c();
				
				if (sizeof($MP->message_array) > 0)
				{
					$message .= $DSP->qdiv('box', $DSP->qdiv('alert', $MP->errors()));
				}
			
				return $this->moblog_home($message);
			}			
		}
		
		return $this->moblog_home($DSP->qdiv('itemWrapper', $DSP->qdiv('alert', $MP->errors())));
    }
    /* END */
    
    
    
	/** ---------------------------------------------------------------
    /**  Fetch ping servers
    /** ---------------------------------------------------------------*/
    // This function displays the ping server checkboxes
    //---------------------------------------------------------------
        
    function fetch_ping_servers($selected = '', $type='update')
    {
        global $LANG, $DB, $SESS, $DSP, $PREFS;
        
        $sent_pings = array();
        
        if ($selected != '')
        {
        	$sent_pings = explode('|', $selected);
        }

        $query = $DB->query("SELECT COUNT(*) AS count 
        					  FROM exp_ping_servers 
        					  WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' 
        					  AND member_id = '".$SESS->userdata['member_id']."'");
        
        $member_id = ($query->row['count'] == 0) ? 0 : $SESS->userdata['member_id'];
              
        $query = $DB->query("SELECT id, server_name, is_default, server_url
        					  FROM exp_ping_servers 
        					  WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'
        					  AND member_id = '$member_id' 
        					  ORDER BY server_order");

        if ($query->num_rows == 0)
        {
            return FALSE;
        }
                
        $r = '';
        $done = array();
		
		foreach($query->result as $row)
		{
			// Because of multiple sites a member might have multiple Ping Servers with the same
			// URL.  The moblog is a module and does not recognize Sites like that, so we simply
			// show all Ping Servers from all Sites, but remove duplicate ones based on the Server URL
			if (in_array($row['server_url'], $done)) continue;
			
			$done[] = $row['server_url'];
		
			if (sizeof($sent_pings) > 0)
			{
				$selected = (in_array($row['id'], $sent_pings)) ? 1 : '';
			}
			elseif($type == 'submit')
			{
				$selected = ($row['is_default'] == 'y') ? 1 : '';
			}			
			
			$r .= $DSP->qdiv('publishPad', $DSP->input_checkbox('ping[]', $row['id'], $selected).' '.$row['server_name']);			
		}
		
        return $r;
    }        
    /* END */
    


    /** -------------------------
    /**  Module installer
    /** -------------------------*/

    function moblog_module_install()
    {
        global $DB;        
        
        $sql[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Moblog', '{$this->version}', 'y')";
		$sql[] = "CREATE TABLE IF NOT EXISTS `exp_moblogs` ( 
		`moblog_id` int(4) unsigned NOT NULL auto_increment, 
		`moblog_full_name` varchar(80) NOT NULL default '', 
		`moblog_short_name` varchar(20) NOT NULL default '', 
		`moblog_enabled` char(1) NOT NULL default 'y', 
		`moblog_file_archive` char(1) NOT NULL default 'n', 
		`moblog_time_interval` int(4) unsigned NOT NULL default '0', 
		`moblog_type` varchar(10) NOT NULL default '', 
		`moblog_gallery_id` int(6) unsigned NOT NULL default '0', 
		`moblog_gallery_category` int(10) unsigned NOT NULL default '0', 
		`moblog_gallery_status` varchar(50) NOT NULL default '', 
		`moblog_gallery_comments` varchar(10) NOT NULL default 'y', 
		`moblog_gallery_author` int(10) unsigned NOT NULL default '1', 
		`moblog_weblog_id` int(4) unsigned NOT NULL default '1', 
		`is_user_blog` char(1) NOT NULL default 'n', 
		`user_blog_id` int(6) unsigned NOT NULL default '0', 
		`moblog_categories` varchar(25) NOT NULL default '', 
		`moblog_field_id` varchar(5) NOT NULL default '', 
		`moblog_status` varchar(50) NOT NULL default '', 
		`moblog_author_id` int(10) unsigned NOT NULL default '1',
		`moblog_sticky_entry` char(1) NOT NULL default 'n',
		`moblog_allow_overrides` char(1) NOT NULL default 'y',
		`moblog_auth_required` char(1) NOT NULL default 'n', 
		`moblog_auth_delete` char(1) NOT NULL default 'n', 
		`moblog_upload_directory` int(4) unsigned NOT NULL default '1', 
		`moblog_template` text NOT NULL, 
		`moblog_image_width` int(5) unsigned NOT NULL default '0', 
		`moblog_image_height` int(5) unsigned NOT NULL default '0', 
		`moblog_resize_image` char(1) NOT NULL default '', 
		`moblog_resize_width` int(5) unsigned NOT NULL default '0', 
		`moblog_resize_height` int(5) unsigned NOT NULL default '0', 
		`moblog_create_thumbnail` char(1) NOT NULL default 'n', 
		`moblog_thumbnail_width` int(5) NOT NULL default '0', 
		`moblog_thumbnail_height` int(5) NOT NULL default '0', 
		`moblog_email_type` varchar(10) NOT NULL default '', 
		`moblog_email_address` varchar(125) NOT NULL default '', 
		`moblog_email_server` varchar(100) NOT NULL default '', 
		`moblog_email_login` varchar(125) NOT NULL default '', 
		`moblog_email_password` varchar(125) NOT NULL default '', 
		`moblog_subject_prefix` varchar(50) NOT NULL default '', 
		`moblog_valid_from` text NOT NULL, 
		`moblog_ignore_text` text NOT NULL, 
		`moblog_ping_servers` varchar(50) NOT NULL default '',
		PRIMARY KEY(`moblog_id`))";
    
        foreach ($sql as $query)
        {
            $DB->query($query);
        }
        
        return true;
    }
    /* END */
    
    
    /** -------------------------
    /**  Module de-installer
    /** -------------------------*/

    function moblog_module_deinstall()
    {
        global $DB;    

        $query = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Moblog'");                 
        $sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";
        $sql[] = "DELETE FROM exp_modules WHERE module_name = 'Moblog'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Moblog'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Moblog_CP'";
        $sql[] = "DROP TABLE IF EXISTS exp_moblogs";

        foreach ($sql as $query)
        {
            $DB->query($query);
        }

        return true;
    }
    /* END */



}
// END CLASS
?>
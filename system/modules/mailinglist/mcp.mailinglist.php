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
 File: mcp.mailinglist.php
-----------------------------------------------------
 Purpose: Basic Mailint List class - CP
=====================================================
*/


if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Mailinglist_CP {

    var $version = '2.0';


    /** -------------------------
    /**  Constructor
    /** -------------------------*/
    
    function Mailinglist_CP( $switch = TRUE )
    {
        global $IN, $DB;
        
        
		/** -------------------------------
		/**  Is the module installed?
		/** -------------------------------*/
        
        $query = $DB->query("SELECT module_version FROM exp_modules WHERE module_name = 'Mailinglist'");
        
        if ($query->num_rows == 0)
        {
        	return;
        }
        
        /* Is the version current? */
        
        if ($query->row['module_version'] != $this->version)
        {
        	$DB->query("UPDATE exp_modules SET module_version = '{$this->version}' WHERE module_name = 'Mailinglist'");
        }
		// --       
        
        if ($switch)
        {
            switch($IN->GBL('P'))
            {
                case 'edit_ml'			:  $this->edit_mailing_list();
                	break;
                case 'update_ml'		:  $this->update_mailing_list();
                	break;
                case 'view'				:  $this->view_mailing_list();
                    break;
                case 'edit_template'	:  $this->edit_template();
                    break;
                case 'update_template'	:  $this->update_template();
                    break;
                case 'del_ml_confirm'	:  $this->delete_mailinglist_confirm();
                	break;
                case 'delete_ml'		:  $this->delete_mailinglists();
                	break;
                case 'del_confirm'		:  $this->delete_confirm();
                	break;
                case 'delete'			:  $this->delete_email_addresses();
                	break;
                case 'subscribe'		:  $this->subscribe_email_addresses();
                	break;
               default					:  $this->mailinglist_home();
                    break;
            }
        }
    }
    /* END */
    
    
    /** -------------------------
    /**  Mailinglist Home Page
    /** -------------------------*/

	function mailinglist_home($msg = '')
	{
		global $DSP, $LANG, $DB;
                        
        $DSP->title = $LANG->line('ml_mailinglist');
        $DSP->crumb = $LANG->line('ml_mailinglist');        
        
		$DSP->right_crumb($LANG->line('ml_create_new'), BASE.AMP.'C=modules'.AMP.'M=mailinglist'.AMP.'P=edit_ml');
       
        $DSP->body .= $DSP->qdiv('tableHeading', $LANG->line('ml_mailinglists')); 
        
        if ($msg != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
                
        
        $query = $DB->query("SELECT * FROM exp_mailing_lists ORDER BY list_title");
        
        
        if ($query->num_rows == 0)
        {
			$DSP->body .= $DSP->div('box');
			$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->heading($LANG->line('ml_no_lists_exist'), 5));
        	$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->anchor( BASE.AMP.'C=modules'.AMP.'M=mailinglist'.AMP.'P=edit_ml', $LANG->line('ml_create_new')));
			$DSP->body .= $DSP->div_c();
        
			return;
        }     
        
        
        $DSP->body	.=	$DSP->toggle();
        
        $DSP->body_props .= ' onload="magic_check()" ';
        
        $DSP->body .= $DSP->magic_checkboxes();
                
        $DSP->body	.=	$DSP->form_open(array('action' => 'C=modules'.AMP.'M=mailinglist'.AMP.'P=del_ml_confirm', 'name' => 'target', 'id' => 'target'));

		$DSP->body .= $DSP->table_open(array('class' => 'tableBorder', 'width' => '100%'));
		$DSP->body .= $DSP->table_row(array(
									array(
											'text'	=> $LANG->line('ml_mailinglist_title'),
											'class'	=> 'tableHeadingAlt'
										),
									array(
											'text'	=> $LANG->line('ml_mailinglist_name'),
											'class'	=> 'tableHeadingAlt'
										),
									array(
											'text'	=> $LANG->line('ml_view_list'),
											'class'	=> 'tableHeadingAlt'
										),
									array(
											'text'	=> $LANG->line('ml_edit_list'),
											'class'	=> 'tableHeadingAlt'
										),
									array(
											'text'	=> $LANG->line('ml_edit_template'),
											'class'	=> 'tableHeadingAlt'
										),
									array(
											'text'	=> $LANG->line('ml_total_emails'),
											'class'	=> 'tableHeadingAlt'
										),
									array(
											'text'	=> $DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\"").NBS.$LANG->line('delete').NBS.NBS,
											'class'	=> 'tableHeadingAlt'
										)
									)
								);
		
		$i = 0;
		$lists = array();
		foreach ($query->result as $row)
		{		
			$lists[$row['list_id']] = $row['list_title'];
		
			$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
			
			$res = $DB->query("SELECT COUNT(*) AS count FROM exp_mailing_list WHERE list_id = '".$row['list_id']."'");

			$DSP->body .= $DSP->table_row(array(
										array(
												'text'	=> $DSP->qdiv('defaultBold', $row['list_name']),
												'class'	=> $style,
												'width'	=> '15%'
											),
										array(
												'text'	=> $DSP->qdiv('defaultBold', $row['list_title']),
												'class'	=> $style,
												'width'	=> '20%'
											),
										array(
												'text'	=> $DSP->qdiv('defaultBold', $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mailinglist'.AMP.'P=view'.AMP.'list_id='.$row['list_id'], $LANG->line('ml_view'))),
												'class'	=> $style,
												'width'	=> '15%'
											),
										array(
												'text'	=> $DSP->qdiv('defaultBold', $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mailinglist'.AMP.'P=edit_ml'.AMP.'list_id='.$row['list_id'], $LANG->line('edit'))),
												'class'	=> $style,
												'width'	=> '15%'
											),
										array(
												'text'	=> $DSP->qdiv('defaultBold', $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mailinglist'.AMP.'P=edit_template'.AMP.'list_id='.$row['list_id'], $LANG->line('ml_edit_template'))),
												'class'	=> $style,
												'width'	=> '15%'
											),
										array(
												'text'	=> $DSP->qdiv('default', $res->row['count']),
												'class'	=> $style,
												'width'	=> '10%'
											),
										array(
												'text'	=> $DSP->input_checkbox('toggle[]', $row['list_id'], '', " id='delete_box_".$row['list_id']."'"),
												'class'	=> $style,
												'width'	=> '10%'
											)
									)
								);
		}
		
		$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
		$DSP->body .= $DSP->table_row(array(
									array(
											'text'		=> $DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=config_mgr'.AMP.'P=mailinglist_cfg', $LANG->line('mailinglist_preferences')),
											'class'		=> $style,
											'colspan'	=> '6'
										),
		
									array(
											'text'		=> $DSP->qdiv('itemWrapper', $DSP->input_submit($LANG->line('delete'))),
											'class'		=> $style,
											'colspan'	=> '1'
										)
									)
								);
	
		
        $DSP->body	.=	$DSP->table_close(); 
        $DSP->body	.=	$DSP->form_close();  
                
		if (count($lists) > 0)
		{
			$DSP->body .= $DSP->qdiv('defaultSmall', NBS);
			
			$DSP->body .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=mailinglist'.AMP.'P=view'));
			$DSP->body .= $DSP->div('box');
			$DSP->body .= $DSP->heading($LANG->line('ml_email_search') ,5);
			$DSP->body .= $DSP->qdiv('itemWrapper', $LANG->line('ml_email_search_cont', 'email'));
			$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->input_text('email', '', '35', '100', 'input', '400px'));
			$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->input_submit($LANG->line('submit')));
			$DSP->body .= $DSP->div_c();                        
			$DSP->body .= $DSP->form_close();   
		
		
			$DSP->body .= $DSP->qdiv('defaultSmall', NBS);
	
			$DSP->body .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=mailinglist'.AMP.'P=subscribe'));
			$DSP->body .= $DSP->div('box');        
			$DSP->body .= $DSP->heading($LANG->line('ml_batch_subscribe') ,5);
			$DSP->body .= $DSP->qdiv('itemWrapper', $LANG->line('ml_add_email_addresses_cont', 'addresses'));
			$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->input_textarea('addresses', '', 6, 'textarea', '100%'));
	
			$DSP->body .= $DSP->div('itemWrapper');
			$DSP->body .= $DSP->input_select_header('sub_action');
			$DSP->body .= $DSP->input_select_option('subscribe', $LANG->line('ml_add_email_addresses'));
			$DSP->body .= $DSP->input_select_option('unsubscribe', $LANG->line('ml_remove_email_addresses'));
			$DSP->body .= $DSP->input_select_footer();        
			$DSP->body .= $DSP->div_c();
	
	
			$DSP->body .= $DSP->div('itemWrapper');
			$DSP->body .= $DSP->qdiv('default', $LANG->line('ml_select_list'));
			
			$DSP->body .= $DSP->input_select_header('list_id');
			
			foreach ($lists as $id => $name)
			{
				$DSP->body .= $DSP->input_select_option($id, $name);
			}
			
			$DSP->body .= $DSP->input_select_footer();        
			$DSP->body .= $DSP->div_c();

			$DSP->body .= $DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line('submit')));
			$DSP->body .= $DSP->div_c();
			$DSP->body .= $DSP->form_close();   
		}
	}
	/* END */
    
    /** -------------------------
    /**  Mailing List Default Template Data
    /** -------------------------*/

function default_template_data()
{
return <<<EOF
{message_text}

To remove your email from the "{mailing_list}" mailing list, click here:
{if html_email}<a href="{unsubscribe_url}">{unsubscribe_url}</a>{/if}
{if plain_email}{unsubscribe_url}{/if}
EOF;
}
/* END */
    
    
    /** -------------------------
    /**  Create/Edit Mailing List
    /** -------------------------*/

    function edit_mailing_list()
    {
		global $IN, $DSP, $LANG, $DB;
		        
        $list_id	= '0';
        $list_title	= '';
        $list_name	= '';
        
        if (is_numeric($IN->GBL('list_id')))
        {
        	$query = $DB->query("SELECT * FROM exp_mailing_lists WHERE list_id = '".$DB->escape_str($IN->GBL('list_id'))."'");
        	
        	if ($query->num_rows == 1)
        	{
        		$list_id = $query->row['list_id'];
        		$list_title = $query->row['list_title'];
        		$list_name = $query->row['list_name'];
        	}
        }
        
    	$title = ($list_id == 0) ? $LANG->line('ml_create_new') : $LANG->line('ml_edit_list');
        $DSP->title = $title;        
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mailinglist', $LANG->line('ml_mailinglist'));
		$DSP->crumb .= $DSP->crumb_item($title);

               
        $DSP->body .= $DSP->qdiv('tableHeading', $title); 
                        
        $DSP->body	.=	$DSP->form_open(
        								array('action' => 'C=modules'.AMP.'M=mailinglist'.AMP.'P=update_ml'),
        								array('list_id' => $list_id)
        								);

        $DSP->body	.=	$DSP->table('tableBorder', '0', '0', '100%').
						$DSP->tr().
						$DSP->table_qcell('tableHeadingAlt', array('&nbsp;', '&nbsp;')).
						$DSP->tr_c();
		
		$style ='tableCellOne';
				  
		$DSP->body .= $DSP->tr();
		$DSP->body .= $DSP->table_qcell($style, $DSP->qdiv('defaultBold', $LANG->line('ml_mailinglist_short_name')).$DSP->qdiv('default', $LANG->line('ml_mailinglist_short_info')), '50%');				
		$DSP->body .= $DSP->table_qcell($style, $DSP->qdiv('defaultBold', $DSP->qdiv('itemWrapper', $DSP->input_text('list_name', $list_name, '35', '40', 'input', '100%'))), '50%');
		$DSP->body .= $DSP->tr_c();
		
		$DSP->body .= $DSP->tr();
		$DSP->body .= $DSP->table_qcell($style, $DSP->qdiv('defaultBold', $LANG->line('ml_mailinglist_long_name')).$DSP->qdiv('default', $LANG->line('ml_mailinglist_long_info')));
		$DSP->body .= $DSP->table_qcell($style, $DSP->qdiv('defaultBold', $DSP->qdiv('itemWrapper', $DSP->input_text('list_title', $list_title, '35', '100', 'input', '100%'))));
		$DSP->body .= $DSP->tr_c();
        $DSP->body .= $DSP->table_c(); 
        
    	
    	$button = ($list_id == 0) ? $LANG->line('ml_create_new') : $LANG->line('update');
    	
		$DSP->body	.=	$DSP->qdiv('itemWrapperTop', $DSP->input_submit($button));             
        
        $DSP->body	.=	$DSP->form_close();     
    }
    /* END */
    
 

    /** -------------------------
    /**  Update Mailing List
    /** -------------------------*/

    function update_mailing_list()
    {
		global $IN, $DSP, $LANG, $DB;
		
		$list_id = (is_numeric($IN->GBL('list_id')) AND $IN->GBL('list_id') != 0) ? $IN->GBL('list_id') : FALSE;
        
        if ($_POST['list_name'] == '' OR $_POST['list_title'] == '')
        {
			return $DSP->error_message($LANG->line('ml_all_fields_required'));
        }
        
        if (preg_match('/[^a-z0-9\-\_]/i', $_POST['list_name']))
        {
			return $DSP->error_message($LANG->line('ml_invalid_short_name'));
        }

		$sql = "SELECT COUNT(*) AS count FROM exp_mailing_lists WHERE list_name = '".$DB->escape_str($_POST['list_name'])."'";

    	if ($list_id !== FALSE)
    	{
    		$sql .= " AND list_id != '".$list_id."'";
    	}

		$query = $DB->query($sql);

    	if ($query->row['count'] > 0)
    	{
			return $DSP->error_message($LANG->line('ml_short_name_taken'));
    	}
    	
    	if ($list_id === FALSE)
    	{
    		$DB->query("INSERT INTO exp_mailing_lists(list_id, list_name, list_title, list_template) values ('', '".$DB->escape_str($_POST['list_name'])."', '".$DB->escape_str($_POST['list_title'])."', '".addslashes($this->default_template_data())."')");
    	}
    	else
    	{
    		$DB->query("UPDATE exp_mailing_lists SET list_name = '".$DB->escape_str($_POST['list_name'])."', list_title = '".$DB->escape_str($_POST['list_title'])."' WHERE list_id = '{$list_id}'");
    	}
    	
    	
    	$msg = ($list_id === FALSE) ? $LANG->line('ml_mailinglist_created') : $LANG->line('ml_mailinglist_updated');
    	
    	return $this->mailinglist_home($msg);
    }
    /* END */
    
    

	/** ---------------------------------
	/**  Mailing List Template
	/** ---------------------------------*/
		
	function edit_template($message = '')
	{
		global $DSP, $DB, $IN, $REGX, $LANG, $PREFS;
		
        if ( ! $list_id = $IN->GBL('list_id'))
        {
            return $DSP->no_access_message();
        }
        
		$query = $DB->query("SELECT list_title, list_template FROM exp_mailing_lists WHERE list_id = '{$list_id}'");

		if ($query->num_rows == 0)
		{
            return $DSP->no_access_message();
		}

		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mailinglist', $LANG->line('ml_mailinglist'));
		$DSP->crumb .= $DSP->crumb_item($LANG->line('mailinglist_template'));
				
		$DSP->title = $LANG->line('mailinglist_template');						 
		
		$DSP->body = $DSP->qdiv('tableHeading', $LANG->line('mailinglist_template'));
		
		$DSP->body .= $DSP->qdiv('box', $DSP->qspan('defaultBold', $LANG->line('mailing_list').NBS.$query->row['list_title']));
		
		$DSP->body .= $DSP->qdiv('box', 
								$DSP->qspan('alert', $LANG->line('mailinglist_template_warning')).
								$DSP->qspan('defaultBold', '{message_text}, {unsubscribe_url}')
								);
		
		if ($message != '')
		{
        	$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $message));		
		}
		
		
        $DSP->body .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=mailinglist'.AMP.'P=update_template'.AMP.'list_id='.$list_id));
      
        $DSP->body .= $DSP->div('itemWrapper')  
					 .$DSP->input_textarea('template_data', $query->row['list_template'], '20', 'textarea', '100%')
					 .$DSP->div_c();
					 
		$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->input_submit($LANG->line('update')))
             		 .$DSP->form_close();
	}
	/* END */
	
	
	/** ---------------------------------
	/**  Update Mailing List Template
	/** ---------------------------------*/
		
	function update_template()
	{
		global $DB, $DSP, $LANG, $IN;
        
        if ( ! $list_id = $IN->GBL('list_id'))
        {
        	return FALSE;
        }

        if ( ! isset($_POST['template_data']))
        {
        	return FALSE;
        }
	
		$DB->query("UPDATE exp_mailing_lists SET list_template = '".$DB->escape_str($_POST['template_data'])."' WHERE list_id = '{$list_id}'");
	
		$this->mailinglist_home($LANG->line('template_updated'));
	}
	/* END */

    /** -------------------------------------------
    /**  Delete Mailing List Confirm
    /** -------------------------------------------*/

    function delete_mailinglist_confirm()
    { 
        global $IN, $DSP, $LANG, $DB;
        
        if ( ! $IN->GBL('toggle', 'POST'))
        { 
            return $this->mailinglist_home();
        }
        
        $DSP->title = $LANG->line('ml_mailinglist');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mailinglist', $LANG->line('ml_mailinglist'));
		$DSP->crumb .= $DSP->crumb_item($LANG->line('ml_delete_mailinglist'));

        $DSP->body	.=	$DSP->form_open(array('action' => 'C=modules'.AMP.'M=mailinglist'.AMP.'P=delete_ml'));

		$sql = "SELECT list_title FROM exp_mailing_lists WHERE list_id IN (";
        
        $i = 0;
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'toggle') AND ! is_array($val))
            {
                $DSP->body	.=	$DSP->input_hidden('delete[]', $val);
                $i++;
                
				$sql .= "'".$DB->escape_str($val)."',";
            }        
        }
        
		$sql = substr($sql, 0, -1);
		$sql .= ")";
		
		$query = $DB->query($sql);        
        
        
		$DSP->body .= $DSP->qdiv('alertHeading', $LANG->line('ml_delete_mailinglist'));
		$DSP->body .= $DSP->div('box');
		$DSP->body .= $DSP->qdiv('defaultBold', $DSP->qdiv('itemWrapper', $LANG->line('ml_delete_list_question')));
		
		
		foreach ($query->result as $row)
		{
			$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->qdiv('highlight', NBS.NBS.NBS.NBS.$row['list_title']));
		}
		
		$DSP->body .= $DSP->qdiv('alert', BR.$LANG->line('ml_all_data_nuked'));		
		$DSP->body .= $DSP->qdiv('alert', BR.$LANG->line('action_can_not_be_undone'));
		$DSP->body .= $DSP->qdiv('', BR.$DSP->input_submit($LANG->line('delete')));
		$DSP->body .= $DSP->qdiv('alert',$DSP->div_c());
		$DSP->body .= $DSP->div_c();
		$DSP->body .= $DSP->form_close();
    }
    /* END */
   
    
    
    /** -------------------------------------------
    /**  Delete Mailing List(s)
    /** -------------------------------------------*/

    function delete_mailinglists()
    { 
        global $IN, $DSP, $LANG, $SESS, $DB;
        
        
        if ( ! $IN->GBL('delete', 'POST'))
        {
            return $this->mailinglist_home();
        }

        $ids = array();
                
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'delete') AND ! is_array($val) AND is_numeric($val))
            {
                $ids[] = "list_id = '".$val."'";
            }        
        }
        
        $IDS = implode(" OR ", $ids);
        
        $DB->query("DELETE FROM exp_mailing_lists WHERE ".$IDS);
        $DB->query("DELETE FROM exp_mailing_list  WHERE ".$IDS);
    
        $message = (count($ids) == 1) ? $LANG->line('ml_list_deleted') : $LANG->line('ml_lists_deleted');

        return $this->mailinglist_home($message);
    }
    /* END */
     
        
   
    /** -------------------------
    /**  Subscribe
    /** -------------------------*/
    
    function subscribe_email_addresses()
    {
		global $IN, $REGX, $DB, $FNS, $DSP, $LANG;
    	
		if ($_POST['addresses'] == '')
		{
			return $this->mailinglist_home();	
		}

		/** ------------------------------
		/**  Fetch existing addresses
		/** ------------------------------*/
		
    	$subscribe = ($IN->GBL('sub_action') == 'unsubscribe') ? FALSE : TRUE;
    	
    	$list_id = $IN->GBL('list_id');
    	
		$query = $DB->query("SELECT email FROM exp_mailing_list WHERE list_id = '".$DB->escape_str($list_id)."'");
		
		$current = array();
    	
		if ($query->num_rows == 0)
		{
			if ($subscribe == FALSE)
			{
				return $this->mailinglist_home();	
			}
		}
		else
		{
			foreach ($query->result as $row)
			{
				$current[] = $row['email'];	
			}
		} 
		
		/** ------------------------------
		/**  Clean up submitted addresses
		/** ------------------------------*/
		
		$email  = trim($_POST['addresses']);
		$email  = preg_replace("/[,|\|]/", "", $email);
		$email  = preg_replace("/[\r\n|\r|\n]/", " ", $email);
		$email  = preg_replace("/\t+/", " ", $email);
		$email  = preg_replace("/\s+/", " ", $email);
		$emails = array_unique(explode(" ", $email));
		
		/** ------------------------------
		/**  Insert new addresses
		/** ------------------------------*/
		
		$good_email = 0;
		$dup_email	= 0;
		
    	$bad_email  = array();
    	    					
		foreach($emails as $addr)
		{
			if (preg_match('#\<(.*)\>#', $addr, $match))
				$addr = $match['1'];
			
		    if ($subscribe == TRUE)
		    {
				if ( ! $REGX->valid_email($addr))
				{
					$bad_email[] = $addr;
					continue;
				}
		    
				if (in_array($addr, $current))
				{
					$dup_email++;
					continue;
				}
		    
		    	// We use the Admins IP address for these inserts
				$DB->query("INSERT INTO exp_mailing_list (user_id, list_id, authcode, email, ip_address) 
							VALUES ('',  '".$DB->escape_str($list_id)."', '".$FNS->random('alpha', 10)."', '".$DB->escape_str($addr)."', '".$DB->escape_str($IN->IP)."')");			
			}
			else
			{			
				$DB->query("DELETE FROM exp_mailing_list WHERE email = '".$DB->escape_str($addr)."' AND list_id = '".$DB->escape_str($list_id)."'");
			}
			
			$good_email++;
		}
    
    
    	if (count($bad_email) == 0 AND $dup_email == 0)
    	{	
		    if ($subscribe == TRUE)
		    {    	
				return $this->mailinglist_home($LANG->line('ml_emails_imported'));	
			}
			else
			{
				return $this->mailinglist_home($LANG->line('ml_emails_deleted'));	
			}
    	}
    	else
    	{    	
			$DSP->title = $LANG->line('ml_mailinglist');
			
			$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mailinglist', $LANG->line('ml_mailinglist'));
		
			$DSP->body  = $DSP->qdiv('tableHeading', $LANG->line('ml_mailinglist'));   
			$DSP->body .= $DSP->div('box');

		    if ($subscribe == TRUE)
		    {
				$DSP->body .= $DSP->heading($LANG->line('ml_total_emails_imported').NBS.NBS.$good_email, 5);
			}
			else
			{
				$DSP->body .= $DSP->heading($LANG->line('ml_total_emails_deleted').NBS.NBS.$good_email, 5);
			}
			
			$DSP->body .= $DSP->heading($LANG->line('ml_total_duplicate_emails').NBS.NBS.$dup_email, 5);
    	
    		if (count($bad_email) > 0)
    		{
				$DSP->body .= $DSP->qdiv('', BR);
	
				if ($subscribe == TRUE)
				{    	
					$DSP->body .= $DSP->heading($LANG->line('ml_bad_email_heading'), 5);
				}
				else
				{
					$DSP->body .= $DSP->heading($LANG->line('ml_bad_email_del_heading'), 5);
				}
				
				foreach ($bad_email as $val)
				{
					$DSP->body .= $DSP->qdiv('', $val);
				}
			}
			
			$DSP->body .= $DSP->div_c();
    	}
    }
    /* END */
    
    
    
    
    /** -------------------------
    /**  View Mailinglist
    /** -------------------------*/
    
    function view_mailing_list()
    {
        global $IN, $DSP, $DB, $LANG;
                
        $row_limit = 100;
        $paginate  = '';
        $row_count = 0;
        
                        
        $DSP->title = $LANG->line('ml_mailinglist');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mailinglist', $LANG->line('ml_mailinglist'));
		$DSP->crumb .= $DSP->crumb_item($LANG->line('ml_view_mailinglist'));

        $DSP->body  = $DSP->qdiv('tableHeading', $LANG->line('ml_mailinglist'));   
        
        
		$sql = "SELECT user_id, list_id, email, ip_address FROM exp_mailing_list ";
		
		$list_id = $IN->GBL('list_id');
		
		if ($list_id !== FALSE)
		{
			$sql .= " WHERE list_id = '".$DB->escape_str($IN->GBL('list_id'))."'";
		}
		
		
		$email = $IN->GBL('email', 'GP');		
              
		if ($email !== FALSE)               
        {
			$email = urldecode($email);
			
			if ($list_id !== FALSE)
			{
				$sql .= " AND ";
			}
			else
			{
				$sql .= " WHERE ";
			}
        
        	$sql .= "  email LIKE '%".$DB->escape_like_str($email)."%'";
        }
    
		$query = $DB->query($sql);    
		
		if ($query->num_rows == 0)
		{
			$DSP->body	.=	$DSP->qdiv('itemWrapper', $DSP->qdiv('highlight', $LANG->line('ml_no_results')));             
			
			return;
		}		
    
		 if ($query->num_rows > $row_limit)
		 { 
			$row_count = ( ! $IN->GBL('row')) ? 0 : $IN->GBL('row');
						
			$url = BASE.AMP.'C=modules'.AMP.'M=mailinglist'.AMP.'P=view';
			
			if ($list_id !== FALSE)
			{
				$url .= AMP.'list_id='.$IN->GBL('list_id');
			}
			
			if ($email)
			{
				$url .= AMP.'email='.urlencode($email);
			}
		 
			$paginate = $DSP->pager(  $url,
									  $query->num_rows, 
									  $row_limit,
									  $row_count,
									  'row'
									);
			 
			$sql .= " LIMIT ".$row_count.", ".$row_limit;
			
			$query = $DB->query($sql);    
    	}
    	
    
        $DSP->body	.=	$DSP->toggle();
        
        $DSP->body_props .= ' onload="magic_check()" ';
        
        $DSP->body .= $DSP->magic_checkboxes();
        
        $form_url = 'C=modules'.AMP.'M=mailinglist'.AMP.'P=del_confirm';

		if ($list_id !== FALSE)
		{
			$form_url .= AMP.'list_id='.$list_id;
		}
        
        $DSP->body	.=	$DSP->form_open(array('action' => $form_url, 'name' => 'target', 'id' => 'target'));

		$DSP->body .= $DSP->table_open(array('class' => 'tableBorder', 'width' => '100%'));
		
		$top[] = array(
						'text'	=> NBS,
						'class'	=> 'tableHeadingAlt',
						'width'	=>  '5%'
					 );
					 
		$top[] = array(
						'text'	=> $DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\"").NBS.NBS.$LANG->line('delete'),
						'class'	=> 'tableHeadingAlt',
						'width'	=>   ($list_id === FALSE) ? '40%' : '95%'
					);

		if ($list_id === FALSE)
		{
			$top[] = array(
							'text'	=> $LANG->line('ml_mailinglist'),
							'class'	=> 'tableHeadingAlt',
							'width'	=>  '55%'
						 );
		}

		$DSP->body .= $DSP->table_row($top);

		
		$row_count++;
		$i = 0;
		
		$lists = array();
		if ($list_id === FALSE)
		{
			$res = $DB->query("SELECT list_id, list_title FROM exp_mailing_lists");
			foreach ($res->result as $row)
			{
				$lists[$row['list_id']] = $row['list_title'];
			}
			
		}		

		foreach ($query->result as $row)
		{
			unset($rows);
			
			$ip = ($row['ip_address'] != '') ? NBS.NBS.'('.$row['ip_address'].')' : '';
			
			$rows[] = $row_count;
			$rows[] = $DSP->input_checkbox('toggle[]', $row['user_id'], '', "id='delete_box_".$row['user_id']."'").NBS.NBS.$DSP->mailto($row['email']).$ip;
		
			if ($list_id === FALSE)
			{
				$rows[] = isset($lists[$row['list_id']]) ?  $lists[$row['list_id']] : '';
			}
			
			$DSP->body .= $DSP->table_qrow( ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo', $rows);
			$row_count++;			
		}
		
		
		$foot[] = NBS;
		$foot[] = $DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\"").NBS.NBS.'<b>'.$LANG->line('delete').'</b>';
		if ($list_id === FALSE)
		{
			$foot[] = NBS;
		}
				
		$DSP->body .= $DSP->table_qrow( ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo', $foot);		
        $DSP->body .=	$DSP->table_c(); 

    	if ($paginate != '')
    	{
    		$DSP->body .= $DSP->qdiv('itemWrapper', $paginate.BR.BR);
    	}
    	
		$DSP->body	.=	$DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line('delete')));             
        
        $DSP->body	.=	$DSP->form_close();
    }
    /* END */
    
  
    
    /** -------------------------------------------
    /**  Delete Emails - Confirm
    /** -------------------------------------------*/

    function delete_confirm()
    { 
        global $IN, $DSP, $LANG;
        
        if ( ! $IN->GBL('toggle', 'POST'))
        { 
            return $this->view_mailing_list();
        }
        
        $DSP->title = $LANG->line('ml_mailinglist');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=mailinglist', $LANG->line('ml_mailinglist'));
		$DSP->crumb .= $DSP->crumb_item($LANG->line('ml_delete_confirm'));

        $DSP->body	.=	$DSP->form_open(array('action' => 'C=modules'.AMP.'M=mailinglist'.AMP.'P=delete'));
        
        $i = 0;
        
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'toggle') AND ! is_array($val) AND is_numeric($val))
            {
                $DSP->body	.=	$DSP->input_hidden('delete[]', $val);
                
                $i++;
            }        
        }
        
		$DSP->body .= $DSP->qdiv('alertHeading', $LANG->line('ml_delete_confirm'));
		$DSP->body .= $DSP->div('box');
		$DSP->body .= $DSP->qdiv('defaultBold', $LANG->line('ml_delete_question'));
		$DSP->body .= $DSP->qdiv('alert', BR.$LANG->line('action_can_not_be_undone'));
		$DSP->body .= $DSP->qdiv('', BR.$DSP->input_submit($LANG->line('delete')));
		$DSP->body .= $DSP->qdiv('alert',$DSP->div_c());
		$DSP->body .= $DSP->div_c();
		$DSP->body .= $DSP->form_close();
    }
    /* END */
    
    
    
    /** -------------------------------------------
    /**  Delete Email Addresses
    /** -------------------------------------------*/

    function delete_email_addresses()
    { 
        global $IN, $DSP, $LANG, $SESS, $DB;
        
        
        if ( ! $IN->GBL('delete', 'POST'))
        {
            return $this->view_mailing_list();
        }

        $ids = array();
                
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'delete') AND ! is_array($val))
            {
                $ids[] = "user_id = '".$val."'";
            }        
        }
        
        $IDS = implode(" OR ", $ids);
        
        $DB->query("DELETE FROM exp_mailing_list WHERE ".$IDS);
    
        $message = (count($ids) == 1) ? $LANG->line('ml_email_deleted') : $LANG->line('ml_emails_deleted');

        return $this->mailinglist_home($message);
    }
    /* END */
     
        
    

    /** -------------------------
    /**  Module installer
    /** -------------------------*/

    function mailinglist_module_install()
    {
        global $DB;       
        		
		$sql[] = "CREATE TABLE IF NOT EXISTS exp_mailing_lists (
		 list_id int(7) unsigned NOT NULL auto_increment,
		 list_name varchar(40) NOT NULL,
		 list_title varchar(100) NOT NULL,
		 list_template text NOT NULL,
		 PRIMARY KEY (list_id),
		 KEY (list_name)
		)";
		
		$sql[] = "CREATE TABLE exp_mailing_list (
		 user_id int(10) unsigned NOT NULL auto_increment,
		 list_id int(7) unsigned default '0' NOT NULL,
		 authcode varchar(10) NOT NULL,
		 email varchar(50) NOT NULL,
		 ip_address VARCHAR(16) NOT NULL,
		 KEY (list_id),
		 KEY (user_id)
		)";
				
		$sql[] = "CREATE TABLE IF NOT EXISTS exp_mailing_list_queue (
		  email varchar(50) NOT NULL,
		  list_id int(7) unsigned default '0' NOT NULL,
		  authcode varchar(10) NOT NULL,
		  date int(10) NOT NULL
		)";
				
        
        $sql[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Mailinglist', '$this->version', 'y')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mailinglist', 'insert_new_email')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mailinglist', 'authorize_email')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mailinglist', 'unsubscribe')";
		$sql[] = "INSERT INTO exp_mailing_lists(list_id, list_name, list_title) values ('', 'default', 'Default Mailing List')";

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

    function mailinglist_module_deinstall()
    {
        global $DB;    

        $query = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Mailinglist'"); 
                
        $sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";        
        $sql[] = "DELETE FROM exp_modules WHERE module_name = 'Mailinglist'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Mailinglist'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Mailinglist_CP'";
        $sql[] = "DROP TABLE IF EXISTS exp_mailing_lists";
        $sql[] = "DROP TABLE IF EXISTS exp_mailing_list";
        $sql[] = "DROP TABLE IF EXISTS exp_mailing_list_queue";

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
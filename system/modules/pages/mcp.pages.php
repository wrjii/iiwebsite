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
 File: mcp.pages.php
-----------------------------------------------------
 Purpose: Pages class - CP
=====================================================
*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}



class Pages_CP {

    var $version        = '1.0.1';
    var $page_array		= array();
    var $pages			= array();

    /** -------------------------
    /**  Constructor
    /** -------------------------*/
    
    function Pages_CP($switch=TRUE)
    {
		global $IN, $DB;
			
		/** -------------------------------------
		/**  Module Installed and What Version?
		/** -------------------------------------*/
        
        $query = $DB->query("SELECT module_version FROM exp_modules WHERE module_name = 'Pages'");
        
        if ($query->num_rows == 0)
        {
        	return;
        }
        elseif($query->row['module_version'] < $this->version)
        {
        	$this->pages_module_update($query->row['module_version']);
        }
        
		/** -------------------------------------
		/**  This Code is So Clever, It Hurts...Owwwww!
		/** -------------------------------------*/
        
        if ($switch)
        {
            if (method_exists($this, $IN->GBL('P')))
			{	
				$this->{$IN->GBL('P')}();
			}
			else
			{
				$this->home();
			}
        }
    }
    /* END */
    
    
	/** -------------------------------------
	/**  Pages Main page
	/** -------------------------------------*/
    
	function home($message='')
    {
        global $IN, $DSP, $LANG, $FNS, $DB, $LOC, $PREFS;
                        
        $DSP->title 	= $LANG->line('pages_module_name');
        $DSP->crumb 	= $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=pages', $LANG->line('pages_module_name')).$DSP->crumb_item($LANG->line('pages_homepage'));  
		$DSP->crumbline = TRUE;
		
		$query = $DB->query("SELECT configuration_value, configuration_name
							 FROM exp_pages_configuration 
							 WHERE configuration_name IN ('homepage_display', 'default_weblog')
							 AND site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'");
		
		$default_weblog = 0;
		$homepage_display = 'not_nested';
		
		if ($query->num_rows > 0)
		{
			foreach($query->result as $row)
			{
				$$row['configuration_name'] = $row['configuration_value'];
			}
		}
		
		if ($default_weblog == 0)
		{
			$DSP->right_crumb($LANG->line('create_page'), BASE.AMP.'C=publish');
		}
		else
		{
			$DSP->right_crumb($LANG->line('create_page'), BASE.AMP.'C=publish'.AMP.'M=entry_form'.AMP.'weblog_id='.$default_weblog);
		}
		
		$DSP->body .= $DSP->table('', '', '', '97%')
					 .$DSP->tr()
					 .$DSP->td('', '', '', '', 'top')
					 .$DSP->heading($LANG->line('pages_module_name'));
             
        if ($message != '')
        {
            $DSP->body .= $DSP->qdiv('success', $message);
        }

        $DSP->body .= $DSP->td_c()
					 .$DSP->td('', '', '', '', 'top')
					 .		(($DSP->allowed_group('can_admin_weblogs') === FALSE) ? 
					 		 '' : 
					 		 $DSP->qdiv('defaultRight', $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=pages'.AMP.'P=configuration', '<b>'.$LANG->line('pages_configuration').'</b>')))
					 .$DSP->td_c()
					 .$DSP->tr_c()
					 .$DSP->table_c();
        
        $pages = $PREFS->ini('site_pages');

        if ($pages === FALSE OR sizeof($pages['uris']) == 0)
        {
			$DSP->body .= $DSP->div('box');
            $DSP->body .= $DSP->qdiv('itemWrapper', '<b>'.$LANG->line('no_pages').'</b>');      
			$DSP->body .= $DSP->div_c();    
            return;
        }
        
        natcasesort($pages['uris']);

        $DSP->body	.=	$DSP->div('itemWrapper').$DSP->toggle();
                
        $DSP->body	.=	$DSP->form_open(array('action' => 'C=modules'.AMP.'M=pages'.AMP.'P=delete_confirm', 'name' => 'target', 'id' => 'target'));
    
        $DSP->body	.=	$DSP->table('tableBorder', '0', '0', '100%').
						$DSP->tr().
						$DSP->table_qcell('tableHeadingAlt', 
											array(
													$LANG->line('page'),
													$LANG->line('view_page'),
													$DSP->input_checkbox('toggleflag', '', '', " onclick=\"toggle(this);\"").NBS.$LANG->line('delete').NBS.NBS
												 )
											).
						$DSP->tr_c();
						
		/** -------------------------------------
		/**  Our Pages
		/** -------------------------------------*/
		
		$i = 0;
		$qm = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';
		$previous = array();
		$spcr = '<img src="'.PATH_CP_IMG.'clear.gif" border="0"  width="24" height="14" alt="" title="" />';
		$indent = $spcr.'<img src="'.PATH_CP_IMG.'cat_marker.gif" border="0"  width="18" height="14" alt="" title="" />';
        
        foreach($pages['uris'] as $entry_id => $url) 
        {        
            $style = ($i % 2) ? 'tableCellOne' : 'tableCellTwo';
            
            $url = ($url == '/') ? '/' : '/'.trim($url, '/').'/';
            
            if ($homepage_display == 'nested' && $url != '/')
            {
            	$x = explode('/', trim($url, '/'));
            	
            	for($i=0, $s=sizeof($x); $i < $s; ++$i)
            	{
            		if (isset($previous[$i]) && $previous[$i] == $x[$i])
            		{
            			continue;
            		}
            		
					$this_indent = ($i == 0) ? '' : str_repeat($spcr, $i-1).$indent;
					
					$page = ($i+1 == $s) ? $this_indent.$DSP->anchor(BASE.AMP.'C=edit'.AMP.'M=edit_entry'.AMP.'entry_id='.$entry_id, $x[$i]) : $this_indent.$x[$i];
					
				
					$DSP->body .= $DSP->tr()
								  .		$DSP->table_qcell($style, '<strong>'.$page.'</strong>', '40%')
								  .		$DSP->table_qcell($style, $DSP->anchor($FNS->fetch_site_index().$qm.'URL='.urlencode($FNS->create_url($url)), $LANG->line('view_page')), '25%')
								  .		$DSP->table_qcell($style, $DSP->input_checkbox('toggle[]', $entry_id), '10%')
								  .$DSP->tr_c();
            	}
            	
            	$previous = $x;
            }
            else
			{
				$DSP->body .= $DSP->tr()
							  .		$DSP->table_qcell($style, '<strong>'.$DSP->anchor(BASE.AMP.'C=edit'.AMP.'M=edit_entry'.AMP.'entry_id='.$entry_id, $url).'</strong>', '40%')
							  .		$DSP->table_qcell($style, $DSP->anchor($FNS->fetch_site_index().$qm.'URL='.urlencode($FNS->create_url($url)), $LANG->line('view_page')), '25%')
							  .		$DSP->table_qcell($style, $DSP->input_checkbox('toggle[]', $entry_id), '10%')
							  .$DSP->tr_c();
			}
        }
		
		$DSP->body	.=	$DSP->table_c();

		$DSP->body  .= $DSP->table('', '0', '0', '100%');
		$DSP->body	.= $DSP->tr();
		$DSP->body	.= $DSP->table_qcell('default', '', '50%'); 
		$DSP->body	.= $DSP->table_qcell('default', '', '40%');
		$DSP->body	.= $DSP->table_qcell('default', $DSP->qdiv('itemWrapper', $DSP->input_submit($LANG->line('delete'))), '10%');             
		$DSP->body	.= $DSP->tr_c();

        $DSP->body	.=	$DSP->table_c(); 
        
        $DSP->body	.=	$DSP->form_close().$DSP->div_c();
    }
    /* END */
    
    
/*
    
    Hunting for Bugs in the Code...
    
           /      \
        \  \  ,,  /  /
         '-.`\()/`.-'
        .--_'(  )'_--.
       / /` /`""`\ `\ \
        |  |  ><  |  |
        \  \      /  /
            '.__.'
 
*/
    
    
	/** -------------------------------------
	/**  Pages Configuration Screen
	/** -------------------------------------*/
    
	function configuration($message='')
    {
        global $IN, $DSP, $LANG, $FNS, $DB, $LOC, $PREFS;
        
        if ( ! $DSP->allowed_group('can_admin_weblogs'))
        {
            return $DSP->no_access_message();
        }
                        
        $DSP->title 	= $LANG->line('pages_module_name');
        $DSP->crumb 	= $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=pages', $LANG->line('pages_module_name')).$DSP->crumb_item($LANG->line('pages_homepage'));
        
        $DSP->body .= $DSP->qdiv('tableHeading', $LANG->line('pages_configuration')).
        			  $DSP->form_open(array('action' => 'C=modules'.AMP.'M=pages'.AMP.'P=save_configuration', 'name' => 'pages_configuration', 'id' => 'pages_configuration'));
    
        $DSP->body .= $DSP->table('tableBorder', '0', '0', '100%').
					  $DSP->tr().
					  $DSP->table_qcell('tableHeadingAlt', 
											array(
													$LANG->line('preference_name'),
													$LANG->line('preference_value'),
												 )
											).
					  $DSP->tr_c();
						
		/** -------------------------------------
		/**  Weblogs and Templates
		/** -------------------------------------*/
		
		$wquery = $DB->query("SELECT blog_title, weblog_id FROM exp_weblogs
							  WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'
							  ORDER BY blog_title");
							  
		$sql = "SELECT tg.group_name, t.template_id, t.template_name
					FROM   exp_template_groups tg, exp_templates t
					WHERE  tg.group_id = t.group_id
					AND    tg.site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' ";
			 
		if (USER_BLOG == TRUE)
		{
			$sql .= "AND tg.group_id = '".$SESS->userdata['tmpl_group_id']."' ";
		}
		else
		{
			$sql .= "AND tg.is_user_blog = 'n' ";
		}
				
		$tquery = $DB->query($sql." ORDER BY tg.group_name, t.template_name");
		
		/** -------------------------------------
		/**  Our Configuration Options
		/** -------------------------------------*/
		
		$configuration_fields = array('homepage_display'	=>
									  array('type' => 'display_pulldown', 
									  		'label' => $LANG->line('pages_display_on_homepage'), 
									  		'value' => ''),
									  		
									  'default_weblog'		=>
									   array('type' 		=> 'other', 
									   		 'label'		=> $LANG->line('default_for_page_creation'), 
									   		 'value'		=> ''));
		
		foreach($wquery->result as $row)
		{
			$configuration_fields['template_weblog_'.$row['weblog_id']] = array('type' => "weblog", 'label' => $LANG->line("default_template").':'.NBS.$row['blog_title'], 'value' => '');
		}
		
		/** -------------------------------------
		/**  Existing Configuration Data
		/** -------------------------------------*/
		
		$data_query = $DB->query("SELECT * FROM exp_pages_configuration WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'");
		
		if ($data_query->num_rows > 0)
		{
			foreach($data_query->result as $row)
			{
				if (isset($configuration_fields[$row['configuration_name']]))
				{
					$configuration_fields[$row['configuration_name']]['value'] = $row['configuration_value'];
				}
			}
		}
		
		/** -------------------------------------
		/**  Create Table Rows
		/** -------------------------------------*/
		
		$i = 0;
        
        foreach($configuration_fields as $field_name => $field_data) 
        {        
            $style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
            
            if ($field_data['type'] == 'weblog')
            {
            	$field = $DSP->input_select_header($field_name);
			
				foreach ($tquery->result as $template)
				{                           
					$field .= $DSP->input_select_option($template['template_id'], $template['group_name'].'/'.$template['template_name'], (($template['template_id'] == $field_data['value']) ? 1 : ''));
				}
			
				$field .= $DSP->input_select_footer();
            }
            elseif($field_data['type'] == 'display_pulldown')
            {
            	$field = $DSP->input_select_header($field_name)
            			 .	$DSP->input_select_option('not_nested', $LANG->line('not_nested'), (('not_nested' == $field_data['value']) ? 1 : ''))
            			 .	$DSP->input_select_option('nested', $LANG->line('nested'), (('nested' == $field_data['value']) ? 1 : ''))
            			 .$DSP->input_select_footer();
            }
            else
            {
            	$field = $DSP->input_select_header($field_name)
            			 .	$DSP->input_select_option(0, $LANG->line('no_default'));
			
				foreach ($wquery->result as $row)
				{                           
					$field .= $DSP->input_select_option($row['weblog_id'], $row['blog_title'], (($row['weblog_id'] == $field_data['value']) ? 1 : ''));
				}
			
				$field .= $DSP->input_select_footer();
            }

			$DSP->body .= $DSP->tr()
						  .		$DSP->table_qcell($style, $DSP->qdiv('defaultBold', $field_data['label']), '50%')
						  .		$DSP->table_qcell($style, $field, '50%')
						  .$DSP->tr_c();
        }
		
		$DSP->body	.=	$DSP->table_c();

		$DSP->body  .= $DSP->table('', '0', '0', '100%');
		$DSP->body	.= $DSP->tr();
		$DSP->body	.= $DSP->table_qcell('default', '', '50%'); 
		$DSP->body	.= $DSP->table_qcell('default', '', '40%');
		$DSP->body	.= $DSP->table_qcell('default', $DSP->qdiv('itemWrapper', $DSP->input_submit($LANG->line('update'))), '10%');             
		$DSP->body	.= $DSP->tr_c();

        $DSP->body	.=	$DSP->table_c(); 
        
        $DSP->body	.=	$DSP->form_close().$DSP->div_c();
    }
    /* END */
    
    
	/** -------------------------------------
 	/**  Save Configuration
 	/** -------------------------------------*/

    function save_configuration()
    { 
        global $PREFS, $DB, $LANG;
        
        $data = array();
        
        foreach($_POST as $key => $value)
        {
        	if ($key == 'homepage_display' && in_array($value, array('nested', 'not_nested')))
        	{
        		$data[$key] = $value;
        	}
        	elseif (is_numeric($value) && ($key == 'default_weblog' OR substr($key, 0, strlen('template_weblog_')) == 'template_weblog_'))
        	{
        		$data[$key] = $value;
        	}
        }
        
        if (sizeof($data) > 0)
        {
        	$DB->query("DELETE FROM exp_pages_configuration WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'");
        	
        	foreach($data as $key => $value)
        	{
        		$DB->query($DB->insert_string("exp_pages_configuration", array('configuration_name'  => $key,
        																	   'configuration_value' => $value, 
        																	   'site_id' => $PREFS->ini('site_id'))));
        	}
        }
        
        $this->home($LANG->line('configuration_updated'));
    }
    /* END */
    
    
	/** -------------------------------------
 	/**  Delete Confirmation
 	/** -------------------------------------*/

    function delete_confirm()
    { 
        global $IN, $DSP, $LANG;
        
        if ( ! $IN->GBL('toggle', 'POST'))
        {
            return $this->home();
        }
        
        $DSP->title = $LANG->line('pages_module_name');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=wiki', $LANG->line('pages_module_name'));
		$DSP->crumb .= $DSP->crumb_item($LANG->line('delete'));

        $DSP->body	.= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=pages'.AMP.'P=delete'));
        
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'toggle') AND ! is_array($val))
            {
                $DSP->body	.=	$DSP->input_hidden('delete[]', $val);
            }        
        }
		
		
		$DSP->body .= $DSP->qdiv('alertHeading', $LANG->line('pages_delete_confirm'));
		$DSP->body .= $DSP->div('box');
		$DSP->body .= $DSP->qdiv('defaultBold', $LANG->line('pages_delete_question'));
		
		$DSP->body .= $DSP->input_hidden('groups', 'n');
		
		$DSP->body .= $DSP->qdiv('alert', BR.$LANG->line('action_can_not_be_undone'));
		$DSP->body .= $DSP->qdiv('', BR.$DSP->input_submit($LANG->line('delete')));
		$DSP->body .= $DSP->qdiv('alert',$DSP->div_c());
		$DSP->body .= $DSP->div_c();
		$DSP->body .= $DSP->form_close();
    }
    /* END */
    
    
	/** -------------------------------------
 	/**  Delete Pages
 	/** -------------------------------------*/

    function delete()
    { 
        global $IN, $DSP, $LANG, $SESS, $DB, $PREFS, $REGX;
        
        if ( ! $IN->GBL('delete', 'POST'))
        {
            return $this->home();
        }

        $ids = array();
                
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'delete') AND ! is_array($val))
            {
                $ids[$val] = $val;
            }        
        }
		
		/** ----------------------------------------
		/**  Pages Stored in Database For Site
		/** ----------------------------------------*/
		
		$query = $DB->query("SELECT site_pages FROM exp_sites WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'");
				
		$pages = $REGX->array_stripslashes(unserialize($query->row['site_pages']));
		
		if (sizeof($pages) == 0)
		{
			return $this->home();
		}
		
		$num = 0;
		
		foreach($pages['uris'] as $entry_id => $value)
		{
			if (isset($ids[$entry_id]))
			{
				unset($pages['uris'][$entry_id]);
				unset($pages['templates'][$entry_id]);
				$num++;
			}
		}
		
		$PREFS->core_ini['site_pages'] = $pages;
		
		$DB->query($DB->update_string('exp_sites', 
									  array('site_pages' => addslashes(serialize($pages))),
									  "site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'"));
									  
		/** ----------------------------------------
		/**  Back to the Present Pages for Site.
		/** ----------------------------------------*/
									  
		return $this->home(($num == 1) ? $LANG->line('page_deleted') : $LANG->line('pages_deleted'));
    }
    /* END */
    

    /** ----------------------------------------
    /**  Module installer
    /** ----------------------------------------*/

    function pages_module_install()
    {
        global $DB;        
        
        $sql[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Pages', '$this->version', 'y')";
        $sql[] = "ALTER TABLE `exp_sites` ADD `site_pages` TEXT NOT NULL";
        
        $sql[] = "CREATE TABLE `exp_pages_configuration` (
				`configuration_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`site_id` INT( 8 ) UNSIGNED NOT NULL DEFAULT '1',
				`configuration_name` VARCHAR( 60 ) NOT NULL ,
				`configuration_value` VARCHAR( 100 ) NOT NULL
				)";

        foreach ($sql as $query)
        {
            $DB->query($query);
        }
        
        return TRUE;
    }
    /* END */
    
    
    
    /** ----------------------------------------
    /**  Module de-installer
    /** ----------------------------------------*/

    function pages_module_deinstall()
    {
        global $DB;    

        $query = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Pages'"); 
                
        $sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";        
        $sql[] = "DELETE FROM exp_modules WHERE module_name = 'Pages'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Pages'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Pages_CP'";
        $sql[] = "ALTER TABLE `exp_sites` DROP `site_pages`";
        $sql[] = "DROP TABLE `exp_pages_configuration`";

        foreach ($sql as $query)
        {
            $DB->query($query);
        }

        return TRUE;
    }
    /* END */
    
    
	/** ------------------------------------
    /**  Update Module
    /** ------------------------------------*/
    
    function pages_module_update($current='')
    {
		global $DB;
		
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		if (version_compare($current, '1.0.1', '<'))
		{
			// no special DB changes
		}
		
		$DB->query("UPDATE exp_modules SET module_version = '".$DB->escape_str($this->version)."' WHERE module_name = 'Pages'");
    }
    /* END */

}
// END CLASS
?>
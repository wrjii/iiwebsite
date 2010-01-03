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
 File: mcp.wiki.php
-----------------------------------------------------
 Purpose: Wiki class - CP 
=====================================================
*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Wiki_CP {

    var $version = '1.2';
    
    /** -------------------------------------
    /**  Constructor
    /** -------------------------------------*/
    
	function Wiki_CP( $switch = TRUE )
    {
        global $IN, $DB;
			
		/** -------------------------------------
		/**  Module Installed and What Version?
		/** -------------------------------------*/
        
        $query = $DB->query("SELECT module_version FROM exp_modules WHERE module_name = 'Wiki'");
        
        if ($query->num_rows == 0)
        {
        	return;
        }
        elseif($query->row['module_version'] < $this->version)
        {
        	$this->wiki_module_update($query->row['module_version']);
        }
        
        if ($switch)
        {
            switch($IN->GBL('P'))
            {
                case 'edit'   			: 
                case 'update'   		: $this->wiki_config();
                    break;	
                case 'create'	  		:  $this->create_wiki();
                    break;
                case 'delete_confirm'  	:  $this->delete_confirm();
                    break;
                case 'delete'   		:  $this->delete_wikis();
                    break;
                default       			:  $this->wiki_home();
                    break;
            }
        }
    }
    /* END */
    
    
	/** -------------------------------------
	/**  A Wiki Config
	/** -------------------------------------*/
    
	function wiki_home($message='')
    {
        global $IN, $DSP, $LANG, $FNS, $DB, $LOC, $PREFS;
                        
        $DSP->title 	= $LANG->line('wiki_module_name');
        $DSP->crumb 	= $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=wiki', $LANG->line('wiki_module_name')).$DSP->crumb_item($LANG->line('wiki_homepage'));  
		
		$DSP->right_crumb($LANG->line('create_wiki'), BASE.AMP.'C=modules'.AMP.'M=wiki'.AMP.'P=create');

        $DSP->body .= $DSP->qdiv('tableHeading', $LANG->line('wiki_module_name'));
    			
    	$query = $DB->query("SELECT wiki_id, wiki_label_name, wiki_short_name FROM exp_wikis ORDER BY wiki_label_name asc");

        if ($query->num_rows == 0)
        {
			$DSP->body .= $DSP->div('box');
            $DSP->body .= $DSP->qdiv('itemWrapper', '<b>'.$LANG->line('no_wiki').'</b>');      
			$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=wiki'.AMP.'P=create', $LANG->line('create_wiki'))); 
			$DSP->body .= $DSP->div_c();    
            return;
        }  
        
        if ($message != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $message);
        }
        
        $total = $query->num_rows;

        $DSP->body	.=	$DSP->toggle();
                
        $DSP->body	.=	$DSP->form_open(array('action' => 'C=modules'.AMP.'M=wiki'.AMP.'P=delete_confirm', 'name' => 'target', 'id' => 'target'));
    
        $DSP->body	.=	$DSP->table('tableBorder', '0', '0', '100%').
						$DSP->tr().
						$DSP->table_qcell('tableHeadingAlt', 
											array(
													$LANG->line('label_name'),
													$LANG->line('short_name'),
													$DSP->input_checkbox('toggleflag', '', '', " onclick=\"toggle(this);\"").NBS.$LANG->line('delete').NBS.NBS
												 )
											).
						$DSP->tr_c();
		
		$i = 0;

		foreach ($query->result as $row)
		{				
			$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
                      
            $DSP->body .= $DSP->tr();
            
            $DSP->body .= $DSP->table_qcell($style, $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=wiki'.AMP.'P=edit'.AMP.'wiki_id='.$row['wiki_id'], '<b>'.$row['wiki_label_name'].'</b>'), '50%');
            $DSP->body .= $DSP->table_qcell($style, $row['wiki_short_name'], '40%');
            $DSP->body .= $DSP->table_qcell($style, $DSP->input_checkbox('toggle[]', $row['wiki_id']), '10%');
											
			$DSP->body .= $DSP->tr_c();
		}
		
		$DSP->body	.=	$DSP->table_c();

		$DSP->body  .= $DSP->table('', '0', '0', '100%');
		$DSP->body	.= $DSP->tr();
		$DSP->body	.= $DSP->table_qcell('default', '', '50%'); 
		$DSP->body	.= $DSP->table_qcell('default', '', '40%');
		$DSP->body	.= $DSP->table_qcell('default', $DSP->input_submit($LANG->line('delete')), '10%');             
		$DSP->body	.= $DSP->tr_c();

        $DSP->body	.=	$DSP->table_c(); 
        
        $DSP->body	.=	$DSP->form_close();     
     
    }
    /* END */
    
    /** -------------------------------------
    /**  A Wiki Config
    /** -------------------------------------*/
    
    function wiki_config()
    {
    	global $DB, $DSP, $IN, $LANG, $OUT;
    	
    	$wiki_id = ($IN->GBL('wiki_id') !== FALSE && is_numeric($IN->GBL('wiki_id'))) ? $IN->GBL('wiki_id') : 1;
    	
    	$msg = '';
    	
    	/** -----------------------------------
    	/**  Save Any Incoming Data
    	/** -----------------------------------*/
    
    	if ($IN->GBL('P') == 'update' && $IN->GBL('wiki_id', 'POST') !== FALSE)
    	{
    		$fields = array('wiki_label_name',
    						'wiki_short_name',
    						'wiki_upload_dir', 
    						'wiki_users',
    						'wiki_admins',
    						'wiki_html_format', 
    						'wiki_text_format', 
    						'wiki_revision_limit', 
    						'wiki_author_limit', 
    						'wiki_namespaces_list',
    						'wiki_moderation_emails');
    	
    		foreach($fields AS $val)
    		{
    			if (($val == 'wiki_revision_limit' OR $val == 'wiki_author_limit') && ! preg_match("/[0-9]+/", $IN->GBL($val)))
    			{
    				continue;
    			}
    			
    			if ($val == 'wiki_namespaces_list')
    			{
    				/** -----------------------------------
    				/**  Namespaces Requiring an Update
    				/** -----------------------------------*/
    			
    				$query = $DB->query("SELECT * FROM exp_wiki_namespaces WHERE wiki_id = '".$DB->escape_str($wiki_id)."'");
    				
    				$labels = array();
    				$names  = array();
    				
    				if ($query->num_rows > 0)
    				{
    					foreach($query->result as $row)
    					{
    						if (isset($_POST['namespace_label_'.$row['namespace_id']]) && isset($_POST['namespace_name_'.$row['namespace_id']]))
    						{
    							if (trim($_POST['namespace_label_'.$row['namespace_id']]) == '' OR 
    								! preg_match("/^\w+$/",$_POST['namespace_name_'.$row['namespace_id']]) OR
    								$_POST['namespace_name_'.$row['namespace_id']] == 'category' OR
    								in_array($_POST['namespace_name_'.$row['namespace_id']], $names) OR 
    								in_array($_POST['namespace_label_'.$row['namespace_id']], $labels))
    							{
    								return $OUT->show_user_error('submission', array($LANG->line('invalid_namespace')));
    							}
    							
    							$DB->query($DB->update_string('exp_wiki_namespaces', 
    														  array(	
    														 		'namespace_name'	=> $_POST['namespace_name_'.$row['namespace_id']],
    																'namespace_label'	=> $_POST['namespace_label_'.$row['namespace_id']],
    																'namespace_admins'	=> ($IN->GBL('namespace_admins_'.$row['namespace_id']) === FALSE) ? '' : implode('|', $IN->GBL('namespace_admins_'.$row['namespace_id'])),
    																'namespace_users'	=> ($IN->GBL('namespace_users_'.$row['namespace_id']) === FALSE) ? '' : implode('|', $IN->GBL('namespace_users_'.$row['namespace_id']))
    																),
    														  "namespace_id = ".$row['namespace_id']
    														  ));
    														  
    							$labels[] = $_POST['namespace_label_'.$row['namespace_id']];
    							$names[]  = $_POST['namespace_name_'.$row['namespace_id']];
    															   
    							unset($_POST['namespace_label_'.$row['namespace_id']]);
    							
    							/** -----------------------------------
								/**  If Short Name changes update article pages
								/** -----------------------------------*/
    							
    							if ($row['namespace_name'] != $_POST['namespace_name_'.$row['namespace_id']])
    							{
    								$DB->query("UPDATE exp_wiki_page 
    											SET page_namespace = '".$DB->escape_str($_POST['namespace_name_'.$row['namespace_id']])."' 
    											WHERE page_namespace = '".$DB->escape_str($row['namespace_name'])."'");
    							}
    						}
    						else
    						{
    							$DB->query("DELETE FROM exp_wiki_namespaces WHERE namespace_id = ".$row['namespace_id']);
    						}
    					}
    				}
    				
    				foreach($_POST as $key => $value)
    				{
    					if (substr($key, 0, strlen('namespace_label_')) == 'namespace_label_')
    					{
    						$number = substr($key, strlen('namespace_label_'));
    						$name = 'namespace_name_'.$number;
    						
    						if (trim($value) == '') continue;
    						
    						if ( ! isset($_POST[$name]) OR ! preg_match("/^\w+$/", $_POST[$name]) OR $_POST[$name] == 'category' OR 
    							in_array($_POST[$name], $names) OR in_array($value, $labels))
    						{
    							return $OUT->show_user_error('submission', array($LANG->line('invalid_namespace')));
    						}
    					
							$DB->query($DB->insert_string('exp_wiki_namespaces', array( 'namespace_name'	=> $_POST[$name], 
																						'namespace_label'	=> $value,
																						'wiki_id'			=> $wiki_id,
																						'namespace_users'	=> ($IN->GBL('namespace_users_'.$number) === FALSE) ? '' : implode('|', $IN->GBL('namespace_users_'.$number)),
																						'namespace_admins'	=> ($IN->GBL('namespace_admins_'.$number) === FALSE) ? '' : implode('|', $IN->GBL('namespace_admins_'.$number))
																					   )));
																						
							$labels[] = $value;
							$names[]  = $_POST[$name];
    					}
    				}
    			}
    			
    			
    			if ($val == 'wiki_short_name')
    			{
    				$query = $DB->query("SELECT COUNT(*) AS count FROM exp_wikis 
    									 WHERE wiki_short_name = '".$DB->escape_str($IN->GBL($val))."'
    									 AND wiki_id != '".$DB->escape_str($wiki_id)."'");
    									 
    				if ($query->row['count'] > 0)
    				{
    					return $OUT->show_user_error('submission', array($LANG->line('duplicate_short_name')));
    				}
    			}
    			
    			if ($val == 'wiki_users' OR $val == 'wiki_admins')
    			{
    				$data[$val] = implode('|', ($IN->GBL($val) === FALSE) ? array() : $IN->GBL($val));	
    			}
    			elseif($val != 'wiki_namespaces_list')
    			{
    				$data[$val] = $IN->GBL($val);
    			}
    		}
    		
    		if (sizeof($data) > 0)
    		{
    			$DB->query($DB->update_string('exp_wikis', $data, "wiki_id = '".$DB->escape_str($wiki_id)."'"));
    			$msg = $LANG->line('update_successful');
    		}
    	}
        
        /** -----------------------------------
    	/**  Begin Page
    	/** -----------------------------------*/
    	
    	$DSP->title  = $LANG->line('wiki_module_name');
    	$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=wiki', $LANG->line('wiki_module_name')).
    				   $DSP->crumb_item($LANG->line('wiki_preferences'));
    				   
    	$query = $DB->query("SELECT * FROM exp_wikis WHERE wiki_id = '".$DB->escape_str($wiki_id)."'");
        
        if ($query->num_rows == 0)
        {
        	$DSP->body .= $DSP->qdiv('box', $DSP->qdiv('highlight', $LANG->line('unauthorized_access')));
        	return;
        }
        
		$DSP->body .= $DSP->qdiv('tableHeading', $LANG->line('wiki_preferences')); 
		
		if ($msg != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
        
        /** -----------------------------------
    	/**  Create Our Form
    	/** -----------------------------------*/
        
        $DSP->body	.=	$DSP->form_open(
        								array(
        										'action' => 'C=modules'.AMP.'M=wiki'.AMP.'P=update', 'name' => 'target', 'id' => 'target',
        									 ),
        								array('wiki_id' => $wiki_id)
        								);
    
        $DSP->body	.=	$DSP->table('tableBorder', '0', '0', '100%').
						$DSP->tr().
						$DSP->table_qcell('tableHeadingAlt', 
											array(
													$LANG->line('preference_name'),
													$LANG->line('preference_value'),
												 )
											).
						$DSP->tr_c();
		
		$i = 0;

		foreach ($query->row as $field_name => $value)
		{				
			$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
                      
            $DSP->body .= $DSP->tr();
            
            $valign = '';
            $field_var = str_replace('wiki_', '', $field_name);
            
            switch($field_var)
            {
            	case 'text_format' :
            		$field = $this->text_format_options($value);
            	break;
            	case 'html_format' :
            		$field = $this->html_format_options($value);
            	break;
            	case 'upload_dir'  :
            		$field = $this->upload_directory_options($value);
            	break;
            	case 'users' :
            		$field = $this->member_group_options('wiki_users', $value);
            		$valign = 'top';
            	break;
            	case 'admins' :
            		$field = $this->member_group_options('wiki_admins', $value);
            		$valign = 'top';
            	break;
            	default :
            		if ($field_var == 'id')
            		{
            			continue(2);
            		}
            		
            		$field = $DSP->input_text($field_name, $value, '20', '400', 'input', '90%');
            	break;
            }
            
            $subtext = '';
            
            if (isset($LANG->language[$field_var.'_subtext']))
            {
            	if (substr(trim($LANG->language[$field_var.'_subtext']), 0, 1) == '-')
            	{
            		$subtext = $LANG->line($field_var.'_subtext');
            	}
            	else
            	{
            		$subtext = $DSP->qdiv('subtext', $LANG->line($field_var.'_subtext'));
            	}
            }

			$DSP->body .= $DSP->table_qcell($style, $DSP->qspan('defaultBold', $LANG->line($field_var)).NL.$subtext, '50%', $valign);
			$DSP->body .= $DSP->table_qcell($style, $field, '50%', $valign);
                                   													   
            $DSP->body .= $DSP->tr_c();
		}
		
        $DSP->body	.=	$DSP->table_c(); 
        
        /** ----------------------------------
        /**  Namespaces and Preferences
        /** ----------------------------------*/
        
		$field = $DSP->qdiv('fieldWrapper', $DSP->qdiv('itemWrapperTop', $DSP->heading($LANG->line('namespaces', 5)).$LANG->line('namespaces_list_subtext'))).
				 $DSP->qdiv('tableHeading', $LANG->line('namespaces_list')).
				 $DSP->table('tableBorder', '0', '0', '100%').
				 $DSP->tr().
				 	$DSP->td('tableHeadingAlt', '30%').$LANG->line('namespace_label').$DSP->td_c().
				 	$DSP->td('tableHeadingAlt', '25%').$LANG->line('namespace_short_name').$DSP->td_c().
				 	$DSP->td('tableHeadingAlt', '20%').$LANG->line('admins').$DSP->td_c().
				 	$DSP->td('tableHeadingAlt', '20%').$LANG->line('users').$DSP->td_c().
				 	$DSP->td('tableHeadingAlt', '5%').''.$DSP->td_c().
				 $DSP->tr_c();
			
		$i = 1;
		
		$query = $DB->query("SELECT * FROM exp_wiki_namespaces WHERE wiki_id = '".$DB->escape_str($wiki_id)."'");
		
		if ($query->num_rows > 0)
		{
			foreach($query->result as $row)
			{
				$field .= '<tr id="namespace_container_'.$row['namespace_id'].'">'.
						  $DSP->table_qcell(($i++ % 2) ? 'tableCellOne' : 'tableCellTwo', 
											array(
													$DSP->input_text('namespace_label_'.$row['namespace_id'], $row['namespace_label'], '20', '120', 'input', '95%'),
													$DSP->input_text('namespace_name_'.$row['namespace_id'], $row['namespace_name'], '20', '120', 'input', '95%').NBS,
													$this->member_group_options('namespace_admins_'.$row['namespace_id'], $row['namespace_admins'], '90%'),
													$this->member_group_options('namespace_users_'.$row['namespace_id'], $row['namespace_users'], '90%'),
													'<a href="#" onclick="add_namespace_fields(); return false;" class="defaultBold">+</a>'.NBS.
													'<a href="#" onclick="delete_namespace_field(this); return false;" class="defaultBold">-</a>',
												 ),
											'',
											'top'
											).
						  $DSP->tr_c();
			}
		}
		
		$results = $DB->query("SELECT MAX(namespace_id) AS max FROM exp_wiki_namespaces");
		
		$start = (empty($results->row['max'])) ? 1 : $results->row['max'] + 1;
		
		$field .= '<tr id="namespace_container_'.$start.'">'.
				  $DSP->table_qcell(($i++ % 2) ? 'tableCellOne' : 'tableCellTwo', 
									array(
											$DSP->input_text('namespace_label_'.$start, '', '20', '120', 'input', '95%'),
											$DSP->input_text('namespace_name_'.$start, '', '20', '120', 'input', '95%').NBS,
											$this->member_group_options('namespace_admins_'.$start, '', '90%'),
											$this->member_group_options('namespace_users_'.$start, '', '90%'),
											'<a href="#" onclick="add_namespace_fields(); return false;" class="defaultBold">+</a>'.NBS.
											'<a href="#" onclick="delete_namespace_field(this); return false;" class="defaultBold">-</a>',
										 ),
									 '',
									 'top'
									).
				  $DSP->tr_c().
				  $DSP->table_c();
				  
		$DSP->body  .=  $this->add_namespace_js($start, ($query->num_rows == 0) ? $start : $query->row['namespace_id']).$field;
    	
		$DSP->body	.=	$DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line('update')));             
        
        $DSP->body	.=	$DSP->form_close();
    }
    /* END */
    
    
    
	/** -------------------------------------
    /**  Add Namespace JS for Configuration
    /** -------------------------------------*/
    
    function add_namespace_js($i, $first)
    {
    	global $LANG;
    	
    	$LANG->fetch_language_file('admin');
    	
    	$action_can_not_be_undone = $LANG->line('action_can_not_be_undone')."\\n\\n".$LANG->line('toggle_extension_confirmation')."\\n";
    	
       	return <<<DOH
       	
<script type="text/javascript"> 
//<![CDATA[

var namespaceCount	= {$i};
var modelNamespace	= {$first};

function add_namespace_fields()
{

	if (document.getElementById('namespace_container_' + modelNamespace))
	{
		// Find last namespace field
		var originalNamespaceField = document.getElementById('namespace_container_' + modelNamespace);
		namespaceCount++;
		
		// Clone it, change the id
		var newNamespaceField = originalNamespaceField.cloneNode(true);
		newNamespaceField.id = 'namespace_container_' + namespaceCount;
		
		// Zero the input and change the names of fields
		var newFieldInputs = newNamespaceField.getElementsByTagName('input');
		newFieldInputs[0].value = '';
		newFieldInputs[0].name = 'namespace_label_' + namespaceCount;
		newFieldInputs[1].value = '';
		newFieldInputs[1].name = 'namespace_name_' + namespaceCount;
		
		// Append it and we're done
		originalNamespaceField.parentNode.appendChild(newNamespaceField);
	}
}

function delete_namespace_field(obj)
{
	if (obj.parentNode && obj.parentNode.parentNode)
	{
		if(!confirm("{$action_can_not_be_undone}")) return false;
		
		siblings = obj.parentNode.parentNode.parentNode.getElementsByTagName('tr');
	
		if (siblings.length == 2)
		{
			add_namespace_fields();
		}
			
		if (obj.parentNode.parentNode.id = siblings[1].id)
		{
			modelNamespace = siblings[2].id.substr(20);
		}
		else
		{
			modelNamespace = siblings[1].id.substr(20);
		}
			
		obj.parentNode.parentNode.parentNode.removeChild(obj.parentNode.parentNode);
	}
}

//-->
//]]>
</script>

DOH;

    }
    /* END */
    
    
	/** -------------------------------------
    /**  List of Plugins
    /** -------------------------------------*/
    
    function text_format_options($value='')
    {
    	global $DSP, $LANG;

    	$list = $this->fetch_plugins();
    	
    	$r = $DSP->input_select_header('wiki_text_format');
		
		foreach($list as $val)
		{
			$name = ucwords(str_replace('_', ' ', $val));
        		
			if ($name == 'Br')
			{
				$name = $LANG->line('auto_br');
			}
			elseif ($name == 'Xhtml')
			{
				$name = $LANG->line('xhtml');
			}
		
			$selected = ($value == $val) ? 1 : '';
				
			$r .= $DSP->input_select_option($val, $name, $selected);
		}		
	                
        $r .= $DSP->input_select_footer();
        
        return $r;
    }
    /* END */
    
    
	/** -------------------------------------
    /**  List of Member Groups
    /** -------------------------------------*/
    
    function member_group_options($name, $data='', $width='')
    {
    	global $DSP, $DB, $PREFS;
    	
    	$values = explode('|', $data);
    	
    	$query = $DB->query("SELECT group_title, group_id 
    						 FROM exp_member_groups 
    						 WHERE group_id NOT IN (2,3,4)
    						 AND site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'");
    	
    	$r = $DSP->input_select_header($name.'[]', 'y', ($query->num_rows > 8) ? 8 : $query->num_rows+1, $width);

		foreach($query->result as $row)
		{
			$r .= $DSP->input_select_option($row['group_id'], $row['group_title'], (in_array($row['group_id'], $values)) ? 1 : '');
		}		
	                
        $r .= $DSP->input_select_footer();
        
        return $r;
    }
    /* END */
    
	/** -------------------------------------
    /**  Fetch Installed Plugins
    /** -------------------------------------*/
    
    function fetch_plugins()
    {
        global $PREFS;
        
        $exclude = array('auto_xhtml');
    
        $filelist = array('br', 'xhtml');
    
        if ($fp = @opendir(PATH_PI)) 
        { 
            while (false !== ($file = readdir($fp))) 
            {
            	if ( preg_match("/pi\.[a-z\_0-9]+?".preg_quote(EXT, '/')."$/", $file))
            	{
            		$file = substr($file, 3, - strlen(EXT));
					
					if ( ! in_array($file, $exclude))
						$filelist[] = $file;
				}
            }
            
			closedir($fp);
		} 
    
        sort($filelist);
		return $filelist;      
    }
    /* END */
    
    
	/** -------------------------------------
    /**  HTMl format - All/Safe/None
    /** -------------------------------------*/
    
    function html_format_options($value='')
    {
    	global $DSP, $LANG;
    	
    	$r = $DSP->input_select_header('wiki_html_format');

        $selected = ($value == 'none') ? 1 : '';
            
        $r .= $DSP->input_select_option('none', $LANG->line('convert_to_entities'), $selected);

        $selected = ($value == 'safe') ? 1 : '';
        
        $r .= $DSP->input_select_option('safe', $LANG->line('allow_safe_html'), $selected);
                
        $selected = ($value == 'all') ? 1 : '';
        
        $r .= $DSP->input_select_option('all', $LANG->line('allow_all_html'), $selected);
                
        $r .= $DSP->input_select_footer();
        
        return $r;
    }
    /* END */
    
	/** -------------------------------------
    /**  Upload Some Files with This Directory
    /** -------------------------------------*/
    
    function upload_directory_options($value='')
    {
    	global $DSP, $DB, $LANG;
    	
    	$query = $DB->query("SELECT id, name FROM exp_upload_prefs WHERE is_user_blog = 'n' ORDER BY name");
    	
    	$r = $DSP->input_select_header('wiki_upload_dir').
    		 $DSP->input_select_option('0', $LANG->line('none'));

		foreach($query->result as $row)
		{
			$selected = ($value == $row['id']) ? 1 : '';
				
			$r .= $DSP->input_select_option($row['id'], $row['name'], $selected);
		}		
	                
        $r .= $DSP->input_select_footer();
        
        return $r;
    }
    /* END */
    
    
	/** -------------------------------------
 	/**  Delete Wikis Confirmation
 	/** -------------------------------------*/

    function delete_confirm()
    { 
        global $IN, $DSP, $LANG;
        
        if ( ! $IN->GBL('toggle', 'POST'))
        {
            return $this->wiki_home();
        }
        
        $DSP->title = $LANG->line('wiki_module_name');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=wiki', $LANG->line('wiki_module_name'));
		$DSP->crumb .= $DSP->crumb_item($LANG->line('delete'));

        $DSP->body	.= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=wiki'.AMP.'P=delete'));
        
        $i = 0;
        
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'toggle') AND ! is_array($val))
            {
                $DSP->body	.=	$DSP->input_hidden('delete[]', $val);
                
                $i++;
            }        
        }
        
		$DSP->body .= $DSP->heading($DSP->qspan('alert', $LANG->line('wiki_delete_confirm')));
		$DSP->body .= $DSP->div('box');
		$DSP->body .= $DSP->qdiv('defaultBold', $LANG->line('wiki_delete_question'));
		$DSP->body .= $DSP->qdiv('alert', BR.$LANG->line('action_can_not_be_undone'));
		$DSP->body .= $DSP->qdiv('', BR.$DSP->input_submit($LANG->line('delete')));
		$DSP->body .= $DSP->qdiv('alert',$DSP->div_c());
		$DSP->body .= $DSP->div_c();
		$DSP->body .= $DSP->form_close();
    }
    /* END */
    
    
    
	/** -------------------------------------
 	/**  Delete Wikis
 	/** -------------------------------------*/

    function delete_wikis()
    { 
        global $IN, $DSP, $LANG, $SESS, $DB;
        
        if ( ! $IN->GBL('delete', 'POST'))
        {
            return $this->wiki_home();
        }

        $ids = array();
                
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'delete') AND ! is_array($val))
            {
                $ids[] = $val;
            }         
        }
        
        $IDS = implode(",", $DB->escape_str($ids));
        
        $DB->query("DELETE FROM exp_wikis WHERE wiki_id IN (".$IDS.')');
        $DB->query("DELETE FROM exp_wiki_page WHERE wiki_id IN (".$IDS.')');
        $DB->query("DELETE FROM exp_wiki_revisions WHERE wiki_id IN (".$IDS.')');
        $DB->query("DELETE FROM exp_wiki_uploads WHERE wiki_id IN (".$IDS.')');
        $DB->query("DELETE FROM exp_wiki_categories WHERE wiki_id IN (".$IDS.')');
    
        $message = (count($ids) == 1) ? $LANG->line('wiki_deleted') : $LANG->line('wikis_deleted');

        return $this->wiki_home($DSP->qdiv('success', $message));
    }
    /* END */
    
    
	/** -------------------------------------
    /**  Create New Wiki
    /** -------------------------------------*/
    
    function create_wiki()
    {
    	global $DB, $FNS;
    	
    	$query = $DB->query("SELECT MAX(wiki_id) AS max FROM exp_wikis");
    	
    	$prefix = ($query->num_rows > 0 && $query->row['max'] != 0) ? '_'.($query->row['max']+1) : '';
    	
    	$id = $this->create_new_wiki($prefix);
    	
    	$FNS->redirect(BASE.AMP.'C=modules'.AMP.'M=wiki'.AMP.'P=edit'.AMP.'wiki_id='.$id);
		exit;
    }
    /* END */
    
    
    /** -------------------------------------
    /**  Create New Wiki
    /** -------------------------------------*/
    
    function create_new_wiki($prefix='')
    {
    	global $DB, $LOC, $SESS, $PREFS, $LANG;
    	
    	/** -------------------------------------
		/**  Default Index Page
		/** -------------------------------------*/
        
        $data  = array(	'wiki_label_name'			=> "EE Wiki".str_replace('_', ' ', $prefix),
        				'wiki_short_name'			=> 'default_wiki'.$prefix,
						'wiki_text_format'			=> 'xhtml',
						'wiki_html_format'			=> 'safe',
						'wiki_admins'				=> '1',
						'wiki_users'				=> '1|5',
						'wiki_upload_dir'			=> '0',
						'wiki_revision_limit'		=> 200,
						'wiki_author_limit'			=> 75,
						'wiki_moderation_emails'	=> $SESS->userdata['email']);
						
		$DB->query($DB->insert_string('exp_wikis', $data));
		$wiki_id = $DB->insert_id;
        
		/** -------------------------------------
		/**  Default Index Page
		/** -------------------------------------*/
		
		$data = array(	'wiki_id'		=> $wiki_id,
						'page_name'		=> 'index',
						'last_updated'	=> $LOC->now);
						
		$DB->query($DB->insert_string('exp_wiki_page', $data));
		
		$LANG->fetch_language_file('wiki');
		
		$page_id = $DB->insert_id;
		
		$data = array(	'page_id'			=> $page_id,
						'wiki_id'			=> $wiki_id,
						'revision_date'		=> $LOC->now,
						'revision_author'	=> $SESS->userdata['member_id'],
						'revision_notes'	=> $LANG->line('default_index_note'),
						'page_content'		=> $LANG->line('default_index_content')
					 );
					 
		$DB->query($DB->insert_string('exp_wiki_revisions', $data));
		
		$last_revision_id = $DB->insert_id;
		
		$DB->query($DB->update_string('exp_wiki_page', array('last_revision_id' => $last_revision_id), array('page_id' => $page_id)));
		
		return $wiki_id;
    }
    /* END */

    /** -------------------------------------
    /**  Module installer
    /** -------------------------------------*/

    function wiki_module_install()
    {
        global $DB, $LOC, $SESS, $PREFS;        
        
        $sql[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Wiki', '$this->version', 'y')";
        
		$sql[] = "CREATE TABLE IF NOT EXISTS exp_wiki_page (
		page_id int(10) unsigned NOT NULL auto_increment,
		wiki_id INT(3) UNSIGNED NOT NULL,
		page_name VARCHAR(100) NOT NULL,
		page_namespace VARCHAR(125) NOT NULL,
		page_redirect VARCHAR(100) NOT NULL,
		page_locked	CHAR(1) NOT NULL DEFAULT 'n',
		page_moderated CHAR(1) NOT NULL DEFAULT 'n',
		last_updated INT(10) UNSIGNED NOT NULL DEFAULT '0',
		last_revision_id INT(10) NOT NULL,
		has_categories CHAR(1) NOT NULL DEFAULT 'n',
		PRIMARY KEY (page_id),
		KEY `wiki_id` (`wiki_id`),
		KEY `page_locked` (`page_locked`),
		KEY `page_moderated` (`page_moderated`),
		KEY `has_categories` (`has_categories`)
		)";
		$sql[] = "CREATE TABLE IF NOT EXISTS `exp_wiki_revisions` (
		`revision_id` int(12) unsigned NOT NULL auto_increment,
		`page_id` int(10) unsigned NOT NULL,
		`wiki_id` INT(3) UNSIGNED NOT NULL,
		`revision_date` int(10) unsigned NOT NULL,
		`revision_author` int(8) NOT NULL,
		`revision_notes` text NOT NULL,
		`revision_status` varchar(10) NOT NULL DEFAULT 'open',
		`page_content` mediumtext NOT NULL,
		PRIMARY KEY (revision_id),
		KEY `page_id` (`page_id`),
		KEY `wiki_id` (`wiki_id`),
		KEY `revision_author` (`revision_author`)
		)";
		$sql[] = "CREATE TABLE IF NOT EXISTS exp_wiki_uploads(
		wiki_upload_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		wiki_id INT(3) UNSIGNED NOT NULL,
		file_name VARCHAR(60) NOT NULL,
		file_hash VARCHAR(32) NOT NULL,
		upload_summary TEXT,
		upload_author INT(8) NOT NULL,
		image_width INT(5) UNSIGNED NOT NULL,
		image_height INT(5) UNSIGNED NOT NULL,
		file_type VARCHAR(50) NOT NULL,
		file_size INT(10) UNSIGNED NOT NULL DEFAULT '0',
		upload_date INT(10) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (`wiki_upload_id`),
		KEY `wiki_id` (`wiki_id`)
		)";
		$sql[] = "CREATE TABLE IF NOT EXISTS exp_wiki_search (
		wiki_search_id VARCHAR(32) NOT NULL,
		wiki_search_query TEXT,
		wiki_search_keywords VARCHAR(150) NOT NULL,
		PRIMARY KEY (`wiki_search_id`)
		)";
		$sql[] = "CREATE TABLE IF NOT EXISTS exp_wikis(
		wiki_id INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
		wiki_label_name VARCHAR(100) NOT NULL,
		wiki_short_name VARCHAR(50) NOT NULL,
		wiki_text_format VARCHAR(50) NOT NULL,
		wiki_html_format VARCHAR(10) NOT NULL,
		wiki_upload_dir INT(3) UNSIGNED NOT NULL DEFAULT '0',
		wiki_admins TEXT,
		wiki_users TEXT,
		wiki_revision_limit INT(8) UNSIGNED NOT NULL,
		wiki_author_limit INT(5) UNSIGNED NOT NULL ,
		wiki_moderation_emails TEXT,
		PRIMARY KEY (`wiki_id`)
		)";
		$sql[] = "CREATE TABLE IF NOT EXISTS `exp_wiki_categories` (
  		`cat_id` int(10) unsigned NOT NULL auto_increment,
  		`wiki_id` INT(8) UNSIGNED NOT NULL,
  		`cat_name` varchar(70) NOT NULL,
  		`parent_id` int(10) unsigned NOT NULL,
  		`cat_namespace` varchar(125) NOT NULL,
  		PRIMARY KEY  (`cat_id`),
  		KEY `wiki_id` (`wiki_id`)
  		)";
  		$sql[] = "CREATE TABLE IF NOT EXISTS `exp_wiki_category_articles` (
  		`page_id` INT(10) UNSIGNED NOT NULL,
  		`cat_id` INT(10) UNSIGNED NOT NULL,
  		KEY `page_id` (`page_id`),
		KEY `cat_id` (`cat_id`))";
    	$sql[] = "CREATE TABLE IF NOT EXISTS `exp_wiki_namespaces` (
		`namespace_id` int(6) NOT NULL auto_increment,
		`wiki_id` int(10) UNSIGNED NOT NULL,
		`namespace_name` varchar(100) NOT NULL,
		`namespace_label` varchar(150) NOT NULL,
		`namespace_users` TEXT,
  		`namespace_admins` TEXT,
		PRIMARY KEY  (`namespace_id`),
		KEY `wiki_id` (`wiki_id`))";
		
        foreach ($sql as $query)
        {
            $DB->query($query);
        }
        
        $wiki_id = $this->create_new_wiki();
        
        return TRUE;
    }
	/* END */    
    
    /** -------------------------------------
    /**  Module de-installer
    /** -------------------------------------*/

    function wiki_module_deinstall()
    {
        global $DB;   
        
        $query = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Wiki'"); 
                
        $sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";
        $sql[] = "DELETE FROM exp_modules WHERE module_name = 'Wiki'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Wiki'";
		$sql[] = "DROP TABLE IF EXISTS exp_wiki_page";
		$sql[] = "DROP TABLE IF EXISTS exp_wiki_revisions";
		$sql[] = "DROP TABLE IF EXISTS exp_wikis";
		$sql[] = "DROP TABLE IF EXISTS exp_wiki_uploads";
		$sql[] = "DROP TABLE IF EXISTS exp_wiki_search";
		$sql[] = "DROP TABLE IF EXISTS exp_wiki_categories";
		$sql[] = "DROP TABLE IF EXISTS exp_wiki_category_articles";
		$sql[] = "DROP TABLE IF EXISTS exp_wiki_namespaces";
    
        foreach ($sql as $query)
        {
            $DB->query($query);
        }

        return true;
    }
    /* END */


	/** ------------------------------------
    /**  Update Module
    /** ------------------------------------*/
    
    function wiki_module_update($current='')
    {
    	require PATH_MOD.'wiki/mod.wiki'.EXT;
    	
    	$WIKI = new Wiki(TRUE);
    	$WIKI->update_module($current);
    }
    /* END */


}
/* END Class */
?>
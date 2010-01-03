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
 File: mcp.updated_sites.php
-----------------------------------------------------
 Purpose: Updated Sites class - CP
=====================================================
*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}



class Updated_sites_CP {

    var $version = '1.0';
    var $field_array = array();
    var $group_array = array();
    
    
    /** -------------------------------------------
    /**  Constructor
    /** -------------------------------------------*/

	function Updated_sites_CP ($switch = TRUE)
	{
		global $IN, $DB;
		
		/** -------------------------------
		/**  Is the module installed?
		/** -------------------------------*/
        
        $query = $DB->query("SELECT COUNT(*) AS count FROM exp_modules WHERE module_name = 'Updated_sites'");
        
        if ($query->row['count'] == 0)
        {
        	return;
        }
		
		/** -------------------------------
		/**  On with the show!
		/** -------------------------------*/

		if ($switch)
        {
            switch($IN->GBL('P'))
            {
                case 'create'			:  $this->modify_configuration('new');
                    break;
                case 'modify'			:  $this->modify_configuration();
                	break;
                case 'save'				:  $this->save_configuration();
                    break;
                case 'delete_confirm'	: $this->delete_confirm();
                	break;
                case 'delete'			:  $this->delete_configs();
                    break;
                case 'pings'			:  $this->pings();
                    break;
                case 'delete_pings_conf':  $this->delete_pings_confirm();
                	break;
                case 'delete_pings'		:  $this->delete_pings();
                	break;
                default					:  $this->homepage();
                    break;
            }
        }
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Control Panel homepage
    /** -------------------------------------------*/

	function homepage($msg = '')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB;
                        
        $DSP->title  = $LANG->line('updated_sites_module_name');
        $DSP->crumb  = $LANG->line('updated_sites_module_name');
        
		$DSP->right_crumb($LANG->line('updated_sites_create_new'), BASE.AMP.'C=modules'.AMP.'M=updated_sites'.AMP.'P=create');
        
        $DSP->body .= $DSP->qdiv('tableHeading', $LANG->line('updated_sites_configurations')); 
        
        if ($msg != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
                
        $qs = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';
        $api_url = $FNS->fetch_site_index(0, 0).$qs.'ACT='.$FNS->fetch_action_id('Updated_sites', 'incoming');		
        
        $query = $DB->query("SELECT updated_sites_pref_name, updated_sites_id FROM exp_updated_sites");
        
        $DSP->body	.=	$DSP->toggle();
                
        $DSP->body	.=	$DSP->form_open(array('action' => 'C=modules'.AMP.'M=updated_sites'.AMP.'P=delete_confirm', 'name' => 'target', 'id' => 'target'));
    
        $DSP->body	.=	$DSP->table('tableBorder', '0', '0', '100%').
						$DSP->tr().
						$DSP->table_qcell('tableHeadingAlt', 
											array(
													$LANG->line('updated_sites_config_name').'/'.$LANG->line('edit'),
													$LANG->line('view_pings'),
													$LANG->line('updated_sites_config_url'),
													$DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\"").NBS.$LANG->line('delete').NBS.NBS
												 )
											).
						$DSP->tr_c();
		
		$i = 0;

		foreach ($query->result as $row)
		{				
			$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
                      
            $DSP->body .= $DSP->tr();
            
            $url = $api_url.'&id='.$row['updated_sites_id'];
            
            $DSP->body .= $DSP->table_qcell($style, $DSP->qdiv('defaultBold',
            														$DSP->anchor(BASE.AMP.'C=modules'.
            													   AMP.'M=updated_sites'.
            													   AMP.'P=modify'.
            													   AMP.'id='.$row['updated_sites_id'],
            													   $row['updated_sites_pref_name'])), '12%');
            													               
            $DSP->body .= $DSP->table_qcell($style, $DSP->qdiv('defaultBold',
            														$DSP->anchor(BASE.AMP.'C=modules'.
            													   AMP.'M=updated_sites'.
            													   AMP.'P=pings'.
            													   AMP.'id='.$row['updated_sites_id'],
            													   $LANG->line('view_pings'))), '12%');
            													   
            $DSP->body .= $DSP->table_qcell($style, $DSP->input_text('', $url, '20', '400', 'input', '100%'), '64%');
                                   													   
            $DSP->body .= $DSP->table_qcell($style, ($row['updated_sites_id'] == '1') ? ' -- ' : $DSP->input_checkbox('toggle[]', $row['updated_sites_id']), '12%');
											
			$DSP->body .= $DSP->tr_c();
		}
		
        $DSP->body	.=	$DSP->table_c(); 
    	
		$DSP->body	.=	$DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line('delete')));             
        
        $DSP->body	.=	$DSP->form_close();     
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Modify Configuration
    /** -------------------------------------------*/

    function modify_configuration($id = '')
    { 
        global $IN, $DSP, $LANG, $DB, $SESS;       
        
        $id = ( ! $IN->GBL('id', 'GET')) ? $id : $IN->GBL('id');
        
        /** ----------------------------
        /**  Form Values
        /** ----------------------------*/
        
        if ($id != 'new')
        {
        	$query = $DB->query("SELECT * FROM exp_updated_sites WHERE updated_sites_id = '{$id}'");
        	
        	if ($query->num_rows == 0)
        	{
        		return $this->homepage();
        	}
        	
        	foreach($query->row as $name => $pref)
        	{
        		${$name} = $pref;
        	}	
        }
        else
        {
        	$updated_sites_pref_name	= 'Updated Sites';
			$updated_sites_short_name	= 'updated_sites';
			$updated_sites_allowed		= '';
			$updated_sites_prune		=  500;
        }
                
        $DSP->title  = $LANG->line('updated_sites_module_name');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=updated_sites', $LANG->line('updated_sites_module_name'));
		$DSP->crumb .= ($id == 'new') ? $DSP->crumb_item($LANG->line('new_config')) : $DSP->crumb_item($LANG->line('modify_config'));
		
		$DSP->body .= ($id == 'new') ? $DSP->qdiv('tableHeading', $LANG->line('new_config')) : $DSP->qdiv('tableHeading', $LANG->line('modify_config'));
		
        $DSP->body .= $DSP->form_open(
									array(
											'action' => 'C=modules'.AMP.'M=updated_sites'.AMP.'P=save', 
											'name'	=> 'configuration', 
											'id' 	=> 'configuration'
										),
        							array('updated_sites_id' => $id)
        						);
            	
    	/** ---------------------------
    	/**  Begin Creating Form
    	/** ---------------------------*/
    				
		$r  =	$DSP->table('tableBorder', '0', '', '100%');
		$r .=	$DSP->tr();
		$r .=   $DSP->td('tableHeadingAlt', '', '2');
		$r .=   $LANG->line('configuration_options');
		$r .=   $DSP->td_c();
		$r .=	$DSP->tr_c();
		
		$i = 0;
		$style = ($i % 2) ? 'tableCellOne' : 'tableCellTwo';
		
		// PREF NAME
		$r .= $DSP->td($style, '50%', '');
		$r .= $DSP->div('defaultBold');
		$r .= $LANG->line('updated_sites_pref_name', 'updated_sites_pref_name');
		$r .= $DSP->div_c();
    	$r .= $DSP->td_c();
    	
    	$r .= $DSP->td($style, '50%', '');
    	$r .= $DSP->input_text('updated_sites_pref_name', $updated_sites_pref_name, '20', '120', 'input', '100%');
    	$r .= $DSP->td_c();
		$r .= $DSP->tr_c();
		
		$i++;
		$style = ($i % 2) ? 'tableCellOne' : 'tableCellTwo';
		
		// SHORT NAME - For display purposes
		$r .= $DSP->td($style, '50%', '');
		$r .= $DSP->div('defaultBold');
		$r .= $LANG->line('updated_sites_short_name', 'updated_sites_short_name').'-'.$DSP->nbs(2).$LANG->line('single_word_no_spaces');
		$r .= $DSP->div_c();
    	$r .= $DSP->td_c();
    	
    	$r .= $DSP->td($style, '50%', '');
    	$r .= $DSP->input_text('updated_sites_short_name', $updated_sites_short_name, '20', '120', 'input', '100%');
    	$r .= $DSP->td_c();
		$r .= $DSP->tr_c();
		
		$i++;
		$style = ($i % 2) ? 'tableCellOne' : 'tableCellTwo';
		
		// Allowed Sites
		$r .= $DSP->td($style, '50%', '1', '1', 'top');
		$r .= $DSP->div('defaultBold');
		$r .= $LANG->line('updated_sites_allowed', 'updated_sites_allowed');
		$r .= $DSP->div_c();
		$r .= $DSP->qdiv('subtext', $LANG->line('updated_sites_allowed_subtext'));
    	$r .= $DSP->td_c();
    	
    	$updated_sites_allowed = str_replace("|", "\n", trim($updated_sites_allowed));
    	
    	$r .= $DSP->td($style, '50%', '');
    	$r .= $DSP->input_textarea('updated_sites_allowed', trim($updated_sites_allowed));
    	$r .= $DSP->td_c();
		$r .= $DSP->tr_c();
		
		$i++;
		$style = ($i % 2) ? 'tableCellOne' : 'tableCellTwo';
		
		
		// PRUNING VALUE - Gotta keep it regular, har de har har
		$r .= $DSP->td($style, '50%', '');
		$r .= $DSP->div('defaultBold');
		$r .= $LANG->line('updated_sites_prune', 'updated_sites_prune');
		$r .= $DSP->div_c();
    	$r .= $DSP->td_c();
    	
    	$r .= $DSP->td($style, '50%', '');
    	$r .= $DSP->input_text('updated_sites_prune', $updated_sites_prune, '6', '10', 'input', '100%');
    	$r .= $DSP->td_c();
		$r .= $DSP->tr_c();
		
		$i++;
		$style = ($i % 2) ? 'tableCellOne' : 'tableCellTwo';
		

		$r .= $DSP->table_c();
		
		$r .= $DSP->qdiv('itemWrapperTop', $DSP->input_submit(($id == 'new') ? $LANG->line('submit') : $LANG->line('update')));
        
		$DSP->body .= $r.$DSP->form_close();       
	}
	/* END */
      

	/** -------------------------------------------
    /**  Save Configuration
    /** -------------------------------------------*/

    function save_configuration()
    {
    	global $IN, $DSP, $LANG, $DB, $OUT;
		
		$required	= array('updated_sites_id', 'updated_sites_pref_name', 'updated_sites_short_name',
							'updated_sites_allowed', 'updated_sites_prune');
							
    	$data = array();
    	
    	foreach($required as $var)
    	{
    		if ( ! isset($_POST[$var]) OR $_POST[$var] == '')
    		{
    			return $OUT->show_user_error('submission', $LANG->line('updated_sites_missing_fields'));
    		}
    		
    		$data[$var] = $_POST[$var];
    	}
    	
    	$data['updated_sites_allowed'] = str_replace("\n", "|", trim($data['updated_sites_allowed']));
    	
    	if ($_POST['updated_sites_id'] == 'new' )
    	{
    		$data['updated_sites_id'] = '';    		
    		$DB->query($DB->insert_string('exp_updated_sites', $data));
    		$message = $LANG->line('configuration_created');
    	}
    	else
    	{    		
			$DB->query($DB->update_string('exp_updated_sites', $data, "updated_sites_id = '".$DB->escape_str($_POST['updated_sites_id'])."'"));
			$message = $LANG->line('configuration_updated');
    	}
    	
    	$this->homepage($message);
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
            return $this->homepage();
        }
        
        $DSP->title = $LANG->line('updated_sites_module_name');
        $DSP->crumb = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=updated_sites', $LANG->line('updated_sites_module_name'));
		$DSP->crumb .= $DSP->crumb_item($LANG->line('delete'));

        $DSP->body	.=	$DSP->form_open(array('action' => 'C=modules'.AMP.'M=updated_sites'.AMP.'P=delete'));
        
        $i = 0;
        
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'toggle') AND ! is_array($val))
            {
                $DSP->body	.=	$DSP->input_hidden('delete[]', $val);
                
                $i++;
            }        
        }
        
		$DSP->body .= $DSP->heading($DSP->qspan('alert', $LANG->line('updated_sites_delete_confirm')));
		$DSP->body .= $DSP->div('box');
		$DSP->body .= $DSP->qdiv('defaultBold', $LANG->line('updated_sites_delete_question'));
		$DSP->body .= $DSP->qdiv('alert', BR.$LANG->line('action_can_not_be_undone'));
		$DSP->body .= $DSP->qdiv('', BR.$DSP->input_submit($LANG->line('delete')));
		$DSP->body .= $DSP->qdiv('alert',$DSP->div_c());
		$DSP->body .= $DSP->div_c();
		$DSP->body .= $DSP->form_close();
    }
    /* END */   
    
    
    
    /** -------------------------------------------
    /**  Delete Configurations
    /** -------------------------------------------*/

    function delete_configs()
    { 
        global $IN, $DSP, $LANG, $SESS, $DB;        
        
        if ( ! $IN->GBL('delete', 'POST'))
        {
            return $this->homepage();
        }

        $ids = array();
                
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'delete') AND ! is_array($val))
            {
                $ids[] = "updated_sites_id = '".$DB->escape_str($val)."'";
            }        
        }
        
        $IDS = implode(" OR ", $ids);
        
        $DB->query("DELETE FROM exp_updated_sites WHERE ".$IDS);
    
        $message = (count($ids) == 1) ? $LANG->line('updated_site_deleted') : $LANG->line('updated_sites_deleted');

        return $this->homepage($message);
    }
    /* END */ 
    
    
    /** -------------------------------------------
    /**  View Pings
    /** -------------------------------------------*/
    
    function pings($id='1', $message='')
    {
        global $IN, $DSP, $LANG, $FNS, $DB, $LOC, $PREFS;
                
        $id = ( ! $IN->GBL('id', 'GET')) ? $id : $IN->GBL('id');
        
        if ( ! $rownum = $IN->GBL('rownum', 'GP'))
        {        
            $rownum = 0;
        }
        
        $perpage = 100;

		$qm = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';
                        
        $DSP->title  = $LANG->line('updated_sites_module_name');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=updated_sites', $LANG->line('updated_sites_module_name'));
        $DSP->crumb .= $DSP->crumb_item($LANG->line('view_pings'));    

        $r = $DSP->qdiv('tableHeading', $LANG->line('view_pings'));     
        
        if ($message != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $message));
        }
        
        $query = $DB->query("SELECT COUNT(*) AS count FROM exp_updated_site_pings
        					 WHERE ping_config_id = '{$id}'");

        if ($query->row['count'] == 0)
        {
            $r .= $DSP->qdiv('box', $DSP->qdiv('highlight', $LANG->line('no_pings')));
        
            $DSP->body .= $r;        

            return;
        }        
        
        $total = $query->row['count'];
        
        $r .= $DSP->qdiv('box', $LANG->line('total_pings').NBS.NBS.$total);
        
        $query = $DB->query("SELECT * FROM exp_updated_site_pings 
        					WHERE ping_config_id = '{$id}' 
        					ORDER BY ping_date desc LIMIT $rownum, $perpage");
        					
        $r .= $DSP->toggle();

		$r .= <<<EOT

<script type="text/javascript">
function showHide(entryID, htmlObj, linkType) {

extTextDivID = ('extText' + (entryID));
extLinkDivID = ('extLink' + (entryID));

if (linkType == 'close')
{
	document.getElementById(extTextDivID).style.display = "none";
	document.getElementById(extLinkDivID).style.display = "block";
	htmlObj.blur();
}
else
{
	document.getElementById(extTextDivID).style.display = "block";
	document.getElementById(extLinkDivID).style.display = "none";
	htmlObj.blur();
}

}
</script>

EOT;

		$r .= $DSP->form_open(
								array(
										'action' => 'C=modules'.AMP.'M=updated_sites'.AMP.'P=delete_pings_conf', 
										'name' 	=> 'target', 
										'id' 	=> 'target'
									),
								array('config_id' => $id)
							);
				
        $r .= $DSP->table('tableBorder', '0', '', '100%').
              $DSP->tr().
              $DSP->table_qcell('tableHeadingAlt', 
                                array(
                                        $DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\"").$LANG->line('delete'),
                                        $LANG->line('ping_name'),
                                        $LANG->line('ping_url'),
                                        $LANG->line('ping_rss'),
                                        $LANG->line('ping_date')
                                     )
                                ).
              $DSP->tr_c();


        $i = 0;
        
		$site_url = $PREFS->ini('site_url');
        
        foreach($query->result as $row)
        {
            $style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';;
                      
            $r .= $DSP->tr();
            
            // Delete Toggle
            
            $r .= $DSP->table_qcell($style, $DSP->input_checkbox('toggle[]', $row['ping_id']), '8%');
            
            // ping_site_name, ping_site_url, ping_site_check, ping_site_rss, ping_date, ping_ipaddress
            
            // SITE NAME
        	$r .= $DSP->table_qcell($style, $row['ping_site_name'], '22%');
            
            
            // Site URL
            $row['ping_site_url'] = str_replace('http://','',$row['ping_site_url']);
            
            if (strlen($row['ping_site_url']) > 40)
            {
            	$from_pieces = explode('/',$row['ping_site_url']);
            	
            	$new_from = $from_pieces['0'].'/';
            	
            	for($p=1; $p < sizeof($from_pieces); $p++)
            	{
            		if (strlen($from_pieces[$p]) + strlen($new_from) <= 40)
            		{
            			$new_from .= ($p == (sizeof($from_pieces) - 1)) ? $from_pieces[$p] : $from_pieces[$p].'/';
            		}
            		else
            		{
            			$new_from .= '&#8230;';
            			break;
            		}
            	} 
            }
            else
            {
            	$new_from = $row['ping_site_url'];
            }
            
            $r .= $DSP->table_qcell($style, $DSP->anchor($FNS->fetch_site_index().$qm.'URL='.$row['ping_site_url'], $new_from, '', 1), '28%');
        
        
            // Site RSS
            $row['ping_site_rss'] = str_replace('http://','',$row['ping_site_rss']);
            
            if (strlen($row['ping_site_rss']) == 0)
            {
            	$r .= $DSP->table_qcell($style, '--', '28%');
            }
            else
            {
            	if (strlen($row['ping_site_rss']) > 40)
            	{
            		$from_pieces = explode('/',$row['ping_site_rss']);
            		
            		$new_from = $from_pieces['0'].'/';
            		
            		for($p=1; $p < sizeof($from_pieces); $p++)
            		{
            			if (strlen($from_pieces[$p]) + strlen($new_from) <= 40)
            			{
            				$new_from .= ($p == (sizeof($from_pieces) - 1)) ? $from_pieces[$p] : $from_pieces[$p].'/';
            			}
            			else
            			{
            				$new_from .= '&#8230;';
            				break;
            			}
            		} 
            	}
            	else
            	{
            		$new_from = $row['ping_site_rss'];
            	}
            	
            	$r .= $DSP->table_qcell($style, $DSP->anchor($FNS->fetch_site_index().$qm.'URL='.$row['ping_site_rss'], $new_from, '', 1), '28%');
            }
        
        
        	// Date
        	$date = ($row['ping_date'] != '' AND $row['ping_date'] != 0) ? $LOC->set_human_time($row['ping_date']) : '-';
        	
        	$r .= $DSP->table_qcell($style, $date, '14%');
        	
        	
			$r .= $DSP->tr_c();          
        }

        $r .= $DSP->table_c();
             
        $r .= $DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line('delete')));             
        
        $r .= $DSP->form_close();     

        // Pass the relevant data to the paginate class so it can display the "next page" links
        
        $r .=  $DSP->div('itemWrapper').
               $DSP->pager(
                            BASE.AMP.'C=modules'.AMP.'M=referrer'.AMP.'P=view',
                            $total,
                            $perpage,
                            $rownum,
                            'rownum'
                          ).
              $DSP->div_c();


        $DSP->body .= $r;        
    }
    /* END */
    
    
    
    /** -------------------------------------------
    /**  Delete Pings Confirmation Page
    /** -------------------------------------------*/
    
    function delete_pings_confirm()
    {
    	global $LANG, $IN, $DSP;
    	
    	$id = ( ! $IN->GBL('config_id', 'GET')) ? '1' : $IN->GBL('config_id');
    	
    	if ( ! $IN->GBL('toggle', 'POST'))
        {
            return $this->pings($id);
        }
        
        $DSP->title  = $LANG->line('updated_sites_module_name');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=updated_sites', $LANG->line('updated_sites_module_name'));
        $DSP->crumb .= $DSP->crumb_item($LANG->line('delete_pings_confirm'));   
        
        $DSP->body	.=	$DSP->form_open(
										array('action' => 'C=modules'.AMP.'M=updated_sites'.AMP.'P=delete_pings'),
										array('config_id' => $id)
        								);
        
        $i = 0;
        
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'toggle') AND ! is_array($val))
            {
                $DSP->body .= $DSP->input_hidden('delete[]', $val);
                
                $i++;
            }        
        }
        
        $DSP->body .= $DSP->qdiv('alertHeading', $LANG->line('delete_pings_confirm'));
		$DSP->body .= $DSP->div('box');
		$DSP->body .= $DSP->qdiv('defaultBold', $LANG->line('ping_delete_question'));
		$DSP->body .= $DSP->qdiv('alert', BR.$LANG->line('action_can_not_be_undone'));
		$DSP->body .= $DSP->div_c();
		
		$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->input_submit($LANG->line('delete')));
		$DSP->body .= $DSP->form_close(); 	
    }
    /* END */
    
    
    /** -------------------------------------------
    /**  Delete Pings
    /** -------------------------------------------*/
    
    function delete_pings()
    {
    	global $DB, $IN, $LANG;
    	
    	$id = ( ! $IN->GBL('config_id', 'GET')) ? '1' : $IN->GBL('config_id');
    	
    	if ( ! $IN->GBL('delete', 'POST'))
        {
            return $this->pings($id);
        }
        
        $ids = array();
                
        foreach ($_POST as $key => $val)
        {        
            if (strstr($key, 'delete') AND ! is_array($val))
            {
                $ids[] = $val;
            }
        }
        
        /** --------------------------
        /**  Delete Referrers
        /** --------------------------*/
        
        $DB->query("DELETE FROM exp_updated_site_pings WHERE ping_id IN ('".implode("','", $ids)."')");
    
        $message = $LANG->line('pings_deleted');

        return $this->pings($id, $message);
    }
    /* END */
    
    
    
    
    
    
	

    /** -------------------------------------------
    /**  Module installer
    /** -------------------------------------------*/

    function updated_sites_module_install()
    {
        global $DB;        
        
        $sql[] = "INSERT INTO exp_modules 
        		  (module_id, module_name, module_version, has_cp_backend) 
        		  VALUES 
        		  ('', 'Updated_sites', '$this->version', 'y')";
        		  
    	$sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Updated_sites', 'incoming')";
    	
    	$sql[] = "CREATE TABLE IF NOT EXISTS `exp_updated_sites` (
    			 `updated_sites_id` int(5) unsigned NOT NULL auto_increment,
    			 `updated_sites_pref_name` varchar(80) NOT NULL default '',
    			 `updated_sites_short_name` varchar(60) NOT NULL default '',
    			 `updated_sites_allowed` text NOT NULL,
    			 `updated_sites_prune` int(6) NOT NULL default '0',
    			 PRIMARY KEY (`updated_sites_id`));";	
    			 
    	$sql[] = "CREATE TABLE IF NOT EXISTS `exp_updated_site_pings` (
    			 `ping_id` int(10) unsigned NOT NULL auto_increment,
    			 `ping_site_name` varchar(80) NOT NULL default '',
    			 `ping_site_url` varchar(80) NOT NULL default '',
    			 `ping_site_check` varchar(80) NOT NULL default '',
    			 `ping_site_rss` varchar(80) NOT NULL default '',
    			 `ping_date` int(10) NOT NULL default '0',
    			 `ping_ipaddress` varchar(16) NOT NULL default '',
    			 `ping_config_id` int(4) NOT NULL default '1',
    			 PRIMARY KEY (`ping_id`),
    			 KEY `ping_config_id` (`ping_config_id`));";	
    			 
 		$sql[] = "INSERT INTO exp_updated_sites 
 				  (updated_sites_id, updated_sites_pref_name, updated_sites_short_name, updated_sites_allowed, updated_sites_prune) 
 				  VALUES ('', 'Default', 'default', '', '500')";

        foreach ($sql as $query)
        {
            $DB->query($query);
        }
        
        return true;
    }
    /* END */
    
    
    /** -------------------------------------------
    /**  Module de-installer
    /** -------------------------------------------*/

    function updated_sites_module_deinstall()
    {
        global $DB;    

        $query = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Updated_sites'"); 
                
        $sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";        
        $sql[] = "DELETE FROM exp_modules WHERE module_name = 'Updated_sites'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Updated_sites'";
        $sql[] = "DROP TABLE IF EXISTS exp_updated_sites";
        $sql[] = "DROP TABLE IF EXISTS exp_updated_site_pings";

        foreach ($sql as $query)
        {
            $DB->query($query);
        }

        return true;
    }
    /* END */



}
/* END */
?>
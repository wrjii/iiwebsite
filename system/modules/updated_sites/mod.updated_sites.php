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
 File: mod.updated_sites.php
-----------------------------------------------------
 Purpose: Updated Sites Functionality
=====================================================

*/


if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Updated_sites {

    var $return_data	= ''; 		// Bah!
    var $LB				= "\r\n";	// Line Break for Entry Output
    
    var $id				= 1;		// Id of Configuration
    var $allowed		= array();
    var $prune			= 500;
    var $throttle		= 15;		 // Minutes between pings

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function Updated_sites()
    {        
    }
    /* END */
    
    
    /** -----------------------------------------
    /**  USAGE: Incoming MetaWeblog API Requests
    /** -----------------------------------------*/
    
    function incoming()
    {
    	global $LANG, $IN, $DB;
    
    	/** ---------------------------------
    	/**  Load the XML-RPC Files
    	/** ---------------------------------*/
    	
    	if ( ! class_exists('XML_RPC'))
		{
			require PATH_CORE.'core.xmlrpc'.EXT;
		}
		
		if ( ! class_exists('XML_RPC_Server'))
		{
			require PATH_CORE.'core.xmlrpcs'.EXT;
		}
		
		/** ---------------------------------
    	/**  Specify Functions
    	/** ---------------------------------*/
    	
    	$functions = array( 'weblogUpdates.extendedPing' => array(
																  'function' => 'Updated_sites.extended',
																  'signature' => array(array('string', 'string','string', 'string')),
																  'docstring' => 'Extended Pings for An EE Site'),
							'weblogUpdates.ping' 		 => array(
																  'function' => 'Updated_sites.regular',
																  'signature' => array(array('string', 'string')),
																  'docstring' => 'Weblogs.com Pings for An EE Site'),);
							
							
		/** ---------------------------------
    	/**  Instantiate the Server Class
    	/** ---------------------------------*/
    	
		$server = new XML_RPC_Server($functions);
    }
    /* END */
    
    
    
    /** -----------------------------------------
    /**  USAGE: Load Configuration Options
    /** -----------------------------------------*/
    
    function _load_config()
    {
    	global $IN, $LANG, $DB;
    	
    	$LANG->fetch_language_file('updated_sites');
    	
    	$this->id = ( ! $IN->GBL('id', 'GET')) ? '1' : $IN->GBL('id');
    	
    	$query = $DB->query("SELECT updated_sites_allowed, updated_sites_prune FROM exp_updated_sites 
    						 WHERE updated_sites_id = '".$DB->escape_str($this->id)."'");
    		
    	if ($query->num_rows > 0)
    	{
    		$this->allowed	= explode('|', trim($query->row['updated_sites_allowed']));
    		$this->prune	= $query->row['updated_sites_prune'];
    	}
    }
     /* END */
     
    
    
	/** -----------------------------------------
    /**  USAGE: Extended Ping
    /** -----------------------------------------*/
    
    function extended($plist)
    {
    	global $DB, $LOC, $REGX, $IN, $LANG;
    	
    	$parameters = $plist->output_parameters();
    	
    	$this->_load_config();
    	
    	if ($this->check_urls(array($parameters['1'], $parameters['2'], $parameters['3'])) !== TRUE)
    	{
    		return $this->error($LANG->line('invalid_access'));
    	}
    	
    	if ($this->throttle_check($parameters['1']) !== TRUE)
    	{
    		return $this->error(str_replace('%X', $this->throttle, $LANG->line('too_many_pings')));
    	}
    	
    	$data = array('ping_id'			=> '',
    				  'ping_site_name'	=> $REGX->xss_clean(strip_tags($parameters['0'])),
    				  'ping_site_url'	=> $REGX->xss_clean(strip_tags($parameters['1'])),
    				  'ping_site_check'	=> $REGX->xss_clean(strip_tags($parameters['2'])),
    				  'ping_site_rss'	=> $REGX->xss_clean(strip_tags($parameters['3'])),
    				  'ping_date'		=> $LOC->now,
    				  'ping_ipaddress'	=> $IN->IP,
    				  'ping_config_id'	=> $this->id);
    				  
    	$DB->query($DB->insert_string('exp_updated_site_pings', $data));
    	
    	return $this->success();
    }
    /* END */
    
    
    /** -----------------------------------------
    /**  USAGE: Regular/Decaf Weblogs.com Ping
    /** -----------------------------------------*/
    
    function regular($plist)
    {
    	global $DB, $LOC, $IN, $REGX, $LANG;
    	
    	$parameters = $plist->output_parameters();
    	
    	$this->_load_config();
    	
    	if ($this->check_urls(array($parameters['1'])) !== TRUE)
    	{
    		return $this->error($LANG->line('invalid_access'));
    	}
    	
    	if ($this->throttle_check($parameters['1']) !== TRUE)
    	{
    		return $this->error(str_replace('%X', $this->throttle, $LANG->line('too_many_pings')));
    	}
    	
    	$data = array('ping_id'			=> '',
    				  'ping_site_name'	=> $REGX->xss_clean(strip_tags($parameters['0'])),
    				  'ping_site_url'	=> $REGX->xss_clean(strip_tags($parameters['1'])),
    				  'ping_date'		=> $LOC->now,
    				  'ping_ipaddress'	=> $IN->IP,
    				  'ping_config_id'	=> $this->id);
    				  
    	$DB->query($DB->insert_string('exp_updated_site_pings', $data));
    	
    	return $this->success();
    }
    /* END */
    
	
	/** -----------------------------------------
    /**  USAGE: Validate Incoming URLs
    /** -----------------------------------------*/
    
    function check_urls($urls)
    {
    	if ( ! is_array($urls) OR sizeof($urls) == 0 OR ! is_array($this->allowed) OR sizeof($this->allowed) == 0)
    	{
    		return FALSE;
    	}
    	
    	$approved = 'n';
    	
    	for($i=0, $s = sizeof($urls); $i < $s && $approved == 'n'; ++$i)
    	{
    		if (trim($urls[$i]) == '')
    		{
    			continue;
    		}
    		
    		if (stristr($urls[$i], '{') !== FALSE OR stristr($urls[$i], '}') !== FALSE)
        	{
        		return FALSE;
        	}
    		
    		for	($l=0, $sl = sizeof($this->allowed); $l < $sl && $approved == 'n'; ++$l)
    		{
    			if (trim($this->allowed[$l]) == '') continue;
    			
    			if (stristr($urls[$i], $this->allowed[$l]) !== FALSE)
    			{
    				$approved = 'y';
    			}
    		}
    	}
    	
    	if ($approved == 'n')
    	{
    		return FALSE;
    	}
    	
    	return TRUE;
    }
    /* END */
    
    
    
    /** -----------------------------------------
    /**  USAGE: Security Check
    /** -----------------------------------------*/
    
    function throttle_check($url)
    {
    	global $LOC, $IN, $DB;
    	
    	/** ---------------------------------------------
        /**  Throttling - Only one ping every X minutes
        /** ---------------------------------------------*/
        	
        $query = $DB->query("SELECT COUNT(*) AS count 
        					 FROM exp_updated_site_pings
        					 WHERE (ping_site_url = '".$DB->escape_str($url)."' OR ping_ipaddress = '{$IN->IP}')
        					 AND ping_date > '".($LOC->now-($this->throttle*60))."'");
        					 
        if ($query->row['count'] > 0)
        {
        	return FALSE;
        }  
        
        return TRUE;
    }
    /* END */
    
    
    
    /** -----------------------------------------
    /**  USAGE: XML-RPC Error Message
    /** -----------------------------------------*/
    
    function error($message)
    {
    	return new XML_RPC_Response('0','401', $message);
    }
    /* END */
    
    
	/** -----------------------------------------
    /**  USAGE: So Long and Thanks for All the Fish!
    /** -----------------------------------------*/
    
    function success()
    {
    	global $DB, $LANG;
    	
    	/** ----------------------------------
		/**  Prune Database
		/** ----------------------------------*/
			
		srand(time());
	
		if ((rand() % 100) < 5) 
		{              
			if ( ! is_numeric($this->prune) OR $this->prune == 0)
			{
				$this->prune = 500;
			}
			
			$query = $DB->query("SELECT MAX(ping_id) as ping_id FROM exp_updated_site_pings");
				
			if ( ! empty($query->row['ping_id']))
			{
				$DB->query("DELETE FROM exp_updated_site_pings WHERE ping_id < ".($query->row['ping_id']-$this->prune)."");
			}
		}
    
    	/** ----------------------------------
		/**  Send Success Message
		/** ----------------------------------*/
    
    	$response = new XML_RPC_Response(new XML_RPC_Values(array('flerror' => new XML_RPC_Values('0',"boolean"),
    										 					  'message' => new XML_RPC_Values($LANG->line('successful_ping'),"string")),'struct'));
    										 
		return $response;
    }
    /* END */
    
    
    
	/** -----------------------------------------
    /**  USAGE: Entries Tag
    /** -----------------------------------------*/
    
    function pings()
    {
    	global $LANG, $LOC, $FNS, $DB, $TMPL;
    	
		/** -------------------------------------
        /**  Build query
        /** -------------------------------------*/

        $sql = "SELECT m.* FROM exp_updated_site_pings m, exp_updated_sites s
        		WHERE m.ping_config_id = s.updated_sites_id ";
        		
       	if ($which = $TMPL->fetch_param('which'))
       	{
       		$sql .= $FNS->sql_andor_string($which, 'updated_sites_short_name', 's');
       	}
       	
       	$order  = $TMPL->fetch_param('orderby');
		$sort   = $TMPL->fetch_param('sort');
		
		switch($order)
		{
			case 'name' :
				$sql .= " ORDER BY m.ping_date ";
			break;
			case 'url' : 
				$sql .= " ORDER BY m.ping_site_url ";
			break;
			case 'rss' :
				$sql .= " ORDER BY m.ping_site_url ";
			break;
			default:
				$sql .= " ORDER BY m.ping_date ";
			break;
		}
		
		if ($sort == FALSE || ($sort != 'asc' AND $sort != 'desc'))
		{
			$sort = "desc";
		}
		
		$sql .= $sort;
        
    
        if ( ! $TMPL->fetch_param('limit'))
        {
            $sql .= " LIMIT 100";
        }
        else
        {
            $sql .= " LIMIT ".$TMPL->fetch_param('limit');
        }

        $query = $DB->query($sql);
        
       	if ($query->num_rows == 0)
        {
        	return $TMPL->no_results();
        }
        
        $total_results = sizeof($query->result);
    
    	foreach($query->result as $count => $row)
    	{
    		$tagdata = $TMPL->tagdata;
    	
    		$row['count']			= $count+1;
    		$row['total_results']	= $total_results;
    		
    		/** ----------------------------------------
			/**  Conditionals
			/** ----------------------------------------*/
			
			$tagdata = $FNS->prep_conditionals($tagdata, $row);
            
            /** ----------------------------------------
			/**  Parse "single" variables
			/** ----------------------------------------*/
    
			foreach ($TMPL->var_single as $key => $val)
			{
				/** ----------------------------------------
				/**  parse {switch} variable
				/** ----------------------------------------*/
				
				if (strncmp('switch', $key, 6) == 0)
				{
					$sparam = $FNS->assign_parameters($key);
					$sw = '';
					
					if (isset($sparam['switch']))
					{
						$sopt = explode("|", $sparam['switch']);
						
						if (count($sopt) == 2)
						{
							if (isset($switch[$sparam['switch']]) AND $switch[$sparam['switch']] == $sopt['0'])
							{
								$switch[$sparam['switch']] = $sopt['1'];
								
								$sw = $sopt['1'];
							}
							else
							{
								$switch[$sparam['switch']] = $sopt['0'];
								
								$sw = $sopt['0'];
							}
						}
					}
					
					$tagdata = $TMPL->swap_var_single($key, $sw, $tagdata);
				}
				
				/** ----------------------------------------
				/**  {ping_date}
				/** ----------------------------------------*/
				
				if (strncmp('ping_date', $key, 9) == 0)
				{
					if ( ! isset($row['ping_date']) || $row['ping_date'] == 0)
                    {
                    	$date = '-';
                    }
                    else
  					{
						$date = $LOC->decode_date($val, $row['ping_date']);
                    }
				
					$tagdata = $TMPL->swap_var_single($key, $date, $tagdata);
				}
				
				/** ----------------------------------------
				/**  Remaining Data
				/** ----------------------------------------*/
				
				if (in_array($key, array('ping_site_name', 'ping_site_url', 'ping_site_check', 'ping_site_rss', 'ping_ipaddress')))
				{
					$rdata = ( ! isset($row[$key]) OR $row[$key] == '') ? '-' : $row[$key];
				
					$tagdata = $TMPL->swap_var_single($val, $rdata, $tagdata);
				}
    		}
    		
    		$this->return_data .= $tagdata;
    	}
    	
    	return $this->return_data;
    }
    /* END */
    
    
    
}
/* END */
?>
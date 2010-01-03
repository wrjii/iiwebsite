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
 File: mod.query.php
-----------------------------------------------------
 Purpose: Allows direct SQL queries in templates
=====================================================

EXAMPLE:

{exp:query sql="select * from exp_members where username = 'joe' "}

 <h1>{username}</h1>
 
 <p>{email}</p>
 
 <p>{url}</p>

{/exp:query}

*/


if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Query {

    var $return_data = ''; 
    
	// Pagination variables
	
    var $paginate				= FALSE;
    var $pagination_links		= '';
    var $page_next				= '';
    var $page_previous			= '';
	var $current_page			= 1;
	var $total_pages			= 1;
	var $total_rows				=  0;
	var $p_limit				= '';
	var $p_page					= '';
	var $basepath				= '';
	var $uristr					= '';

    /** -------------------------------------
    /**  Constructor
    /** -------------------------------------*/

    function Query()
    {        
        $this->query = $this->basic_select();
    }
    /* END */



    /** -------------------------------------
    /**  Basic SQL 'select' query
    /** -------------------------------------*/

    function basic_select()
    {
        global $DB, $FNS, $TMPL, $LOC, $IN, $PREFS;
        
        // Extract the query from the tag chunk
        
        if (FALSE === $TMPL->fetch_param('sql'))
            return false;
        
        $sql = str_replace(SLASH, '/', $TMPL->fetch_param('sql'));
             
        if (substr(strtolower(trim($sql)), 0, 6) != 'select')
            return false;
		
		/** --------------------------------------
		/**  Pagination checkeroo!
		/** --------------------------------------*/
		
		if (preg_match("/".LD."paginate".RD."(.+?)".LD.SLASH."paginate".RD."/s", $TMPL->tagdata, $match))
		{ 
			// Run the query
						
			$query = $DB->query(preg_replace("/SELECT(.*?)\s+FROM\s+/is", 'SELECT COUNT(*) AS count FROM ', $sql));
			
			if ($query->row['count'] == 0)
			{
				return $this->return_data = $TMPL->no_results();
			}
		
			$this->paginate		 = TRUE;
			$this->paginate_data = $match['1'];
			$this->basepath		 = $FNS->create_url($IN->URI, 1);
						
			$TMPL->tagdata = preg_replace("/".LD."paginate".RD.".+?".LD.SLASH."paginate".RD."/s", "", $TMPL->tagdata);
			
			if ($IN->QSTR != '' && preg_match("#^P(\d+)|/P(\d+)#", $IN->QSTR, $match))
			{					
				$this->p_page = (isset($match['2'])) ? $match['2'] : $match['1'];	
					
				$this->basepath = $FNS->remove_double_slashes(str_replace($match['0'], '', $this->basepath));
			}
			
			$this->total_rows = $query->row['count'];
			$this->p_limit  = ( ! $TMPL->fetch_param('limit'))  ? 50 : $TMPL->fetch_param('limit');
			$this->p_page = ($this->p_page == '' || ($this->p_limit > 1 AND $this->p_page == 1)) ? 0 : $this->p_page;
				
			if ($this->p_page > $this->total_rows)
			{
				$this->p_page = 0;
			}
								
			$this->current_page = floor(($this->p_page / $this->p_limit) + 1);
				
			$this->total_pages = intval(floor($this->total_rows / $this->p_limit));
			
			/** ----------------------------------------
			/**  Create the pagination
			/** ----------------------------------------*/
			
			if ($this->total_rows % $this->p_limit) 
			{
				$this->total_pages++;
			}
			
			if ($this->total_rows > $this->p_limit)
			{
				if ( ! class_exists('Paginate'))
				{
					require PATH_CORE.'core.paginate'.EXT;
				}
				
				$PGR = new Paginate();
				
				if ( ! stristr($this->basepath, SELF) AND $PREFS->ini('site_index') != '')
				{
					$this->basepath .= SELF.'/';
				}
																	
				$first_url = (preg_match("#\.php/$#", $this->basepath)) ? substr($this->basepath, 0, -1) : $this->basepath;			
				
				$PGR->first_url 	= $first_url;
				$PGR->path			= $this->basepath;
				$PGR->prefix		= 'P';
				$PGR->total_count 	= $this->total_rows;
				$PGR->per_page		= $this->p_limit;
				$PGR->cur_page		= $this->p_page;

				$this->pagination_links = $PGR->show_links();
				
				if ((($this->total_pages * $this->p_limit) - $this->p_limit) > $this->p_page)
				{
					$this->page_next = $this->basepath.'P'.($this->p_page + $this->p_limit).'/';
				}
				
				if (($this->p_page - $this->p_limit ) >= 0) 
				{						
					$this->page_previous = $this->basepath.'P'.($this->p_page - $this->p_limit).'/';
				}
				
				$sql .= " LIMIT ".$this->p_page.', '.$this->p_limit;
			}
			else
			{
				$this->p_page = '';
			}
		}
		
		$query = $DB->query($sql);
		
		if ($query->num_rows == 0)
		{
			return $this->return_data = $TMPL->no_results();
		}
		
		/** --------------------------------------
		/**  Indy!  Bad Dates!
		/** --------------------------------------*/
		
		$dates = array();
		
		if (preg_match_all("/".LD."([a-z\_]*?)\s+format=[\"'](.*?)[\"']".RD."/is", $TMPL->tagdata, $matches))
		{
			for ($j = 0; $j < count($matches['0']); $j++)
			{
				$matches['0'][$j] = str_replace(array(LD, RD), '', $matches['0'][$j]);
					
				if ( isset($query->row[$matches['1'][$j]]) && is_numeric($query->row[$matches['1'][$j]]))
				{
					$dates[$matches['0'][$j]] = array($matches['1'][$j], $LOC->fetch_date_params($matches['2'][$j]));
				}
			}
		}
		
		$total_results = sizeof($query->result);
		
        foreach ($query->result as $count => $row)
        {
            $tagdata = $TMPL->tagdata;
            
            $row['count']			= $count+1;
            $row['total_results']	= $total_results;

            /** ----------------------------------------
			/**  Conditionals
			/** ----------------------------------------*/
			
			$tagdata = $FNS->prep_conditionals($tagdata, $row);
 
            /** ----------------------------------------
			/**  Single Variables
			/** ----------------------------------------*/
			
			foreach ($TMPL->var_single as $key => $val)
            {       
            	if (isset($dates[$key]))
            	{
            		foreach ($dates[$key]['1'] as $dvar)
						$val = str_replace($dvar, $LOC->convert_timestamp($dvar, $row[$dates[$key]['0']], TRUE), $val);					

					$tagdata = $TMPL->swap_var_single($key, $val, $tagdata);
            	}
                elseif (isset($row[$val]))
                {                    
                    $tagdata = $TMPL->swap_var_single($val, $row[$val], $tagdata);
                }
                
                if (strncmp('switch', $key, 6) == 0)
				{
					$sparam = $FNS->assign_parameters($key);

					$sw = '';

					if (isset($sparam['switch']))
					{
						$sopt = @explode("|", $sparam['switch']);
						$sw = $sopt[($count + count($sopt)) % count($sopt)];
					}

					$tagdata = $TMPL->swap_var_single($key, $sw, $tagdata);
				}
            }
            
          $this->return_data .= $tagdata;
        }

		if (($backspace = $TMPL->fetch_param('backspace')) !== FALSE && is_numeric($backspace))
		{
			$this->return_data = substr($this->return_data, 0, -$backspace);
		}        

        if ($this->paginate == TRUE)
        {
			$this->paginate_data = str_replace(LD.'current_page'.RD, $this->current_page, $this->paginate_data);
			$this->paginate_data = str_replace(LD.'total_pages'.RD,	$this->total_pages, $this->paginate_data);
			$this->paginate_data = str_replace(LD.'pagination_links'.RD, $this->pagination_links, $this->paginate_data);
        	
        	if (preg_match("/".LD."if previous_page".RD."(.+?)".LD.SLASH."if".RD."/s", $this->paginate_data, $match))
        	{
        		if ($this->page_previous == '')
        		{
        			 $this->paginate_data = preg_replace("/".LD."if previous_page".RD.".+?".LD.SLASH."if".RD."/s", '', $this->paginate_data);
        		}
        		else
        		{
					$match['1'] = preg_replace("/".LD.'path.*?'.RD."/", 	$this->page_previous, $match['1']);
					$match['1'] = preg_replace("/".LD.'auto_path'.RD."/",	$this->page_previous, $match['1']);
			
					$this->paginate_data = str_replace($match['0'],	$match['1'], $this->paginate_data);
				}
       	 	}
        	
        	
        	if (preg_match("/".LD."if next_page".RD."(.+?)".LD.SLASH."if".RD."/s", $this->paginate_data, $match))
        	{
        		if ($this->page_next == '')
        		{
        			 $this->paginate_data = preg_replace("/".LD."if next_page".RD.".+?".LD.SLASH."if".RD."/s", '', $this->paginate_data);
        		}
        		else
        		{
					$match['1'] = preg_replace("/".LD.'path.*?'.RD."/", 	$this->page_next, $match['1']);
					$match['1'] = preg_replace("/".LD.'auto_path'.RD."/",	$this->page_next, $match['1']);
			
					$this->paginate_data = str_replace($match['0'],	$match['1'], $this->paginate_data);
				}
        	}
                
			$position = ( ! $TMPL->fetch_param('paginate')) ? '' : $TMPL->fetch_param('paginate');
			
			switch ($position)
			{
				case "top"	: $this->return_data  = $this->paginate_data.$this->return_data;
					break;
				case "both"	: $this->return_data  = $this->paginate_data.$this->return_data.$this->paginate_data;
					break;
				default		: $this->return_data .= $this->paginate_data;
					break;
			}
        }
        
        
    }
    /* END */
    
    
}
// END CLASS
?>
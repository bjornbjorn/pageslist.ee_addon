<?php 

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name' => 'PagesList',
  'pi_version' => '1.0',
  'pi_author' => 'Bjorn Borresen',
  'pi_author_url' => 'http://bybjorn.com/',
  'pi_description' => 'List site pages',
  'pi_usage' => Pageslist::usage()
  );

/**
 * PagesList
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Bjorn Borresen
 * @copyright		Copyright (c) 2009, Bjorn Borresen
 * @link			http://ee.bybjorn.com/pageslist
 */

class Pageslist
{
	
	var $return_data = "";
	 
	function Pageslist()
	{
		$this->EE =& get_instance();
		
		$conditional_field = $this->EE->TMPL->fetch_param('conditional_field');
		$conditional_field_value = $this->EE->TMPL->fetch_param('conditional_field_value');
		$selected_add = $this->EE->TMPL->fetch_param('selected_add');
		$sort_by = $this->EE->TMPL->fetch_param('sort_by');
		if($sort_by == '') 
		{
			$sort_by = "entry_date";
		}
		else
		{
			$fq = $this->EE->db->get_where('channel_fields', array('field_name' => $sort_by));
			if($fq->num_rows() > 0)
			{
				$sort_by = 'field_id_'.$fq->row('field_id');
			}
		}
		
		$all_site_pages = $this->EE->config->item('site_pages');		
		$site_pages = $all_site_pages[$this->EE->config->item('site_id')];
								
		$entry_ids = '';

		if(isset($site_pages['uris']) && count($site_pages['uris']) > 0)	// if we have any pages
		{

			foreach($site_pages['uris'] as $entry_id => $page_uri)
			{
				$entry_ids .= $entry_id .",";
			}
			$entry_ids = substr($entry_ids, 0, strlen($entry_ids)-1);
			
			if($conditional_field != '')
			{
				$fq = $this->EE->db->get_where('channel_fields', array('field_name' => $conditional_field));
				if($fq->num_rows() > 0)
				{
					$field_id = $fq->row('field_id');
					$query = $this->EE->db->query("SELECT c.entry_id, c.title FROM exp_channel_titles c, exp_channel_data d WHERE d.entry_id=c.entry_id AND d.field_id_{$field_id}='{$conditional_field_value}' AND c.entry_id IN({$entry_ids}) ORDER BY {$sort_by}");
				}
				else
				{
					// default to listing all if field name not found
					$query = $this->EE->db->query("SELECT entry_id, title FROM exp_channel_titles WHERE entry_id IN({$entry_ids}) ORDER BY {$sort_by}");
				}	
			}
			else
			{
				$query = $this->EE->db->query("SELECT entry_id, title FROM exp_channel_titles WHERE entry_id IN({$entry_ids}) ORDER BY {$sort_by}");	
			}		
				
			foreach($query->result() as $page) 
			{			
				$tagdata = $this->EE->TMPL->tagdata;
									
				$tagdata = str_replace(LD.'pageslist_page_uri'.RD, $site_pages['uris'][$page->entry_id], $tagdata);
				$tagdata = str_replace(LD.'pageslist_page_title'.RD, $page->title, $tagdata);
				
				if($selected_add != "")
				{
					if($this->EE->uri->uri_string == trim($site_pages['uris'][$page->entry_id], "/"))
					{
						$tagdata = str_replace(LD.'selected_add'.RD, $selected_add, $tagdata);				
					}
					else
					{
						$tagdata = str_replace(LD.'selected_add'.RD, "", $tagdata);
					}
				}
					
				$this->return_data .= $tagdata;
				
			}
		}
		
		return $this->return_data;		
	}
  


	// --------------------------------------------------------------------
	/**
	 * Usage
	 *
	 * This function describes how the plugin is used.
	 *
	 * @access	public
	 * @return	string
	 */
	
  //  Make sure and use output buffering

	  function usage()
	  {
	  ob_start(); 
	  ?>
		Enables you to list all site pages using EE tags.
		
		Usage example:
		=====================
		<ul>
			{exp:pageslist}
				<li><a href='{path={pageslist_page_uri}}'>{pageslist_page_title}</a></li>
			{/exp:pageslist}
		</ul>
		
		Optional parameters:
		=====================
		
		selected_add - add this if the the current uri matches the page uri (nice for highlighting the current menu item for instance)
		
		conditional_field 
		conditional_field_value
				
		These two parameters will restrict the pages to those who have the conditional field with a specific value. For instance,
		in the usage example below there is a custom field called "include_in_menu" which is a select dropdown with 
		values "Yes" and "No". This pageslist tag will only list those pages who have selected include_in_menu "Yes" 
		
		You can use these variables to select pages by any kind of custom field.
		
		<ul>
			{exp:pageslist conditional_field='include_in_menu' conditional_field_value='Yes'}
 				<li><a href="{path={pageslist_page_uri}}">{pageslist_page_title}</a></li>
			{/exp:pageslist}	    	
		</ul>
		
		
	  <?php
	  $buffer = ob_get_contents();
		
	  ob_end_clean(); 
	
	  return $buffer;
	  }
	  // END

}
/* End of file pi.pageslist.php */ 

/* Location: ./system/expressionengine/third_party/pageslist/pi.pageslist.php */ 
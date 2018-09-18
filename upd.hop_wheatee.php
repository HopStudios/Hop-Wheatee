<?php

class Hop_wheatee_upd 
{

    var $version = '0.1';
    var $module_name = "Hop_wheatee";

    // ----------------------------------------
    //	Module installer
    // ----------------------------------------
    function install() 
    {
        $data = array(
            'module_name'	=> $this->module_name,
            'module_version'	=> $this->version,
            'has_cp_backend'	=> 'y',
            'has_publish_fields' => 'n'
        );

        ee()->db->insert('modules', $data);

        return true;
    }

    function update($current = '')
    {
        if (version_compare($current, '0.1', '=')) {
            return FALSE;
        }

        if (version_compare($current, '0.1', '<')) {
            // Do your update code here
        }

        return TRUE;
    }

    	// ----------------------------------------
	//	Module de-installer
	// ----------------------------------------
	function uninstall()
	{
		ee()->db->select('module_id');
		$query = ee()->db->get_where('modules', array('module_name' => $this->module_name));

		ee()->db->where('module_id', $query->row('module_id'));
		ee()->db->delete('module_member_groups');

		ee()->db->where('module_name', $this->module_name);
		ee()->db->delete('modules');

		return true;
	}
}
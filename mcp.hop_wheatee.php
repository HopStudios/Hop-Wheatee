<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use EllisLab\ExpressionEngine\Library\CP\Table;

class Hop_wheatee_mcp {
    public function index()
    {
        $this->build_nav();
        $header = array(
            'title'     => 'Home',
        );
        ee()->view->header = $header;

        return array(
            'heading'   => 'Hop Wheatee',
            'body'      => ee('View')->make('hop_wheatee:index')->render(),
			'breadcrumb'	=> array(
				ee('CP/URL', 'addons/settings/hop_wheatee')->compile() => lang('hop_wheatee_module_name')
			),
        );
    }

    private function build_nav()
    {
        $sidebar = ee('CP/Sidebar')->make();

        // list of all channels
        $channel_div = $sidebar->addHeader(lang('nav_channel_list'));
        $channel_div_list = $channel_div->addBasicList();
        $all_channels = ee()->db->select('channel_id, channel_title')->from('exp_channels')->order_by('channel_title', 'ASC')->get()->result_array();
        foreach ($all_channels as $channel) {
            $channel_div_list->addItem($channel['channel_title'], ee('CP/URL', 'addons/settings/hop_wheatee/fields_by_channel/' . $channel['channel_id']));
        }

        // list of all fields
        $sd_div = $sidebar->addHeader(lang('nav_field_list'));
        $sd_div_list = $sd_div->addBasicList();

        $channel_fields_query = ee()->db->order_by('field_label', 'ASC')->get('exp_channel_fields');
        $channel_fields = $channel_fields_query->result();
        foreach ($channel_fields as $channel_field) {
            $sd_div_list->addItem($channel_field->field_label, ee('CP/URL', 'addons/settings/hop_wheatee/channels_by_field/' . $channel_field->field_id));
        }

    }
    
    public function channels_by_field($field_id)
    {
        $this->build_nav();

        $distinct_channel_ids = $this->get_channel_ids_from_field_id($field_id);

        if ($distinct_channel_ids) {
            // get plain English channel names to display
            $channels = ee()->db->select('channel_id, channel_name, channel_title, channel_url')
            ->from('exp_channels')
            ->where_in('channel_id', $distinct_channel_ids)
            ->get()->result_array();

            // create table for view
            $data = array();
            foreach ($channels as $channel) {
                $data[] = array(
                    $channel['channel_title'],
                    $channel['channel_id'],
                    $channel['channel_name'],
                    array('toolbar_items' => array(
                        'edit' => array(
                          'href' => ee('CP/URL', 'channels/edit')->compile() . '/' . $channel['channel_id'],
                          'title' => 'Edit ' . $channel['channel_name'] . ' Channel',
                        ),
                    )
                ));
            }
        } else {
            $data = null;
        }

        $table = ee('CP/Table');
        $table->setColumns(
            array(
                'Channel Label',
                'Channel Id',
                'Channel Name',
                'Manage Fields' => array(
                    'type'  => Table::COL_TOOLBAR
                  ),
            )
        );
        $table->setNoResultsText('no_channels');
        $table->setData($data);

        $current_field_info = ee()->db->select('field_label')
        ->from('exp_channel_fields')
        ->where('field_id', $field_id)
        ->get()->result_array();
        
        $vars['table'] = $table->viewData();
        $vars['field_label'] = $current_field_info[0]['field_label'];
        
        return array(
            'heading'   => $field_id,
            'body'      => ee('View')->make('hop_wheatee:channels_by_field')->render($vars),
			'breadcrumb'	=> array(
				ee('CP/URL', 'addons/settings/hop_wheatee')->compile() => lang('hop_wheatee_module_name')
			),
        );
    }

    private function get_channel_ids_from_field_id($field_id) {
        // TODO: we could try to combine this section into some join selects once I know more active record syntax
        // grab all database entries from -- exp_channel_data_field_$field_id
        $select_table = 'exp_channel_data_field_' . $field_id;
        $entries = ee()->db->select('entry_id')
            ->from($select_table)
            ->get()->result_array();

        $entry_ids = array();
        foreach ($entries as $row) {
            $entry_ids[] = $row['entry_id'];
        }

        // get all distinct Entries from channel data with a channel id of $entry_ids
        $channel_ids = ee()->db->select('channel_id')
            ->distinct('channel_id')
            ->from('exp_channel_data')
            ->where_in('entry_id', $entry_ids)
            ->get()->result_array();

        $distinct_channel_ids = array();
        foreach ($channel_ids as $row) {
            $distinct_channel_ids[] = $row['channel_id'];
        }
        
        return $distinct_channel_ids;
    }

    private function create_field_table($fields) {
        // create table for view
        if ($fields) {
            $data = array();
            foreach ($fields as $field) {
                $data[] = array(
                    $field['field_label'],
                    $field['field_id'],
                    $field['field_type'],
                    $field['field_name'],
                    array('toolbar_items' => array(
                        'edit' => array(
                            'href' => ee('CP/URL', 'fields/edit')->compile() . '/' . $field['field_id'],
                            'title' => 'Edit ' . $field['field_name'] . ' Field',
                        ),
                    )
                ));
            }
        } else {
            $data = null;
        }

        $tableData = ee('CP/Table');
        $tableData->setColumns(
            array(
                'Field Label',
                'Field Id',
                'Field Type',
                'Field Name',
                'Manage Fields' => array(
                    'type'  => Table::COL_TOOLBAR
                    ),
            )
        );
        $tableData->setNoResultsText('no_channels');
        $tableData->setData($data);

        return $tableData;
    }


    public function fields_by_channel($channel_id)
    {
        $this->build_nav();

        // get list of directly added fields from exp_channels_channel_fields
        $unGroupedFields = ee()->db->select('exp_channel_fields.field_label, exp_channel_fields.field_id, exp_channel_fields.field_type, exp_channel_fields.field_name')
            ->from('exp_channels_channel_fields')
            ->join('exp_channel_fields', 'exp_channel_fields.field_id = exp_channels_channel_fields.field_id')
            ->where('exp_channels_channel_fields.channel_id', $channel_id)
            ->get()->result_array();

        // get list of fields added through field_groups
        $groupedFields = ee()->db->select('exp_channel_fields.field_label, exp_channel_fields.field_id, exp_channel_fields.field_type, exp_channel_fields.field_name')
            ->from('exp_channels_channel_field_groups')
            ->join('exp_channel_field_groups_fields', 'exp_channel_field_groups_fields.group_id = exp_channels_channel_field_groups.group_id')
            ->join('exp_channel_fields', 'exp_channel_fields.field_id = exp_channel_field_groups_fields.field_id')
            ->where('exp_channels_channel_field_groups.channel_id', $channel_id)
            ->order_by('exp_channel_fields.field_label', 'ASC')
            ->get()->result_array();

        $ungroupedTable = $this->create_field_table($unGroupedFields);
        $groupedTable = $this->create_field_table($groupedFields);

        $current_field_info = ee()->db->select('channel_title')
        ->from('exp_channels')
        ->where('channel_id', $channel_id)
        ->get()->result_array();
        
        $vars['ungroupedTable'] = $ungroupedTable->viewData();
        $vars['groupedTable'] = $groupedTable->viewData();
        $vars['channel_title'] = $current_field_info[0]['channel_title'];
        
        return array(
            'heading'   => $channel_id,
            'body'      => ee('View')->make('hop_wheatee:fields_by_channel')->render($vars),
			'breadcrumb'	=> array(
				ee('CP/URL', 'addons/settings/hop_wheatee')->compile() => lang('hop_wheatee_module_name')
			),
        );
    }
}
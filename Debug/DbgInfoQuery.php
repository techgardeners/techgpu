<?php
/*
 * This file is part of the Tech Gardeners PHP Utility Project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\Debug;

use Symfony\Component\Stopwatch\StopwatchEvent;

use TechG\TechGPU\Debug\Interfaces\DbgInfoInterface;
use TechG\TechGPU\Debug\Items\DbgInfoItem;
use TechG\TechGPU\Debug\Items\DbgInfoSqlItem;
use TechG\TechGPU\Debug\Items\DbgInfoArrayItem;

use TechG\TechGPU\Misc\TgArrayUtil;
use TechG\TechGPU\DB\Mysql\TgMysqlUti;


/**
* Rappresenta un elemento Info Debug di tipo Query
*/
class DbgInfoQuery extends DbgInfoInterface
{ 
    
    public function __construct($sql = null, StopwatchEvent $timeEvent = null, $conn = null, $res = null)
    {

        parent::__construct();
        
        $this->name = "QueryInfo_".uniqid();
        
        // Contiene la stringa Sql utilizzata per la query
        $this->value->add('sql', new DbgInfoSqlItem('Executed Query', $sql));
        
            // Contiene i dati dei risultati (num risultati, paginazione, etc..)
            $resultInfoArray = array(
                'affected_rows' => new DbgInfoItem('affected_rows', 0, DbgInfoItem::TYPE_NUMERIC),
            );

        $this->value->add('exec_info', new DbgInfoArrayItem('Execution result info', $resultInfoArray, array('render_type' => 'list')));
        $this->addTimeEventInfo($timeEvent);
        $this->addErrorInfo($conn);
        $this->addExtendedInfo($conn);
        $this->addFieldsInfo($res);
        
    }
    
    
    public function addTimeEventInfo(StopwatchEvent $timeEvent = null) {

        if (!$timeEvent) return $this;
            // Contiene i dati sui tempi di esecuzione
            $timeInfoArray = array(
                'exec_time' => new DbgInfoItem('exec_time', ($timeEvent) ? $timeEvent->getDuration() : 0, DbgInfoItem::TYPE_NUMERIC),
                'memory_usage' => new DbgInfoItem('exec_mem', ($timeEvent) ? $timeEvent->getMemory() : 0, DbgInfoItem::TYPE_MEMSIZE),
            );

        return $this->add('system_info', new DbgInfoArrayItem('System usage info', $timeInfoArray, array('render_type' => 'list')));        
    }
    
    public function addErrorInfo($conn = null) {
        if (!$conn) return $this;
        
        if ($conn->errno) {
            
            $errorInfoArray = array(
                'error_no' => new DbgInfoItem('error_no', $conn->errno, DbgInfoItem::TYPE_NUMERIC),
                'error_msg' => new DbgInfoItem('error_msg', $conn->error, DbgInfoItem::TYPE_STRING),
                'error_sqlstate' => new DbgInfoItem('error_sqlstate', $conn->sqlstate, DbgInfoItem::TYPE_NUMERIC),
            );
            
            $this->add('error_info', new DbgInfoArrayItem('Execution error info', $errorInfoArray, array('render_type' => 'list')));
            
        }         
        
    }
    public function addExtendedInfo($conn = null) {

        if (!$conn) return $this;

        $this->value['exec_info']['affected_rows']->value = $conn->affected_rows;

        $mysqlInfoArray = array(
            'client_info' => new DbgInfoItem('client_version', $conn->client_info, DbgInfoItem::TYPE_STRING),
            'server_info' => new DbgInfoItem('server_version', $conn->server_info, DbgInfoItem::TYPE_STRING),
            'warning' => new DbgInfoItem('warning', $conn->warning_count, DbgInfoItem::TYPE_STRING),
        );
            
        $this->add('version_info', new DbgInfoArrayItem('Mysql info', $mysqlInfoArray, array('render_type' => 'list')));

        return $this;        
    }
    
    public function addFieldsInfo($res = null) {

        if (!$res) return $this;
        
        $fields = $res->fetch_fields();
        
        $fieldsInfoArray = array(
            'field_num' => new DbgInfoItem('field_num', count($fields), DbgInfoItem::TYPE_NUMERIC),
            'field_info' => new DbgInfoArrayItem('Fields', array(), array('render_type' => 'list')),
        );
            
        $this->add('fields_info', new DbgInfoArrayItem('Fields info', $fieldsInfoArray, array('render_type' => 'list')));
        
        
        foreach ($fields as $field) {
            $fieldInfoArray = array(
                'field_orgname' => new DbgInfoItem('real_name', $field->orgname, DbgInfoItem::TYPE_STRING),
                'field_type' => new DbgInfoItem('type', TgMysqlUti::decodeMysqlFieldsType($field->type)."($field->type)", DbgInfoItem::TYPE_STRING),
                'field_length' => new DbgInfoItem('length',  $field->length, DbgInfoItem::TYPE_STRING),
                'field_decimals' => new DbgInfoItem('decimals', $field->decimals, DbgInfoItem::TYPE_STRING),
                'field_table_alias' => new DbgInfoItem('table_alias', $field->table, DbgInfoItem::TYPE_STRING),
                'field_table' => new DbgInfoItem('table', $field->orgtable, DbgInfoItem::TYPE_STRING),
            );
            
            $this->value['fields_info']['field_info']->add($field->name, new DbgInfoArrayItem($field->name, $fieldInfoArray, array('render_type' => 'list')));
        }
        
        
        return $this;        
    }
    
    
    public function addSubQuery($name, $subQueryItem) {
        
        if (!is_object($this->value['sub_querys'])) {
            $this->add('sub_querys', new DbgInfoArrayItem('Sub Querys info', array(), array('render_type' => 'list')));    
        }
        
        $this->value['sub_querys']->add($name, $subQueryItem);
        
        return $this;
    }
    
    
    public function render() { 

        //echo  TgArrayUtil::makeList(array('pippo' => 'testo', 'frutta' => array('arancio' => 'rosso', 'mela' => 'verde')), 1);
        //echo  TgArrayUtil::makeList($this->value,0);
        //echo  TgArrayUtil::makeTable($this->value['sub_querys'],0);
        
        return $this->value->renderValue();
    
    }
    
    public function getArrayValue() {
     
     $ret = array();
     
     $ret['exec_info']['sql'] = $this->value->value['sql']->value;
     $ret['exec_info']['affected_rows'] = $this->value->value['exec_info']['affected_rows']->value;
     $ret['exec_info']['returned_rows'] = $this->value->value['exec_info']['returned_rows']->value;
     $ret['exec_info']['filtered_records'] = (array_key_exists('filtered_records',$this->value->value['exec_info'])) ? $this->value->value['exec_info']['filtered_records']->value : null;
     $ret['exec_info']['total_table_records'] = $this->value->value['exec_info']['total_table_records']->value;
     $ret['exec_info']['page'] = $this->value->value['exec_info']['page']->value;
     $ret['exec_info']['page_size'] = $this->value->value['exec_info']['page_size']->value;
     $ret['exec_info']['tot_page'] = $this->value->value['exec_info']['tot_page']->value;
     
     $ret['system_info']['exec_time'] = $this->value->value['system_info']['exec_time']->value;
     $ret['system_info']['memory_usage'] = $this->value->value['system_info']['memory_usage']->value;
     
     $ret['version_info']['client_info'] = $this->value->value['version_info']['client_info']->value;
     $ret['version_info']['server_info'] = $this->value->value['version_info']['server_info']->value;
     $ret['version_info']['warning'] = $this->value->value['version_info']['warning']->value;

     $ret['error_info']['error_no'] = (array_key_exists('error_info',$this->value->value)) ? $this->value->value['error_info']['error_no']->value : null;
     $ret['error_info']['error_msg'] = (array_key_exists('error_info',$this->value->value)) ? $this->value->value['error_info']['error_msg']->value : null;
     $ret['error_info']['error_sqlstate'] = (array_key_exists('error_info',$this->value->value)) ? $this->value->value['error_info']['error_sqlstate']->value : null;

     return $ret;
        
    }
    
    

    
}
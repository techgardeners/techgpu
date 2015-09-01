<?php
/*
 * This file is part of the Tech Gardeners PHP Utility Project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\DB;

use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\Driver\PDOStatement;
use Symfony\Component\Stopwatch\StopwatchEvent;

class QueryResult
{

    CONST MAIN_QUERY = 'main';
    CONST SUB_QUERY = 'sub';

    public $id; // uniq identification of query execution
    public $sql; // executed query
    public $type = self::MAIN_QUERY; // Type of query executed
    public $subQuery = array(); // QueryResults array of subquerys
    public $data = array(); // Query datas

    public $execInfos = array(); // ExecutionInfos
    public $errorInfos = array(); // Errors Infos
    public $systemInfos = array(); // Errors Infos

    public function __construct($conn, $sql, $resData, StopwatchEvent $timeEvent = null)
    {
        $this->id = "QueryResult_".uniqid();
        $this->sql = $sql;

        $this->addExecutionInfo($resData);
        $this->addExtendedInfo($conn);
        $this->addErrorInfo($conn);
        $this->addTimeEventInfo($timeEvent);
    }

    public function hasResult()
    {

        return $this->execInfos['returned_rows'];
    }

    public function hasErrors()
    {

        return $this->errorInfos['error_no'] != 0;
    }

    public function addSubQuery($key, QueryResult $subq) {

        $subq->type = self::SUB_QUERY;

        $this->subQuery[$key] = $subq;

        return true;
    }

    public function addPaginationInfo($page, $page_size) {

        $this->execInfos['page'] = ($this->execInfos['returned_rows'] > 0) ? $page : 0;
        $this->execInfos['page_size'] = $page_size;
        $this->execInfos['total_table_records'] = $this->execInfos['returned_rows'];
        $this->execInfos['filtered_records'] = $this->execInfos['returned_rows'];

        return true;
    }

    public function addTimeEventInfo(StopwatchEvent $timeEvent = null) {

        $this->execInfos['exec_time'] = ($timeEvent) ? $timeEvent->getDuration() : -1;
        $this->execInfos['exec_mem'] = ($timeEvent) ? $timeEvent->getMemory() : -1;

        $this->addTotalTimeEventInfo($timeEvent);

        return true;
    }

    public function addTotalTimeEventInfo(StopwatchEvent $timeEvent = null) {

        $this->execInfos['total_exec_time'] = ($timeEvent) ? $timeEvent->getDuration() : -1;
        $this->execInfos['total_exec_mem'] = ($timeEvent) ? $timeEvent->getMemory() : -1;

        return true;
    }

    // ***********************************************************************************************************
    // ***********************************************************************************************************

    private function addExecutionInfo($resData) {

        if (($resData instanceof PDOStatement)) {

            $this->data = ($resData) ? $resData->fetchAll() : array();

            $this->execInfos['field_count'] = ($resData) ? $resData->columnCount() : 0;
            $this->execInfos['affected_rows'] = ($resData) ? $resData->rowCount() : 0;
            $this->execInfos['returned_rows'] = count($this->data);

            $errorInfo = $resData->errorInfo();
            if($errorInfo[0] != 0) {
                $this->errorInfos['error_no'] = $errorInfo[0];
                $this->errorInfos['error_msg'] = $errorInfo[2];
                $this->errorInfos['error_sqlstate'] = $errorInfo[1];
            } else {
                $this->errorInfos['error_no'] = 0;
                $this->errorInfos['error_msg'] = '';
                $this->errorInfos['error_sqlstate'] = 0;
            }

        } else {

            $this->data = ($resData) ? $resData->fetch_all(MYSQL_ASSOC) : array();

            $this->execInfos['field_count'] = ($resData) ? $resData->field_count : 0;
            $this->execInfos['returned_rows'] = ($resData) ? $resData->num_rows : 0;
        }


    }

    private function addExtendedInfo($conn = null) {

        if (!$conn) return false;

        if (($conn instanceof PDOConnection)) {

        } else {

            $this->execInfos['affected_rows'] = $conn->affected_rows;
            $this->systemInfos['client_info'] = $conn->client_info;
            $this->systemInfos['server_info'] = $conn->server_info;
            $this->systemInfos['host_info'] = $conn->host_info;
            $this->systemInfos['protocol_version'] = $conn->protocol_version;

        }
        return true;
    }

    private function addErrorInfo($conn) {

        if (($conn instanceof PDOConnection)) {

        } else {

            $this->errorInfos['warning'] = $conn->warning_count;

            $this->errorInfos['error_no'] = $conn->errno;
            $this->errorInfos['error_msg'] = $conn->error;
            $this->errorInfos['error_sqlstate'] = $conn->sqlstate;
        }
        return true;
    }


}
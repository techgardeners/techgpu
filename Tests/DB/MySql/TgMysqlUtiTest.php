<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\Tests\DB\Mysql;

use TechG\TechGPU\DB\Mysql\TgMysqlUti;
use TechG\TechGPU\DB\QueryResult;


class TgMysqlUtiTest extends \PHPUnit_Framework_TestCase
{

    protected $conn;

    protected function getConnection()
    {
        $this->conn = ($this->conn) ? $this->conn : mysqli_connect("127.0.0.1","root","","testdb") or die("Error " . mysqli_error());

        return $this->conn;
    }

    protected function getSqlQueries()
    {
        $queries = array();

        $query = array();
        $query['sql'] = "SELECT * FROM simple";
        $query['num_fields'] = 5;
        $query['num_records'] = 1;
        $query['fields'] = array('id','field_1','field_2','field_3','field_4');

        $queries[] = $query;

        $err_query = array();
        $err_query['sql'] = "SELECT * FROM simples";
        $err_query['num_fields'] = 0;
        $err_query['num_records'] = 0;
        $queries[] = $err_query;

        $err_query = array();
        $err_query['sql'] = "SELECT pippo FROM simple";
        $err_query['num_fields'] = 0;
        $err_query['num_records'] = 0;
        $queries[] = $err_query;

        return $queries;
    }


    protected function checkPaginatedQueryResult(QueryResult $qr, $q)
    {
        $this->checkQueryResult($qr, $q);

        $this->assertEquals($q['num_records'], $qr->hasResult());

    }

    protected function checkQueryResult(QueryResult $qr, $q)
    {
        $this->assertInstanceOf('TechG\TechGPU\DB\QueryResult', $qr);
        $this->assertEquals($q['sql'], $qr->sql);
        $this->assertEquals($q['num_fields'], $qr->execInfos['field_count']);
        $this->assertEquals($q['num_records'], $qr->hasResult());
        $this->assertEquals($qr->hasResult(), $qr->execInfos['returned_rows']);
        $this->assertEquals($this->conn->affected_rows, $qr->execInfos['affected_rows']);
        $this->assertEquals($this->conn->warning_count, $qr->errorInfos['warning']);
        $this->assertEquals($this->conn->error, $qr->errorInfos['error_msg']);
        $this->assertEquals($this->conn->errno, $qr->errorInfos['error_no']);
        $this->assertEquals($this->conn->sqlstate, $qr->errorInfos['error_sqlstate']);
        $this->assertEquals($this->conn->client_info, $qr->systemInfos['client_info']);
        $this->assertEquals($this->conn->server_info, $qr->systemInfos['server_info']);
        $this->assertEquals($this->conn->host_info, $qr->systemInfos['host_info']);
        $this->assertEquals($this->conn->protocol_version, $qr->systemInfos['protocol_version']);
    }


    /**
     * @covers TechG\TechGPU\DB\Mysql\TgMysqlUti::executeQuery
     */
    public function testExecuteQuery()
    {
        $queryies = $this->getSqlQueries();

        foreach ($queryies as $q) {
            $res = TgMysqlUti::executeQuery($this->getConnection(), $q['sql']);

            $this->checkQueryResult($res, $q);
        }

    }

    /**
     * @covers TechG\TechGPU\DB\Mysql\TgMysqlUti::executePaginatedQuery
     */
    public function testExecutePaginatedQuery()
    {
        $queryies = $this->getSqlQueries();

        foreach ($queryies as $q) {
            $res = TgMysqlUti::executePaginatedQuery($this->getConnection(), $q['sql']);

            $this->checkPaginatedQueryResult($res, $q);
        }

    }

// *********************************************************************************************************************************************************
// *********************************************************************************************************************************************************    
// *********************************************************************************************************************************************************
    
    /**
    * For Unit Test purpose
    * 
    * @param mixed $name
    * @return \ReflectionMethod
    */
    protected static function getPrivateMethod($name) {
      $class = new \ReflectionClass('TechG\TechGPU\DB\Mysql\TgMysqlUti');
      $method = $class->getMethod($name);
      $method->setAccessible(true);
      return $method;
    }        
    
    
}

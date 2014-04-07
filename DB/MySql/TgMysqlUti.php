<?php
/*
 * This file is part of the Tech Gardeners PHP Utility Project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\TechGPU\DB\Mysql;

use Symfony\Component\Stopwatch\Stopwatch;

use TechG\TechGPU\Debug\Items\DbgInfoItem;
use TechG\TechGPU\Debug\Items\DbgInfoArrayItem;
use TechG\TechGPU\Debug\DbgInfoQuery;

class TgMysqlUti
{

    /**
    * Metodo che esegue la query sql collezionando informazioni aggiuntive
    * Se non viene passato il parametro extendeInfo viene eseguita la query in modalità fast, senza nessun overhead
    * 
    * @param mixed $conn
    * @param mixed $sql
    * @param mixed $extendedInfo
    * @param mixed $parameters
    * @return DbgInfoQuery
    */
    public static function executeQuery($conn, $sql, &$extendedInfo = null, array $parameters = array())
    {
        
        // If not set, execeuted faster then possible
        if ($extendedInfo === NULL) {
            return $conn->query($sql);    
        }
        
        $stopwatch = new Stopwatch();
        $stopwatch->start('executeQuery');

            $resultQ = $conn->query($sql);
        
        $timeEvent = $stopwatch->stop('executeQuery');
        
        $extendedInfo = new DbgInfoQuery($sql, $timeEvent, $conn, $resultQ);
        
        return $resultQ;
    }    
    
    
    public static function executePaginatedQuery($conn, $sql, $page_size = null, $page = null, $filterClauses = null, $orderClauses = null, &$extendedInfo = null, array $parameters = array())
    {

        $stopwatch = new Stopwatch();
        $stopwatch->start('executePaginatedQuery');
        
        // Compongo sql con limiti filtri e ordinamenti

        $limitClause = self::getPaginatedLimitClause($page_size, $page);         
        $filterClause = self::getFilterClause($filterClauses);         
        $orderClause = self::getOrderClause($orderClauses);         
        
        $mainQuery = $sql.$filterClause.$orderClause.$limitClause;
        
        $mainQRes = self::executeQuery($conn, $mainQuery, $extendedInfo, $parameters);

        // Aggiungo le info sulla paginazione
        if ($extendedInfo && $page && $page_size) {
            $extendedInfo->value['exec_info']->add('page', new DbgInfoItem('page', $page, DbgInfoItem::TYPE_NUMERIC));
            $extendedInfo->value['exec_info']->add('page_size', new DbgInfoItem('page_size', $page_size, DbgInfoItem::TYPE_NUMERIC));
        }
        
        
        if ($mainQRes && $limitClause) {
            
            $countQuery = preg_replace('/^SELECT .* FROM/i', 'SELECT count(*) FROM', $sql);
            
            if ($filterClause) {
                $fq_info = ($extendedInfo) ? array() : null;
                $fq_res = self::executeQuery($conn, $countQuery.$filterClause, $fq_info, $parameters);
                
                if ($fq_res) {
                    $fq_res = $fq_res->fetch_array(MYSQL_NUM);
                    $fq_res = $fq_res[0];
                }

                if ($extendedInfo) {
                    $extendedInfo->addSubQuery('count_filter_record', $fq_info);
                    $extendedInfo->value['exec_info']->add('filtered_records', new DbgInfoItem('filtered_records', $fq_res , DbgInfoItem::TYPE_NUMERIC));    
                }                
                
            }

            $tq_info = ($extendedInfo) ? array() : null;
            $tq_res = self::executeQuery($conn, $countQuery, $tq_info, $parameters);
            
            if ($tq_res) {
                $tq_res = $tq_res->fetch_array(MYSQL_NUM);
                $tq_res = $tq_res[0];
            }

            if ($extendedInfo) {
                $extendedInfo->addSubQuery('count_total_record', $tq_info);
                $extendedInfo->value['exec_info']->add('total_records', new DbgInfoItem('total_records', $tq_res , DbgInfoItem::TYPE_NUMERIC));    
            }            
            
        }
        
       $event = $stopwatch->stop('executePaginatedQuery');
       
       $extendedInfo->addTimeEventInfo($event);
          
       return $mainQRes;            
        
    }
    
    
    
// *******************************************************************************************    
// COMMON UTILITY
// *******************************************************************************************    
    
  
    /**
    * put your comment there...
    *     
    * @param mixed $from
    * @param mixed $length
    */
    public static function getLimitClause($from=null, $length=-1)
    {
        $limitClause = "";
        
        if ( isset( $from ) ) {

            $limitClause = " LIMIT ".intval( $from );

            if ( $length != '-1' ) { $limitClause .= ", ".intval( $length ); }             
            
        }
        
        return $limitClause;       
    }
        
    public static function getPaginatedLimitClause($page_size=null, $page=0)
    {
        
        if ( isset( $page_size ) ) {

            return self::getLimitClause($page_size*$page, $page_size);             
        }
        
        return '';
        
    }
        
    public static function getFilterClause($filterClauses = null)
    {
        
        if ( isset( $filterClauses ) ) {
            
        }
        
        return '';
        
    }
        
    public static function getOrderClause($orderClauses = null)
    {
        
        if ( isset( $orderClauses ) ) {
            
        }
        
        return '';
        
    }
   
   
    public static function decodeMysqlFieldsType($type) {
        
        $mysql_data_type_hash = array(
            1=>'tinyint',
            2=>'smallint',
            3=>'int',
            4=>'float',
            5=>'double',
            7=>'timestamp',
            8=>'bigint',
            9=>'mediumint',
            10=>'date',
            11=>'time',
            12=>'datetime',
            13=>'year',
            14=>'newdate',
            16=>'bit',
            246=>'decimal',
            248=>'set',
            249=>'tiny_blob',
            250=>'medium_blob',
            251=>'long_blob',
            252=>'blob',
            253=>'varchar',
            254=>'char',
            255=>'geometry',

        );        
        
        return $mysql_data_type_hash[$type];
        
    }
        
    
}
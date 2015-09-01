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

use TechG\TechGPU\DB\QueryResult;

class TgMysqlUti
{

    public static function executeRawQuery($conn, $sql, $parameters = array())
    {
        return $conn->query($sql);
    }

    /**
    * Metodo che esegue la query sql collezionando informazioni aggiuntive
    * Se non viene passato il parametro extendeInfo viene eseguita la query in modalità fast, senza nessun overhead
    * 
    * @param mixed $conn
    * @param mixed $sql
    * @param mixed $extendedInfo
    * @param mixed $parameters
    * @return
    */
    public static function executeQuery($conn, $sql, $parameters = array())
    {
        
        // If Set parameter 'only_data' no return debug info
        if (array_key_exists('only_data', $parameters) && $parameters['only_data']) {
            return self::executeRawQuery($conn, $sql);
        }

        $stopwatch = new Stopwatch();
        $stopwatch->start('executeQuery');

            $resData = self::executeRawQuery($conn, $sql, $parameters);

        $qr = new QueryResult($conn, $sql, $resData);
        $qr->addTimeEventInfo($timeEvent = $stopwatch->stop('executeQuery'));

        return $qr;
    }
    
    
    public static function executePaginatedQuery($conn, $sql, $page = 1, $page_size = null, $filterClauses = null, $orderClauses = null, $parameters = array())
    {

        $stopwatch = new Stopwatch();
        $stopwatch->start('executePaginatedQuery');
        
        // Compongo sql con limiti filtri e ordinamenti
        $limitClause = self::getPaginatedLimitClause($page_size, $page);         
        $filterClause = self::getFilterClause($filterClauses);         
        $orderClause = self::getOrderClause($orderClauses);         
        
        $mainQuery = $sql.$filterClause.$orderClause.$limitClause;

        // ----------------------------------------------------------------------

        $queryResult = self::executeQuery($conn, $mainQuery, $parameters);
        $queryResult->addPaginationInfo($page, $page_size);

        $paginated_records = $queryResult->execInfos['returned_rows'];

        // Se ho dei risultati e se li ho ottenuti limitando la query, ho bisogno di sapere
        // quanti sono i record totali non limitati

        if ($queryResult->hasresult() && $limitClause) {
            
            // Conta il numero di risultati non limitati
            $countQuery = preg_replace('/^SELECT .* FROM/i', 'SELECT count(*) as num FROM', $sql);

            $tq_res = self::executeQuery($conn, $countQuery, $parameters);
            $queryResult->addSubQuery('total_table_count', $tq_res);
            $queryResult->execInfos['total_table_records'] = ($tq_res->hasresult()) ? $tq_res->data[0]['num'] : 0;

            $paginated_records = $queryResult->execInfos['total_table_records'];
            $queryResult->execInfos['filtered_records'] = $queryResult->execInfos['total_table_records'];

            // se sono anche filtrati per prima cosa mi prendo il numero dei risultati totali FILTRATI
            if ($filterClause) {

                $fq_res = self::executeQuery($conn, $countQuery.$filterClause, $parameters);
                $queryResult->addSubQuery('filtered_count', $fq_res);
                $queryResult->execInfos['filtered_records'] = ($fq_res->hasresult()) ? $fq_res->data[0]['num'] : 0;

                $paginated_records = $queryResult->execInfos['filtered_records'];
            }

        }
        
        $tot_page = ($page_size) ? ((int)($paginated_records / $page_size)) + (($paginated_records % $page_size > 0) ? 1 : 0) : 1;

        $queryResult->execInfos['tot_page'] = $tot_page;

        $queryResult->addTotalTimeEventInfo($stopwatch->stop('executePaginatedQuery'));
          
        return $queryResult;
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
        
    public static function getPaginatedLimitClause($page_size=null, $page=1)
    {
        
        if ( isset( $page_size ) ) {

            $page = ($page && $page > 0) ? $page : 1;
            return self::getLimitClause($page_size*($page-1), $page_size);             
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
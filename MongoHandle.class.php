<?php
/**
 * Created by PhpStorm.
 * User: maqin
 * Date: 2018/2/5
 * Time: 21:35
 */

class MongoHandle
{
    /*
  * 实例化mongodb
  * @params $host ip
  * @params $port 端口号
  * @params $user 管理员名称
  * @params $pwd  密码
  * @return $model  建立的mongo对象
  * */
    private $mongoManager;
    private $db;
    function __construct()
    {
        //$uri = 'mongodb://mongouser:8xNm!HtGi*39@10.168.10.194:27017/admin';
        $uri = 'mongodb://'.MONGO_USER.':'.MONGO_PWD.'@'.MONGO_HOST.':'.MONGO_PORT.'/'.MONGO_DB.'?authSource=admin&readPreference=secondaryPreferred';
        $this->mongoManager = new MongoDB\Driver\Manager($uri);
        $this->db = MONGO_DB;
    }

    /**
     * 查询数据
     * @param $collection      string  查询的文档名
     * @param array $filter    array   查询条件
     * @param array $options
     * @return array
     */
    public function executeQuery($collection,$filter = array(),$options = array())
    {
        $query = new MongoDB\Driver\Query($filter, $options);
        return $this->mongoManager->executeQuery($this->db.'.'.$collection,$query)->toArray();
    }

    /**
     * 添加数据
     * @param $doc             array    插入的数据
     * @param $collection      string   文档名称
     * @param bool $fetched
     * @return bool|mixed
     */
    public function insertData($doc,$collection,$fetched = FALSE)
    {
        if (empty($doc) || $collection === NULL){
            return false ;
        }
        try{
            $bulk = new \MongoDB\Driver\BulkWrite();
            $bulk->insert($doc);
            $insert_res = $this->mongoManager->executeBulkWrite($this->db.'.'.$collection,$bulk);
            if($insert_res){
                return true ;
            }else{
                return false ;
            }
        }catch(Exception $e){
            $this->throwError($e->getMessage());
        }
    }

    /**
     * 修改数据
     * @param $collection   文档名称
     * @param $filter       修改的条件
     * @param $updated      修改的数据
     * @param array $options
     * @return \MongoDB\Driver\WriteResult
     */
    public function updateData($collection,$update_where,$update_data,$options=array('multi' => false, 'upsert' => true))
    {
        if($collection===NULL || empty($update_where) || empty($update_data)){
            $this->throwError('Updated data can not be empty!');
        }
        $timeout = 1000;
        $wc = new MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY,$timeout);
        $bulk  =new MongoDB\Driver\BulkWrite();
        $bulk->update($update_where,$update_data,$options);
        try {
            $result = $this->mongoManager->executeBulkWrite("{$this->db}.$collection", $bulk, $wc);
            return true;
        }catch(\MongoException $e){
            $this->throwError($e->getMessage());
        }
    }

    /**
     * 删除操作
     * @param $doc             array   删除条件
     * @param $collection      string  文档名称
     * @param bool $extra      删除条数，false删除所有的，true删除一条
     * @return bool|mixed
     */
    public function deleteData($doc,$collection,$extra = array())
    {
        if ($collection === NULL){
            $this->throwError('文档集不能为空');
        }

        if(!is_array($doc)){
            $this->throwError('删除条件不能为空');
        }
        try{
            $bulk = new \MongoDB\Driver\BulkWrite();
            $bulk->delete($doc,$extra);
            $del = $this->mongoManager->executeBulkWrite("{$this->db}.$collection",$bulk);
            $del_res  =$del->getDeletedCount();
            if($del_res){
                return true ;
            }
        }catch(Exception $e){
            $this->throwError($e->getMessage());
        }
    }

    private function throwError($errorInfo='')
    {
        echo "<h3>Error:$errorInfo</h3>" ;
    }


}
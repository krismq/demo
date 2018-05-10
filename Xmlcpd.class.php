<?php
class Xmlcpd
{
    protected $dom;
    /*
     * 构造函数
     * */
    function __construct()
    {
        $this->dom = new DomDocument('1.0','utf-8');
        $this->dom->formatOutput = true ;
    }

    /**
     * 新增媒体
     * @param $filepath             string     储存媒体的XML文档
     * @param $channel_id           string     媒体ID
     * @param $channel_name         string     媒体名称
     * @param int $status           int        媒体的状态（1是正常；2是禁止）
     * @param string $updatetime    string     修改时间
     * @return bool
     */
    function addChannel($filepath,$channel_id,$channel_key,$ad_program,$ad_apk_md5,$ad_costcount,$ad_begintime,$ad_endtime,$status='1',$updatetime=' ')
    {
        //检测当前媒体是否存在
        if($this->selectChannel($filepath,$channel_id)){
            $time = date('Y-m-d H:i:s',time());
            //存在修改数据
            $id = $this->updateNodesInfo($filepath,'channel','id',$channel_id,'id',$channel_id);
            $programid = $this->updateNodesInfo($filepath,'channel','id',$channel_id,'programid',$ad_program);
            $md5 = $this->updateNodesInfo($filepath,'channel','id',$channel_id,'apkmd5',$ad_apk_md5);
            $costcount =$this->updateNodesInfo($filepath,'channel','id',$channel_id,'costcount',$ad_costcount);
            $begintime = $this->updateNodesInfo($filepath,'channel','id',$channel_id,'begintime',$ad_begintime);
            $endtime = $this->updateNodesInfo($filepath,'channel','id',$channel_id,'endtime',$ad_endtime);
            $updatetime = $this->updateNodesInfo($filepath,'channel','id',$channel_id,'updatetime',$time);
            //只要修改状态都为1
            $status = $this->setChannelStatus($filepath,$channel_id,1);
            if($id || $programid || $md5 || $costcount || $begintime || $endtime || $updatetime || $status )
            {
                return array('code'=>1,'msg'=>'修改成功');
            }else{
                return array('code'=>0,'msg'=>'修改失败');
            }
        }
        //创建标签并赋值
        $tag = $this->dom->createElement('channel');
        //给标签创建属性
        $tag->setAttribute('id',$channel_id);     //媒体使用状态
        //查询父节点，追加新标签
        $parent_node = $this->dom->getElementsByTagName('partners')[0];
        $insert_tag = $parent_node->appendChild($tag);
        if(!$insert_tag->nodeName){
            return false ;
        }
        //创建媒体的标签并赋值
        $tag_id           = $this->dom->createElement('id',$channel_id);
        $tag_ad_programid = $this->dom->createElement('programid',$ad_program);
        $tag_ad_apkmd5    = $this->dom->createElement('apkmd5',$ad_apk_md5);
        $tag_ad_costcount = $this->dom->createElement('costcount',$ad_costcount);
        $tag_ad_begintime = $this->dom->createElement('begintime',$ad_begintime);
        $tag_ad_endtime   = $this->dom->createElement('endtime',$ad_endtime);
        $tag_addtime      = $this->dom->createElement('addtime',date('Y-m-d H:i:s',time()));
        $tag_updatetime   = $this->dom->createElement('updatetime',$updatetime);
        //给id标签创建属性
        $tag_id->setAttribute('status',$status);     //媒体使用状态
        $tag_id->setAttribute('key',$channel_key);   //媒体key
        $tag_id->setAttribute('updatetime',date('Y-m-d H:i:s',time()));
        //查询插入的位置，添加新媒体
        $insert_id             = $tag->appendChild($tag_id);
        $insert_ad_programid   = $tag->appendChild($tag_ad_programid);
        $insert_ad_apkmd5      = $tag->appendChild($tag_ad_apkmd5);
        $insert_ad_costcount   = $tag->appendChild($tag_ad_costcount);
        $insert_ad_begintime   = $tag->appendChild($tag_ad_begintime);
        $insert_ad_endtime     = $tag->appendChild($tag_ad_endtime);
        $insert_addtime        = $tag->appendChild($tag_addtime);
        $insert_updatetime     = $tag->appendChild($tag_updatetime);
        if($insert_id || $insert_ad_programid || $insert_ad_apkmd5 || $insert_ad_costcount || $insert_ad_begintime || $insert_ad_endtime || $insert_addtime || $insert_updatetime)
        {
            //保存
            $res = $this->dom->save($filepath);
            if($res)
            {
                return  array('code'=>1,'msg'=>'添加成功') ;
            }
        }
        return  array('code'=>0,'msg'=>'添加失败');
    }

    /*
     * 查询媒体
     * @params $channel_id   媒体ID
     * */
    function selectChannel($filepath,$channel_id)
    {
        $this->dom->load($filepath);
        //查询媒体是否存在
        $channel_ids = $this->dom->getElementsByTagName('id');
        var_dump($channel_ids);
        echo 'channel_ids';
        foreach($channel_ids as $v)
        {
            echo 'v';
            print_r($v);
            if($v->nodeValue==$channel_id)
            {
                //获取属性status的值
                $data['status'] = $v->getAttribute('status');
                $data['key']    = $v->getAttribute('key');
                return $data;
            }
        }
        return false ;
    }

    /**
     * 设置媒体，正常合作和禁止合作
     * @param $filepath       string    储存媒体的XML文档
     * @param $channel_id     string    媒体ID
     * @param $status         string    媒体状态
     */
    function setChannelStatus($filepath,$channel_id,$status)
    {
        $this->dom->load($filepath);
        //查询媒体设置
        $time = time();
        $channel_ids = $this->dom->getElementsByTagName('id');
//        echo '<pre>';
//        var_dump($channel_ids);die;
        foreach($channel_ids as $v)
        {
            if($v->nodeValue==$channel_id)
            {
                //更新媒体状态和修改时间
                $v->setAttribute('status',$status);
                $v->setAttribute('updatetime',date('Y-m-d H:i:s',time()));
            }
        }
        //保存
        $res = $this->dom->save($filepath);
        if($res)
        {
            return true ;
        }else{
            return false ;
        }
    }

    /*
     * 根据节点属性查询子节点值
     * @params $filepath     string   xml文档
     * @params $tag          string   节点名称
     * @params $attr         string    属性
     * @params $attr_val     string    属性值
     * @params $child        string    子节点名称
     * */
    function selectNodesInfo($filepath,$tag,$attr,$attr_val,$child)
    {
        $this->dom->load($filepath);
        //查询媒体是否存在
        $channel_tag = $this->dom->getElementsByTagName($tag);
        foreach($channel_tag as $v)
        {
//            echo '<pre>';
//            print_r($v);
            //根据属性筛选出查找的父节点
            if($v->getAttribute($attr)==$attr_val){
                //获取子节点集合
                foreach($v->childNodes as $value)
                {
//                        print_r($value);
                    //筛选出指定的子节点值
                    if($value->nodeName==$child){
                        return $value->nodeValue;
                    }
                }
            }
        }
    }

    /*
     * 根据节点属性修改子节点
     * @params $filepath     string   xml文档
     * @params $tag          string   节点名称
     * @params $attr         string    属性
     * @params $attr_val     string    属性值
     * @params $child        string    子节点名称
     * */
    function updateNodesInfo($filepath,$tag,$attr,$attr_val,$child,$chlid_value)
    {
        $this->dom->load($filepath);
        //查询媒体是否存在
        $channel_tag = $this->dom->getElementsByTagName($tag);
        foreach($channel_tag as $v)
        {
            // echo '<pre>';
//            print_r($v);
            //根据属性筛选出查找的父节点
            if($v->getAttribute($attr)==$attr_val){
                //获取子节点集合
                foreach($v->childNodes as $value)
                {
//                        print_r($value);
                    //筛选出指定的子节点值
                    if($value->nodeName==$child){
                        $value->nodeValue=$chlid_value;
                    }
                }
            }
        }
        //保存
        $res = $this->dom->save($filepath);
        if($res)
        {
            return true ;
        }else{
            return false ;
        }
    }

    /**
     * 删除操作
     * @param $filepath           string    储存媒体的XML文档
     * @param $tags               string    要删除的标签名称
     * @param string $attr_name   string    属性名称
     * @param string $attr_val    string    属性值
     * @return bool
     */
    function deleteNode($filepath,$tags,$attr_name='',$attr_val='')
    {
        //加载文件
        $this->dom->load($filepath);
        //获取根标签
        $root = $this->dom->documentElement;
        //查询节点
        $channel_ids = $this->dom->getElementsByTagName($tags);
        foreach($channel_ids as $v){
            if($attr_name==''){
                $root->removeChild($v);
            }else{
                if($v->getAttribute($attr_name)==$attr_val){
                    //删除节点
                    $root->removeChild($v);
                }
            }
        }
        //保存
        $res = $this->dom->save($filepath);
        if($res){
            return true ;
        }else{
            return false ;
        }
    }


}
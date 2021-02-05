<?php
/**
 * Created by PhpStorm.
 * User: 73938
 * Date: 2018/4/28
 * Time: 22:16
 */
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Db;
use think\Image;
class OrderUser extends Model
{// 设置当前模型对应的完整数据表名称
    protected $table = 'guard_order_user';
    /**
     * 获取order列表数据
     * @author 韩春雷 2019.3.21
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    array       $where [筛选条件]
     * @param    int         $page  [页码]
     * @param    int         $limit [每页数量]
     * @return   [array]                  
     */
    //type == 1 我的订单 | type=2 我的经销商 | type ==3  我的前台用户订单
    //check== 2 我的采购订单审核 |  check == 1 代理商采购订单审核
    public static function getOrderList($where,$type,$check,$page=null,$limit=null){
        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        //判断代理商权限
        if($_SESSION['think']['manageinfo']['user_type'] == 1){
          $user_id  = $_SESSION['think']['manageinfo']['uid'];
        }else{
          $user_id  = $_SESSION['think']['manageinfo']['agent_id'];
        }
        //我的采购订单
        if($type == 1){
            if($page){
                $list =  Db::name("order_user")->alias('o')
                        ->field('o.*,u.username,u.phone')
                        ->join('guard_user u','o.user_id = u.uid')
                        ->where($where)->page($page,$limit)->order('o.strtime desc')->select();
                foreach ($list as $key => $value) {
                  if($value['orderCheck'] == 1){
                    $list[$key]['orderCheck_name'] = '内部待审核';
                  }elseif($value['orderCheck'] == 3){
                    $list[$key]['orderCheck_name'] = '上级待审核';
                  }elseif($value['orderCheck'] == 4){
                    $list[$key]['orderCheck_name'] = '审核完成';
                  }elseif($value['orderCheck'] == 5){
                    $list[$key]['orderCheck_name'] = '审核失败';
                  }
                  $list[$key]['type'] = $type;
                  $list[$key]['check'] = $check;
                }
                return $list;
            }else{
                $count =  Db::name("order_user")->alias('o')->join('guard_user u','o.user_id = u.uid')->field('o.id')->where($where)->count();
                return $count;
            }
        }else{                 //经销商采购订单        
            //获取所有的子类代理商
            $idList = Db::name('user')
                        ->where('parent_id = '.$user_id.' and user_type = 1')
                        ->select();
            $uid = array();
            if(isset($idList)){
                foreach ($idList as $key => $value) {
                    $uid[] = $value['uid'];
                }
            }
            // $uid[] = $user_id;
            //转换数据类型
            $uid = implode(',',$uid);
            $where['o.user_id'] = array('in',$uid);
            if(!isset($where['o.orderCheck'])){
              $where['o.orderCheck'] =array('egt',3);     //子级代理财务审核通过 父级代理审核
            }
            if($page){
                $list =  Db::name("order_user")->alias('o')
                        ->field('o.*,u.username,u.phone')
                        ->join('guard_user u','o.user_id = u.uid')
                        ->where($where)->page($page,$limit)->order('o.strtime desc')->select();
                foreach ($list as $key => $value) {
                  if($value['orderCheck'] == 3){
                    $list[$key]['orderCheck_name'] = '待审核';
                  }elseif($value['orderCheck'] == 4){
                    $list[$key]['orderCheck_name'] = '审核完成';
                  }elseif($value['orderCheck'] == 5){
                    $list[$key]['orderCheck_name'] = '审核失败';
                  }
                  $list[$key]['type'] = $type;
                  $list[$key]['check'] = $check;
                }
                return $list;
            }else{
                $count =  Db::name("order_user")->alias('o')->join('guard_user u','o.user_id = u.uid')->field('o.id')->where($where)->count();
                return $count;
            }
        }
    }
    /**
     * 获取order详细数据
     * @author 韩春雷 2019.3.21
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    int       $data [order ID]
     * @return   [array]                  
     */
    public static function getOneOrder($data){
        $where['o.id']=$data;
        $list = Db::name("order_user")->alias('o')
                    ->join('guard_user u','o.user_id = u.uid')
                    ->join('guard_order_user_info ui','ui.order_id = o.id',"LEFT")
                    ->field('ui.*,o.money price,u.username,u.phone,o.strtime,o.payMoney,o.orderCheck,o.remark,o.is_forbidden,o.order_type,o.state,o.order,o.remark')
                    ->where($where)
                    ->select();
        return $list;
    }
}
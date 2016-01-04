<?php
require_once dirname(__FILE__) . '/include/config.inc.php';
require_once dirname(__FILE__) . '/include/media.inc.php';
require_once dirname(__FILE__) . '/include/forms.inc.php';

require_once dirname(__FILE__) . '/include/classes/class.ftp.php';
require_once dirname(__FILE__).'/api/classes/AgentManager.php';

#查出所有的集群
#查出一个集群中所有的主机
#查询每个主机的item
#查询每个主机的运行状态
#查询运行主机的平均CPU
#超过80%，则扩展一台，否则缩一台
echo "start scala task ".date("Y-m-d H:i:s")."</br>\n";

$groupIds = DBfetchArray(DBselect('SELECT groupid from groups where ishidden=1 and isscala = 1'));

foreach($groupIds as $groupId)
{
    echo "start scala group ".$groupId['groupid']."</br>\n";

    #$hosts = DBfetchArray(DBselect('select hostid from hosts_groups where groupid = '.$groupId['groupid']));
    $hosts = DBfetchArray(DBselect('select hg.hostid as hostid from hosts h left join hosts_groups hg on hg.hostid = h.hostid and hg.groupid = '.$groupId['groupid'].' where h.server_type = 1'));
    $hosts_run=array();
    $hosts_stop=array();
    foreach($hosts as $host)
    {
        $items = DBfetchArray(DBselect("select itemid,name from items where hostid = ".$host['hostid']." and (name = 'net.tcp.listen.run.status' or name = 'oncemon.scala.cpu.util')"));
        $cpu_util=0;
        $run_status='0';
        foreach($items as $item)
        {
            if('oncemon.scala.cpu.util' == $item['name'])
            {
                #查看十分钟之内的CPU负债率
                $cpu_utilX = DBfetch(DBselect("select avg(h.value) as cpu_util from history h  where h. itemid =  ".$item['itemid']." and h.clock > ".(time() - 600),1));
                $cpu_util = $cpu_utilX['cpu_util'];
            }
            elseif('net.tcp.listen.run.status' == $item['name'])
            {
                #查看十分钟钟之内运行状态
                $item['itemid'];
                $statusX = DBfetch(DBselect("select h.value from history_uint h  where h. itemid =  ".$item['itemid']." and h.clock > ".(time() - 600)." order by h.clock desc",1));
                if($statusX == false || null == $statusX)
                {
                    $run_status = '0';
                    break;
                }
                else
                {
                    $run_status = $statusX['value'];
                }
            }
        }

        if($run_status == '1')
        {
            $hosts_run[$host['hostid']]=$cpu_util;
        }
        else
        {
            $hosts_stop[$host['hostid']]=$cpu_util;
        }
    }

    if(sizeof($hosts_run) == 0)
    {
        #没有启动的，什么也不做。
        echo "the count of host in running is 0,then do noting.</br>\n";
        break;
    }
    else
    {
        $total_cpu = 0;
        foreach($hosts_run as $hostid=>$cpu_util)
        {
            $total_cpu +=$cpu_util;
        }
        $avg_cpu = $total_cpu/sizeof($hosts_run);

        if($avg_cpu>0.8)
        {
            #如果超过80%，则启动一个新主机
            echo "the average cpu usage rate is bigger than 0.8,then start one node.</br>\n";
            if(sizeof($hosts_stop) == 0)
            {
                echo "there is no host in stop status to start.</br>\n";
            }
            #循环直至有一个成功的；
            foreach($hosts_stop as $hostIdXX=>$cpu_utilXX)
            {
                $host_interface = DBfetch(DBselect("select ip,port from interface where type = 5 and hostid = ".$hostIdXX,1));
                if(null != $host_interface and sizeof($host_interface) > 0 )
                {
                    $response_result = AgentManager::send($host_interface['ip'],$host_interface['port'],"{servertype:'tomcat',optype:'100',args:{}}");
                    if(null == $response_result || $response_result['result'] == 'false')
                    {
                        echo "start node failed,hostid = ".$hostIdXX.",response result :".$response_result."</br>\n";
                        continue;
                    }
                    if($response_result['result'] == 'true')
                    {
                        echo "start node success,hostid = ".$hostIdXX."</br>\n";
                        DBstart();
                        $statusXXXX2 = DBexecute("INSERT into history_uint(itemid,clock,value,ns) select i.itemid,".time().",1,999999999 from items i where i.hostid =".$hostIdXX." and i.name = 'net.tcp.listen.run.status'");
                        DBend($statusXXXX2);
                        break;
                    }
                }
            }
        }
        elseif($avg_cpu<0.5 and sizeof($hosts_run)>1)
        {
            #如果低于50%，并且主机大于两个，则关闭一台；
            echo "the average cpu usage rate is smaller than 0.5,then stop one node.</br>\n";
            if(sizeof($hosts_run) == 0)
            {
                echo "there is no host in run status to stop.</br>\n";
            }
            #循环直至有一个成功的；
            foreach($hosts_run as $hostIdXX22=>$cpu_utilXX22)
            {
                $host_interface = DBfetch(DBselect("select ip,port from interface where type = 5 and hostid = ".$hostIdXX22,1));
                if(null != $host_interface and sizeof($host_interface) > 0 )
                {
                    $response_result = AgentManager::send($host_interface['ip'],$host_interface['port'],"{servertype:'tomcat',optype:'101',args:{}}");
                    if(null == $response_result || $response_result['result'] == 'false')
                    {
                        echo "shutdown node failed,hostid = ".$hostIdXX22.",response result :".$response_result."</br>\n";
                        continue;
                    }
                    if($response_result['result'] == 'true')
                    {
                        echo "shutdown node success,hostid = ".$hostIdXX22."</br>\n";
                        DBstart();
                        $statusXXXX3 = DBexecute("INSERT into history_uint(itemid,clock,value,ns) select i.itemid,".time().",0,999999999 from items i where i.hostid =".$hostIdXX22." and i.name = 'net.tcp.listen.run.status'");
                        DBend($statusXXXX3);
                        break;
                    }
                }
            }
        }
        else
        {
            #do nothing
            echo "the average cpu usage rate is ".$avg_cpu.",then do nothing.</br>\n";
        }
    }

    echo "end scala group ".$groupId['groupid']."</br>\n";
}

echo "end scala task ".date("Y-m-d H:i:s")."</br>\n";
?>
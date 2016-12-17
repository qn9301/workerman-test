<?php
namespace app\index\controller;
use think\Controller;
use Workerman\Worker;
class Index extends Controller
{
    public function index()
    {
    	Worker::$daemonize = true;
    	$w = new Worker("websocket://127.0.0.1:8080");
    	$users = array();
    	$w->onConnect = function ($c)
    	{
    		global $users;
    		$users[$c->id] = ['source'=>$c,"data"=>['left'=>0,"top"=>0]];
    		$data = json_encode(['type'=>1, "id"=>$c->id]);
    		$c->send($data);
    	};

    	$w->onMessage = function ($c, $d)
    	{
    		global $users;
    		// var_dump($users);
    		list($x,$y) = explode(":", $d);
			$data = [];
    		foreach ($users as $k => $v) {
    			if($k == $c->id){
    				$users[$k]['data'] = ['left'=>$x,"top"=>$y];
    			}
    			$data[$k] = [];
    			$data[$k]['data'] = ['left'=>$v['data']['left'],"top"=>$v['data']['top']];
    		}

    		$req = json_encode($data);
    		echo $req;
    		foreach ($users as $k=>$v){
    			if($k == $c->id){
    				continue;
    			}
    			$v['source']->send($req);
    			echo 1;
    		}
    	};

    	// $w->onClose = function ($c)
    	// {
    	// 	global $users;
    	// 	global $w;
    	// 	$count = count($w->connections);
    	// 	foreach ($users as $k=>$v)
    	// 	{
    	// 		if($v['source']===$k){
    	// 			unset($users[$k]);
    	// 			continue;
    	// 		}
    	// 		$v->send("有人离开了讨论组，现在共有".($count)."人");
    	// 	}
    	// };

    	Worker::runAll();
    }

    public function socketClient()
    {
    	return $this->fetch('socketClient');
    }
}

<?php
// +------------------------------------------------------------
// | Call 系统类调用
// +------------------------------------------------------------
// | 调用路由器、拦截器和控制器
// +------------------------------------------------------------
// | Author : yuanhang.chen@gmail.com
// +------------------------------------------------------------

/**
 * 调用路由器
 *
 * @param string $router 路由器
 * @return type $dispatch 返回结果
 */
function lb_call_router($router) {
	// 路由器组件存在时执行调度
	if (lb_require_file ( _LIB_PATH, 'Mod.Router.' . $router )) {
		$router_class = 'Mod_Router_' . lb_convert_quote_to_class ( $router );
		$router = new $router_class ();
		return $router->dispatch ();
	}
}

/**
 * 调用拦截器
 *
 * @param string $intercepter 拦截器
 * @return type $interrupt 返回结果
 */
function lb_call_intercepter($intercepter) {
	// 拦截器组件存在时执行中断
	if (lb_require_lib ( 'Intercepter.' . $intercepter )) {
		$intercepter_class = 'Intercepter_' . lb_convert_quote_to_class ( $intercepter );
		$intercepter = new $intercepter_class ();
		return $intercepter->interrupt ();
	}
}

/**
 * 调用控制器
 *
 * @param string $controller 控制器
 * @return type $act 返回结果
 */
function lb_call_controller($controller) {
	// 控制器存在时执行操作
	if (lb_require_lib ( 'Controller.' . $controller )) {
		$controller_class = 'Controller_' . lb_convert_quote_to_class ( $controller );
		$controller = new $controller_class ();
		return $controller->act ();
	}
}

// +------------------------------------------------------------
// | Load 外部资源加载
// +------------------------------------------------------------
// | 加载数据库、脚本文件和样式文件
// +------------------------------------------------------------
// | Author : yuanhang.chen@gmail.com
// +------------------------------------------------------------

/**
 * 加载数据库
 *
 * @param string $name 配置索引
 * @return object $database 数据库连接
 */
function lb_load_database($name) {
	// 主从数据库配置
	$db_conf = lb_read_database ( $name );
	preg_match ( '/\.slave$/', $name ) && $db_conf = $db_conf [rand ( 0, count ( $db_conf ) - 1 )];
	// 连接数据库并返回
	$db_index = explode ( '.', $name );
	if (lb_require_file ( _LIB_PATH, 'Driver.Db.' . $db_index [0] )) {
		$db_class = 'Driver_Db_' . ucfirst ( strtolower ( $db_index [0] ) );
		$db = new $db_class ();
		return $db->connect ( $db_conf ['host'], $db_conf ['dbname'], $db_conf ['user'], $db_conf ['password'], $db_conf ['charset'] );
	}
}

/**
 * 加载脚本文件
 *
 * @param string $name 配置索引
 * @param array $files 脚本文件
 */
function lb_load_script($name, $files) {
	// 负载均衡配置
	$static_conf = lb_read_static ( $name );
	preg_match ( '/\.balance$/', $name ) && $static_conf = $static_conf [rand ( 0, count ( $static_conf ) - 1 )];
	// 输出脚本标签及文件版本
	foreach ( ( array ) $files as $script ) {
		$file_path = strtolower ( lb_convert_quote_to_path ( $script ) ) . '.js?version=' . $static_conf ['time'];
		echo '<script type="text/javascript" src="' . $static_conf ['domain'] . $file_path . '"></script>';
		echo "\n";
	}
}

/**
 * 加载样式文件
 *
 * @param string $name 配置索引
 * @param array $files 样式文件
 */
function lb_load_style($name, $files) {
	// 负载均衡配置
	$static_conf = lb_read_static ( $name );
	preg_match ( '/\.balance$/', $name ) && $static_conf = $static_conf [rand ( 0, count ( $static_conf ) - 1 )];
	// 输出样式标签及文件版本
	foreach ( ( array ) $files as $style ) {
		$file_path = strtolower ( lb_convert_quote_to_path ( $style ) ) . '.css?version=' . $static_conf ['time'];
		echo '<link rel="stylesheet" href="' . $static_conf ['domain'] . $file_path . '" />';
		echo "\n";
	}
}
?>
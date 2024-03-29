<?php
class Controller_Api_Server_Store extends Controller_Api_Server_Base {

	public function act() {
		// 存储查询
		$command = trim ( Util_Server_Request::get_param ( 'command', 'post' ) );
		$value = trim ( Util_Server_Request::get_param ( 'value', 'post' ) );
		
		$response = "";
		
		foreach ( explode ( '|', $command ) as $command ) {
			$command = trim ( strtolower ( $command ) );
			if (preg_match ( '/^config:\w+$/', $command )) {
				$response = $this->get_config ( $command, $value );
			}
		}
		
		$this->add_result ( $response );
		
		echo $response;
	}

	private function get_config($command, $value) {
		// 获取配置项
		$package_id = ( int ) Util_Server_Request::get_param ( 'packageid', 'post' );
		
		$config_keyword = explode ( ':', $command );
		$config = M ( 'Conf' )->get_by_keyword ( $package_id, 'data', $config_keyword [1] );
		$config_value = json_decode ( $config ['value'], true );
		$options = array ();
		
		if (! $config_value || count ( $config_value ) !== 2) {
			return '系统错误: 数据源配置关键字未找到。';
		}
		
		foreach ( explode ( ';', $config_value [1] ) as $option ) {
			$option = explode ( '=', $option );
			if (count ( $option ) === 2) {
				$options [$option [0]] = $option [1];
			}
		}
		
		if ($config_value [0] === 'mysql') {
			return $this->connect_mysql ( $options, $value );
		} else if ($config_value [0] === 'mysql_mysqli') {
			return $this->connect_mysqli ( $options, $value );
		} else if ($config_value [0] === 'mysql_pdo') {
			return $this->connect_pdo_mysql ( $options, $value );
		} else if ($config_value [0] === 'sqlsrv') {
			return $this->connect_sqlsrv ( $options, $value );
		} else if ($config_value [0] === 'sqlsrv_pdo') {
			return $this->connect_pdo_sqlsrv ( $options, $value );
		} else if ($config_value [0] === 'oracle_oci') {
			return $this->connect_oci_oracle ( $options, $value );
		}
	}

	private function add_result($content) {
		// 创建测试结果
		$temp = ( int ) Util_Server_Request::get_param ( 'temp', 'post' );
		
		if ($temp) {
			return;
		}
		
		$_POST ['stepid'] = 1;
		$_POST ['steptype'] = '存储查询';
		$_POST ['resultcontent'] = $content;
		M ( 'Result' )->insert ();
	}

	private function connect_mysql($options, $sql) {
		// MYSQL查询
		if (isset ( $options ['server'] ) && isset ( $options ['user'] ) && isset ( $options ['password'] ) && isset ( $options ['database'] ) && isset ( $options ['charset'] )) {
			try {
				$mysql = mysql_connect ( $options ['server'], $options ['user'], $options ['password'] );
				mysql_select_db ( $options ['database'], $mysql );
				mysql_query ( 'set names ' . $options ['charset'], $mysql );
				$result = mysql_query ( $sql, $mysql );
				if ($result) {
					$row = mysql_fetch_row ( $result );
					return implode ( ',', $row );
				}
			} catch ( Exception $e ) {
				return $e;
			}
		} else {
			return '系统错误: 连接串不正确，请检查是否包含server, user, password, database, charset参数。';
		}
		return '';
	}

	private function connect_mysqli($options, $sql) {
		// MYSQL(MYSQLI)查询
		if (isset ( $options ['server'] ) && isset ( $options ['user'] ) && isset ( $options ['password'] ) && isset ( $options ['database'] ) && isset ( $options ['charset'] )) {
			try {
				$mysqli = new mysqli ( $options ['server'], $options ['user'], $options ['password'], $options ['database'] );
				$mysqli->query ( 'set names ' . $options ['charset'] );
				$result = $mysqli->query ( $sql );
				if ($result) {
					$row = $result->fetch_row ();
					return implode ( ',', $row );
				}
			} catch ( Exception $e ) {
				return $e;
			}
		} else {
			return '系统错误: 连接串不正确，请检查是否包含server, user, password, database, charset参数。';
		}
		return '';
	}

	private function connect_pdo_mysql($options, $sql) {
		// MYSQL(PDO)查询
		if (isset ( $options ['server'] ) && isset ( $options ['user'] ) && isset ( $options ['password'] ) && isset ( $options ['database'] ) && isset ( $options ['charset'] )) {
			try {
				$pdo = new Pdo ( 'mysql:host=' . $options ['server'] . ';dbname=' . $options ['database'], $options ['user'], $options ['password'] );
				$pdo->setAttribute ( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
				$pdo->query ( 'set names ' . $options ['charset'] );
				$result = $pdo->query ( $sql );
				if ($result) {
					$row = $result->fetch ();
					return implode ( ',', $row );
				}
			} catch ( Exception $e ) {
				return $e;
			}
		} else {
			return '系统错误: 连接串不正确，请检查是否包含server, user, password, database, charset参数。';
		}
		return '';
	}

	private function connect_sqlsrv($options, $sql) {
		// SQLServer查询
		if (isset ( $options ['server'] ) && isset ( $options ['user'] ) && isset ( $options ['password'] ) && isset ( $options ['database'] ) && isset ( $options ['charset'] )) {
			try {
				$sqlsrv = sqlsrv_connect ( $options ['server'], array (
						'UID' => $options ['user'],
						'PWD' => $options ['password'],
						'Database' => $options ['database'],
						'CharacterSet' => $options ['charset'] 
				) );
				$result = sqlsrv_query ( $sqlsrv, $sql );
				if ($result) {
					$row = sqlsrv_fetch_array ( $result, SQLSRV_FETCH_ASSOC );
					return implode ( ',', $row );
				}
			} catch ( Exception $e ) {
				return $e;
			}
		} else {
			return '系统错误: 连接串不正确，请检查是否包含server, user, password, database, charset参数。';
		}
		return '';
	}

	private function connect_pdo_sqlsrv($options, $sql) {
		// SQLServer(PDO)查询
		if (isset ( $options ['server'] ) && isset ( $options ['user'] ) && isset ( $options ['password'] ) && isset ( $options ['database'] ) && isset ( $options ['charset'] )) {
			try {
				$pdo = new Pdo ( 'sqlsrv:server=' . $options ['server'] . ';database=' . $options ['database'], $options ['user'], $options ['password'] );
				$pdo->setAttribute ( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
				$pdo->query ( 'set character_set_connection=' . $options ['charset'] . ', character_set_results=' . $options ['charset'] . ', character_set_client=' . $options ['charset'] );
				$result = $pdo->query ( $sql );
				if ($result) {
					$row = $result->fetch ();
					return implode ( ',', $row );
				}
			} catch ( Exception $e ) {
				return $e;
			}
		} else {
			return '系统错误: 连接串不正确，请检查是否包含server, user, password, database, charset参数。';
		}
		return '';
	}

	private function connect_oci_oracle($options, $sql) {
		// ORACLE(OCI)查询
		if (isset ( $options ['server'] ) && isset ( $options ['user'] ) && isset ( $options ['password'] ) && isset ( $options ['charset'] )) {
			try {
				$conn = oci_connect ( $options ['user'], $options ['password'], $options ['server'], $options ['charset'] );
				if ($conn) {
					$result = oci_parse ( $conn, $sql );
					oci_execute ( $result, OCI_DEFAULT );
					if ($result) {
						$row = oci_fetch_row ( $result );
						return implode ( ',', $row );
					}
				}
			} catch ( Exception $e ) {
				return $e;
			}
		} else {
			return '系统错误: 连接串不正确，请检查是否包含server, user, password, charset参数。';
		}
		return '';
	}
}
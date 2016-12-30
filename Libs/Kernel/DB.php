<?php
/**
 * 数据库接口
 * 
 * @TODO 待接口统一
 *
 */
interface Kernel_DB{

	public function connect();
	
	public function disconnect();
}

##1. 初始化

    $PHPExcel = new PHPExcel_Lite();

##2. 使用
提供两个基础封装好的方法分别是exportExcel,importExcel分表接触导出和接受的问题

exportExcel接受三个参数,$data基础数据,$headArr标题,$filename文件名称下面是一个例子

      $data=array(
            array('username'=>'zhangsan','password'=>"123456"),
            array('username'=>'lisi','password'=>"abcdefg"),
            array('username'=>'wangwu','password'=>"111111"),
        );

        $filename    = "test_excel.xlsx";
        $headArr     = array("用户名", "密码");
        $PHPExcel = new PHPExcel_Lite();
        $PHPExcel->exportExcel($filename, $data, $headArr);


importExcel接受三个参数,$filename文件名称,$firstRowTitle标题(可选默认从第一行作为标题),$Sheet工作表(默认第一张工作表)

    $rs = $PHPExcel->importExcel("./test.xlsx");

**当然PHPExcel是一个强大的工具可以通过$PHPExcel->getPHPExcel();获得完整的PHPExcel实例自由使用**
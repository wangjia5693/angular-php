<?php
require_once dirname(__FILE__) . '/../../Public/init.php';

Service()->loader->addDirs(array('Demo', 'Library', 'Library/Task/Task'));

try {
    $progress = new Task_Progress();
    $progress->run();
} catch (Exception $ex) {
    echo $ex->getMessage();
    echo "\n\n";
    echo $ex->getTraceAsString();
    // notify ...
}

<?php
/**
 * Model_{%API_NAME%}
 * @author {%AUTHOR_NAME%} {%CREATE_TIME%}
 */

class Model_{%API_NAME%} extends App_Model_NotORM {

    protected function getTableName($id) {
        return '{%TABLE_NAME%}';
    }
}

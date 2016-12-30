<?php

/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/11/30.
 * 格式化上传文件
 *
 */
class Kernel_Request_Formatter_File extends Kernel_Request_Formatter_Base implements Kernel_Request_Formatter {

    /**
     * 格式化文件类型
     *
     * @param array $rule array('name' => '', 'type' => 'file', 'default' => array(...), 'min' => '', 'max' => '', 'range' => array(...))
     *
     * @throws Kernel_Exception_BadRequest
     */
    public function parse($value, $rule) {

        $default = isset($rule['default']) ? $rule['default'] : NULL;

        $index = $rule['name'];
        // 未上传
        if (!isset($_FILES[$index])) {
            // 有默认值 || 非必须
            if ($default !== NULL || (isset($rule['require']) && !$rule['require'])) {
                return $default;
            }
        }

        if (!isset($_FILES[$index]) || !isset($_FILES[$index]['error']) || !is_array($_FILES[$index])) {
            throw new Kernel_Exception_BadRequest(T('miss upload file: {file}', array('file' => $index)));
        }

        if ($_FILES[$index]['error'] != UPLOAD_ERR_OK) {
            throw new Kernel_Exception_BadRequest(T('fail to upload file with error = {error}', array('error' => $_FILES[$index]['error'])));
        }

        $sizeRule         = $rule;
        $sizeRule['name'] = $sizeRule['name'] . '.size';
        $this->filterByRange($_FILES[$index]['size'], $sizeRule);

        if (!empty($rule['range']) && is_array($rule['range'])) {
            $rule['range'] = array_map('strtolower', $rule['range']);
            $this->formatEnumValue(strtolower($_FILES[$index]['type']), $rule);
        }

        //对于文件后缀进行验证
        if (!empty($rule['ext'])) {
            $ext = trim(strrchr($_FILES[$index]['name'], '.'), '.');
            if (is_string($rule['ext'])) {
                $rule['ext'] = explode(',', $rule['ext']);
            }
            if (!$ext) {
                throw new Kernel_Exception_BadRequest(T('Not the file type {ext}', array('ext' => json_encode($rule['ext']))));
            }
            if (is_array($rule['ext'])) {
                $rule['ext'] = array_map('strtolower', $rule['ext']);
                $rule['ext'] = array_map('trim', $rule['ext']);
                if (!in_array(strtolower($ext), $rule['ext'])) {
                    throw new Kernel_Exception_BadRequest(T('Not the file type {ext}', array('ext' => json_encode($rule['ext']))));
                }
            }
        }

        return $_FILES[$index];
    }
}

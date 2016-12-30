<?php

class Task_Runner_Remote_Connector_Http extends Task_Runner_Remote_Connector {

    protected function doRequest($url, $data, $timeoutMs) {
        $curl = Service()->get('curl', 'Kernel_CUrl');

        return $curl->post($url, $data, $timeoutMs);
    }
}

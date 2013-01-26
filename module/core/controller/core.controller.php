<?php

class Core_Core_Controller extends Polaris_Controller {
    
    function init()
    {
        $this->load->model('user/auth');
        
        if ( ! $this->auth->isUser())
        {
            echo 'No es un usuario';
        }
    }
}
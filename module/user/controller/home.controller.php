<?php

class User_Home_Controller extends Polaris_Controller {
    
    var $user_id;
    var $_load = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->user_id = 9;
    }
    
    public function action_index($sVar = '')
    {
        echo 'Cargando un módulo/controlador definido.';
    }
    
    public function action_view()
    {
        echo '<br>Cargando un módulo/controlador/método definido';
    }
    
    public function call_by_controller()
    {
        echo '<br>Fui llamado por otro controlador';
        echo $this->user_id;
        echo $this->_load;
        $this->load->view('profile');
    }
}
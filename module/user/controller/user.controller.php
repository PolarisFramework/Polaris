<?php

class User_User_Controller extends Polaris_Controller {
    
    public function action_index($sVar = '')
    {
        $this->load->view('user');
    }
    public function action_view()
    {
        echo '<br>Cargando un módulo/método definido.';
    }
    public function member()
    {
        $this->load->view('profile');
    }
}
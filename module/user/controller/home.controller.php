<?php

class User_Home_Controller extends App_Controller {
    
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
    }
}
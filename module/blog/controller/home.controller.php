<?php
class Blog_Home_Controller extends App_Controller {
    
    public $autoload = array(
        //'helper' => array('url'),
        'model' => array('user/auth')
    );
    
    public function action_index()
    {
        $home = $this->load->module('user/home');
        $home->call_by_controller();
        
        $this->layout->title('Index');
        $this->layout->show('home');
    }
}
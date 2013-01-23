<?php
class Blog_Home_Controller extends App_Controller {
    
    public function action_index()
    {
        $this->load->set_var('varname', 'value');        
        $this->layout->title('Index');
        $this->layout->meta('og:vide_url', 'algo');
        //$this->load->view('home');
        //$this->layout->show('home');
        $this->layout->show('home');
        //var_dump($this->layout);
        //$this->load->view('home');
    }
}
<?php
class Blog_Home_Controller extends App_Controller {
    
    public function action_index()
    {
        $this->layout->title('Index');
        
        $this->load->database();
        $users = null;
        //$users = $this->db->select('u.*')->from('u_miembros', 'u')->where('u.user_activo = 1')->order('u.user_registro DESC')->left_join('u_perfil', 'p', 'u.user_id = p.user_id')->execute('row');
        //$users = $this->db->select('user_name')->from('u_miembros')->where('user_id = 1')->execute('field');
        //$users = $this->db->simple_query('SELECT user_name FROM u_miembros WHERE user_id = 2')->execute('row');
        
        $this->load->set_var('users', $users);
        $this->layout->show('home');
    }
}
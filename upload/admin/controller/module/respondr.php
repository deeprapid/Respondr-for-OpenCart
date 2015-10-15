<?php
class ControllerModuleRespondr extends Controller {
	private $error = array(); 
	
	public function index() {   
		$this->load->language('module/respondr');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('respondr', $this->request->post);		
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['entry_site_id'] = $this->language->get('entry_site_id');
		$data['entry_enable'] = $this->language->get('entry_enable');
		
		$data['help_site_id1'] = $this->language->get('help_site_id1');
		$data['help_site_id2'] = $this->language->get('help_site_id2');
		$data['help_enable'] = $this->language->get('help_enable');
		
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
 		if (isset($this->error['site_id'])) {
			$data['error_site_id'] = $this->error['site_id'];
		} else {
			$data['error_site_id'] = '';
		}
		

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/respondr', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$data['action'] = $this->url->link('module/respondr', 'token=' . $this->session->data['token'], 'SSL');
		
		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->post['respondr_site_id'])) {
			$data['respondr_site_id'] = $this->request->post['respondr_site_id'];
		} else {
			$data['respondr_site_id'] = $this->config->get('respondr_site_id');
		}	
		
		if (isset($this->request->post['respondr_enable'])) {
			$data['respondr_enable'] = $this->request->post['respondr_enable'];
		} else {
			$data['respondr_enable'] = $this->config->get('respondr_enable');
		}	
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/respondr.tpl', $data));	
	}
	
	// Validate the user inputs in the POST data.
	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/respondr')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (empty($this->request->post['respondr_site_id']))
		{
			$this->error['site_id'] = $this->language->get('error_site_id');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>
<?php
defined('BASEPATH') or exit('No direct script access allowed');
// Created By Seymur Umudov
class FileController extends CI_Controller
{
	public function store()
	{
		$client = $this->getAwsClient();

		$this->form_validation->set_rules('file_name', 'File Name', 'required');

		if ($this->form_validation->run() == true) {
			$data = $this->uploadFile('user_file');

			if (is_array($data)) {
				$client->putObject([
					'Bucket' => $this->get_config('STORAGE_NAME'),
					'Key' => $this->input->post('file_name', true),
					'Body' => file_get_contents($data['full_path']),
					'ACL' => 'private'
				]);
				$this->response('File upload successfull!', 200);
				unlink($data['full_path']);
			}
		} else {
			$this->response($this->getErrorMessages(), 422);
		}
	}

	private function uploadFile($name)
	{
		$config['upload_path'] = APPPATH . 'uploads/';
		$config['allowed_types'] = 'zip';
		$config['encrypt_name'] = true;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload($name)) {
			$this->response($this->upload->display_errors('', ''), 422);
		} else {
			return $this->upload->data();
		}
	}

	private function getAwsClient()
	{
		return new Aws\S3\S3Client([
			'version' => 'latest',
			'region' => $this->get_config('REGION_NAME'),
			'endpoint' => $this->get_config('ENDPOINT_URL'),
			'credentials' => [
				'key' => $this->get_config('AWS_ACCESS_KEY_ID'),
				'secret' => $this->get_config('AWS_SECRET_ACCESS_KEY'),
			],
		]);
	}

	private function response($message, $code)
	{
		$this->output
			->set_content_type('application/json')
			->set_status_header($code)
			->set_output(json_encode([
				'code' => $code,
				'messages' => $message,
			]));
	}

	private function get_config($key)
	{
		return $this->config->item($key);
	}

	private function getErrorMessages()
	{
		return array_filter(explode('.', str_replace("\n", '', strip_tags(validation_errors()))));
	}
}

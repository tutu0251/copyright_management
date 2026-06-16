<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Binary asset storage for works. Files live on disk under uploads/work_<id>/
 * (the old GridFS bucket); each row in work_assets is the metadata. Download
 * streams bytes back with inline/attachment disposition and HTTP Range support
 * so audio/video can seek — mirroring server/src/routes/assets.js.
 */
class Assets extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('asset_model');
		$this->load->model('work_model');
	}

	private function dir_for($work_id)
	{
		return UPLOAD_ROOT.'/work_'.(int) $work_id;
	}

	public function upload($work_id)
	{
		$this->require_perm('works.update');
		$work = $this->work_model->get($work_id);
		if ( ! $work) show_404();

		$dir = $this->dir_for($work_id);
		if ( ! is_dir($dir))
		{
			@mkdir($dir, DIR_WRITE_MODE, TRUE);
		}

		$config = array(
			'upload_path'   => $dir,
			'allowed_types' => '*',          // any file type
			'max_size'      => 0,            // bound by php.ini upload_max_filesize
			'encrypt_name'  => TRUE,         // random stored name; avoids collisions
		);
		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload('file'))
		{
			$this->session->set_flashdata('error', strip_tags($this->upload->display_errors('', ' ')));
			redirect('works/show/'.$work_id);
			return;
		}

		$d = $this->upload->data();
		$bytes = is_file($d['full_path']) ? filesize($d['full_path']) : 0;

		$id = $this->asset_model->insert(array(
			'work_id'      => (int) $work_id,
			'filename'     => $d['orig_name'],
			'stored_name'  => $d['file_name'],
			'content_type' => $d['file_type'] ? $d['file_type'] : 'application/octet-stream',
			'size'         => $bytes,
			'uploaded_by'  => $this->current_user['id'],
		));
		audit_log('upload', 'asset', $id, array('work_id' => (int) $work_id, 'filename' => $d['orig_name']));
		$this->session->set_flashdata('ok', 'File uploaded.');
		redirect('works/show/'.$work_id);
	}

	public function download($id)
	{
		$this->require_perm('works.view');
		$asset = $this->asset_model->get($id);
		if ( ! $asset) show_404();

		$path = $this->dir_for($asset['work_id']).'/'.$asset['stored_name'];
		if ( ! is_file($path))
		{
			show_error('File missing on disk', 404);
			return;
		}

		$total = (int) filesize($path);
		$ctype = $asset['content_type'] ? $asset['content_type'] : 'application/octet-stream';
		$disp = $this->input->get('download') ? 'attachment' : 'inline';
		$safe = str_replace(array("\r", "\n", '"'), '_', $asset['filename']);

		// Stream outside the CI output buffer.
		while (ob_get_level() > 0) { ob_end_clean(); }

		header('Content-Type: '.$ctype);
		header('Content-Disposition: '.$disp.'; filename="'.$safe.'"');
		header('Accept-Ranges: bytes');

		$range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : '';
		if ($range && $total && preg_match('/bytes=(\d*)-(\d*)/', $range, $m))
		{
			$start = ($m[1] === '') ? 0 : (int) $m[1];
			$end   = ($m[2] === '') ? $total - 1 : (int) $m[2];
			if ($end >= $total) $end = $total - 1;
			if ($start > $end || $start >= $total)
			{
				header('Content-Range: bytes */'.$total);
				$this->output->set_status_header(416);
				return;
			}
			$this->output->set_status_header(206);
			header('Content-Range: bytes '.$start.'-'.$end.'/'.$total);
			header('Content-Length: '.($end - $start + 1));
			$this->_stream($path, $start, $end);
			return;
		}

		header('Content-Length: '.$total);
		$this->_stream($path, 0, $total - 1);
	}

	/** Send bytes [$start, $end] of $path in chunks. */
	private function _stream($path, $start, $end)
	{
		$fp = fopen($path, 'rb');
		if ($fp === FALSE) return;
		fseek($fp, $start);
		$remaining = $end - $start + 1;
		$chunk = 8192;
		while ($remaining > 0 && ! feof($fp))
		{
			$read = ($remaining > $chunk) ? $chunk : $remaining;
			echo fread($fp, $read);
			flush();
			$remaining -= $read;
		}
		fclose($fp);
		exit;
	}

	public function delete($id)
	{
		$this->require_perm('works.update');
		$asset = $this->asset_model->get($id);
		if ( ! $asset) show_404();

		$path = $this->dir_for($asset['work_id']).'/'.$asset['stored_name'];
		if (is_file($path)) @unlink($path);

		$this->asset_model->soft_delete($id);
		audit_log('delete', 'asset', $id, array('work_id' => (int) $asset['work_id']));
		$this->session->set_flashdata('ok', 'File deleted.');
		redirect('works/show/'.$asset['work_id']);
	}
}

/* End of file assets.php */

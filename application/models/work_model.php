<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class Work_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'works';
	}

	/**
	 * List works, each decorated with its asset count + total bytes. Done as two
	 * queries (works, then one GROUP BY over work_assets) rather than a
	 * derived-table join, which CI 2.1.3's Active Record does not handle cleanly.
	 */
	public function list_with_asset_stats($limit = 500)
	{
		$works = $this->all($limit, 'updated_at DESC');
		if (empty($works)) return $works;

		$ids = array();
		foreach ($works as $w) $ids[] = (int) $w['id'];

		$rows = $this->db->select('work_id, COUNT(*) AS cnt, SUM(size) AS bytes', FALSE)
			->where('deleted_at IS NULL', NULL, FALSE)
			->where_in('work_id', $ids)
			->group_by('work_id')
			->get('work_assets')->result_array();

		$by_work = array();
		foreach ($rows as $r) $by_work[(int) $r['work_id']] = $r;

		foreach ($works as &$w)
		{
			$s = isset($by_work[(int) $w['id']]) ? $by_work[(int) $w['id']] : NULL;
			$w['asset_count'] = $s ? (int) $s['cnt'] : 0;
			$w['asset_size']  = $s ? (int) $s['bytes'] : 0;
		}
		unset($w);
		return $works;
	}

	public function distinct_types()
	{
		$rows = $this->db->distinct()->select('work_type')
			->where('deleted_at IS NULL', NULL, FALSE)
			->where("work_type <> ''", NULL, FALSE)
			->order_by('work_type ASC')
			->get('works')->result_array();
		$out = array();
		foreach ($rows as $r) $out[] = $r['work_type'];
		return $out;
	}

	/** Map of id => title for select dropdowns. */
	public function options()
	{
		$rows = $this->db->select('id, title')
			->where('deleted_at IS NULL', NULL, FALSE)
			->order_by('title ASC')->get('works')->result_array();
		$out = array();
		foreach ($rows as $r) $out[(int) $r['id']] = $r['title'];
		return $out;
	}

	public function recent($limit = 5)
	{
		return $this->db->select('id, title, copyright_status')
			->where('deleted_at IS NULL', NULL, FALSE)
			->order_by('updated_at DESC')->limit((int) $limit)
			->get('works')->result_array();
	}

	/** Count of works registered per YYYY-MM since $since (for the dashboard). */
	public function counts_by_month($since)
	{
		return $this->db
			->select("DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt", FALSE)
			->where('deleted_at IS NULL', NULL, FALSE)
			->where('created_at >=', $since)
			->group_by('ym')
			->get('works')->result_array();
	}
}

/* End of file work_model.php */

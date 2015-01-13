<?php
/**
	 * 解析淘宝助理CSV数据
	 *
	 * @param string $csv_string
	 * @return string
	 */
	private function parse_taobao_csv($csv_string)
	{
		/* 定义CSV文件中几个标识性的字符的ascii码值 */
		define('ORD_SPACE', 32); // 空格
		define('ORD_QUOTE', 34); // 双引号
		define('ORD_TAB',    9); // 制表符
		define('ORD_N',     10); // 换行\n
		define('ORD_R',     13); // 换行\r

		/* 字段信息 */
		$import_fields = $this->taobao_fields(); // 需要导入的字段在CSV中显示的名称
		$fields_cols = array(); // 每个字段所在CSV中的列序号，从0开始算
		$csv_col_num = 0; // csv文件总列数

		$pos = 0; // 当前的字符偏移量
		$status = 0; // 0标题未开始 1标题已开始
		$title_pos = 0; // 标题开始位置
		$records = array(); // 记录集
		$field = 0; // 字段号
		$start_pos = 0; // 字段开始位置
		$field_status = 0; // 0未开始 1双引号字段开始 2无双引号字段开始
		$line =0; // 数据行号
		while($pos < strlen($csv_string))
		{
			$t = ord($csv_string[$pos]); // 每个UTF-8字符第一个字节单元的ascii码
			$next = ord($csv_string[$pos + 1]);
			$next2 = ord($csv_string[$pos + 2]);
			$next3 = ord($csv_string[$pos + 3]);

			if ($status == 0 && !in_array($t, array(ORD_SPACE, ORD_TAB, ORD_N, ORD_R)))
			{
				$status = 1;
				$title_pos = $pos;
			}
			
			if ($status == 1)
			{
				if ($field_status == 0 && $t== ORD_N)
				{
					static $flag = null;
					if ($flag === null)
					{
						$title_str = substr($csv_string, $title_pos, $pos - $title_pos);
						$title_arr = explode("\t", trim($title_str));
						$fields_cols = $this->taobao_fields_cols($title_arr, $import_fields);
						
						if (count($fields_cols) != count($import_fields))
						{
							return false;
						}
						$csv_col_num = count($title_arr); // csv总列数
						$flag = 1;
					}

					if ($next == ORD_QUOTE)
					{
						$field_status = 1; // 引号数据单元开始
						$start_pos = $pos = $pos + 2; // 数据单元开始位置(相对\n偏移+2)
					}
					else
					{
						$field_status = 2; // 无引号数据单元开始
						$start_pos = $pos = $pos + 1; // 数据单元开始位置(相对\n偏移+1)
					}
					continue;
				}

				if($field_status == 1 && $t == ORD_QUOTE && in_array($next, array(ORD_N, ORD_R, ORD_TAB))) // 引号+换行 或 引号+\t
				{
					$records[$line][$field] = addslashes(substr($csv_string, $start_pos, $pos - $start_pos));
					$field++;
					if ($field == $csv_col_num)
					{
						$line++;
						$field = 0;
						$field_status = 0;
						continue;
					}
					if (($next == ORD_N && $next2 == ORD_QUOTE) || ($next == ORD_TAB && $next2 == ORD_QUOTE) || ($next == ORD_R && $next2 == ORD_QUOTE))
					{
						$field_status = 1;
						$start_pos = $pos = $pos + 3;
						continue;
					}
					if (($next == ORD_N && $next2 != ORD_QUOTE) || ($next == ORD_TAB && $next2 != ORD_QUOTE) || ($next == ORD_R && $next2 != ORD_QUOTE))
					{
						$field_status = 2;
						$start_pos = $pos = $pos + 2;
						continue;
					}
					if ($next == ORD_R && $next2 == ORD_N && $next3 == ORD_QUOTE)
					{
						$field_status = 1;
						$start_pos = $pos = $pos + 4;
						continue;
					}
					if ($next == ORD_R && $next2 == ORD_N && $next3 != ORD_QUOTE)
					{
						$field_status = 2;
						$start_pos = $pos = $pos + 3;
						continue;
					}
				}

				if($field_status == 2 && in_array($t, array(ORD_N, ORD_R, ORD_TAB))) // 换行 或 \t
				{
					$records[$line][$field] = addslashes(substr($csv_string, $start_pos, $pos - $start_pos));
					$field++;
					if ($field == $csv_col_num)
					{
						$line++;
						$field = 0;
						$field_status = 0;
						continue;
					}
					if (($t == ORD_N && $next == ORD_QUOTE) || ($t == ORD_TAB && $next == ORD_QUOTE) || ($t == ORD_R && $next == ORD_QUOTE))
					{
						$field_status = 1;
						$start_pos = $pos = $pos + 2;
						continue;
					}
					if (($t == ORD_N && $next != ORD_QUOTE) || ($t == ORD_TAB && $next != ORD_QUOTE) || ($t == ORD_R && $next != ORD_QUOTE))
					{
						$field_status = 2;
						$start_pos = $pos = $pos + 1;
						continue;
					}
					if ($t == ORD_R && $next == ORD_N && $next2 == ORD_QUOTE)
					{
						$field_status = 1;
						$start_pos = $pos = $pos + 3;
						continue;
					}
					if ($t == ORD_R && $next == ORD_N && $next2 != ORD_QUOTE)
					{
						$field_status = 2;
						$start_pos = $pos = $pos + 2;
						continue;
					}
				}
			}

			if($t > 0 && $t <= 127) {
				$pos++;
			} elseif(192 <= $t && $t <= 223) {
				$pos += 2;
			} elseif(224 <= $t && $t <= 239) {
				$pos += 3;
			} elseif(240 <= $t && $t <= 247) {
				$pos += 4;
			} elseif(248 <= $t && $t <= 251) {
				$pos += 5;
			} elseif($t == 252 || $t == 253) {
				$pos += 6;
			} else {
				$pos++;
			}	
		}
		$return = array();
		foreach ($records as $key => $record)
		{
			foreach ($record as $k => $col)
			{
				$col = trim($col); // 去掉数据两端的空格
				/* 对字段数据进行分别处理 */
				switch ($k)
				{
					case $fields_cols['goods_body']		: $return[$key]['goods_body'] = str_replace(array("\\\"\\\"", "\"\""), array("\\\"", "\""), $col); break;
					case $fields_cols['goods_image']	: $return[$key]['goods_image'] = trim($col,'"');break;
					//case $fields_cols['goods_show']		: $return[$key]['goods_show'] = $col == 1 ? 0 : 1; break;
					case $fields_cols['goods_name']		: $return[$key]['goods_name'] = $col; break;
					case $fields_cols['spec_goods_storage']	: $return[$key]['spec_goods_storage'] = $col; break;
					case $fields_cols['goods_store_price']: $return[$key]['goods_store_price'] = $col; break;
					case $fields_cols['goods_commend']	: $return[$key]['goods_commend'] = $col; break;
					case $fields_cols['sale_attr']		: $return[$key]['sale_attr'] = $col; break;
					case $fields_cols['goods_form']	: $return[$key]['goods_form'] = $col; break;
					case $fields_cols['goods_transfee_charge']		: $return[$key]['goods_transfee_charge'] = $col; break;
					case $fields_cols['py_price']	: $return[$key]['py_price'] = $col; break;
					case $fields_cols['es_price']		: $return[$key]['es_price'] = $col; break;
					case $fields_cols['kd_price']		: $return[$key]['kd_price'] = $col; break;
					case $fields_cols['kd_price']		: $return[$key]['kd_price'] = $col; break;
//					case $fields_cols['goods_indate']	: $return[$key]['goods_indate'] = $col; break;
				}
			}
		}
		return $return;
	}
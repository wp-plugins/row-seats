<style>
.admin_rst_wrap table.rst_records {


	width: 100%;


	border-collapse: collapse;


}


.admin_rst_wrap table.rst_records tr {





}


.admin_rst_wrap table.rst_records td,


.admin_rst_wrap table.rst_records th {


	padding: 5px 5px 5px 5px;


	vertical-align: top;


	border: 1px solid #C0C0C0;


}





.admin_rst_wrap table.rst_records th {


	background-color: #E0DBD0;


	vertical-align: middle;


}





.admin_rst_wrap table.rst_records a{


	color: #0080FF;


	text-decoration: none;


}


.admin_rst_wrap table.rst_records a:hover{


	text-decoration: underline;


}


.rst_buttons {text-align: right; margin-bottom: 10px; margin-top: 10px; float: right;}


.rst_pageswitcher {


	margin-right: 200px;


	float: left;


}


.rst_pageswitcher div {float: none !important;}

</style>

<?php

//print "Inside";

define('RST_CSV_SEPARATOR', ',');

define('RST_ROWS_PER_PAGE', '25');

define('RST_STATUS_DRAFT', '0');

define('RST_STATUS_ACTIVE', '1');




		global $wpdb;

		if (isset($_GET["s"])) $search_query = trim(stripslashes($_GET["s"]));

		else $search_query = '';

		if (isset($_GET["tid"])) $transaction_id = trim(stripslashes($_GET["tid"]));

		else $transaction_id = '';

		$tmp = $wpdb->get_row("SELECT COUNT(*) AS total FROM rst_payment_transactions WHERE deleted = '0'".(strlen($transaction_id) > 0 ? " AND tx_str = '".$transaction_id."'" : "").((strlen($search_query) > 0) ? " AND (payer_name LIKE '%".addslashes($search_query)."%' OR payer_email LIKE '%".addslashes($search_query)."%')" : ""), ARRAY_A);

		$total = $tmp["total"];
		
		//print "<br>total=".$total;

		$totalpages = ceil($total/RST_ROWS_PER_PAGE);

		if ($totalpages == 0) $totalpages = 1;

		if (isset($_GET["p"])) $page = intval($_GET["p"]);

		else $page = 1;

		if ($page < 1 || $page > $totalpages) $page = 1;

		$switcher = page_switcher(admin_url('admin.php')."?page=rst-transactions".((strlen($search_query) > 0) ? "&s=".rawurlencode($search_query) : "").(strlen($transaction_id) > 0 ? "&tid=".$transaction_id : ""), $page, $totalpages);

		$sql = "SELECT * FROM rst_payment_transactions WHERE deleted = '0'".(strlen($transaction_id) > 0 ? " AND tx_str = '".$transaction_id."'" : "").((strlen($search_query) > 0) ? " AND (payer_name LIKE '%".addslashes($search_query)."%' OR payer_email LIKE '%".addslashes($search_query)."%')" : "")." ORDER BY created DESC LIMIT ".(($page-1)*RST_ROWS_PER_PAGE).", ".RST_ROWS_PER_PAGE;

		$rows = $wpdb->get_results($sql, ARRAY_A);

		$message = '';
		//print "<br>2total=".$total;
		

		if (!empty($error)) $message = "<div class='error'><p>".$error."</p></div>";

		else if (!empty($info)) $message = "<div class='updated'><p>".$info."</p></div>";

		$transaction_columns = array(

			'bookingid' => array('title' => __('ID', 'rst'), 'style' => 'width: 20px', 'class' => ''),

			'payer' => array('title' => __('Payer', 'rst'), 'style' => '', 'class' => ''),
			'events' => array('title' => __('Event details', 'rst'), 'style' => '', 'class' => ''),
			'amount' => array('title' => __('Amount', 'rst'), 'style' => 'width: 100px', 'class' => ''),

			'status' => array('title' => __('Status', 'rst'), 'style' => 'width: 120px;', 'class' => ''),

			'created' => array('title' => __('Created', 'rst'), 'style' => 'width: 130px;', 'class' => ''),

			'delete' => array('title' => '', 'style' => 'width: 100px;', 'class' => '')

		);

		//$transaction_columns = apply_filters('rst_payment_transaction_columns', $transaction_columns);



		echo '

			<div class="wrap admin_rst_wrap">

				<div id="icon-edit-pages" class="icon32"><br /></div><h2>'.__('Row Seats - Transactions', 'rst').'</h2><br />

				'.$message.'

				<form action="'.admin_url('admin.php').'" method="get" style="margin-bottom: 10px;">

				<input type="hidden" name="page" value="rst-transactions" />

				'.(strlen($transaction_id) > 0 ? '<input type="hidden" name="tid" value="'.$transaction_id.'" />' : '').'

				'.__('Search', 'rst').': <input type="text" name="s" value="'.htmlspecialchars($search_query, ENT_QUOTES).'">

				<input type="submit" class="button-secondary action" value="'.__('Search', 'rst').'" />

				'.((strlen($search_query) > 0) ? '<input type="button" class="button-secondary action" value="'.__('Reset search results', 'rst').'" onclick="window.location.href=\''.admin_url('admin.php').'?page=rst-transactions\';" />' : '').'

				</form>

				<div class="rst_buttons"><a class="button" href="'.admin_url('admin.php').'?action=rst-export-transactions">'.__('Export to CSV', 'rst').'</a></div>

				<div class="rst_pageswitcher">'.$switcher.'</div>

				<table class="rst_records">

				<tr>';

		foreach($transaction_columns as $value) {

			echo '

					<th'.(!empty($value['style']) ? ' style="'.$value['style'].'"' : '').''.(!empty($value['class']) ? ' class="'.$value['class'].'"' : '').'>'.esc_attr($value['title']).'</th>';

		}

		echo '

				</tr>';



		if (sizeof($rows) > 0) {

			foreach ($rows as $row) {

				//$certificates = $wpdb->get_results("SELECT * FROM rst_certificates WHERE tx_str = '".$row["tx_str"]."'", ARRAY_A);

				$list = array();

				//foreach ($certificates as $certificate) {

					//if ($certificate["deleted"] == 0) $list[] = '<a href="'.admin_url('admin.php').'?page=rst-certificates&s='.$certificate["code"].'">'.esc_attr($certificate["code"]).'</a>';

					//else $list[] = '*'.$certificate["code"];

				//}

$offlinepaymentstring="";			
				
if($row['transaction_type']=='Offline Payment:Not Paid')		
{

$offlinepaymentstring='&nbsp;<a href="'.admin_url('admin.php').'?action=rst-paid-transaction&id='.$row['id'].'" title="'.__('Offline Payment . Mark as Paid', 'rst').'"><img src="'.RSTPLN_URL.'/images/paid.png'.'" alt="'.__('Offline Payment . Mark as Paid', 'rst').'"  border="0"></a>';

}

$offlinepaymentreport="";			
				
if($row['tx_str'])		
{

$offlinepaymentreport='&nbsp;<a href="'.admin_url('admin.php').'?page=rst-reports&bookingid='.$row['tx_str'].'" title="'.__('View report', 'rst').'"><img src="'.RSTPLN_URL.'/images/reports.png'.'" alt="'.__('View Report', 'rst').'"  border="0"></a>&nbsp;';

}


		

					
				

				$transaction_column_values = array(

					'certificates' => array(

						'value' => $row['id'], 

						'style' => '', 

						'class' => ''

					),

					'payer' => array(

						'value' => esc_attr($row['payer_name']).'<br /><em class="rst_table_description">'.esc_attr($row['payer_email']).'</em>',

						'style' => '', 

						'class' => ''

					),
						'eventdetails' => array(

						'value' => esc_attr($row['show_name']).'<br /><em class="rst_table_description">'.esc_attr($row['show_date']).'</em>',

						'style' => '', 

						'class' => ''

					),				

					'amount' => array(

						'value' => number_format($row['gross'], 2, ".", "").' '.$row['currency'],

						'style' => 'text-align: right;', 

						'class' => ''

					),

					'status' => array(

						'value' => '<a href="'.admin_url('admin.php').'?action=rst-transaction-details&id='.$row['id'].'" class="thickbox" title="'.__('Transaction Details', 'rst').'">'.esc_attr($row["payment_status"]).'</a><br /><em class="rst_table_description">'.esc_attr($row["transaction_type"]).'</em>',

						'style' => '', 

						'class' => ''

					),

					'created' => array(

						'value' => date("Y-m-d H:i:s", $row["created"]),

						'style' => '', 

						'class' => ''

					),

					'delete' => array(

						'value' => $offlinepaymentreport.'<a href="'.admin_url('admin.php').'?action=rst-delete-transaction&id='.$row['id'].'" title="'.__('Delete transaction', 'rst').'"><img src="'.RSTPLN_URL.'/images/delete.png'.'" alt="'.__('Delete transaction', 'rst').'" onclick="return rst_submit_action();" border="0"></a>'.$offlinepaymentstring,

						'style' => 'text-align: center;', 

						'class' => ''

					)

				);

				

				$transaction_column_values = apply_filters('rst_payment_transaction_column_values', $transaction_column_values, $row);

				echo '

				<tr>';

				foreach($transaction_column_values as $value) {

					echo '

					<td'.(!empty($value['style']) ? ' style="'.$value['style'].'"' : '').''.(!empty($value['class']) ? ' class="'.$value['class'].'"' : '').'>'.$value['value'].'</td>';

				}

				echo '

				</tr>';

			}

		} else {

			print ('

				<tr><td colspan="'.sizeof($transaction_columns).'" style="padding: 20px; text-align: center;">'.((strlen($search_query) > 0) ? 'No results found.' : 'List is empty.').'</td></tr>

			');

		}

		echo '

				</table>

				<div class="rst_buttons"><a class="button" href="'.admin_url('admin.php').'?action=rst-export-transactions">'.__('Export to CSV', 'rst').'</a></div>

				<div class="rst_pageswitcher">'.$switcher.'</div>

				<div class="rst_legend">';



		

		echo '

			</div>

			<script type="text/javascript">

				function rst_submit_action() {

					var answer = confirm("Do you really want to continue?")

					if (answer) return true;

					else return false;

				}

			</script>';


	function page_switcher ($_urlbase, $_currentpage, $_totalpages) {

		$pageswitcher = "";

		if ($_totalpages > 1) {

			$pageswitcher = '<div class="tablenav bottom"><div class="tablenav-pages">'.__('Pages:', 'wpgc').' <span class="pagiation-links">';

			if (strpos($_urlbase,"?") !== false) $_urlbase .= "&amp;";

			else $_urlbase .= "?";

			if ($_currentpage == 1) $pageswitcher .= "<a class='page disabled'>1</a> ";

			else $pageswitcher .= " <a class='page' href='".$_urlbase."p=1'>1</a> ";



			$start = max($_currentpage-3, 2);

			$end = min(max($_currentpage+3,$start+6), $_totalpages-1);

			$start = max(min($start,$end-6), 2);

			if ($start > 2) $pageswitcher .= " <b>...</b> ";

			for ($i=$start; $i<=$end; $i++) {

				if ($_currentpage == $i) $pageswitcher .= " <a class='page disabled'>".$i."</a> ";

				else $pageswitcher .= " <a class='page' href='".$_urlbase."p=".$i."'>".$i."</a> ";

			}

			if ($end < $_totalpages-1) $pageswitcher .= " <b>...</b> ";



			if ($_currentpage == $_totalpages) $pageswitcher .= " <a class='page disabled'>".$_totalpages."</a> ";

			else $pageswitcher .= " <a class='page' href='".$_urlbase."p=".$_totalpages."'>".$_totalpages."</a> ";

			$pageswitcher .= "</span></div></div>";

		}

		return $pageswitcher;

	}

			
			?>
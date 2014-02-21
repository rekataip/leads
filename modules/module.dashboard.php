<?php/** Widget */Class Wp_lead_Dashboard_Widgets {    function __construct()    {		$enable = get_option('wpl-main-enable-dashboard',1);		$disable = get_option('wpl-main-disable-widgets',1);		if ($disable)		{			add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );		}		if ($enable)		{			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );		}    }    function remove_dashboard_widgets()	{	    global $remove_defaults_widgets;	    foreach ($remove_defaults_widgets as $wigdet_id => $options)	    {	        remove_meta_box($wigdet_id, $options['page'], $options['context']);	    }	}    function add_dashboard_widgets()	{	    global $custom_dashboard_widgets;	    foreach ($custom_dashboard_widgets as $widget_id => $options)	    {	        wp_add_dashboard_widget(	            $widget_id,	            $options['title'],	            $options['callback']	        );	    }	}}$wdw = new Wp_lead_Dashboard_Widgets();add_action( 'admin_enqueue_scripts', 'custom_register_admin_scripts' );function custom_register_admin_scripts( $hook ) {	if( 'index.php' == $hook ) {		wp_register_script( 'jquery-cookie', WPL_URL . '/js/jquery.cookie.js' );		wp_enqueue_script( 'jquery-cookie' );		wp_register_script( 'flot', WPL_URL . '/js/jquery.flot.js' );		wp_enqueue_script( 'flot' );		wp_register_script( 'flot-stack', WPL_URL . '/js/jquery.flot.stack.js' );		wp_enqueue_script( 'flot-stack' );		wp_register_script( 'flot-time', WPL_URL . '/js/jquery.flot.time.js' );		wp_enqueue_script( 'flot-time' );		wp_register_script( 'flot-axislabels', WPL_URL . '/js/jquery.flot.axislabels.js' );		wp_enqueue_script( 'flot-axislabels' );		wp_register_script( 'lead-flot-functions', WPL_URL . '/js/lead-flot-functions.js' );		wp_enqueue_script( 'lead-flot-functions' );		wp_register_script( 'custom-dashboard-js', WPL_URL . '/js/custom-dashboard.js' );		wp_enqueue_script( 'custom-dashboard-js' );		wp_register_script( 'jquery-dropdown', WPL_URL . '/js/jquery.dropdown.js' );		wp_enqueue_script( 'jquery-dropdown' );		wp_enqueue_style('custom-dashboard-css', WPL_URL . '/css/wpl.dashboard.css');	} // end if} // end custom_register_admin_scripts// Dashboard Functionsfunction get_lead_count_from_last_24h($post_type ='wp-lead') {    global $wpdb;    $numposts = $wpdb->get_var(        $wpdb->prepare(            "SELECT COUNT(ID) ".            "FROM {$wpdb->posts} ".            "WHERE ".                "post_status='publish' ".                "AND post_type= %s ".                "AND post_date> %s",            $post_type, date('Y-m-d H:i:s', strtotime('-24 hours'))        )    );    return $numposts;}function get_lead_count_from_today($post_type ='wp-lead') {    global $wpdb;    $wordpress_date_time = $timezone_format = _x('Y-m-d', 'timezone date format');    $wordpress_date_time =  date_i18n($timezone_format);    $wordpress_date = $timezone_day = _x('d', 'timezone date format');    $wordpress_date =  date_i18n($timezone_day);    $today = $wordpress_date_time; // Corrected timezone    $tomorrow = date("Y-m-d",strtotime("+2 day")); // Hack to look 2 days ahead    $numposts = $wpdb->get_var(        $wpdb->prepare(            "SELECT COUNT(ID) ".            "FROM {$wpdb->posts} ".            "WHERE post_status='publish' ".                "AND post_type= %s ".                "AND wp_posts.post_date BETWEEN %s AND %s",            $post_type, $today, $tomorrow        )    );    return $numposts;}function lead_count_by_time($post_type ='wp-lead', $start_current, $end_current) {    global $wpdb;    $numposts = $wpdb->get_var(        $wpdb->prepare(            "SELECT COUNT(ID) ".            "FROM {$wpdb->posts} ".            "WHERE post_status='publish' ".                "AND post_type= %s ".                "AND wp_posts.post_date BETWEEN %s AND %s",            $post_type, $start_current, $end_current        )    );    return $numposts;}/** Render*/$remove_defaults_widgets = array(    'dashboard_incoming_links' => array(        'page'    => 'dashboard',        'context' => 'normal'    ),    'dashboard_right_now' => array(        'page'    => 'dashboard',        'context' => 'normal'    ),    'dashboard_recent_drafts' => array(        'page'    => 'dashboard',        'context' => 'side'    ),    'dashboard_quick_press' => array(        'page'    => 'dashboard',        'context' => 'side'    ),    'dashboard_plugins' => array(        'page'    => 'dashboard',        'context' => 'normal'    ),    'dashboard_primary' => array(        'page'    => 'dashboard',        'context' => 'side'    ),    'dashboard_secondary' => array(        'page'    => 'dashboard',        'context' => 'side'    ),    'dashboard_recent_comments' => array(        'page'    => 'dashboard',        'context' => 'normal'    ),    'rg_forms_dashboard' => array(        'page'    => 'dashboard',        'context' => 'normal'    ),);$custom_dashboard_widgets = array(    'wp-lead-stats' => array(        'title' => 'Lead Stats',        'callback' => 'wp_lead_dashboard_stats'    ),    'wp-lead-dashboard-list' => array(        'title' => 'Lead Lists',        'callback' => 'wp_lead_dashboard_list'    ),);function wp_lead_dashboard_list(){    $user = wp_get_current_user();    $admin_url = get_admin_url() ?>    <div id='leads-list'>    <?php  $r = new WP_Query( apply_filters( 'widget_posts_args', array(        'posts_per_page' => -1,        'post_type' => 'list',        'post_status' => 'publish') ) );    if ($r->have_posts()) : ?>    <h4 class='marketing-widget-header'><?php _e('Lists' , 'leads'); ?><span class="toggle-lead-list">-</span></h4>    <ul id='lead-ul' class="dashboard-lead-lists">    <?php 		$leads_count = get_transient('wpleads_list_count');				if (!$leads_count)		{			while ( $r->have_posts() ) : $r->the_post(); 			$leads_count[get_the_ID()]['list_name'] = get_the_title();			$leads_count[get_the_ID()]['count'] =	wpleads_count_associated_lead_items( get_the_ID() );					endwhile; 				set_transient('wpleads_list_count' , $lead_ids , 60*60*24);		}				foreach ( $leads_count as $lead_id => $lead )		{		?>		<li>           <a href="<?php echo $admin_url;?>/wp-admin/post.php?post=<?php echo $lead_id; ?>&action=edit"><?php echo $lead['list_name'];?></a> <span class='lead-list-count'><?php echo $lead['count']; ?></span>        </li>		<?php		}		?>    </ul>    <?php endif; ?>    </div><?php }function get_lead_graph_data($post_type ='wp-lead', $month, $type){	global $wpdb;    $wordpress_date_time = $timezone_format = _x('Y-m-d', 'timezone date format');    $wordpress_date_time =  date_i18n($timezone_format);    $wordpress_date = $timezone_day = _x('d', 'timezone date format');    $wordpress_date =  date_i18n($timezone_day);    $this_year = _x('Y', 'timezone date format');    $this_year =  date_i18n($this_year);    $loop_count = date('d',strtotime('last day of this month'));    $final_loop_count = cal_days_in_month(CAL_GREGORIAN, $month, $this_year); // Count of days in month    //echo $final_loop_count; // How many times to run    $lead_increment = 0;    for ($i = 1; $i < $final_loop_count + 1; $i++) {           // echo "hi" . $i;        $year = $this_year;        $day = $i;        $next_day = $i + 1;        $m = $month;        $Date = strtotime($year . "-" . $m . "-" . $day);        $Date_next = strtotime($year . "-" . $m . "-" . $next_day);        $clean_date_one = date('Y-m-d', $Date);        $clean_date_one_formatted = date('Y, n, d', $Date);        if ($type === "last-month"){            $Date = strtotime($year . "-" . $m . "-" . $day . ' +1 months');            $clean_date_one_formatted = date('Y, n, d', $Date);        }        $clean_date_two = date('Y-m-d', $Date_next);        //echo $clean_date_one . "<br>";       $numposts = $wpdb->get_var(        $wpdb->prepare(            "SELECT COUNT(ID) ".            "FROM {$wpdb->posts} ".            "WHERE post_status='publish' ".                "AND post_type= %s ".                "AND wp_posts.post_date BETWEEN %s AND %s",            $post_type, $clean_date_one, $clean_date_two        )    );    $lead_increment += $numposts;    //echo "Day is: ". $day . " " . $numposts . " on " . $clean_date_one  .  "<br>";    echo "[gd(". $clean_date_one_formatted . "), "  . $lead_increment . ", ". $numposts ."], ";    }}function wp_lead_dashboard_stats(){    global $wpdb;    $count_posts = wp_count_posts('wp-lead');    $url = site_url();	$c_month   = date( 'n' ) == 1 ? 12 : date( 'n' ); // GETS INT from EDD	$previous_month   = date( 'n' ) == 1 ? 12 : date( 'n' ) - 1; // GETS INT from EDD	$previous_year    = $previous_month == 12 ? date( 'Y' ) - 1 : date( 'Y' ); // Gets INT year val	$start_current = date("Y-m-01"); // start of current month	$end_current = date("Y-m-t",strtotime('last day of this month')); // end of current month	$prev_month = strtotime('previous month');	$previous_month_start = date("Y-m-01", $prev_month);	$previous_month_end = date("Y-m-t", $prev_month);	$this_month = lead_count_by_time('wp-lead', $start_current, $end_current);	$last_month = lead_count_by_time('wp-lead', $previous_month_start, $previous_month_end);	$all_time_leads = $count_posts->publish;	$all_lead_text = ($all_time_leads == 1) ? "Lead" : "Leads";	$leads_today = get_lead_count_from_today('wp-lead');	$leads_today_text = ($leads_today == 1) ? "Lead" : "Leads";	$month_comparasion = $this_month - $last_month;	if ($month_comparasion < 0)	{		$month_class = 'negative-leads';		$sign = "";		$sign_text = "decrease";	} elseif($month_comparasion === 0) {		$month_class= 'no-change';		$sign = "";		$sign_text = "No Change ";	} else {		$month_class = 'positive-leads';		$sign = "+";		$sign_text = "increase";	}	echo '<div id="lead-before-dashboard">';	do_action('wp_lead_before_dashboard');	echo "</div>";	$clean_dates = date("m", strtotime("first day of previous month") );	$clean_date_two = date("m");	 ?>	<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/wp-content/plugins/lead-dashboard-widgets/js/flot/excanvas.min.js"></script><![endif]-->	<div class="wp_leads_dashboard_widget">	   <script>	var data1 = [ <?php echo get_lead_graph_data('wp-lead', $clean_date_two, 'this-month'); ?> ];	var data2 = [ <?php echo get_lead_graph_data('wp-lead', $clean_dates, 'last-month'); ?> ];	// [(DATE), lead_month_total, how_many_per_day]	/*var data1 = [		[gd(2013, 7, 2), 0, "Test"], [gd(2013, 7, 3), 2, "Test"], [gd(2013, 7, 4), 4,"Test"],		[gd(2013, 7, 7), 6, "Test"]	]; */	//Buy	/*var data2 = [		[gd(2013, 7, 2), 0, 20], [gd(2013, 7, 3), 3, 4], [gd(2013, 7, 4), 7, 12],		[gd(2013, 7, 7), 10, 14]	];	*/	   </script>		<div id="flot-placeholder" style='width: 100%; height: 250px; margin: 10px auto 0px; padding: 0px; position: relative; margin-bottom:10px;'></div>	   <div id="wp-leads-stat-boxes">		<div class='wp-leads-today'>			<a class="data-block widget-block" alt='Click to View Todays Leads' href="<?php echo $url . "/wp-admin/edit.php?post_type=wp-lead&current_date";?>">				<section>					<span class=""><?php echo $leads_today; ?><span class='stat-label'><?php echo $leads_today_text;?></span></span>					<strong><?php _e('Today' , 'leads'); ?></strong>				</section>			</a>		</div>		<div class='wp-leads-this-month'>			<a class="data-block widget-block" alt='Click to View This Months Leads' href="<?php echo $url . "/wp-admin/edit.php?post_type=wp-lead&current_month";?>">				<section>					<span class=""><?php echo $this_month; ?><span class='stat-label'><?php echo $all_lead_text;?></span></span>					<strong><?php _e('This Month' , 'leads'); ?></strong>					<small class='<?php echo $month_class; ?>'><?php echo "<span>" . $sign . $month_comparasion . "</span> " . $sign_text;?> <?php _e('Since Last Month' , 'leads'); ?></small>				</section>			</a>		</div>		 <div class='wp-leads-all-time'>			<a class="data-block widget-block" title='Click to View All Leads' href="<?php echo $url . "/wp-admin/edit.php?post_type=wp-lead";?>">				<section>					<span class=""><?php echo $all_time_leads;?><span class='stat-label'><?php _e('Leads' , 'leads'); ?></span></span>					<strong><?php _e('All Time' , 'leads'); ?></strong>				</section>			</a>		</div>	</div>	   <!-- <div class='wp-leads-last-month'>		last month: <?php echo $last_month; ?>		<?php echo $this_month - $last_month;  ?>		</div>   -->		<div id='leads-list'>		<?php  $r = new WP_Query( apply_filters( 'widget_posts_args', array(			'posts_per_page' => 20,			'post_type' => 'wp-lead',			'post_status' => 'publish') ) );		if ($r->have_posts()) : ?>		<h4 class='marketing-widget-header'>Latest Leads<span class="toggle-lead-list">-</span></h4>		<ul id='lead-ul'>		<?php while ( $r->have_posts() ) : $r->the_post(); ?>			<li><?php $id = get_the_ID();			$first_name = get_post_meta( $id , 'wpleads_first_name',true );			$last_name = get_post_meta( $id , 'wpleads_last_name', true );			$name = $first_name . " " . $last_name;			if ($name === " ") {				$name = get_the_title( $id );			}			?>			   <?php edit_post_link($name);?> on <?php the_time('F jS, Y'); ?> (<?php the_title();?>)			</li>		<?php endwhile; ?>		</ul>		<?php endif; ?>		</div>	</div>	<?php}
<?php
/*
Template Name: RACC Calendar 
*/
/*
Wren Miles for Regional Arts and Culture Council 2024
adapted somewhat from C2's previous calendar code, but mainly just the curl pieces
*/
function get_content($URL){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $URL);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}


get_header(); ?>
<?php wp_enqueue_style( 'style', get_stylesheet_uri() ); ?>
<?php wp_enqueue_style( 'calendar', get_stylesheet_uri() ); ?>
<?php get_template_part( 'parts/featured-image' ); ?>

<div class="row">
	<?php get_template_part('parts/calendarclass'); ?>
	<?php get_template_part( 'parts/check-if-sidebar-exist' ); ?>
	<?php do_action( 'foundationpress_before_content' ); ?>
	<?php while ( have_posts() ) : the_post(); ?>
	<article <?php post_class() ?> id="post-<?php the_ID(); ?>">
		<header>
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</header>
		<?php do_action( 'foundationpress_page_before_entry_content' ); ?>
		<div class="entry-content">
			<?php the_content(); ?> 

		
		<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
		<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
		<link href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css">
    	<script type="text/javascript">
			$(function() {
				$('.date-picker').datepicker( {
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true,
				dateFormat: 'MM yy',
				onClose: function(dateText, inst) { 
					$(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
				}
				});
			});
	</script>
	<div class="content home">
		<form id="ccsearch" method="POST" action="">
			<div>
				<span id="CCStartDate">Month: <input name="startDate" id="startDate" class="date-picker"/></span>
				<input id="CCSubmit" type="submit" name="submit" value="Search for Events"/>
			</div>
		</form>
		<?php //checks if user selected a month, otherwise uses current month, reformats in both scenarios (WM)  
			date_default_timezone_set('America/Los_Angeles');
			if($_REQUEST['startDate'] != ""){
				$datepicked = date("Y-m-d", strtotime(str_replace('/', '-',$_REQUEST['startDate'])));
				$calendar = new Calendar($datepicked);
				$datepicked = date('Y-m-01', strtotime($datepicked));
				$datepickedend = date('Y-m-t', strtotime($datepicked));
			}else{
				$calendar = new Calendar();
				$today = date("Y-m-d");
				$datepicked = date('Y-m-01', strtotime($today));
				$datepickedend = date('Y-m-t', strtotime($today));
			}
			//Some default values. in case you're wondering what's up with the today var, it's for the date display stuff in the h3 below.
			$ccbaseurl = "https://event-admin.travelportland.com/api/2/events";
			$localkeyurl = "https://event-admin.travelportland.com/api/2/events/search";
			$types = "&type=41162+32405+33036+33037+30353987346528+33047+33049+33048+33054+33042+33040+33043";
			/*32405 - music, 33036 - Art & Design, 33037 - comedy, 30353987346528 - free, 33047 - lgbt+, 33049 - Cultural Communities, 
			33048 - Readings & Talks, 33054 - Performing Arts, 33042 - Festival, 33040 - Exhibition, 33043 - film
			In order to get results back, you need to search a date range. 
			This will be the whole month, so use the current day to get those dates.
			*/	
			$concurl = $ccbaseurl . "?start=" . $datepicked . "&end=" . $datepickedend . $types . "&pp=99";
			$today = strtotime(date("m/d/y"));
			//grabs JSON and dumps it into an array	as obj
			$ccapi = get_content($concurl);
			$obj = json_decode($ccapi, TRUE);
			//$noRepeats = array();
			foreach($obj['events'] as $events) {
				/*below used for debugging (WM)
				foreach($events['event']['event_instances']['0'] as $instance){
					echo $events['event']['title'];
					//echo date("F jS", strtotime($instance['start']));
					echo $instance ? 'true' : 'false';
					echo nl2br ("\n");
				}*/

				/*checks if we've already displayed the event. Not currently applicable (WM)
				$donotdisplay = 0;
				if(!(in_array($events['event']['id'], $noRepeats))){
					$noRepeats[] = strval($events['event']['id']);
				}else{
					$donotdisplay = 1;
				}

				if ($donotdisplay == "1") {
					continue;
				}
				*/
				$color = ' ';
				if(isset($events['event']['filters']['event_types'])){
					$eventtype = $events['event']['filters']['event_types'][0]['id'];
					if($eventtype == 32405){ //32405 - music
						$color = 'olive';
					}else if($eventtype == 33037){ //33037 - comedy
						$color = 'green';
					}else if($eventtype == 33048){ //33048 - Readings & Talks
						$color = 'teal';
					}else if($eventtype == 33054){ //33054 - Performing Arts
						$color = 'blue';
					}else if($eventtype == 33047 || $eventtype == 33049){ // 33047 - lgbt+, 33049 - Cultural Communities
						$color = 'purple';
					}
				}
				$eventtimestart = date("g:i a", strtotime($events['event']['event_instances']['0']['event_instance']['start']));
				$eventdatestart = date("F jS", strtotime($events['event']['event_instances']['0']['event_instance']['start']));
				$eventdatefirst = date("F jS", strtotime($events['event']['first_date']));
				$eventdateend = date("F jS", strtotime($events['event']['last_date']));
				$eventlength = abs(strtotime($eventdateend) - strtotime($eventdatefirst));

				if ($eventtimestart == "12:00 am") {
					$eventtimestart = "All Day";
				}
				$eventIcon = '<a href="https://www.travelportland.com/event/'.@$events['event']['id'].'" style="border:none;" target="_blank">' . '<img src="'.$events['event']['photo_url'].'" style="height: 100px; width: 100px; padding:2px; margin: auto; display: block;"/></a>';
				$detailstrunc = preg_replace('/\s+?(\S+)?$/','', mb_substr($events['event']['description_text'], 0, 300,'UTF-8'));
				$detailsnocr = str_replace("\r\n", "\n", $detailstrunc);
				$ccsite = '<a href="https://www.travelportland.com/event/'.@$events['event']['id'].'">...more info</a>';
				$officialwebsite = '<a href="'.$events['event']['url'].'">more info...</a>';

				$title = explode(':', $events['event']['title']);

				$calendar->add_event($title[0], $detailsnocr, $eventdatestart, $eventtimestart, $eventIcon, $officialwebsite, $events['event']['location_name'], 1, $color);
			};
		?> 
		<?php
			echo $calendar;
		?>
	</div>
		</div>
		<footer>
			<?php wp_link_pages( array('before' => '<nav id="page-nav"><p>' . __( 'Pages:', 'foundationpress' ), 'after' => '</p></nav>' ) ); ?>
			<p><?php the_tags(); ?></p>
		</footer>
		<?php do_action( 'foundationpress_page_before_comments' ); ?>
		<?php comments_template(); ?>
		<?php do_action( 'foundationpress_page_after_comments' ); ?>
	</article>
	<?php endwhile;?>

	<?php do_action( 'foundationpress_after_content' ); ?>

	</div>
	<?php get_sidebar(); ?>
</div>


<?php get_footer(); ?>

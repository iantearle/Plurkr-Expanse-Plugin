<?php
/*
Plugin Name: Plurkr
Plugin URI: http://blog.iantearle.com/
Description: Provided by your friends at Media By to give you control over your site.
Author: Ian Tearle
Version: 1.0
Author URI: http://www.iantearle.com/
*/

/*
-------------------------------------------------
Disclaimer
=================================================
*/
/* 
Plurkr is a PHP function that parses your Plurk RSS feed so it can be displayed on any website. No need to use the styled Plurk plugin!

Usage: 	Upload all files into the same directory.
		Just include this file in your page and then call: <?php if(function_exists('put_Plurkr')){put_Plurkr();} ?>
		You will need to configure the first section of the file below to add your credentials. 
		To get your Plurk RSS feed URL, see instructions at http://blog.iantearle.com/
Credit: Goes to Blake Brannon http://www.blakebrannon.com for the Facebook script in which this was based on.
*/
if(!class_exists('SimpleXMLObject')){include_once('simplexml.class.php');}
ozone_action('preferences_menu','plurkr_config_menu');

function plurkr_config_menu()
{

	?>
	<!-- /*   Plurkr Menu   //===============================*/ -->
	<h3 class="stretchToggle" title="plurkr"><a href="#plurkr"><span>Plurkr</span></a></h3>
    <div class="stretch" id="plurkr">
    <label for="plurkr_rss_uri">Plurk Feed</label>
    <input type="text" name="plurkr_rss_uri" id="plurkr_rss_uri" value="<?php echo getOption('plurkr_rss_uri'); ?>">
	<?php tooltip('Plurk Feed', 'Enter your Plurk RSS feed here.');
	?>
	<label for="plurk_name">Plurk Username</label>
    <input type="text" name="plurk_name" id="plurk_name" value="<?php echo getOption('plurk_name'); ?>">
	<?php tooltip('Plurk Userame', 'Enter your Plurk username.');
	?>
	<label for="plurk_display_name">Plurk Display Name</label>
    <input type="text" name="plurk_display_name" id="plurk_display_name" value="<?php echo getOption('plurk_display_name'); ?>">
	<?php tooltip('Plurk Userame', 'Enter your Plurk username.');
	?>
	<label for="numofupdates">Plurk Updates</label>
    <input type="text" name="numofupdates" id="numofupdates" value="<?php echo getOption('numofupdates'); ?>">
	<?php tooltip('Plurk Updates', 'Enter the number of updates you would like to show.');
	?>
	<label for="rmnameis">Remove Name</label>
	<input type="hidden" value="false" name="rmnameis" />
	<input type="checkbox" name="rmnameis" value="true" <?php echo getOption('rmnameis') == 'true' ? 'checked="checked"' : ''; ?> class="cBox" id="rmnameis" />
	<?php tooltip('Remove Name', 'If checked, this option will remove the "Name" before your updates.'); 
	?>
	<label for="showtimestamp">Show Timestamp</label>
	<input type="hidden" value="false" name="showtimestamp" />
	<input type="checkbox" name="showtimestamp" value="true" <?php echo getOption('showtimestamp') == 'true' ? 'checked="checked"' : ''; ?> class="cBox" id="showtimestamp" />
	<?php tooltip('Show Timestamp', 'If checked, this option will show the time of the status update.'); 
	?>
	<label for="showfeedback">Show Feedback</label>
	<input type="hidden" value="false" name="showfeedback" />
	<input type="checkbox" name="showfeedback" value="true" <?php echo getOption('showfeedback') == 'true' ? 'checked="checked"' : ''; ?> class="cBox" id="showfeedback" />
	<?php tooltip('Show Feedback', 'If checked, this option will show the feeback link at the end.'); 
	?>
	<label for="profileLink">Profile Link</label>
	<input type="hidden" value="false" name="profileLink" />
	<input type="checkbox" name="profileLink" value="true" <?php echo getOption('profileLink') == 'true' ? 'checked="checked"' : ''; ?> class="cBox" id="profileLink" />
	<?php tooltip('Profile Link', 'If checked, this option will add a direct link to your profile.'); 
	?>
	
	</div>
	<?php
}

/*
-------------------------------------------------
Configure
=================================================
*/
function put_Plurkr() {
if(getOption('plurkr_rss_uri') == ''){
	$uri = 'http://www.plurk.com/iantearle.xml';
}else{
	$uri = getOption('plurkr_rss_uri');
}
	// Please edit the variables below. All 11 variables should be specified.
	$url = $uri;		// Your Facebook RSS Feed
	$plurk_name = getOption('plurk_name');		// Plurk username (that matches the above feed).
	$display_name = getOption('plurk_display_name');		// Name you would like to display as.
	$numofupdates = getOption('numofupdates');		// Number of updates you want to display, (1, 2, 3, 4,...).	
	$rmnameis = getOption('rmnameis');		// Boolean that dictates if you would like to remove the "Name" before your updates (true or false).
	$showtimestamp = getOption('showtimestamp');		// Show the time of the status update (true or false).
	$showfeedback = getOption('showfeedback');		// Show the feeback link at the end (true or false).
	$profileLink = getOption('profileLink');		// Show a direct link to your profile. (true or false).
	$MessagePrefix = '<p class="plurkMessage">';		// HTML Tags to go before your Plurk message. ex. <p>, <ul><li>,...
	$MessageSuffix = '</p>';	// HTML Tags to go after your Plurk message. ex. </p>, </li></ul>,...
	$DatePrefix = '<em class="plurkDate">';		// HTML Tags to go before your Plurk Posted Date. ex. <em>, <span>,...
	$DateSuffix = '</em>';		// HTML Tags to go after your Plurk Posted Date. ex. </em>, </span>,...	
// ********************************************************************************************************************************


/*
-------------------------------------------------
DO NOT EDIT BELOW
=================================================
*/
	$echo = '';
	// Variable needed for the algorithm.
	$firstpass = true;	// Identifies the first loop.
	// Fix numofupdates because it is indexed from zero
	$numofupdates--;
	
	
	// Setup curl
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, 'CULROPT_HEADER', 0);
	
	// Spoof Firefox
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1.1) Gecko/20061223 Firefox/2.0.0.1");
	
	//Begin output buffering
	ob_start();
	curl_exec ($ch);
	curl_close ($ch);
	
	//Save buffer to string
	$xmlstr = ob_get_contents();
	ob_end_clean();
	
	//Convert string to xml object
	if($xmlstr != ''){
		$xml = new SimpleXMLElement($xmlstr);
	}else{
		$echo.= '<p class="plurkError">not loaded</p>';
		return $echo;
		exit;
	}
	
	
	// Start the loop
	for ($count = 0; $count <= $numofupdates; $count++) { 
	
		$message = $xml->entry[$count]->content;
		
		//mixed search, mixed replace, mixed subject [, int &count]
		
		// If you want to place the timestamp on the update.
		if ($showtimestamp == 'true' && $message != '') {
			
			$timestamp = strtotime(preg_replace('/T/', ' ', $xml->entry[$count]->published)); // Get the timestamp for the post update.
			
			// Calculate how long it's been since the status was updated (relative).
			$currenttime = time();
			$delta = $currenttime - $timestamp;
			
			// Display how long it's been since the last update.
			$timestampdisplay = $MessageSuffix . $DatePrefix . "Plurked ";
			
			// Show days if it's been more than a day.
			if(floor($delta / 84600) > 0) {
				$timestampdisplay .= floor($delta / 84600);
				if(floor($delta / 84600) == 1) { $timestampdisplay .= ' day, '; } else { $timestampdisplay .= ' days, '; }
				$delta -= 84600 * floor($delta / 84600);
			}
			
			// Show hours if it's been more than an hour.
			if(floor($delta / 3600) > 0) {				
				$timestampdisplay .= floor($delta / 3600);
				if(floor($delta / 3600) == 1) { $timestampdisplay .= ' hour, '; } else { $timestampdisplay .= ' hours, '; }
				$delta -= 3600 * floor($delta / 3600);
			}
			
			// Show minutes if it's been more than a minute.
			if(floor($delta / 60) > 0) {	
				$timestampdisplay .= floor($delta / 60);
				if(floor($delta / 60) == 1) { $timestampdisplay .= ' minute ago'; } else { $timestampdisplay .= ' minutes ago'; }
				$delta -= 60 * floor($delta / 60);				
				}else{			
				$timestampdisplay .= $delta;
				if($delta == 1) { $timestampdisplay .= ' second ago'; } else { $timestampdisplay .= ' seconds ago'; }		
				}
				
			
			$message .= $timestampdisplay;
		
		}// End of Timestamp
	
		// If no updates are available.
		if ($message == '' && $firstpass) {
			$message = $plurk_name . ' has no recent status updates.';
			$echo.= $prefix . $message . $suffix;
			break; 
		}
		
		// Remove the default Plurk Username, you can cusomise now!
		$message = preg_replace('/'.$plurk_name.' /', '', $message);

		// Find the Plurk words ready to be Spanned! 
		if(preg_match('/^([^ ]+)(.*)/', $message, $matchArray))
		{
			$classArray = array(
				'says' => 'plurkSays',
				'is' => 'plurkIs',
				'thinks' => 'plurkThinks',
				'feels' => 'plurkFeels',
				'wonders' => 'plurkWonders',
				'was' => 'plurkWas',
				'has' => 'plurkHas',
				'asks' => 'plurkAsks',
				'hopes' => 'plurkHopes',
				'will' => 'plurkWill',
				'needs' => 'plurkNeeds',
				'wishes' => 'plurkWishes',
				'wants' => 'plurkWants',
				'hates' => 'plurkHates',
				'gives' => 'plurkGives',
				'shares' => 'plurkShares',
				'likes' => 'plurkLikes',
				'loves' => 'plurkLoves'			
				);
	
			$matchedWord = $matchArray[1];
			// wrap the matched word with the appropriate class, you can now set the styles in the style sheet.
			if(isset($classArray[$matchedWord]))
				$message = ' <span class="' . $classArray[$matchedWord] . '">' . $matchedWord . '</span>' . $matchArray[2];
		}
		
		// Set the value of $firstpass to know that at least one update has been posted
		$firstpass = false;
		
		// Echo out the status update based upon your name preference
		if($rmnameis == 'true')
		{
			$echo= $MessagePrefix . $message . $DateSuffix;
		}else
		{
			if($profileLink == 'true')
			{
				$echo= $MessagePrefix . '<a href="http://www.plurk.com/user/' . $plurk_name . '">' . $display_name . '</a>' . $message . $DateSuffix;
			}else{
				$echo= $MessagePrefix . $display_name . $message . $DateSuffix;
			}
		}
	}// end of Loop
	
	// Add feedback link.
	if ($showfeedback == 'true'){
		$echo.= '<p class="plurkFeedback">Media By <a href="http&#58;//www.iantearle.com/">Plurkr</a></p>';
	}else{
		$echo.= '<!-- Media By <a href="http&#58;//www.iantearle.com/">Plurkr</a> -->';
	}
	return $echo;
}
include_once(PLUGINS.'/dream-variables.php');
$putPlurkr = put_Plurkr();
$putPlurkr= preg_replace('/:/', '&#58;', $putPlurkr);
if(function_exists('add_variable')){
	add_variable('plurk:'.$putPlurkr, 'header');
}
?>
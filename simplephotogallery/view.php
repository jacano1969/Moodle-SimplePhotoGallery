<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of simplephotogallery
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 * * 
 * @package    mod 
 * @subpackage simplephotogallery 
 * @copyright  2012 Liam Mann | LiamMann.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace simplephotogallery with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

     $PAGE->requires->js('/mod/simplephotogallery/js/jquery-1.4.3.min.js', true);
     $PAGE->requires->js('/mod/simplephotogallery/js/jquery.fancybox-1.3.4.pack.js', true);
     $PAGE->requires->js('/mod/simplephotogallery/js/jquery.easing-1.3.pack.js', true);



$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // simplephotogallery instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('simplephotogallery', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $simplephotogallery  = $DB->get_record('simplephotogallery', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $simplephotogallery  = $DB->get_record('simplephotogallery', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $simplephotogallery->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('simplephotogallery', $simplephotogallery->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'simplephotogallery', 'view', "view.php?id={$cm->id}", $simplephotogallery->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/simplephotogallery/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($simplephotogallery->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('simplephotogallery-'.$somevar);

// Output starts here
echo $OUTPUT->header();



// Replace the following lines with you own code
echo $OUTPUT->heading($simplephotogallery->name);

$flickrseturl = $simplephotogallery->flickrseturl;
$flickrsetarray = array_filter ( explode ( "/", $flickrseturl ) );

# uses libcurl to return the response body of a GET request on $url
function getResource($url){
  $chandle = curl_init();
  curl_setopt($chandle, CURLOPT_URL, $url);
  curl_setopt($chandle, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($chandle);
  curl_close($chandle);

  return $result;
}


#insert your own Flickr API KEY here

  $api_key = "380d3b75a7fa4e38ae8e73d5932ddde0";
  $photoset = $flickrsetarray['6'];
  $url = "http://api.flickr.com/services/rest/?method=flickr.photosets.getPhotos&api_key={$api_key}&photoset_id={$photoset}";
  $feed = getResource($url);
  $xml = simplexml_load_string($feed);

# http://www.flickr.com/services/api/misc.urls.html
# http://farm{farm-id}.static.flickr.com/{server-id}/{id}_{secret}.jpg
echo "<ul class='simplephotogallery simplephotogallery-{$simplephotogallery->id}'>";
foreach ($xml->photoset->photo as $photo) {
	$title = $photo['title'];
	$farmid = $photo['farm'];
	$serverid = $photo['server'];
	$id = $photo['id'];
	$secret = $photo['secret'];
	$owner = $photo['owner'];
	
	$thumb_url = "http://farm{$farmid}.static.flickr.com/{$serverid}/{$id}_{$secret}_z.jpg";
	$full_url = "http://farm{$farmid}.static.flickr.com/{$serverid}/{$id}_{$secret}_z.jpg";
	$page_url = "http://www.flickr.com/photos/{$owner}/{$id}";
	$image_html= "<a rel='photos' href='{$full_url}'><img alt='{$title}' src='{$thumb_url}'/></a>";
	print "<li class='pic'>$image_html</li>";


}
echo "</ul>";
echo $OUTPUT->footer();

?>

<script>
$(document).ready(function() {
	$(".simplephotogallery a[rel=photos]").fancybox({
		'overlayShow'	: true,
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'titlePosition' 	: 'over',
		'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
			return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
		}
	});
});
</script>
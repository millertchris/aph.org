<?php
/**
 *
 * Stores all email sent in a log file.
 *
 * @param $args
 *
 * @return array
 */

function filter_local_mail( $args ) {
	// Modify the options here
	$custom_mail = array(
		'to'          => $args['to'],
		'subject'     => $args['subject'],
		'message'     => $args['message'],
		'headers'     => $args['headers'],
		'attachments' => $args['attachments'],
	);

	$html = '<!doctype html>';
	$html .= '<html lang="en">';
	$html .= '<head>';
	$html .= '</head>';
	$html .= '<body>';
	$html .= '<p><b>Subject:</b> '.$custom_mail['subject'].'</p>';
	$html .= '<p><b>To:</b> '.$custom_mail['to'].'</p>';
	$html .= '<p><b>Message:</b> '.$custom_mail['message'].'</p>';
	$html .= '<p><b>Headers:</b> '. json_encode($custom_mail['headers'], JSON_PRETTY_PRINT) .'</p>';
	$html .= '</body>';
	$html .= '</html>';
	file_put_contents(APH_LOGDIR . "/email_".mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $custom_mail['subject'])."_".date('m-d-Y_h-i-s').".html", $html);

	// Return the value to the original function to send the email
	return $custom_mail;
}
add_filter( 'wp_mail', 'filter_local_mail' );

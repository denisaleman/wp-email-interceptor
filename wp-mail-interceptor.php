<?php
/**
 * Plugin Name: WP Mail Interceptor
 * Version: 0.1
 * Description: Logging outgoing emails
 * Author: Denis Alemán
 * Text Domain: wp-mail-interceptor
 * Domain Path: /languages/
 * License: GPL v3
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

class WpMailInterceptor {

	private static function getFilePath ( $folder, $file ) {
        $uploads  = wp_upload_dir( null, false );

        $logs_dir = $uploads['basedir'] . '/' . $folder;

        if ( ! is_dir( $logs_dir ) ) {
            mkdir( $logs_dir, 0755, true );
        }

        $filepath = $logs_dir . '/' . $file;

        return $filepath;
    }

	public static function setup() {

		add_filter('wp_mail', function ( $atts ) {

			$message  = "\r\n\r\n";
			$message .= date("Y-m-d H:i:s");

			foreach ($atts as $key => $value) {
				$message .= "\r\n" . $key . ' : ' . $value; 
			}

			self::append('debug_wp_mail', 'wp_mail.log', $message);
		});

		add_filter('wp_mail_from', function ( $from ) {
			$message = "\r\n".'email_from : ' . $from;

			self::append('debug_wp_mail', 'wp_mail.log', $message);
		});

		add_filter('wp_mail_from_name', function ( $from ) {
			$message = "\r\n".'email_from_name : ' . $from;

			self::append('debug_wp_mail', 'wp_mail.log', $message);
		});

		add_filter('wp_mail_content_type', function ( $content_type ) {
			$message = "\r\n".'content_type : ' . $content_type;

			self::append('debug_wp_mail', 'wp_mail.log', $message);
		});

		add_filter('wp_mail_charset', function ( $charset ) {
			$message = "\r\n".'charset : ' . $charset;

			self::append('debug_wp_mail', 'wp_mail.log', $message);
		});

		add_action('wp_mail_failed', function ( $error ) {
			$message = "\r\n".'email failed! : ';
			$message .= "\r\n".'WP_Error message : ' . $error->get_error_message();
			$message .= "\r\n".'WP_Error code    : ' . $error->get_error_code();

			self::append('debug_wp_mail', 'wp_mail.log', $message);
		});

  	}

  	public static function append( $folder, $file, string $text = NULL ) {
        $filepath = self::getFilePath( $folder, $file );
		
        $file = fopen( $filepath, 'a+' );

        @fwrite($file, $text);
    }

    public static function prepend( $folder, $file, string $text = NULL ) {
        $filepath = self::getFilePath( $folder, $file );

        $file = fopen( $filepath, "r+" );
        $len = strlen($text);
        $final_len = filesize($file) + $len;
        $cache_old = fread($file, $len);
        rewind($file);
        $i = 1;
        while (ftell($file) < $final_len) {
          fwrite($file, $text);
          $text = $cache_old;
          $cache_old = fread($file, $len);
          fseek($file, $i * $len);
          $i++;
        }
    }

    public static function read( $folder, $file ) {

        $uploads  = wp_upload_dir( null, false );

        $logs_dir = $uploads['basedir'] . '/' . $folder;

        if ( !is_dir( $logs_dir ) ) {
            return false;
        }
        
        $filepath = $logs_dir . '/' . $file;

        return @file_get_contents( $filepath );

    }

}

WpMailInterceptor::setup();
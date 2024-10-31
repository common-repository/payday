<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';

class Payday_Log_Viewer_Page
{
	use Payday_Singleton_Trait;

	public function handle_download()
	{
		if (isset($_GET['download_log'])) {
			if (file_exists(Payday_Logger::$LOG_FILE)) {
				$log = file_get_contents(Payday_Logger::$LOG_FILE);

				$log = mb_ereg_replace('^[\p{Z}\s]+', '', $log);

				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="payday-log.txt"');
				echo $log;
				exit;
			} else {
				if (!Payday_Logger::create_empty_log_file()) {
					wp_die('Log file not found, unable to download.');
				}
				$log = file_get_contents(Payday_Logger::$LOG_FILE);

				$log = mb_ereg_replace('^[\p{Z}\s]+', '', $log);

				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="payday-log.txt"');
				echo $log;
			}
		}
	}

	public function display_page_content()
	{
		if (isset($_POST['clear_log'])) {
			if (file_exists(Payday_Logger::$LOG_FILE)) {
				file_put_contents(Payday_Logger::$LOG_FILE, '');
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Log cleared.', 'payday') . '</p></div>';
			} else {
				if (!Payday_Logger::create_empty_log_file()) {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Log file not found, unable to clear.', 'payday') . '</p></div>';
				}
				file_put_contents(Payday_Logger::$LOG_FILE, '');
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Log cleared.', 'payday') . '</p></div>';
				// create the log file
			}
		}


		$log = '';
		if (!file_exists(Payday_Logger::$LOG_FILE)) {
			if (!Payday_Logger::create_empty_log_file()) {
				$log = esc_html__('No log file found.', 'payday');
			} else {
				$log = file_get_contents(Payday_Logger::$LOG_FILE);
			}
		} else {
			$log = file_get_contents(Payday_Logger::$LOG_FILE);
		}

		echo '<div class="wrap">';
		echo '<h2>' . esc_html__('Log Viewer', 'payday') . '</h2>';
		echo '<textarea id="logTextArea" readonly style="width: 100%; height: 500px; padding-bottom: 100px; overflow: scroll; overflow-y: scroll; overflow-x: scroll; overflow:-moz-scrollbars-vertical;" wrap="off">' . esc_textarea($log) . '</textarea>';
		echo '<div style="display: flex; align-items: center;">';
		echo '<form method="POST" style="margin-right: 10px;">';
		echo '<input type="submit" name="clear_log" value="' .  esc_html__('Clear Log', 'payday') . '" />';
		echo '</form>';
		echo '<form method="GET" style="margin-right: 10px;">';
		echo '<input type="hidden" name="download_log" value="1" />';
		echo '<input type="submit" value="' . esc_html__('Download Log', 'payday') . '" />';
		echo '</form>';
		echo '</div>';
		echo '</div>';

		echo '<script>
            var logTextArea = document.getElementById("logTextArea");
            logTextArea.scrollTop = logTextArea.scrollHeight;
        </script>';
	}
}

add_action('init', [Payday_Log_Viewer_Page::instance(), 'handle_download']);

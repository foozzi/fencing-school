<?

include_once( dirname( dirname( __FILE__ ) ).'/includes/application.php' );

Application::run('config,locale,route');

Cron::run();

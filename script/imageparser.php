<?

include_once( dirname( dirname( __FILE__ ) ).'/includes/application.php' );

Application::run('config');


//Crawler_VseKolesa::runWheelImages();
Crawler_VseKolesa::runTyreImages();
//Crawler_AutoDiski::runWheelImages();
Crawler_Autodiski::runTyreImages();
Console::writeln();

exit;




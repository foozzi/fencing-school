<?

$pwd = '11L02POd';

echo '<pre>';

$out = array();

svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_USERNAME, 'r15');
svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_PASSWORD, $pwd);

svn_cleanup('.');

$rev = svn_update('.');

echo "Current revision is $rev\n";

$out = array();
$cmd = 'php script/install.php';
exec( $cmd, $out );
echo implode("\n", $out),"\n";


echo '</pre>';
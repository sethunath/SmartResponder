<?php
// web/index.php
require_once __DIR__.'/vendor/autoload.php';

class SmartResponderExtension extends Twig_Extension
{
    public function getName()
    {
        return 'smartResponder';
    }

    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('sr', function($text){
            	Unirest::verifyPeer(false);
            	$response = Unirest::get("https://sethunath-smart-responder-v1.p.mashape.com/translations/".$text."/1",
				  array(
				    "X-Mashape-Key" => "dQ7JvsYKMlmshEnSbnCKXqq6PhBrp1RfrrTjsnHbzD57IC0lhY"
				  )
				);
				return $response->body;
            }),
        );
    }

}

$app = new Silex\Application();

// Enable debug mode
$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options' => array (
        'app' => array(
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => 'lap',
            'user'      => 'root',
            'password'  => '',
            'charset'   => 'utf8',
        )
    ),
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app["twig"] = $app->share($app->extend("twig", function (\Twig_Environment $twig, Silex\Application $app) {
    $twig->addExtension(new SmartResponderExtension($app));

    return $twig;
}));

$app->get('/admin/keys', function () use ($app) {
	$languages = $app['db']->fetchAll('SELECT * FROM languages');
	$lans = array();
	foreach ($languages as $language) {
		$lans[] = $language['language'];
	}
    return $app['twig']->render('admin/keys.twig', array(
    	'languages'=> json_encode($lans)
    ));
});
$app->get('/api/translations/{key}/{locale}', function ($key,$locale) use ($app) {
	if($locale){
		$sql = '
			SELECT * FROM translations t
			INNER JOIN `keys` k ON
			t.key_id = k.id
			WHERE `key` = ? AND language_id = ? ORDER BY RAND()
			LIMIT 1;
		';
		$trans = $app['db']->fetchAll($sql,array($key,$locale));
		//echo print_r($trans,true);
		if(isset($trans[0])){
			return $trans[0]['translation'];
		}
		else{
			return '';
		}
	}else{
		$sql = '
			SELECT * FROM translations t
			INNER JOIN `keys` k ON
			t.key_id = k.id
			WHERE `key` = ?  ORDER BY RAND()
			LIMIT 1;
		';
		$trans = $app['db']->fetchAll($sql,array($key));
		return print_r($trans,true);	
	}
	
})->value('locale', '');
$app->post('/admin/saveData', function () use ($app) {
	$data =  json_decode(file_get_contents("php://input"));
	foreach ($data as $language => $texts) {
		//find the id of the language
		$lanId = $app['db']->fetchAll('SELECT * FROM languages where language = ?',array($language));
		// save the text
		$lanId[0]['id'];
		foreach ($texts as $text) {
			
		}
	}
});
$app->get('/page', function () use ($app) {
	return $app['twig']->render('page.twig', array(
    	
    ));
});
$app->run();



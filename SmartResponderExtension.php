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
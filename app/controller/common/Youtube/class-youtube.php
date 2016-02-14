<?php

//TODO?: Pasar errores a constantes localizables y asignar códigos de error.

/**
 * 
 * Enter description here ...
 * @author miguel.lopez
 *
 */
class Youtube
{
	protected $deleted = false;
	
	protected $token; 
	protected $id;
	protected $title;
	protected $content;
	protected $author;
	protected $published;
	protected $updated;
	protected $duration;
	protected $rating;
	protected $minRate;
	protected $maxRate;
	protected $ratersCount;
	protected $viewCount;
	protected $favouriteCount;
	protected $hasCC;
	protected $keywords;
	protected $error = "";
	
	/**
	 * 
	 * Crea un objeto Youtube con toda la informacion del video.
	 * @param $id
	 */
	public function Youtube($id, $auth=null)
	{
		$this->id = $id;
		$this->auth = $auth;
		$this->extractVideoInfo();
	}
	
	/**
	 * 
	 * Extrae la informacion del video a partir del XML proprocionado por el servicio gdata de Youtube.
	 * @param $id
	 */
	protected function extractVideoInfo()
	{
		//Call to gdata.youtube	
		$url = "http://gdata.youtube.com/feeds/api/videos/" . $this->id;	
		$response = $this->request($url);
		//$xml = file_get_contents(sprintf("http://gdata.youtube.com/feeds/api/videos/%s", $id));
		if (!$response) {
			$this->error = sprintf("No se ha podido obtener la información del vídeo %s.", $this->id);
			return false;
		}
		
		try
		{
			$xml = @simplexml_load_string($response);
			
			if (!$xml)
			{
				$this->error = sprintf("No se ha obtenido un XML válido en la dirección %s.", $url);
				return false;
			}

			$ns = $xml->getNamespaces(true);
			
			//Namespaces
			if (isset($ns["media"])) {
				$xml->RegisterXPathNamespace("media", $ns["media"]);
				//$ns_title = $xml->xpath("//media:title");
				//$ns_title ? (string)$ns_title[0] : '';
				$ns_description = $xml->xpath("//media:group/media:description");
				$ns_description = $ns_description ? (string)$ns_description[0] : '';
				
				if ($this->auth != null) {
					$ns_keywords = $xml->xpath("//media:group/media:keywords");
					$ns_keywords = $ns_keywords ? (string)$ns_keywords[0] : '';
					$ns_keywords = explode(",", $ns_keywords);
				} else {
					$ns_keywords = array();
				}
			} else {
				$this->deleted = true;
				return false;
			}
			
			if (isset($ns["yt"])) {
				$xml->RegisterXPathNamespace("yt", $ns["yt"]);
				$ns_duration = $xml->xpath("//yt:duration");
				$ns_duration = $ns_duration[0]->attributes();
				$ns_statistics = $xml->xpath("//yt:statistics");
				$ns_statistics = $ns_statistics ? $ns_statistics[0]->attributes() : false;
			} else {
				$this->deleted = true;
				return false;
			}
			
			if (isset($ns["gd"])) {
				$xml->RegisterXPathNamespace("gd", $ns["gd"]);
				$ns_rating = $xml->xpath("//gd:rating");
				$ns_rating = $ns_rating ? $ns_rating[0]->attributes() : false;
			} else {
				$this->deleted = true;
				return false;
			}
			
			
			//Construct the Youtube properties
			$this->title = (string)$xml->title;
			$this->content = $ns_description ? $ns_description : '';
			$this->author = (string)$xml->author->name;
			$this->published = (string)$xml->published;
			$this->updated = (string)$xml->updated;
			$this->duration = (int)$ns_duration["seconds"];
			$this->rating = $ns_rating ? (float)$ns_rating["average"] : 0;
			$this->maxRate = $ns_rating ? (int)$ns_rating["max"] : 0;
			$this->minRate = $ns_rating ? (int)$ns_rating["min"] : 0;
			$this->ratersCount = $ns_rating ? $ns_rating["numRaters"] : 0;
			$this->viewCount = $ns_statistics ? (int)$ns_statistics["viewCount"] : 0;
			$this->favouriteCount = $ns_statistics ? (int)$ns_statistics["favoriteCount"] : 0;
			$this->keywords = $ns_keywords ? $ns_keywords : array();
			$this->hasCC = false;
			
			$url = "http://www.youtube.com/get_video_info?video_id=" . $this->id;
			$response = $this->request($url);
			
			$params = array();
			foreach (explode('&', $response) as $chunk) {
				$param = explode("=", $chunk);
			
				if ($param) {
					$params[urldecode($param[0])] = urldecode($param[1]);
				}
			}
			
			$this->hasCC = isset($params["has_cc"]) && $params["has_cc"] == "True";
			
			if (isset($params["keywords"])) {
				$this->keywords = explode(",", $params["keywords"]);
			}
			
			return true;
		}
		catch (Exception $ex)
		{
			$this->error = $ex->getMessage();
			return false;
		}
		//preg_match("#<yt:duration seconds='([0-9]+)'/>#", $xml, $duracion);
		//$xml = simplexml_load_string($xml);
		//return array($xml->title, $xml->content, $duracion[1]);
		
	}
	
	protected function request($url) {
		$request = curl_init($url);
		if (!is_resource($request))
		{
			$this->error = sprintf("No se obtiene respuesta de la dirección %s.", $url);
			return false;
		}
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($request, CURLOPT_HTTPHEADER, array('X-GData-Key: key='.$this->devKey));
		if ($this->auth != null) {
			curl_setopt($request, CURLOPT_HTTPHEADER, array('Host: gdata.youtube.com',
															'Authorization: Bearer ' . $this->auth["accessToken"], 
															'GData-Version: 2.1',
															'X-GData-Key: key=' . $this->auth["developerKey"]));
		}
		$response = curl_exec($request);
		curl_close($request);

		return $response;
	}

	/**
	 * getId
	 * Obtiene el id del video
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * getTitle
	 * Obtiene el titulo del video.
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * getDescription
	 * Obtiene la descricion del video.
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * getPublished
	 * Obtiene la fecha de publicacion del video.
	 */
	public function getPublished()
	{
		return $this->published;
	}
	
	/**
	 * getUpdated
	 * Obtiene la fecha de la �ltima actualizaci�n del video.
	 */
	public function getUpdated()
	{
		return $this->updated;
	}
	
	/**
	 * getDuration
	 * Obtiene la duracion en segundos
	 */
	public function getDuration($format=false)
	{
		if ($format === false) {
			return $this->duration;
		} else {
			return date($format, $this->duration);
		}
	}
	
	/**
	 * getRating
	 * Obtiene la valoracion media del video
	 */
	public function getRating()
	{
		return $this->rating;
	}
	
	/**
	 * getMinRate
	 * Obtiene la valoracion minima del video (1).
	 */
	public function getMinRate()
	{
		return $this->minRate;
	}
	
	/**
	 * getMaxRate
	 * Obtiene la valoracion maxima del video (5).
	 */
	public function getMaxRate()
	{
		return $this->maxRate;
	}
	
	/**
	 * getRatingCount
	 * Obtiene el numero de usuarios que ha valorado el video.
	 */
	public function getRatersCount()
	{
		return $this->ratersCount;
	}
	
	/**
	 * getViewCount
	 * Obtiene el numero de vistas del video.
	 */
	public function getViewCount()
	{
		return $this->viewCount;
	}
	
	/**
	 * getFavouriteCount
	 * Obtiene el numero de usuarios que ha marcado el video como favorito.
	 */
	public function getFavouriteCount()
	{
		return $this->favouriteCount;
	}
	
	/**
	 * getKeywords
	 * Obtiene las etiquetas de los vídeos
	 */
	public function getKeywords() {
		return $this->keywords;
	}
	
	
	public function hasSubtitles() {
		return $this->hasCC;
	}
	
	/**
	 * getUrl
	 * Obtiene la Url del video.
	 */
	public function getUrl($params=array())
	{
		$url = "https://www.youtube.com/watch?v=" . $this->id;
		foreach ($params as $key=>$value) {
			$url .= "&amp;" . $key . "=" . $value;
		}
		
		return $url;
	}
	
	/**
	 * getThumbnailUrl
	 * Obtiene la Url de la imagen de muestra del video
	 * @param $option puede ser 0, 1, 2 o 3. Tambi�n se admite "big". Cualquier otra cadena obtiene la imagen por defecto. 
	 */
    public function getThumbnailUrl($option="default")
    {
    	switch ($option)
    	{
    		case "small":
    			$option = "default";
    		case "big":
    			$option = "hqdefault";
    			break;
    		case "mqdefault":
    			break;
    		case "hqdefault":
    			break;
    		case 0:
    			break;
    		case 1:
    			break;
    		case 2:
    			break;
    		case 3:
    			break;
       		default:
          		$option = "default";
        		break;
      	}
		return sprintf("http://i.ytimg.com/vi/%s/%s.jpg", $this->id, $option);
    }
	
    public function getEmbedUrl($params=array()) {
    	$url = "http://www.youtube.com/embed/" . $this->getId();
    	$first = true;
    	foreach ($params as $key=>$value) {
    		if ($first) {
    			$url .= "?";
    			$first = false;
    		} else {
    			$url .= "&amp;";
    		}
    		$url .= $key . "=" . $value;
    	}
    	
    	return $url;
    }
    
	/**
	 * getHtmlEmbedIframe
	 * Obtiene el fragmento HTML para embeber el video
	 * @param $width
	 * @param $height
	 * @param $autoplay
	 */
	public function getHtmlEmbedIframe($width=640, $height=480, $params=array()) {
		return '<iframe class="youtube-player" type="text/html" width="'.$width.'" height="'.$height.'" src="'.$this->getEmbedUrl($params).'" frameborder="0">';
	}
	
	
	public function getDownloadLinks() {
		$response = $this->request($this->getUrl());
		$response = str_replace("\u0026amp;", "&", utf8_encode($response));
		
		preg_match_all('/url_encoded_fmt_stream_map\=(.*)/', $response, $matches);
		$formatUrl = urldecode($matches[1][0]);
		
		$urls = preg_split('/url=/', $formatUrl);
		
		$videoUrls = array();
		
		foreach ($urls as $url) {
			$url = urldecode($url);
			$urlparts = explode(";", $url);
			$url = $urlparts[0];
			$urlparts = explode(",", $url);
			$url = $urlparts[0];
		
			parse_str($url, $data);
		
			if (isset($data['watermark']) || empty($url)) {
				continue;
			} else {
				if (!empty($data['type']) && !empty($data['quality'])) {
					//TODO: File size
					$videoUrls[] = array("type" => $data['type'], "quality" => $data['quality'], "url" =>  $url);
				}
			}
		}
		
		return $videoUrls;
	}
	
	public function isDeleted() {
		return $this->deleted;
	}
	
	public function getError() {
		return $this->error;
	}
	
	static function Url2Id($url)
	{
		$url_string = parse_url($url, PHP_URL_QUERY);
		parse_str($url_string, $args);
		return isset($args['v']) ? $args['v'] : false;
	}
}
?>
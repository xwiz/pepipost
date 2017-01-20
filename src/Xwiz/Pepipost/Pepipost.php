<?php

namespace Xwiz\Pepipost;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Factory;
use PepipostAPIV10Lib\Controllers\Email;
use Closure;

class Pepipost
{

	/**
	 * The view environment instance.
	 *
	 * @var \Illuminate\View\Factory
	 */
	protected $views;

	/**
	 * Peipost message Object
	 *
	 * @var \Xwiz\Pepipost\Message
	 */
	protected $message;

	/**
	 * Api key from Pepipost
	 */
	private $apiKey;
	private $from;
	private $replyTo;
	private $footer;
	private $clickTrack;
	private $openTrack;
	private $unsubscribe;

	/**
	 * Create a new Pepipost Mailer instance.
	 *
	 * @param  \Illuminate\View\Factory $views
	 *
	 * @return \Xwiz\Pepipost\Pepipost
	 */
	function __construct(Factory $views)
	{
		//Load config
		$this->apiKey = Config::get('pepipost::api_key');
		$this->from = Config::get('pepipost::from', Config::get('mail.from'));
		$this->replyTo = $this->from['address'];
		$this->clickTrack = Config::get('pepipost::click_track', false);
		$this->openTrack = Config::get('pepipost::open_track', true);
		$this->unsubscribe = Config::get('pepipost::unsubscribe', false);
		$this->footer = Config::get('pepipost::footer', false);

		$this->views = $views;		
		$this->message = new Message();
	}

	/**
	 * Send a new message
	 *
	 * @param  string|array   $view
	 * @param  array          $data
	 * @param  Closure|string $callback
	 *
	 * @return response object containing error_code and error_message
	 */
	public function send($view, array $data, $callback)
	{
		$this->buildMessage($callback, $this->message);

		//todo: How best do we embed initial templates?
		$this->getMessage($view, $data);

		$email = new Email();

		$data = array(
			'api_key'   =>  $this->apiKey,
			'recipients'    =>  $message->get('recipients'),
			'email_details' => array(
				'from'          =>  $message->get('fromEmail', $this->from['address']),
				'fromname'      =>  $message->get('fromName', $this->from['name']),
				'subject'       =>  $message->get('subject'),
				'content'       =>  $message->get('html', $message->get('text', '')),
				'replytoid'     =>  $message->get('replyTo', $this->replyTo),
				'bcc'           =>  $message->get('bcc'),
			),
			'tags'          =>  'Transactional',
			'settings' => array(
				'footer'        =>  $this->footer,
				'clicktrack'    =>  $this->clickTrack,
				'opentrack'     =>  $this->openTrack,
				'unsubscribe'   =>  $this->unsubscribe,
				'bcc'           =>  $message->get('bcc'),
			),
			'files' => $message->get('attachments'),
		);

		try
		{
			$response = $email->sendJson( $data );
			if(empty($response->errorcode))
			{
				return true;
			}
			else
			{
				Log::debug("Pepipost Error {$response->errorcode}. Message: {$response->errormessage}");
				return false;
			}
		}
		catch(Exception $e)
		{
			Log::debug($e);
			return false;
		}
	}


	/**
	 * Call the provided message builder.
	 *
	 * @param  Closure $callback
	 * @param  \Xwiz\Pepipost\Message $message
	 * @return mixed
	 */
	protected function callMessageBuilder(Closure $callback, $message)
	{
		return call_user_func($callback, $message);
	}

	/**
	 * Get HTML and/or Text message
     *
	 * @param  string $view
	 * @param  array $data
	 */
	protected function getMessage($view, $data)
	{
		if (is_string($view))
		{
			$this->getHtmlMessage($view, $data);
		}

		if (is_array($view) and isset($view[0]))
		{
			$this->getHtmlMessage($view[0], $data);
			if (isset($view[1]))
			{
				$this->getTextMessage($view[1], $data);
			}
		}
		elseif (is_array($view))
		{
			if (isset($view['html']))
			{
				$this->getHtmlMessage($view['html'], $data);
			}
			if (isset($view['text']))
			{
				$this->getTextMessage($view['text'], $data);
			}
		}
	}

	/**
	 * Get rendered HTML body
	 * @param  string $view
	 * @param  array $data
	 */
	protected function getHtmlMessage($view, $data)
	{
		$renderedView = $this->getView($view, $data);
		$this->message->html($renderedView);
	}

	/**
	 * Get rendered text body
     *
	 * @param  string $view
	 * @param  array $data
	 */
	protected function getTextMessage($view, $data)
	{
		$renderedView = $this->getView($view, $data);
		$this->message->text($renderedView);
	}

	/**
	 * Render the given view.
	 *
	 * @param  string $view
	 * @param  array $data
	 * @return \Illuminate\View\View
	 */
	protected function getView($view, $data)
	{		
		//todo: search views folder if not exist?
		return $this->views->make($view, $data)->render();
	}
}
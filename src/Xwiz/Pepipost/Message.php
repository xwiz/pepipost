<?php namespace Xwiz\Pepipost;

use Illuminate\Support\Facades\Config;

class Message
{

	public $variables = array();
	public $attachments = array();
	public $recipients = array();

	/**
	 * Add a "from" address to the message.
	 *
	 * @param  string $email
	 * @param  string $name
	 * @return \Xwiz\Pepipost\Message
	 */
	public function from($email, $name = false)
	{
		if ($name !== false)
		{
			$this->fromName = $name;
		}
		$this->fromEmail = $email;
		return $this;
	}

	/**
	 * Add a recipient to the message.
	 *
	 * @param  string|array $email
	 * @param  string $name
	 * @return \Xwiz\Pepipost\Message
	 */
	public function to($email, $name = false)
	{
		if (is_array($email))
		{
			foreach ($email as $address => $name)
			{
				$this->addRecipient($address, $name);
			}
		}
		else
		{
			$this->addRecipient($email, $name);
		}
		return $this;
	}
	
	/**
	 * Add a blind carbon copy to the message.
	 *
	 * @param  string|array $email
	 * @param  string $name
	 * @return \Xwiz\Pepipost\Message
	 */
	public function bcc($email, $name = false)
	{
		if (is_array($email))
		{
			$this->bcc = implode(',', $email);
		}
		else
		{
			$this->bcc = "'$name' <$email>";
		}
		return $this;
	}
	
	public function addRecipient($email, $name)
	{
		//if first variable is array index, then $name should be actual email
		if(is_int($email))
		{
			$this->recipients[] = $name;			
		}
		$this->recipients[] = "'$name' <$email>";
	}

	/**
	 * Add a reply-to address to the message.
	 *
	 * @param  string $email
	 * @param  string $name
	 * @return \Xwiz\Pepipost\Message
	 */
	public function replyTo($email, $name = false)
	{
		$this->replyTo = $name === false ? $email : "'$name' <email>";
		return $this;
	}

	/**
	 * Set the HTML body for the message.
	 *
	 * @param  string $html
	 * @return \Xwiz\Pepipost\Message
	 */
	public function html($html)
	{
		$this->html = $html;
		return $this;
	}

	/**
	 * Set the text for the message.
	 *
	 * @param  string $text
	 * @return \Xwiz\Pepipost\Message
	 */
	public function text($text)
	{
		$this->text = $text;
		return $this;
	}

	/**
	 * Set the subject of the message.
	 *
	 * @param  string $subject
	 * @return \Xwiz\Pepipost\Message
	 */
	public function subject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Add tags to the message.
	 * 
	 * @param  string|array $tags
	 * @return \Xwiz\Pepipost\Message
	 */
	public function tags($tags)
	{
		if(is_array($tags))
		{
			$this->tags = implode(',', $tags);
		}
		$this->tags = $tags;
		return $this;
	}

	/**
	 * Attach a file to the message.
	 *
	 * @param  string $path
	 * @param  string $name
	 * @return \Xwiz\Pepipost\Message
	 */
	public function attach($path, $name = null)
	{
		$this->attachments[] = [$name => base64_encode(trim(file_get_contents($path)))];
		return $this;
	}
	
	public function get($name, $default)
	{
		if(property_exists($this, $name))
		{
			return $this->{$name};
		}
		return $default;
	}
}
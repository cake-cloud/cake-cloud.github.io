<?php

/**
 * Implementation for ReCAPTCHA. Uses a global key by default.
 * Note that this was originally for ReCAPTCHA v1 but this is being retired, so this will now
 * silently change to ReCAPTCHA v2 (NoCAPTCHA).
 *
 * @package XenForo_Captcha
 */
class XenForo_Captcha_ReCaptcha extends XenForo_Captcha_NoCaptcha
{
	/**
	 * Constructor.
	 *
	 * @param array|null $config
	 */
	public function __construct(array $config = null)
	{
		// we can't pass the config through to the NoCaptcha class as the keys are different
		parent::__construct();
	}
}
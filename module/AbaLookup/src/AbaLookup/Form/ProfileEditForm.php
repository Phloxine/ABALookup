<?php

namespace AbaLookup\Form;

use
	AbaLookup\Entity\User,
	Zend\Filter\Digits,
	Zend\Filter\StringTrim,
	Zend\Form\Exception\DomainException,
	Zend\Form\Form,
	Zend\I18n\Filter\Alnum,
	Zend\Validator\EmailAddress as EmailAddressValidator,
	Zend\Validator\NotEmpty,
	Zend\Validator\StringLength as StringLengthValidator
;

/**
 * The form for editing a user's profile
 */
class ProfileEditForm extends Form
{
	/**
	 * Constants for form element IDs and names
	 */
	const ELEMENT_NAME_DISPLAY_NAME  = 'display-name';
	const ELEMENT_NAME_EMAIL_ADDRESS = 'email-address';
	const ELEMENT_NAME_PHONE_NUMBER  = 'phone-number';

	/**
	 * Error message
	 */
	protected $message;

	public function __construct(User $user)
	{
		parent::__construct();
		// Display name
		$this->add([
			'name' => self::ELEMENT_NAME_DISPLAY_NAME,
			'type' => 'text',
			'attributes' => [
				'id'    => self::ELEMENT_NAME_DISPLAY_NAME,
				'value' => $user->getDisplayName(),
			],
			'options' => [
				'label' => 'Your display name'
			],
		]);
		// Email address
		$this->add([
			'name' => self::ELEMENT_NAME_EMAIL_ADDRESS,
			'type' => 'email',
			'attributes' => [
				'id'    => self::ELEMENT_NAME_EMAIL_ADDRESS,
				'value' => $user->getEmail(),
			],
			'options' => [
				'label' => 'Your email address'
			],
		]);
		// Phone number
		$this->add([
			'name' => self::ELEMENT_NAME_PHONE_NUMBER,
			'type' => 'text',
			'attributes' => [
				'id'    => self::ELEMENT_NAME_PHONE_NUMBER,
				'type'  => 'tel',
				'value' => $user->getPhone(),
			],
			'options' => [
				'label' => 'Your phone number (optional)'
			],
		]);
		// Submit btn
		$this->add([
			'type' => 'submit',
			'name' => 'update',
			'attributes' => [
				'value' => 'Update your information'
			],
		]);
	}

	/**
	 * Returns whether the display name is valid
	 *
	 * Also sets the error message appropriately.
	 *
	 * @return bool
	 */
	protected function isDisplayNameValid()
	{
		$displayName = (new Alnum(/* Allow whitespace */ TRUE))
		               ->filter($this->data[self::ELEMENT_NAME_DISPLAY_NAME]);
		// Is valid?
		$isValid =    isset($displayName)
		           && (new StringLengthValidator(['min' => User::MINIMUM_LENGTH_DISPLAY_NAME]))
		              ->isValid($displayName)
		           && (new NotEmpty())->isValid($displayName)
		;
		// Set the message
		if (!$isValid) {
			$this->message = 'The entered display name is invalid.';
		}
		return $isValid;
	}

	/**
	 * Returns whether the email address is valid
	 *
	 * Also sets the error message appropriately.
	 *
	 * @return bool
	 */
	protected function isEmailAddressValid()
	{
		// Is valid?
		$isValid = (new EmailAddressValidator())
		           ->isValid($this->data[self::ELEMENT_NAME_EMAIL_ADDRESS]);
		// Set the message
		if (!$isValid) {
			$this->message = 'The entered email address is not valid.';
		}
		return $isValid;
	}

	/**
	 * Returns whether the phone number is valid
	 *
	 * Also sets the error message appropriately.
	 *
	 * @return bool
	 */
	protected function isPhoneNumberValid()
	{
		// Filter out all but digits
		$phone = (new Digits())->filter($this->data[self::ELEMENT_NAME_PHONE_NUMBER]);
		$this->data[self::ELEMENT_NAME_PHONE_NUMBER] = $phone;
		// Is valid?
		if ((new NotEmpty())->isValid($phone)) {
			$isValid = (new StringLengthValidator(['min' => User::MINIMUM_LENGTH_PHONE_NUMBER]))
			           ->isValid($phone);
			// Set the message
			if (!$isValid) {
				$this->message = 'The entered phone number is not valid.';
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * Validates the form
	 *
	 * Overrides Zend\Form\Form::isValid.
	 *
	 * @return bool
	 * @throws DomainException
	 */
	public function isValid()
	{
		if ($this->hasValidated) {
			// Validation has already occurred
			return $this->isValid;
		}
		// Default to invalid
		$this->isValid = FALSE;
		if (!is_array($this->data)) {
			$data = $this->extract();
			if (!is_array($data) || !isset($this->data)) {
				// No data has been set
				throw new DomainException(sprintf(
					'%s is unable to validate as there is no data currently set', __METHOD__
				));
			}
			$this->data = $data;
		}
		// Trim all the data
		$strtrim = new StringTrim();
		foreach ($this->data as $k => $v) {
			$this->data[$k] = $strtrim->filter($v);
		}
		// Validate the form
		if (
			   $this->isDisplayNameValid()
			&& $this->isEmailAddressValid()
			&& $this->isPhoneNumberValid()
		) {
			// The form is valid
			$this->isValid = TRUE;
		}
		$this->hasValidated = TRUE;
		return $this->isValid;
	}

	/**
	 * Returns the error message generated by the form
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return isset($this->message) ? $this->message : '';
	}

	/**
	 * Updates the user with their new information
	 *
	 * Takes the user to update and populates the fields with the updated data.
	 *
	 * @param User $user The user to update.
	 * @return bool Whether the update was successful.
	 */
	public function updateUser(User $user)
	{
		if (!$this->hasValidated || !$this->isValid) {
			return FALSE;
		}
		// Aliases
		$displayName = $this->data[self::ELEMENT_NAME_DISPLAY_NAME];
		$email       = $this->data[self::ELEMENT_NAME_EMAIL_ADDRESS];
		$phone       = $this->data[self::ELEMENT_NAME_PHONE_NUMBER];
		// Update the information
		$user->setDisplayName($displayName);
		$user->setEmail($email);
		if ($phone) {
			// The user entered a phone number
			$user->setPhone((int) $phone);
		}
		return TRUE;
	}
}

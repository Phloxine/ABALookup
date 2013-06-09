<?php

namespace AbaLookupTest\Entity;

use
	AbaLookup\Entity\User,
	PHPUnit_Framework_TestCase
;

/**
 * Test methods for the User entity
 */
class UserTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var AbaLookup\Entity\User
	 */
	protected $user;

	/**
	 * Reset the user
	 */
	public function setUp()
	{
		$displayName = "Jane";
		$email = "jane@email.com";
		$password = "password";
		$therapist = TRUE;
		$sex = "F";
		$abaCourse = TRUE;
		$codeOfConduct = TRUE;
		$this->user = new User($displayName, $email, $password, $therapist, $sex, $abaCourse, $codeOfConduct);
	}

	public function testGetDisplayName()
	{
		$this->assertEquals("Jane", $this->user->getDisplayName());
	}

	/**
	 * @depends testGetDisplayName
	 */
	public function testSetDisplyName()
	{
		$name = "Mary";
		$this->user->setDisplayName($name);
		$this->assertEquals($name, $this->user->getDisplayName());
	}

	public function testGetEmail()
	{
		$this->assertEquals("jane@email.com", $this->user->getEmail());
	}

	/**
	 * @depends testGetEmail
	 */
	public function testSetEmail()
	{
		$email = "somebody@email.com";
		$this->user->setEmail($email);
		$this->assertEquals($email, $this->user->getEmail());
	}

	public function testVerifyPassword()
	{
		$this->assertTrue($this->user->verifyPassword("password"));
	}

	public function testGetTherapist()
	{
		$this->assertTrue($this->user->getTherapist());
	}

	/**
	 * @depends testGetTherapist
	 */
	public function testSetTherapist()
	{
		$this->user->setTherapist(FALSE);
		$this->assertFalse($this->user->getTherapist());
	}

	public function testGetSex()
	{
		$this->assertEquals("F", $this->user->getSex());
	}

	/**
	 * @depends testGetSex
	 */
	public function testSetSex()
	{
		$this->user->setSex(NULL);
		$this->assertNull($this->user->getSex());
	}

	public function testGetAbaCourse()
	{
		$this->assertTrue($this->user->getAbaCourse());
	}

	/**
	 * @depends testGetAbaCourse
	 */
	public function testSetAbaCourse()
	{
		$this->user->setAbaCourse(FALSE);
		$this->assertFalse($this->user->getAbaCourse());
	}

	public function testGetCodeOfConduct()
	{
		$this->assertTrue($this->user->getCodeOfConduct());
	}

	/**
	 * @depends testGetCodeOfConduct
	 */
	public function testSetCodeOfConduct()
	{
		$this->user->setCodeOfConduct(FALSE);
		$this->assertFalse($this->user->getCodeOfConduct());
	}

	public function testGetVerified()
	{
		$this->assertFalse($this->user->getVErified());
	}

	/**
	 * @depends testGetVerified
	 */
	public function testSetVerified()
	{
		$this->user->setVerified(TRUE);
		$this->assertTrue($this->user->getVerified());
	}

	public function testGetModerator()
	{
		$this->assertFalse($this->user->getModerator());
	}

	/**
	 * @depends testGetModerator
	 */
	public function testSetModerator()
	{
		$this->user->setModerator(TRUE);
		$this->assertTrue($this->user->getModerator());
	}
}
